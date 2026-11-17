<?php

declare(strict_types=1);

namespace App\Audit;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Records every insert, update and delete of application entities into the audit trail.
 *
 * Changes are collected in onFlush (where old values are still known) and appended in
 * postFlush, i.e. only for successful flushes and – for HTTP write requests – inside the same
 * database transaction as the business data.
 */
#[AsDoctrineListener(event: Events::onFlush)]
#[AsDoctrineListener(event: Events::postFlush)]
final class DoctrineAuditListener implements ResetInterface
{
    private const array ALWAYS_IGNORED = ['updatedAt'];
    private const string REDACTED = '[redacted]';

    /** @var list<AuditEvent> */
    private array $pending = [];

    /** @var array<class-string, Auditable> */
    private array $configCache = [];

    public function __construct(private readonly AuditTrail $auditTrail)
    {
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $this->collect($entity, AuditAction::ENTITY_CREATED, $em->getClassMetadata($entity::class), $uow);
        }
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $this->collect($entity, AuditAction::ENTITY_UPDATED, $em->getClassMetadata($entity::class), $uow);
        }
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $this->collect($entity, AuditAction::ENTITY_DELETED, $em->getClassMetadata($entity::class), $uow);
        }
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        if ($this->pending === []) {
            return;
        }

        $events = $this->pending;
        $this->pending = [];
        $this->auditTrail->append($events);
    }

    public function reset(): void
    {
        $this->pending = [];
    }

    /**
     * @param ClassMetadata<object> $metadata
     */
    private function collect(object $entity, string $action, ClassMetadata $metadata, UnitOfWork $uow): void
    {
        $config = $this->config($metadata);
        if (!$config->enabled) {
            return;
        }

        $ignored = [...self::ALWAYS_IGNORED, ...$config->ignore];
        $changes = [];

        if ($action === AuditAction::ENTITY_UPDATED) {
            foreach ($uow->getEntityChangeSet($entity) as $field => [$old, $new]) {
                if (\in_array($field, $ignored, true)) {
                    continue;
                }
                $changes[$field] = \in_array($field, $config->redact, true)
                    ? ['old' => self::REDACTED, 'new' => self::REDACTED]
                    : ['old' => $this->normalize($old, $uow), 'new' => $this->normalize($new, $uow)];
            }
            if ($changes === []) {
                return;
            }
        } else {
            $key = $action === AuditAction::ENTITY_CREATED ? 'new' : 'old';
            foreach ([...$metadata->getFieldNames(), ...$this->singleValuedAssociations($metadata)] as $field) {
                if (\in_array($field, $ignored, true) || $field === 'id') {
                    continue;
                }
                $value = $metadata->getFieldValue($entity, $field);
                $changes[$field] = [$key => \in_array($field, $config->redact, true) ? self::REDACTED : $this->normalize($value, $uow)];
            }
        }

        $identifier = $metadata->getIdentifierValues($entity);
        $subjectId = $identifier === [] ? null : (string) reset($identifier);

        $this->pending[] = $this->auditTrail->createEvent($action, $metadata->getReflectionClass()->getShortName(), $subjectId, $changes);
    }

    /**
     * @param ClassMetadata<object> $metadata
     *
     * @return list<string>
     */
    private function singleValuedAssociations(ClassMetadata $metadata): array
    {
        return array_values(array_filter(
            $metadata->getAssociationNames(),
            static fn (string $name): bool => $metadata->isSingleValuedAssociation($name),
        ));
    }

    /**
     * @param ClassMetadata<object> $metadata
     */
    private function config(ClassMetadata $metadata): Auditable
    {
        return $this->configCache[$metadata->getName()] ??= (static function () use ($metadata): Auditable {
            $attributes = $metadata->getReflectionClass()->getAttributes(Auditable::class);

            return $attributes === [] ? new Auditable() : $attributes[0]->newInstance();
        })();
    }

    private function normalize(mixed $value, UnitOfWork $uow): mixed
    {
        return match (true) {
            $value === null, \is_scalar($value) => $value,
            $value instanceof \DateTimeInterface => $value->format(\DATE_ATOM),
            $value instanceof \BackedEnum => $value->value,
            $value instanceof \UnitEnum => $value->name,
            \is_array($value) => array_map(fn (mixed $item): mixed => $this->normalize($item, $uow), $value),
            \is_object($value) && property_exists($value, 'id') => $this->entityReference($value),
            $value instanceof \Stringable => (string) $value,
            default => get_debug_type($value),
        };
    }

    private function entityReference(object $entity): ?string
    {
        /** @var mixed $id */
        $id = property_exists($entity, 'id') ? $entity->id : null;

        return \is_scalar($id) ? (string) $id : null;
    }
}

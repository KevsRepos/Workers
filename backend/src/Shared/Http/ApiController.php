<?php

declare(strict_types=1);

namespace App\Shared\Http;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * Base controller for JSON API endpoints.
 */
abstract class ApiController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    #[Required]
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Flushes pending changes (inside the request transaction) so that generated ids and
     * timestamps are available, then serialises the result.
     */
    protected function persisted(callable $build, int $status = 200): JsonResponse
    {
        $this->entityManager->flush();

        return $this->json($build(), $status);
    }
}

<?php

declare(strict_types=1);

namespace App\Audit\Http;

use App\Audit\AuditLogReader;
use App\Modules\Account\Role;
use App\Shared\Http\PageRequest;
use App\Shared\Http\PageResponse;
use App\Shared\Http\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/audit-log', name: 'admin_audit_log_')]
#[IsGranted(Role::ADMIN)]
final class AuditLogController extends ApiController
{
    public function __construct(private readonly AuditLogReader $reader)
    {
    }

    #[Route('', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $page = PageRequest::fromRequest($request, defaultLimit: 50, maxLimit: 200);
        $query = $request->query;

        $result = $this->reader->search([
            'action' => $query->getString('action') ?: null,
            'category' => $query->getString('category') ?: null,
            'actorId' => $query->getString('actorId') ?: null,
            'subjectType' => $query->getString('subjectType') ?: null,
            'subjectId' => $query->getString('subjectId') ?: null,
            'from' => self::date($query->getString('from')),
            'to' => self::date($query->getString('to')),
        ], $page->page, $page->limit);

        return $this->json(new PageResponse($result['items'], $page->page, $page->limit, $result['total']));
    }

    #[Route('/verification', name: 'verify', methods: ['GET'])]
    public function verify(): JsonResponse
    {
        return $this->json($this->reader->verify());
    }

    private static function date(string $value): ?\DateTimeImmutable
    {
        if ($value === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }
    }
}

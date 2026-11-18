<?php

declare(strict_types=1);

namespace App\Modules\TimeTracking\Http;

use App\Modules\Account\Role;
use App\Modules\TimeTracking\Reminder\ReminderData;
use App\Modules\TimeTracking\Reminder\ReminderRepository;
use App\Modules\TimeTracking\Reminder\ReminderSetting;
use App\Shared\Http\ApiController;
use App\Shared\Security\CurrentUser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/time-tracking/reminder', name: 'time_tracking_reminder_')]
#[IsGranted(Role::USER)]
final class ReminderController extends ApiController
{
    public function __construct(
        private readonly ReminderRepository $reminders,
        private readonly CurrentUser $currentUser,
    ) {
    }

    #[Route('', name: 'get', methods: ['GET'])]
    public function get(): JsonResponse
    {
        $setting = $this->reminders->findForAccount($this->currentUser->account()) ?? new ReminderSetting($this->currentUser->account());

        return $this->json(self::present($setting));
    }

    #[Route('', name: 'update', methods: ['PUT'])]
    public function update(#[MapRequestPayload] ReminderData $payload): JsonResponse
    {
        $account = $this->currentUser->account();
        $setting = $this->reminders->findForAccount($account);
        if ($setting === null) {
            $setting = new ReminderSetting($account);
            $this->reminders->add($setting);
        }

        $setting->enabled = $payload->enabled;
        $setting->time = new \DateTimeImmutable($payload->time);
        $weekdays = array_values(array_unique(array_map('intval', $payload->weekdays)));
        sort($weekdays);
        $setting->weekdays = $weekdays;

        return $this->persisted(static fn () => self::present($setting));
    }

    /**
     * @return array{enabled: bool, time: string, weekdays: list<int>}
     */
    private static function present(ReminderSetting $setting): array
    {
        return ['enabled' => $setting->enabled, 'time' => $setting->time->format('H:i'), 'weekdays' => $setting->weekdays];
    }
}

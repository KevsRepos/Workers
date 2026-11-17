<?php

declare(strict_types=1);

namespace App\Modules\TimeTracking\Reminder;

use App\Modules\TimeTracking\Repository as TimeSheetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
final readonly class SendDueRemindersHandler
{
    public function __construct(
        private ReminderRepository $reminders,
        private TimeSheetRepository $sheets,
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager,
        private ClockInterface $clock,
        private LoggerInterface $logger,
        #[Autowire('%env(MAILER_FROM)%')]
        private string $from,
    ) {
    }

    public function __invoke(SendDueRemindersMessage $message): void
    {
        $now = $this->clock->now();
        $today = $now->setTime(0, 0);
        $weekday = (int) $now->format('N');
        $minutesNow = (int) $now->format('G') * 60 + (int) $now->format('i');

        foreach ($this->reminders->findEnabled() as $reminder) {
            $due = (int) $reminder->time->format('G') * 60 + (int) $reminder->time->format('i');
            if (!\in_array($weekday, $reminder->weekdays, true) || $minutesNow < $due || $reminder->lastSentOn == $today) {
                continue;
            }

            $sheet = $this->sheets->findForMonth($reminder->account, (int) $now->format('Y'), (int) $now->format('n'));
            if ($sheet?->entryForDay((int) $now->format('j')) === null) {
                try {
                    $this->mailer->send((new Email())
                        ->from($this->from)
                        ->to($reminder->account->emailAddress)
                        ->subject('Erinnerung: Arbeitszeit erfassen')
                        ->text(\sprintf("Hallo %s,\n\nfür heute (%s) ist noch keine Arbeitszeit erfasst.\nBitte trage deine Zeiten in Workers ein.\n", $reminder->account->firstName, $now->format('d.m.Y'))));
                } catch (\Throwable $e) {
                    $this->logger->error('Sending time tracking reminder failed', ['exception' => $e, 'account_id' => $reminder->account->id]);
                    continue;
                }
            }

            $reminder->lastSentOn = $today;
        }

        $this->entityManager->flush();
    }
}

<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\EmailDto\EmailDto;
use App\Entity\StatisticsDto\LastLoginDto;

final readonly class StrategicEmailService
{
    public function __construct(
        private StatisticsService $statisticsService,
        private EmailTemplateService $emailTemplateService,
        private EmailService $emailService,
    ) {
    }

    /**
     * Send login reminder emails to users who have not logged in for a long time.
     *
     * @template T of bool
     *
     * @param T $dryRun whether to send the email or not
     *
     * @return ($dryRun is true ? EmailDto : null)
     *
     * @throws \Throwable if the email content cannot be rendered
     */
    public function sendLoginReminderEmail(bool $dryRun = false): ?EmailDto
    {
        $bccUsers = array_map(
            fn (LastLoginDto $lastLoginDto) => $lastLoginDto->getUser(),
            $this->statisticsService->lastLoginAt()
        );

        $emailDto = $this->emailTemplateService->createLoginReminderDto($bccUsers);
        if ($dryRun) {
            return $emailDto;
        }

        $this->emailService->send($emailDto);

        return null;
    }
}

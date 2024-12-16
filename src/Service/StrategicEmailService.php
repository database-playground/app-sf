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
        $lastLoginAt = array_filter(
            // Filter out users who have logged in within the last 7 days
            $this->statisticsService->lastLoginAt(),
            function (LastLoginDto $lastLoginDto): bool {
                $lastLoginAt = $lastLoginDto->getLastLoginAt();
                if (null === $lastLoginAt) {
                    return true;
                }

                return $lastLoginAt->diff(new \DateTimeImmutable())->days >= 5;
            }
        );

        $bccUsers = array_map(
            fn (LastLoginDto $lastLoginDto) => $lastLoginDto->getUser(),
            $lastLoginAt,
        );

        $emailDto = $this->emailTemplateService->createLoginReminderDto($bccUsers);
        if ($dryRun) {
            return $emailDto;
        }

        $this->emailService->send($emailDto);

        return null;
    }
}

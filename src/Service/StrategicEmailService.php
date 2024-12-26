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
    ) {}

    /**
     * Send login reminder emails to users who have not logged in for a long time.
     *
     * @param callable(EmailDto): void $target the function that sends the email
     *                                         according to the given EmailDto
     *
     * @throws \Throwable if the email content cannot be rendered
     */
    public function sendLoginReminderEmail(callable $target): void
    {
        $lastLoginAt = array_filter(
            // Filter out users who have logged in within the last 7 days
            $this->statisticsService->lastLoginAt(),
            static function (LastLoginDto $lastLoginDto): bool {
                $lastLoginAt = $lastLoginDto->getLastLoginAt();
                if (null === $lastLoginAt) {
                    return true;
                }

                return $lastLoginAt->diff(new \DateTimeImmutable())->days >= 5;
            }
        );

        $bccUsers = array_map(
            static fn (LastLoginDto $lastLoginDto) => $lastLoginDto->getUser(),
            $lastLoginAt,
        );

        $emailDto = $this->emailTemplateService->createLoginReminderDto($bccUsers);

        $target($emailDto);
    }
}

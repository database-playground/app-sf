<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\StatisticsDto\LastLoginDto;
use App\Entity\User;
use App\Repository\UserRepository;

final readonly class StatisticsService
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    /**
     * Get the last login time of all users in descending order.
     *
     * @return LastLoginDto[]
     *
     * @throws \DateMalformedStringException
     */
    public function lastLoginAt(): array
    {
        /**
         * @var list<array{u: User, last_login_at: string|null}> $results
         */
        $results = $this->userRepository->createQueryBuilder('user')
            ->leftJoin('user.loginEvents', 'loginEvent')
            ->select(
                'user AS u',
                'MAX(loginEvent.createdAt) AS last_login_at',
            )
            ->groupBy('user.id')
            ->orderBy('last_login_at', 'DESC')
            ->getQuery()
            ->getResult();

        /**
         * @var list<LastLoginDto> $resultsWithRecency
         */
        $resultsWithRecency = [];

        /**
         * @var list<LastLoginDto> $resultsThatNeverLogin
         */
        $resultsThatNeverLogin = [];

        foreach ($results as $result) {
            $lastLoginAt = ($lastLoginAt = $result['last_login_at']) !== null
                ? new \DateTimeImmutable($lastLoginAt)
                : null;
            $lastLoginDto = (new LastLoginDto())
                ->setUser($result['u'])
                ->setLastLoginAt($lastLoginAt);

            if (null !== $lastLoginAt) {
                $resultsWithRecency[] = $lastLoginDto;
            } else {
                $resultsThatNeverLogin[] = $lastLoginDto;
            }
        }

        return $resultsWithRecency + $resultsThatNeverLogin;
    }
}

<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:stat:last-login', description: 'List the last login date of users in database.')]
class LastLoginStatCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'moreThan',
            'm',
            InputOption::VALUE_OPTIONAL,
            'Only show users who have not logged in for more than this number of days',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $moreThan = ($moreThan_ = $input->getOption('moreThan')) !== null
            ? (int) $moreThan_
            : null;

        /**
         * @var list<array{email: string, last_login_at: string|null}> $results
         */
        $results = $this->userRepository->createQueryBuilder('user')
            ->leftJoin('user.loginEvents', 'loginEvent')
            ->select('user.email', 'MAX(loginEvent.createdAt) as last_login_at')
            ->groupBy('user.email')
            ->orderBy('last_login_at', 'DESC')
            ->getQuery()
            ->getResult();

        $table = new Table($output);
        $table->setHeaderTitle('Last login date of users');
        $table->setHeaders(['Email', 'Last login', 'Recency']);
        foreach ($results as $result) {
            $lastLoginAt = ($lastLoginAt = $result['last_login_at']) !== null
                ? new \DateTime($lastLoginAt)
                : null;

            if (null !== $lastLoginAt) {
                $lastLoginAtString = $lastLoginAt->format('Y-m-d H:i:s');
                $recency = $lastLoginAt->diff(new \DateTime());

                if (null !== $moreThan && $recency->days < $moreThan) {
                    continue;
                }

                $recencyString = $recency->format('%a days %h hours');

                $table->addRow([$result['email'], $lastLoginAtString, $recencyString]);
            } else {
                $table->addRow([$result['email'], 'Never logged in', 'N/A']);
            }
        }

        $table->render();

        return Command::SUCCESS;
    }
}

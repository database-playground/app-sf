<?php

declare(strict_types=1);

namespace App\Command\Email;

use App\Service\StrategicEmailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:email:send-login-reminder', description: 'Send login reminder emails to users who have not logged in for a long time.')]
class SendLoginReminderCommand extends Command
{
    public function __construct(
        private readonly StrategicEmailService $strategicEmailService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Whether to send the email or not');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');

        $result = $this->strategicEmailService->sendLoginReminderEmail($dryRun);

        if (null !== $result) {
            $table = $io->createTable();

            $table->setHeaderTitle('Email to be sent');
            $table->setHeaders(['Field', 'Value']);
            $table->addRow(['Subject', $result->getSubject()]);
            $table->addRow(['To', $result->getToAddress()->toString()]);

            $bcc = implode(', ', array_map(fn ($address) => $address->toString(), $result->getBcc()));
            $table->addRow(['Bcc', $bcc]);

            $table->addRow(['Kind', $result->getKind()->value]);
            $table->addRow(['Content', $result->getText()]);
            $table->render();
        } else {
            $io->success('Emails have been sent successfully.');
        }

        return Command::SUCCESS;
    }
}

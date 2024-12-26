<?php

declare(strict_types=1);

namespace App\Command\Email;

use App\Entity\EmailDto\EmailDto;
use App\Service\EmailService;
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
        private readonly EmailService $emailService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Whether to send the email or not');
        $this->addOption('test-email', 't', InputOption::VALUE_NONE, 'Send the test email to the test email address');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');
        $testEmail = $input->getOption('test-email');

        if (!$dryRun && $testEmail) {
            $io->error('The --test-email option can only be used with the --dry-run option.');

            return Command::FAILURE;
        }

        $target = ($dryRun && $testEmail)
            ? fn (EmailDto $emailDto) => $this->sendTestEmailDto($io, $emailDto)
            : ($dryRun
                ? fn (EmailDto $emailDto) => $this->printEmailDto($io, $emailDto)
                : fn (EmailDto $emailDto) => $this->sendEmailDto($io, $emailDto));

        $this->strategicEmailService->sendLoginReminderEmail($target);

        return Command::SUCCESS;
    }

    private function printEmailDto(SymfonyStyle $io, EmailDto $emailDto): void
    {
        $io->writeln('Email to be sent:');
        $io->writeln('Subject: '.$emailDto->getSubject());
        $io->writeln('To: '.$emailDto->getToAddress()->toString());
        $io->writeln('Bcc: '.implode(', ', array_map(static fn ($address) => $address->toString(), $emailDto->getBcc())));
        $io->writeln('Kind: '.$emailDto->getKind()->value);
        $io->writeln('Content: '.$emailDto->getText());
    }

    private function sendEmailDto(SymfonyStyle $io, EmailDto $emailDto): void
    {
        $this->emailService->send($emailDto);
        $io->success('Email sent successfully.');
    }

    private function sendTestEmailDto(SymfonyStyle $io, EmailDto $emailDto): void
    {
        $this->emailService->sendToTest($emailDto);
        $io->success('Test email sent successfully.');
    }
}

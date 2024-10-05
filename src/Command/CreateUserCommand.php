<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Creates a new user.',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of this account.');
        $this->addArgument('email', InputArgument::REQUIRED, 'The email of this account.');
        $this->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'The password of this account.');
        $this->addOption('roles', 'r', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The roles of this account. Can specify multiple times.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /**
         * @var string $name
         */
        $name = $input->getArgument('name');

        /**
         * @var string $email
         */
        $email = $input->getArgument('email');

        /**
         * @var string $password
         */
        $password = $input->getOption('password');

        /**
         * @var list<string> $roles
         */
        $roles = $input->getOption('roles');

        $user = (new User())->setName($name)->setEmail($email)->setRoles($roles);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success("Created a user $email with password: $password");

        return Command::SUCCESS;
    }
}

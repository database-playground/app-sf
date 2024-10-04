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

    public function getNameArg(InputInterface $input): string
    {
        $name = $input->getArgument('name');
        if (!\is_string($name)) {
            throw new \InvalidArgumentException('The name must be a string.');
        }
        if ('' === $name) {
            throw new \InvalidArgumentException('The name cannot be empty.');
        }

        return $name;
    }

    public function getEmailArg(InputInterface $input): string
    {
        $email = $input->getArgument('email');
        if (!\is_string($email)) {
            throw new \InvalidArgumentException('The email must be a string.');
        }
        if ('' === $email) {
            throw new \InvalidArgumentException('The email cannot be empty.');
        }

        return $email;
    }

    public function getPasswordArg(InputInterface $input): string
    {
        $password = $input->getOption('password');
        if (!\is_string($password)) {
            throw new \InvalidArgumentException('The password must be a string.');
        }
        if ('' === $password) {
            throw new \InvalidArgumentException('The password cannot be empty.');
        }

        return $password;
    }

    /**
     * @return list<string>
     */
    public function getRolesOpt(InputInterface $input): array
    {
        $roles = $input->getOption('roles');

        \assert(\is_array($roles) && array_is_list($roles), 'The roles must be an array.');

        /**
         * @var list<string> $rolesCasted
         */
        $rolesCasted = [];

        foreach ($roles as $role) {
            if (!\is_string($role)) {
                throw new \InvalidArgumentException('The roles must be strings.');
            }
            $rolesCasted[] = $role;
        }

        return $rolesCasted;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $name = $this->getNameArg($input);
        $email = $this->getEmailArg($input);
        $password = $this->getPasswordArg($input);
        $roles = $this->getRolesOpt($input);

        $user = (new User())->setName($name)->setEmail($email)->setRoles($roles);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success("Created a user {$email} with password: {$password}");

        return Command::SUCCESS;
    }
}

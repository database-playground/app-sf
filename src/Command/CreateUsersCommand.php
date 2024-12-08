<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Group;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-users',
    description: 'Creates new users from a CSV file. Passwords will be generated randomly.',
)]
class CreateUsersCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('filename', InputArgument::REQUIRED, 'The filename of this account.');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Whether to perform a dry run.');
    }

    /**
     * @throws RandomException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /**
         * @var string $filename
         */
        $filename = $input->getArgument('filename');
        /**
         * @var bool $dryRun
         */
        $dryRun = $input->getOption('dry-run');

        $file = fopen($filename, 'r');
        if (false === $file) {
            $io->error("Could not open file $filename for reading.");

            return Command::FAILURE;
        }

        $io->title("Creating users from $filename");

        /**
         * @var array{email: string, name: string, roles: list<string>, group: string|null}[] $users
         */
        $users = $io->progressIterate(self::parseUsers($filename));

        /**
         * @var array{email: string, password: string}[] $userPasswordPair
         */
        $userPasswordPair = [];

        $this->entityManager->beginTransaction();

        foreach ($users as $user) {
            $password = self::generateRandomPassword();

            /**
             * @var Group|null $group
             */
            $group = null;

            if (null !== $user['group']) {
                $group = $this->entityManager->getRepository(Group::class)->findOneBy(['name' => $user['group']]);
                if (null === $group) {
                    $io->warning("Group {$user['group']} not found for user {$user['email']}. Skipping.");
                    continue;
                }
            }

            $user = (new User())
                ->setName($user['name'])
                ->setEmail($user['email'])
                ->setRoles($user['roles'])
                ->setGroup($group);

            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);

            $userPasswordPair[] = [
                'email' => $user->getEmail(),
                'password' => $password,
            ];
        }

        self::writePasswordList(str_replace('.csv', '-password.csv', $filename), $userPasswordPair);

        if ($dryRun) {
            $this->entityManager->rollback();
            $io->warning('Dry run completed. No changes were made.');
        } else {
            $this->entityManager->commit();
            $this->entityManager->flush();
            $io->success("Created users from $filename");
        }

        return Command::SUCCESS;
    }

    /**
     * @return array{email: string, name: string, roles: list<string>, group: string|null}[]
     */
    private static function parseUsers(string $filename): array
    {
        $file = fopen($filename, 'r');
        if (false === $file) {
            throw new \RuntimeException("Could not open file $filename for reading.");
        }

        $header = fgetcsv($file);
        if (false === $header) {
            throw new \RuntimeException("Could not read header from $filename.");
        }

        $emailIndex = array_search('email', $header, true);
        $nameIndex = array_search('name', $header, true);
        $rolesIndex = array_search('roles', $header, true);
        $groupIndex = array_search('group', $header, true);

        if (!\is_int($emailIndex) || !\is_int($nameIndex) || !\is_int($rolesIndex)) {
            throw new \RuntimeException("Could not find email, name, or roles in the header of $filename.");
        }

        $users = [];
        while (($row = fgetcsv($file)) !== false) {
            $email = $row[$emailIndex];
            $name = $row[$nameIndex];
            $roles = $row[$rolesIndex];
            $group = false !== $groupIndex ? $row[$groupIndex] : null;

            if (!\is_string($email) || !\is_string($name) || !\is_string($roles) || !\is_string($group)) {
                throw new \RuntimeException("Invalid row in $filename.");
            }

            $users[] = [
                'email' => $email,
                'name' => $name,
                'roles' => explode(',', $roles),
                'group' => $group,
            ];
        }

        fclose($file);

        return $users;
    }

    /**
     * @param array<array<string, string>> $userPasswordPair
     */
    private static function writePasswordList(string $filename, array $userPasswordPair): void
    {
        $file = fopen($filename, 'w');
        if (false === $file) {
            throw new \RuntimeException("Could not open file $filename for writing.");
        }

        fputcsv($file, array_keys($userPasswordPair[0]));

        foreach ($userPasswordPair as $pair) {
            fputcsv($file, array_values($pair));
        }

        fclose($file);
    }

    /**
     * @throws RandomException
     */
    private function generateRandomPassword(): string
    {
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < 16; ++$i) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }

        return implode('', $pieces);
    }
}

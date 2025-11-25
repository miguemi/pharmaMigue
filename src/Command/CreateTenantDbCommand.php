<?php

namespace App\Command;

use App\Entity\Main\TenantDbConfig;
use Doctrine\ORM\EntityManagerInterface;
use Hakam\MultiTenancyBundle\Enum\DatabaseStatusEnum;
use Hakam\MultiTenancyBundle\Enum\DriverTypeEnum;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-tenant-db',
    description: 'Add database config',
)]
class CreateTenantDbCommand extends Command
{
    public function __construct(private EntitymanagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Create a new Tenant Database');

        $dbName = $io->ask('Enter the name of the database', null, function ($dbName) {
            if (!$dbName) {
                throw new \RuntimeException('The database name cannot be empty');
            }
            return $dbName;
        });

        $dbUser = $io->ask('Enter the database user', null, function ($dbUser) {
            if (!$dbUser) {
                throw new \RuntimeException('The database user cannot be empty');
            }
            return $dbUser;
        });

        $dbPass = $io->ask('Enter the password of the database');

        $dbHost = $io->ask('Enter the database host eg. host.docker.internal', 'host.docker.internal', function ($dbHost) {
            if (!$dbHost) {
                throw new \RuntimeException('The database host cannot be empty');
            }
            return $dbHost;
        });

        $dbPort = $io->ask('Enter the database port eg. 3306', '3306');


        $tenant = new TenantDbConfig();
        $tenant->setDbName($dbName);
        $tenant->setDbUserName($dbUser);
        $tenant->setDbHost($dbHost);
        $tenant->setDbPort($dbPort);
        $tenant->setDbPassword($dbPass);
        $tenant->setDriverType(DriverTypeEnum::MYSQL);
        $tenant->setDatabaseStatus(DatabaseStatusEnum::DATABASE_NOT_CREATED);
        $this->entityManager->persist($tenant);
        $this->entityManager->flush();
        $io->success("Tenant \"$dbName\" created successfully!");

        return Command::SUCCESS;
    }
}

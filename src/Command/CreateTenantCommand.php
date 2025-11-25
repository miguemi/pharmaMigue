<?php

namespace App\Command;

use App\Entity\Main\Tenant;
use App\Entity\Main\TenantDbConfig;
use App\Repository\Main\TenantDbConfigRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-tenant',
    description: 'Create a tenant a join it to a database',
)]
class CreateTenantCommand extends Command
{
    public function __construct(
        private EntitymanagerInterface $entityManager,
        private TenantDbConfigRepository $tenantDbConfigRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Create a new Tenant');


        $name = $io->ask('Enter the tenant name', null, function ($tenantName) {
            if (!$tenantName) {
                throw new \RuntimeException('The tenant name cannot be empty');
            }
            return $tenantName;
        });

        $code = $io->ask('Enter the tenant code', null, function ($tenantCode) {
            if (!$tenantCode) {
                throw new \RuntimeException('The tenant code cannot be empty');
            }
            return $tenantCode;
        });

        $dbConfigs = $this->tenantDbConfigRepository->findAllWithoutTenants();

        if (count($dbConfigs) === 0) {
            $io->error('No available database configurations found. Please create a tenant database configuration first.');
            return Command::FAILURE;
        }

        $choices = [];
        foreach ($dbConfigs as $index => $config) {
            $choices[$index] = sprintf(
                '%s (Host: %s:%s, User: %s)',
                $config->getDbName(),
                $config->getDbHost(),
                $config->getDbPort(),
                $config->getDbUserName()
            );
        }

        $selectedOp = $io->choice(
            'Select the database to associate with this tenant',
            $choices
        );
        $selectedKey = array_search($selectedOp, $choices, true);

        /** @var TenantDbConfig $selectedConfig **/
        $selectedConfig = $dbConfigs[$selectedKey];

        if (!$selectedConfig) {
            $io->error('Invalid database configuration selected.');
            return Command::FAILURE;
        }

        // create the tenant
        $tenant = new Tenant();
        $tenant->tenant_name = $name;
        $tenant->tenant_code = $code;
        $tenant->addDbConfig($selectedConfig);

        $this->entityManager->persist($tenant);
        $this->entityManager->flush();

        $io->success("Tenant '$name' created and linked to DB '{$selectedConfig->getDbName()}'!");

        return Command::SUCCESS;
    }
}

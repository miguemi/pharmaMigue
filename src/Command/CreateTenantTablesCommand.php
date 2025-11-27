<?php

namespace App\Command;

use App\Repository\Main\TenantRepository;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-tenant-tables',
    description: 'Crear tablas necesarias en las bases de datos de los tenants',
)]
class CreateTenantTablesCommand extends Command
{
    public function __construct(
        private TenantRepository $tenantRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Crear tablas en bases de datos de Tenants');

        $tenants = $this->tenantRepository->findAll();

        if (empty($tenants)) {
            $io->warning('No hay tenants registrados');
            return Command::SUCCESS;
        }

        $tables = [
            'products' => "
                CREATE TABLE IF NOT EXISTS products (
                    id INT AUTO_INCREMENT NOT NULL,
                    name VARCHAR(100) NOT NULL,
                    category VARCHAR(50) NOT NULL,
                    price DECIMAL(10, 2) NOT NULL,
                    stock INT NOT NULL DEFAULT 0,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    created_at DATETIME NOT NULL,
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
            ",
            'categories' => "
                CREATE TABLE IF NOT EXISTS categories (
                    id INT AUTO_INCREMENT NOT NULL,
                    name VARCHAR(100) NOT NULL,
                    description VARCHAR(255) DEFAULT NULL,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    created_at DATETIME NOT NULL,
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
            ",
        ];

        foreach ($tenants as $tenant) {
            $dbConfigs = $tenant->getDbConfigs();

            if ($dbConfigs->isEmpty()) {
                $io->warning("Tenant '{$tenant->getTenantName()}' no tiene configuración de BD");
                continue;
            }

            $dbConfig = $dbConfigs->first();

            try {
                $connection = DriverManager::getConnection([
                    'driver' => 'pdo_mysql',
                    'host' => $dbConfig->getDbHost(),
                    'port' => $dbConfig->getDbPort(),
                    'dbname' => $dbConfig->getDbName(),
                    'user' => $dbConfig->getDbUserName(),
                    'password' => $dbConfig->getDbPassword(),
                ]);

                foreach ($tables as $tableName => $sql) {
                    $connection->executeStatement($sql);
                    $io->success("Tabla '$tableName' creada en: {$dbConfig->getDbName()}");
                }
            } catch (\Exception $e) {
                $io->error("Error en {$dbConfig->getDbName()}: {$e->getMessage()}");
            }
        }

        $io->success('Proceso completado');

        return Command::SUCCESS;
    }
}

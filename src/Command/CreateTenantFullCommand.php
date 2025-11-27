<?php

namespace App\Command;

use App\Entity\Main\Tenant;
use App\Entity\Main\TenantDbConfig;
use App\Repository\Main\TenantRepository;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;
use Hakam\MultiTenancyBundle\Enum\DatabaseStatusEnum;
use Hakam\MultiTenancyBundle\Enum\DriverTypeEnum;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-full',
    description: 'Crear un tenant completo (BD + configuración + tablas)',
)]
class CreateTenantFullCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TenantRepository $tenantRepository,
        private string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Crear Nuevo Tenant Completo');

        // 1. Pedir datos del tenant
        $tenantName = $io->ask('Nombre del tenant (ej: Farmacia Norte)');
        $tenantCode = $io->ask('Código del tenant (ej: norte)', strtolower(str_replace(' ', '_', $tenantName)));

        // Verificar que no exista
        $existing = $this->tenantRepository->findOneBy(['tenant_code' => $tenantCode]);
        if ($existing) {
            $io->error("Ya existe un tenant con el código '$tenantCode'");
            return Command::FAILURE;
        }

        // 2. Pedir datos de conexión
        $io->section('Configuración de Base de Datos');
        $dbName = $io->ask('Nombre de la base de datos', $tenantCode);
        $dbHost = $io->ask('Host', '127.0.0.1');
        $dbPort = $io->ask('Puerto', '3306');
        $dbUser = $io->ask('Usuario', 'root');
        $dbPassword = $io->askHidden('Contraseña');

        // 3. Crear la base de datos
        $io->section('Creando base de datos...');
        try {
            $connection = DriverManager::getConnection([
                'driver' => 'pdo_mysql',
                'host' => $dbHost,
                'port' => $dbPort,
                'user' => $dbUser,
                'password' => $dbPassword,
            ]);

            $connection->executeStatement("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $io->success("Base de datos '$dbName' creada");
        } catch (\Exception $e) {
            $io->error("Error creando BD: {$e->getMessage()}");
            return Command::FAILURE;
        }

        // 4. Crear configuración de BD
        $io->section('Creando configuración...');
        $dbConfig = new TenantDbConfig();
        $dbConfig->setDbName($dbName);
        $dbConfig->setDbHost($dbHost);
        $dbConfig->setDbPort($dbPort);
        $dbConfig->setDbUserName($dbUser);
        $dbConfig->setDbPassword($dbPassword);
        $dbConfig->setDriverType(DriverTypeEnum::MYSQL);
        $dbConfig->setDatabaseStatus(DatabaseStatusEnum::DATABASE_CREATED);

        $this->entityManager->persist($dbConfig);
        $io->success("Configuración de BD creada");

        // 5. Crear tenant
        $io->section('Creando tenant...');
        $tenant = new Tenant();
        $tenant->tenant_name = $tenantName;
        $tenant->tenant_code = $tenantCode;
        $tenant->addDbConfig($dbConfig);

        $this->entityManager->persist($tenant);
        $this->entityManager->flush();
        $io->success("Tenant '$tenantName' creado");

        // 6. Crear tablas en la BD del tenant
        $io->section('Creando tablas...');
        try {
            $tenantConnection = DriverManager::getConnection([
                'driver' => 'pdo_mysql',
                'host' => $dbHost,
                'port' => $dbPort,
                'dbname' => $dbName,
                'user' => $dbUser,
                'password' => $dbPassword,
            ]);

            $sql = "
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
            ";

            $tenantConnection->executeStatement($sql);
            $io->success("Tabla 'products' creada");
        } catch (\Exception $e) {
            $io->error("Error creando tablas: {$e->getMessage()}");
            return Command::FAILURE;
        }

        // Resumen
        $io->success('¡Tenant creado exitosamente!');
        $io->table(
            ['Campo', 'Valor'],
            [
                ['Nombre', $tenantName],
                ['Código', $tenantCode],
                ['Base de datos', $dbName],
                ['Host', $dbHost . ':' . $dbPort],
            ]
        );

        $io->note("Ahora puedes asignar este tenant a un usuario desde /users");

        return Command::SUCCESS;
    }
}

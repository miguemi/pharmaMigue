<?php

namespace App\Command;

use App\Entity\Main\User;
use App\Repository\Main\UserRepository;
use App\Repository\Main\TenantRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Crear un nuevo usuario en el sistema',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private TenantRepository $tenantRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Crear Nuevo Usuario');

        // Pedir email
        $email = $io->ask('Email del usuario');

        if ($this->userRepository->emailExists($email)) {
            $io->error('El email ya está registrado');
            return Command::FAILURE;
        }

        // Pedir nombre
        $name = $io->ask('Nombre completo');

        // Pedir contraseña
        $password = $io->askHidden('Contraseña');

        // Pedir rol
        $isAdmin = $io->confirm('¿Es administrador?', false);
        $roles = $isAdmin ? ['ROLE_ADMIN'] : ['ROLE_USER'];

        // Mostrar tenants disponibles
        $tenants = $this->tenantRepository->findAll();

        if (!empty($tenants)) {
            $tenantChoices = [];
            foreach ($tenants as $tenant) {
                $tenantChoices[$tenant->getId()] = $tenant->getTenantCode() . ' - ' . $tenant->getTenantName();
            }

            $selectedTenants = $io->choice(
                'Selecciona los tenants (separados por coma)',
                $tenantChoices,
                null,
                true
            );
        }

        // Crear usuario
        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        $user->setRoles($roles);
        $user->setIsActive(true);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Asignar tenants
        if (!empty($selectedTenants)) {
            foreach ($tenants as $tenant) {
                $key = $tenant->getTenantCode() . ' - ' . $tenant->getTenantName();
                if (in_array($key, $selectedTenants)) {
                    $user->addTenant($tenant);
                }
            }
        }

        $this->userRepository->save($user);

        $io->success('Usuario creado exitosamente!');

        $io->table(
            ['Campo', 'Valor'],
            [
                ['ID', $user->getId()],
                ['Email', $user->getEmail()],
                ['Nombre', $user->getName()],
                ['Roles', implode(', ', $user->getRoles())],
                ['Tenants', implode(', ', array_map(fn($t) => $t->getTenantCode(), $user->getTenants()->toArray()))],
            ]
        );

        return Command::SUCCESS;
    }
}

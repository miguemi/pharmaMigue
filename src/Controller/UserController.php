<?php

namespace App\Controller;

use App\Entity\Main\User;
use App\Repository\Main\UserRepository;
use App\Repository\Main\TenantRepository;
use Rompetomp\InertiaBundle\Architecture\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/users', name: 'users_')]
class UserController extends AbstractController
{
    public function __construct(
        private InertiaInterface $inertia,
        private UserRepository $userRepository,
        private TenantRepository $tenantRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    private function getCurrentUser(Request $request): ?array
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) return null;

        $user = $this->userRepository->find($userId);
        if (!$user) return null;

        return [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'isAdmin' => in_array('ROLE_ADMIN', $user->getRoles()),
            'tenants' => array_map(fn($t) => [
                'id' => $t->getId(),
                'name' => $t->getTenantName(),
                'code' => $t->getTenantCode(),
            ], $user->getTenants()->toArray())
        ];
    }

    #[Route('', name: 'index')]
    public function index(Request $request): Response
    {
        $users = $this->userRepository->findAll();

        return $this->inertia->render('Users/Index', [
            'user' => $this->getCurrentUser($request),
            'users' => array_map(fn(User $user) => $this->serializeUser($user), $users)
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request): Response
    {
        $tenants = $this->tenantRepository->findAll();

        return $this->inertia->render('Users/Create', [
            'user' => $this->getCurrentUser($request),
            'tenants' => array_map(fn($t) => [
                'id' => $t->getId(),
                'name' => $t->getTenantName(),
                'code' => $t->getTenantCode(),
            ], $tenants)
        ]);
    }

    #[Route('/store', name: 'store', methods: ['POST'])]
    public function store(Request $request): RedirectResponse
    {
        $content = $request->getContent();
        if ($content) {
            $data = json_decode($content, true);
        } else {
            $data = $request->request->all();
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setName($data['name']);
        $user->setRoles($data['isAdmin'] ?? false ? ['ROLE_ADMIN'] : ['ROLE_USER']);
        $user->setIsActive(true);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        if (!empty($data['tenantIds'])) {
            foreach ($data['tenantIds'] as $tenantId) {
                $tenant = $this->tenantRepository->find($tenantId);
                if ($tenant) {
                    $user->addTenant($tenant);
                }
            }
        }

        $this->userRepository->save($user);

        return $this->redirectToRoute('users_index');
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(int $id, Request $request): Response
    {
        $editUser = $this->userRepository->find($id);
        $tenants = $this->tenantRepository->findAll();

        return $this->inertia->render('Users/Edit', [
            'user' => $this->getCurrentUser($request),
            'editUser' => $this->serializeUser($editUser),
            'tenants' => array_map(fn($t) => [
                'id' => $t->getId(),
                'name' => $t->getTenantName(),
                'code' => $t->getTenantCode(),
            ], $tenants)
        ]);
    }

    #[Route('/{id}/update', name: 'update', methods: ['POST'])]
    public function update(int $id, Request $request): RedirectResponse
    {
        $user = $this->userRepository->find($id);

        $content = $request->getContent();
        if ($content) {
            $data = json_decode($content, true);
        } else {
            $data = $request->request->all();
        }

        $user->setEmail($data['email']);
        $user->setName($data['name']);
        $user->setRoles($data['isAdmin'] ?? false ? ['ROLE_ADMIN'] : ['ROLE_USER']);
        $user->setIsActive($data['isActive'] ?? true);

        if (!empty($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        foreach ($user->getTenants() as $tenant) {
            $user->removeTenant($tenant);
        }
        if (!empty($data['tenantIds'])) {
            foreach ($data['tenantIds'] as $tenantId) {
                $tenant = $this->tenantRepository->find($tenantId);
                if ($tenant) {
                    $user->addTenant($tenant);
                }
            }
        }

        $this->userRepository->save($user);

        return $this->redirectToRoute('users_index');
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(int $id): RedirectResponse
    {
        $user = $this->userRepository->find($id);
        $this->userRepository->remove($user);

        return $this->redirectToRoute('users_index');
    }

    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'roles' => $user->getRoles(),
            'isAdmin' => in_array('ROLE_ADMIN', $user->getRoles()),
            'isActive' => $user->isActive(),
            'createdAt' => $user->getCreatedAt()?->format('Y-m-d H:i:s'),
            'tenants' => array_map(fn($t) => [
                'id' => $t->getId(),
                'name' => $t->getTenantName(),
                'code' => $t->getTenantCode(),
            ], $user->getTenants()->toArray())
        ];
    }
}

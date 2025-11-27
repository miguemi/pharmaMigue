<?php

namespace App\Controller\Api;

use App\Entity\Main\User;
use App\Repository\Main\UserRepository;
use App\Repository\Main\TenantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users', name: 'api_users_')]
class UserController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private TenantRepository $tenantRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    /**
     * GET /api/users - Listar todos los usuarios
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $users = $this->userRepository->findAll();

        return $this->json([
            'success' => true,
            'data' => array_map(fn(User $user) => $this->serializeUser($user), $users)
        ]);
    }

    /**
     * GET /api/users/{id} - Obtener un usuario
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'success' => true,
            'data' => $this->serializeUser($user)
        ]);
    }

    /**
     * POST /api/users - Crear un nuevo usuario
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['name']) || empty($data['password'])) {
            return $this->json([
                'success' => false,
                'message' => 'Email, nombre y contraseña son requeridos'
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($this->userRepository->emailExists($data['email'])) {
            return $this->json([
                'success' => false,
                'message' => 'El email ya está registrado'
            ], Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setName($data['name']);
        $user->setRoles($data['roles'] ?? ['ROLE_USER']);
        $user->setIsActive($data['isActive'] ?? true);

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

        return $this->json([
            'success' => true,
            'message' => 'Usuario creado exitosamente',
            'data' => $this->serializeUser($user)
        ], Response::HTTP_CREATED);
    }

    /**
     * PUT /api/users/{id} - Actualizar un usuario
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!empty($data['email']) && $data['email'] !== $user->getEmail()) {
            if ($this->userRepository->emailExists($data['email'], $user->getId())) {
                return $this->json([
                    'success' => false,
                    'message' => 'El email ya está registrado'
                ], Response::HTTP_CONFLICT);
            }
            $user->setEmail($data['email']);
        }

        if (!empty($data['name'])) {
            $user->setName($data['name']);
        }

        if (isset($data['roles'])) {
            $user->setRoles($data['roles']);
        }

        if (isset($data['isActive'])) {
            $user->setIsActive($data['isActive']);
        }

        if (!empty($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        if (isset($data['tenantIds'])) {
            foreach ($user->getTenants() as $tenant) {
                $user->removeTenant($tenant);
            }
            foreach ($data['tenantIds'] as $tenantId) {
                $tenant = $this->tenantRepository->find($tenantId);
                if ($tenant) {
                    $user->addTenant($tenant);
                }
            }
        }

        $this->userRepository->save($user);

        return $this->json([
            'success' => true,
            'message' => 'Usuario actualizado',
            'data' => $this->serializeUser($user)
        ]);
    }

    /**
     * DELETE /api/users/{id} - Eliminar un usuario
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], Response::HTTP_NOT_FOUND);
        }

        $this->userRepository->remove($user);

        return $this->json([
            'success' => true,
            'message' => 'Usuario eliminado'
        ]);
    }

    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'roles' => $user->getRoles(),
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

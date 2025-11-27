<?php

namespace App\Controller;

use App\Entity\Tenant\Category;
use App\Repository\Main\UserRepository;
use App\Repository\Main\TenantRepository;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Rompetomp\InertiaBundle\Architecture\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tenant/{tenantCode}/categories', name: 'tenant_categories_')]
class CategoryController extends AbstractController
{
    public function __construct(
        private InertiaInterface $inertia,
        private UserRepository $userRepository,
        private TenantRepository $tenantRepository,
        private string $projectDir,
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

    private function getTenantEntityManager(string $tenantCode): ?EntityManager
    {
        $tenant = $this->tenantRepository->findOneBy(['tenant_code' => $tenantCode]);
        if (!$tenant) return null;

        $dbConfigs = $tenant->getDbConfigs();
        if ($dbConfigs->isEmpty()) return null;

        $dbConfig = $dbConfigs->first();

        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: [$this->projectDir . '/src/Entity/Tenant'],
            isDevMode: true,
        );

        $connection = DriverManager::getConnection([
            'driver' => 'pdo_mysql',
            'host' => $dbConfig->getDbHost(),
            'port' => $dbConfig->getDbPort(),
            'dbname' => $dbConfig->getDbName(),
            'user' => $dbConfig->getDbUserName(),
            'password' => $dbConfig->getDbPassword(),
        ]);

        return new EntityManager($connection, $config);
    }

    private function getTenantInfo(string $tenantCode): ?array
    {
        $tenant = $this->tenantRepository->findOneBy(['tenant_code' => $tenantCode]);
        if (!$tenant) return null;

        return [
            'id' => $tenant->getId(),
            'name' => $tenant->getTenantName(),
            'code' => $tenant->getTenantCode(),
        ];
    }

    #[Route('', name: 'index')]
    public function index(string $tenantCode, Request $request): Response
    {
        $em = $this->getTenantEntityManager($tenantCode);
        if (!$em) {
            return $this->redirectToRoute('dashboard');
        }

        $categories = $em->getRepository(Category::class)->findAll();

        return $this->inertia->render('Categories/Index', [
            'user' => $this->getCurrentUser($request),
            'tenant' => $this->getTenantInfo($tenantCode),
            'categories' => array_map(fn($c) => [
                'id' => $c->getId(),
                'name' => $c->getName(),
                'description' => $c->getDescription(),
                'isActive' => $c->isActive(),
                'createdAt' => $c->getCreatedAt()?->format('Y-m-d H:i:s'),
            ], $categories)
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(string $tenantCode, Request $request): Response
    {
        return $this->inertia->render('Categories/Create', [
            'user' => $this->getCurrentUser($request),
            'tenant' => $this->getTenantInfo($tenantCode),
        ]);
    }

    #[Route('/store', name: 'store', methods: ['POST'])]
    public function store(string $tenantCode, Request $request): RedirectResponse
    {
        $em = $this->getTenantEntityManager($tenantCode);
        if (!$em) {
            return $this->redirectToRoute('dashboard');
        }

        $content = $request->getContent();
        if ($content) {
            $data = json_decode($content, true);
        } else {
            $data = $request->request->all();
        }

        $category = new Category();
        $category->setName($data['name']);
        $category->setDescription($data['description'] ?? null);
        $category->setIsActive(true);

        $em->persist($category);
        $em->flush();

        return $this->redirectToRoute('tenant_categories_index', ['tenantCode' => $tenantCode]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(string $tenantCode, int $id, Request $request): Response
    {
        $em = $this->getTenantEntityManager($tenantCode);
        if (!$em) {
            return $this->redirectToRoute('dashboard');
        }

        $category = $em->getRepository(Category::class)->find($id);
        if (!$category) {
            return $this->redirectToRoute('tenant_categories_index', ['tenantCode' => $tenantCode]);
        }

        return $this->inertia->render('Categories/Edit', [
            'user' => $this->getCurrentUser($request),
            'tenant' => $this->getTenantInfo($tenantCode),
            'category' => [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'description' => $category->getDescription(),
                'isActive' => $category->isActive(),
            ]
        ]);
    }

    #[Route('/{id}/update', name: 'update', methods: ['POST'])]
    public function update(string $tenantCode, int $id, Request $request): RedirectResponse
    {
        $em = $this->getTenantEntityManager($tenantCode);
        if (!$em) {
            return $this->redirectToRoute('dashboard');
        }

        $category = $em->getRepository(Category::class)->find($id);
        if (!$category) {
            return $this->redirectToRoute('tenant_categories_index', ['tenantCode' => $tenantCode]);
        }

        $content = $request->getContent();
        if ($content) {
            $data = json_decode($content, true);
        } else {
            $data = $request->request->all();
        }

        $category->setName($data['name']);
        $category->setDescription($data['description'] ?? null);
        $category->setIsActive($data['isActive'] ?? true);

        $em->flush();

        return $this->redirectToRoute('tenant_categories_index', ['tenantCode' => $tenantCode]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(string $tenantCode, int $id, Request $request): RedirectResponse
    {
        $em = $this->getTenantEntityManager($tenantCode);
        if (!$em) {
            return $this->redirectToRoute('dashboard');
        }

        $category = $em->getRepository(Category::class)->find($id);
        if ($category) {
            $em->remove($category);
            $em->flush();
        }

        return $this->redirectToRoute('tenant_categories_index', ['tenantCode' => $tenantCode]);
    }
}

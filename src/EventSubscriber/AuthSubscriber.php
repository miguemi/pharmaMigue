<?php

namespace App\EventSubscriber;

use App\Repository\Main\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthSubscriber implements EventSubscriberInterface
{
    private array $protectedRoutes = [
        'dashboard',
        'users_index',
        'users_create',
        'users_store',
        'users_edit',
        'users_update',
        'users_delete',
        'tenant_products_index',
        'tenant_products_create',
        'tenant_products_store',
        'tenant_products_edit',
        'tenant_products_update',
        'tenant_products_delete',
        'tenant_categories_index',
        'tenant_categories_create',
        'tenant_categories_store',
        'tenant_categories_edit',
        'tenant_categories_update',
        'tenant_categories_delete',
    ];

    private array $adminRoutes = [
        'users_index',
        'users_create',
        'users_store',
        'users_edit',
        'users_update',
        'users_delete',
    ];

    private array $tenantRoutes = [
        'tenant_products_index',
        'tenant_products_create',
        'tenant_products_store',
        'tenant_products_edit',
        'tenant_products_update',
        'tenant_products_delete',
        'tenant_categories_index',
        'tenant_categories_create',
        'tenant_categories_store',
        'tenant_categories_edit',
        'tenant_categories_update',
        'tenant_categories_delete',
    ];

    public function __construct(
        private UserRepository $userRepository,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');

        if (!in_array($routeName, $this->protectedRoutes)) {
            return;
        }

        $userId = $request->getSession()->get('user_id');

        if (!$userId) {
            $event->setResponse(new RedirectResponse(
                $this->urlGenerator->generate('auth_login')
            ));
            return;
        }

        $user = $this->userRepository->find($userId);

        if (!$user || !$user->isActive()) {
            $request->getSession()->remove('user_id');
            $event->setResponse(new RedirectResponse(
                $this->urlGenerator->generate('auth_login')
            ));
            return;
        }

        // Verificar rutas de admin
        if (in_array($routeName, $this->adminRoutes)) {
            if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                $event->setResponse(new RedirectResponse(
                    $this->urlGenerator->generate('dashboard')
                ));
                return;
            }
        }

        // Verificar acceso a tenant
        if (in_array($routeName, $this->tenantRoutes)) {
            $tenantCode = $request->attributes->get('tenantCode');

            if (!$user->hasAccessToTenantCode($tenantCode)) {
                $event->setResponse(new RedirectResponse(
                    $this->urlGenerator->generate('dashboard')
                ));
                return;
            }
        }

        $request->attributes->set('current_user', $user);
    }
}

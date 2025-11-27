<?php

namespace App\Controller;

use App\Repository\Main\UserRepository;
use Rompetomp\InertiaBundle\Architecture\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    public function __construct(
        private InertiaInterface $inertia,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    #[Route('/login', name: 'auth_login')]
    public function login(Request $request): Response|RedirectResponse
    {
        // Si ya está autenticado, redirigir al dashboard
        if ($request->getSession()->get('user_id')) {
            return $this->redirectToRoute('dashboard');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            // Leer datos de JSON (Inertia) o form-data
            $content = $request->getContent();
            if ($content) {
                $data = json_decode($content, true);
                $email = $data['email'] ?? null;
                $password = $data['password'] ?? null;
            } else {
                $email = $request->request->get('email');
                $password = $request->request->get('password');
            }

            if (!$email || !$password) {
                $error = 'Email y contraseña son requeridos';
            } else {
                $user = $this->userRepository->findByEmail($email);

                if (!$user) {
                    $error = 'Usuario no encontrado';
                } elseif (!$user->isActive()) {
                    $error = 'Usuario desactivado';
                } elseif (!$this->passwordHasher->isPasswordValid($user, $password)) {
                    $error = 'Contraseña incorrecta';
                } else {
                    // Guardar usuario en sesión
                    $request->getSession()->set('user_id', $user->getId());
                    return $this->redirectToRoute('dashboard');
                }
            }
        }

        return $this->inertia->render('Auth/Login', [
            'error' => $error
        ]);
    }

    #[Route('/logout', name: 'auth_logout')]
    public function logout(Request $request): RedirectResponse
    {
        $request->getSession()->remove('user_id');
        return $this->redirectToRoute('auth_login');
    }

    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(Request $request): Response|RedirectResponse
    {
        $userId = $request->getSession()->get('user_id');

        if (!$userId) {
            return $this->redirectToRoute('auth_login');
        }

        $user = $this->userRepository->find($userId);

        if (!$user) {
            $request->getSession()->remove('user_id');
            return $this->redirectToRoute('auth_login');
        }

        return $this->inertia->render('Dashboard', [
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'isAdmin' => in_array('ROLE_ADMIN', $user->getRoles()),
                'tenants' => array_map(fn($t) => [
                    'id' => $t->getId(),
                    'name' => $t->getTenantName(),
                    'code' => $t->getTenantCode(),
                ], $user->getTenants()->toArray())
            ]
        ]);
    }
}

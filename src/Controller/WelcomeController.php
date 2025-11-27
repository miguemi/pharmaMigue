<?php

namespace App\Controller;

use Rompetomp\InertiaBundle\Architecture\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

final class WelcomeController extends AbstractController
{
    public function __construct(
        private InertiaInterface $inertia,
    ) {}

    #[Route('/', name: 'app_home')]
    public function index(Request $request): RedirectResponse
    {
        // Si está autenticado, ir al dashboard
        if ($request->getSession()->get('user_id')) {
            return $this->redirectToRoute('dashboard');
        }

        // Si no, ir al login
        return $this->redirectToRoute('auth_login');
    }

    #[Route('/ejemplo', name: 'app_welcome')]
    public function ejemplo(): Response
    {
        return $this->render('index.html.twig', [
            'controller_name' => 'WelcomeController',
        ]);
    }

    #[Route('/hello', name: 'app_react')]
    public function hello(): Response
    {
        return $this->inertia->render('hello', [
            'productos' => "s"
        ]);
    }
}

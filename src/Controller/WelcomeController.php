<?php

namespace App\Controller;

use Rompetomp\InertiaBundle\Architecture\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WelcomeController extends AbstractController
{
    public function __construct(private InertiaInterface $inertia)
    {
    }

    #[Route('/normal', name: 'app_welcome')]
    public function index_normal(): Response
    {
        return $this->render('welcome/index.html.twig', [
            'controller_name' => 'WelcomeController',
        ]);
    }

    #[Route('/', name: 'app_react')]
    public function index(): Response
    {
        return $this->inertia->render('hello');
    }
}

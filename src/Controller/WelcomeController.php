<?php

namespace App\Controller;

use Rompetomp\InertiaBundle\Architecture\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProductsRepository as ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;


final class WelcomeController extends AbstractController
{
    public function __construct(
        private InertiaInterface $inertia,
        private ProductRepository $product,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/ejemplo', name: 'app_welcome')]
    public function index_normal(): Response
    {
        return $this->render('index.html.twig', [
            'controller_name' => 'WelcomeController',
        ]);
    }

    #[Route('/', name: 'app_react')]
    public function index(): Response
    {
        // $productos = $this->product->findAll();
        // return $this->json($productos);
        return $this->inertia->render('hello', [
            'productos' => "s"
        ]);
    }

    #[Route('/save/{name}/{category}/{price}', name: 'app_save_product')]
    public function saveProduct($name, $category, $price): JsonResponse
    {
        $newProduct = new \App\Entity\Products();
        $newProduct->setName($name);
        $newProduct->setCategory($category);
        $newProduct->setPrice($price);

        $this->entityManager->persist($newProduct);
        $this->entityManager->flush();
        return $this->json(['status' => 'Product saved!']);
    }
}

<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    #[Route('/catalog', name: 'app_catalog', methods: ['GET'])]
    #[Route('/cart', name: 'app_cart', methods: ['GET'])]
    #[Route('/orders/{id}', name: 'app_order_detail', methods: ['GET'])]
    #[Route('/admin/products', name: 'app_admin_products', methods: ['GET'])]
    #[Route('/login', name: 'app_login', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('base.html.twig');
    }
}


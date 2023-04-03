<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;

#[Route('/')]
class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_main_index')]
    public function index(ManagerRegistry $doctrine): Response
    {

        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Product::class)->findAll();

        // this looks exactly the same
        return $this->render('main/main.html.twig', ['products' => $product] );
    }
}
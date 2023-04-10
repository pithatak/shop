<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Category;

#[Route('/')]
class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_main_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository ): Response
    {

        dump($categoryRepository->findAll() );
        dump("darowa");

        // this looks exactly the same
        return $this->render('main/index.html.twig', [
            'products' => $productRepository->findAll(),
            'categories' => $categoryRepository->findAll()
        ]);
    }
}
<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class CategoryController
{
    #[Route('/{id}', name: 'app_category_show', methods: ['GET'])]
    public function show(Category $category, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {
         dump('$productRepository->findBy($category) =', $productRepository->findBy($category));
         exit;
        return $this->render('main/index.html.twig', [
            'products' => $productRepository->findBy($category),

        ]);
    }
}
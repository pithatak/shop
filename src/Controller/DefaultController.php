<?php

namespace App\Controller;

use App\Form\SearchForm;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

#[Route('/')]
class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_main_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository, Request $request): Response
    {

        $form = $this->createForm(SearchForm::class);
        $search = null;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $searchN = $productRepository->findByName($form->getData());
            $searchD = $productRepository->findByDescription($form->getData());
            $search['Word'] = $form->getData()['search'];
            $search['Object'] = array_unique(array_merge($searchN, $searchD), SORT_REGULAR);

        }

        return $this->render('main/index.html.twig', [
            'productRepository' => $productRepository,
            'categoryRepository' => $categoryRepository,
            'form' => $form->createView(),
            'search' => $search
        ]);
    }

}
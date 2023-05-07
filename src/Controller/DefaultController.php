<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/')]
class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_main_index', methods: ['GET'])]
    public function index(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {

        $search = null;

        if ($request->query->get('search')) {

            $search['Word'] = $request->query->get('search');
            $searchN = $productRepository->findByName($search['Word']);
            $searchD = $productRepository->findByDescription($search['Word']);
            $search['Object'] = array_unique(array_merge($searchN, $searchD), SORT_REGULAR);
        }

        return $this->render('main/index.html.twig', [
            'productRepository' => $productRepository,
            'categoryRepository' => $categoryRepository,
            'search' => $search
        ]);
    }

    #[Route('/search', name: 'app_main_search', methods: ['POST'])]
    public function searchAction(Request $request)
    {
        return $this->redirectToRoute('app_main_page', [ 'search' => $request->request->get('search')]);
    }
}
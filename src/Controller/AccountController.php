<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/account')]
class AccountController extends AbstractController
{
    #[Route('/show', name: 'show_account', methods: ['GET', 'POST'])]
    public function showAccount(Request $request, ProductRepository $productRepository): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        $csrfToken = $request->request->get('formToken');

        if ($form->isSubmitted() && $form->isValid()) {


            $product->setUser($this->container->get('security.token_storage')->getToken()->getUser());

            $productRepository->save($product, true);

            return $this->render('registration/account.html.twig', [
                'form' => $form,
                'message' => 'Success! You`re product - "'. $product->getName() . '" is added!'
            ]);
        }

        return $this->render('registration/account.html.twig', [
            'form' => $form
        ]);
    }
}
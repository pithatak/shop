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

        return $this->render('registration/account.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/get/product/form', name: 'get_product_form', methods: ['GET', 'PUT'])]
    public function getForm(Request $request = null, ProductRepository $productRepository){

        $edit = false;

        if($request->get('productId') !== null){
            $product = $productRepository->find($request->get('productId'));
            $edit = true;
        }else {
            $product = new Product();
        }

        $form = $this->createForm(ProductType::class, $product);

/*
        dump(' $form->createView() = ',  $form->createView());
        dump('parameters = ', $this->getParameter('brochures_directory'));
        $filePath = $this->getParameter('brochures_directory')."/".$product->getBrochureFilename();
        dump('filepath =  ', $filePath);
        $file = new File($filePath);
        dump('$file =  ', $file);
        $form->get('brochure')->setData($file);
        dump('$$form->get(brochure)=  ', $form->get('brochure'));
        exit;*/

        return $this->render('product/_form.html.twig', [
            'form' => $form->createView(),
            'edit' => $edit,
            'productId' => $product->getId()
        ]);
    }
}
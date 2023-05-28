<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Product;
use App\Entity\Review;
use App\Form\ProductType;
use App\Form\ReviewType;
use App\Repository\CartRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\RatingRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProductRepository $productRepository): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productRepository->save($product, true);

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/show/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product, CategoryRepository $categoryRepository): Response
    {

        $form = $this->createForm(ReviewType::class);

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
            'categoryRepository' => $categoryRepository
        ]);
    }

    #[Route('/edit/{id}', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($request->get('productId'));

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productRepository->save($product, true);

            return $this->redirectToRoute('show_account', [], Response::HTTP_SEE_OTHER);
        }

        if ($request->isXmlHttpRequest()) {

            $response = new JsonResponse(['form' => $form, 'message' => 'Product deleted']);

            return $response;

        }

        return $this->renderForm('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, ProductRepository $productRepository): Response
    {

        if ($request->isXmlHttpRequest()){

            $product = $productRepository->find($request->get('productId'));
            $response = new JsonResponse(['productName' => $product->getName(), 'message' => 'Product deleted']);
            $productRepository->remove($product, true);

            return $response;
        }

        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $productRepository->remove($product, true);
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/change/rating', name: 'product_change_rating', methods: ['POST'])]
    public function setRating(Request $request, RatingRepository $ratingRepository, UserRepository $userRepository, ProductRepository $productRepository): Response
    {

         $product = $productRepository->find($request->request->get('productId'));
         $user = $userRepository->find($request->request->get('userId'));

         $findRating = $ratingRepository->findBy([
             'Product' => $product,
             'User' => $user,
         ]);

        if(empty($findRating)){
            $rating = new Review();
            $rating->setProduct($product);
            $rating->setUser($user);
        }else {
            $rating = $findRating[0];
        }

        $form = $this->createForm(ReviewType::class, $rating);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $rating->setDate(new \DateTime());
            $ratingRepository->save($rating, true);

            return $this->redirectToRoute('app_product_show', ['id' => $request->request->get('productId')], Response::HTTP_CREATED);
        }

        return $this->redirectToRoute('app_product_show', ['id' => $request->request->get('productId')], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/add/cart', name: 'product_add_cart', methods: ['POST'])]
    public function addCart(Request $request, ProductRepository $productRepository, CartRepository $cartRepository): Response
    {
        $product = $productRepository->find($request->get('productId'));
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        $findRating = $cartRepository->findBy([
            'product' => $product,
            'user' => $user,
        ]);

        if(empty($findRating)){
            $cart = new Cart();
            $cart->setProduct($product);
            $cart->setUser($user);
        }else {

            return new Response(json_encode([['answer' => 'Product is already in cart.'], Response::HTTP_OK, ['content-type' => 'text/html']]));
        }

        $cartRepository->save($cart, true);

        $response = ['userCartCount' => count($user->getCarts())];

        return new Response(json_encode([$response, Response::HTTP_OK, ['content-type' => 'text/html']]));
    }

    #[Route('/unset/cart', name: 'product_unset_cart', methods: ['POST'])]
    public function unsetCart(Request $request, ProductRepository $productRepository, CartRepository $cartRepository): Response
    {

        $product = $productRepository->find($request->get('productId'));
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        $cart = $cartRepository->findBy([
            'product' => $product,
            'user' => $user,
        ]);

        $user->getCarts();
        $cartRepository->remove($cart[0], true);
        $response = ['userCartCount' => count($user->getCarts())];

        return new Response(json_encode([$response, Response::HTTP_OK, ['content-type' => 'text/html']]));
    }
}

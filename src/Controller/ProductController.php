<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\OrderProducts;
use App\Form\OrderType;
use App\Entity\Product;
use App\Entity\Review;
use App\Form\ProductType;
use App\Form\ReviewType;
use App\Repository\CartRepository;
use App\Repository\CategoryRepository;
use App\Repository\OrderProductsRepository;
use App\Repository\ProductRepository;
use App\Repository\RatingRepository;
use App\Repository\UserRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;

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

    #[Route('/new', name: 'app_product_new', methods: ['POST'])]
    public function new(Request $request, ProductRepository $productRepository, SluggerInterface $slugger): Response
    {

        $productId = $request->get('product')['id'];
       if(empty($productId)){
           $product = new Product();
       }else {
           $product = $productRepository->find($productId);
       }

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $brochureFile = $form->get('brochure')->getData();

            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

                try {
                    $brochureFile->move(
                        $this->getParameter('brochures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {

                }

                $product->setBrochureFilename($newFilename);
            }
            $product->setUser($this->container->get('security.token_storage')->getToken()->getUser());

            $productRepository->save($product, true);
            if(empty($productId)){
                return $this->render('registration/account.html.twig', [
                    'form' => $form,
                    'message' => 'Success! You`re product - "' . $product->getName() . '" is added!'
                ]);
            }else {
                return $this->render('registration/account.html.twig', [
                    'form' => $form,
                    'message' => 'Success! You`re product - "' . $product->getName() . '" is changed!'
                ]);
            }

        }

        return $this->render('registration/account.html.twig', [
            'form' => $form,
            'message' => 'Error! You`re product - "'. $product->getName() . '" is not valid!'
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
    public function edit(int $id, Request $request, Product $product, ProductRepository $productRepository): Response
    {

        $product = $productRepository->find($id);

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()){

            $form->submit($product);
            if ($form->isValid()) {
                $productRepository->save($product, true);

                return $this->redirectToRoute('show_account', [], Response::HTTP_SEE_OTHER);
            }
            $response = new JsonResponse(['form' => $form, 'message' => 'Product deleted']);

        }

        if ($form->isSubmitted() && $form->isValid()) {
            $productRepository->save($product, true);

            return $this->redirectToRoute('show_account', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, ProductRepository $productRepository, RatingRepository $ratingRepository): Response
    {

        if ($request->isXmlHttpRequest()){

            $product = $productRepository->find($request->get('productId'));
            $response = new JsonResponse(['productName' => $product->getName(), 'message' => 'Product deleted']);

            foreach ($product->getReviews() as $review){
                $ratingRepository->remove($review, true);
            }

            $productRepository->remove($product, true);

            return $response;
        }

        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $productRepository->remove($product, true);
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/set/rating/{id}', name: 'product_change_rating', methods: ['POST'])]
    public function setRating(int $id, Request $request, RatingRepository $ratingRepository, UserRepository $userRepository, ProductRepository $productRepository): Response
    {

         $product = $productRepository->find($id);
         $user = $this->container->get('security.token_storage')->getToken()->getUser();

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

            return $this->redirectToRoute('app_product_show', ['id' => $id], Response::HTTP_CREATED);
        }

        return $this->redirectToRoute('app_product_show', ['id' => $id], Response::HTTP_BAD_REQUEST);
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

    #[Route('/order', name: 'product_order_cart', methods: ['GET', 'POST'])]
    public function orderProducts(Request $request, OrderRepository $orderRepository, ProductRepository $productRepository, OrderProductsRepository $orderProductsRepository): Response
    {
        if($request->get('productId')){

            $product = $productRepository->find($request->get('productId'));

            $order = new Order();
            $form = $this->createForm(OrderType::class, $order);
            $form->handleRequest($request);
            $form->get('productId')->setData($request->get('productId'));

            return $this->render('order/index.html.twig', [
                'form' => $form->createView(),
                'price' => $product->getDiscount() ? $product->getPrice() : $product->getDiscount(),
                'product' => $product
            ]);
        }

        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        $token = $this->container->get('security.token_storage')->getToken();
        if($token !== null){
            $user = $token->getUser();
            $userCarts =  $user->getCarts();
            $price = 0;
            foreach ($userCarts as $userCart){
                $orderProduct = new OrderProducts();
                $orderProduct->setProduct($userCart->getProduct());
                $orderProduct->setOrderName($order);
                $orderProductsRepository->save($orderProduct);
                $price+= $userCart->getProduct()->getDiscount() == 0 ? $userCart->getProduct()->getPrice() : $userCart->getProduct()->getDiscount();
            }
            $order->setUser($user);
        }else {
            $product = $productRepository->find($form->get('productId')->getData());
            $price = $product->getDiscount() == 0 ? $product->getPrice() : $product->getDiscount();
            $orderProduct = new OrderProducts();
            $orderProduct->setProduct($product);
            $orderProduct->setOrderName($order);
        }



        if($form->isSubmitted()){

            $order->setDate(new \DateTime());
            $order->setTotalPrice($price);
            $order->setpaidFor(false);

            $orderRepository->save($order);
            $orderProductsRepository->save($orderProduct,true);


            return $this->render('order/buy.html.twig', [
                'orderId' => $order->getId()
            ]);
        }

        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'price' => $price,
            'userCarts' => $userCarts
        ]);
    }

    #[Route('/buy', name: 'product_buy', methods: ['POST'])]
    public function buyProducts(Request $request, OrderRepository $orderRepository, CartRepository $cartRepository): Response
    {
        $order = $orderRepository->find($request->get('orderId'));

        $order->setpaidFor(true);
        $orderRepository->save($order, true);
        $token = $this->container->get('security.token_storage')->getToken();
        if($token !== null) {
            $user = $token->getUser();

            $userCarts = $user->getCarts();
            foreach ($userCarts as $userCart) {

                $cartRepository->remove($userCart, true);
            }
        }
        $response = ['answer' => 'success'];

        return new Response(json_encode([$response, Response::HTTP_OK, ['content-type' => 'text/html']]));
    }
}

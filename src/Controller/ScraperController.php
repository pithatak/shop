<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\WebScraper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ScraperController extends AbstractController
{
    private $scraper;

    public function __construct(WebScraper $scraper)
    {
        $this->scraper = $scraper;
    }

    /**
     * @Route("/scrape/Dashumiaocoin", name="scrapeDashumiaocoin")
     */
    public function scrapeDashumiaocoin(ProductRepository $productRepository): Response
    {
//        $url = 'https://www.dashumiaocoin.com';
        $url = 'https://www.dashumiaocoin.com/Products/list-r1.html';

        $data = $this->scraper->scrapeDashumiaocoinProducts($url);
        $brochures_directory = $this->getParameter('brochures_directory');
        $user = $this->getUser();

        foreach ($data as $productData) {
//            dump( $product['image_url']);
            $product = new Product();
            $product->setName($productData['name']);
            $product->setPrice(explode (' ', $productData['price'])[1]);
//             dump($test);
            $bruchureName = $this->scraper->downloadImage($brochures_directory, $productData['image_url']);
            $product->setBrochureFilename($bruchureName);
            $product->setUser($user);
            $productRepository->save($product, true);
        }
         dump($data);
        return $this->redirectToRoute('sonata_admin_dashboard');
    }

    /**
     * @Route("/scrape/Amazon", name="scrapeAmazon")
     */
    public function scrapeAmazon(ProductRepository $productRepository): Response
    {
//        $url = 'https://www.dashumiaocoin.com';
        $url = 'https://www.amazon.pl/gp/bestsellers?ref_=nav_cs_bestsellers';

        $data = $this->scraper->scrapeAmazonProducts($url);
//        dump($data);
//        exit();
        $brochures_directory = $this->getParameter('brochures_directory');
        $user = $this->getUser();

        foreach ($data as $productData) {
            $product = new Product();
            $product->setName($productData['name']);
// Извлекаем числовую часть
            $price = str_replace(',', '.', explode(' ', $productData['price'])[0]);

            preg_match('/[\d,]+/', $price, $matches);
            $numbers = $matches[0];


            $product->setPrice($numbers);
            $bruchureName = $this->scraper->downloadImage($brochures_directory, $productData['image_url']);
            $product->setBrochureFilename($bruchureName);
            $product->setUser($user);
            $productRepository->save($product, true);
        }
//        dump($data);
//        exit();
        return $this->redirectToRoute('sonata_admin_dashboard');
    }

    /**
     * @Route("/scrape/Prom", name="/scrape/Prom")
     */
    public function scrapeProm(ProductRepository $productRepository): Response
    {
        $url = 'https://prom.ua/sc/military';

        $data = $this->scraper->scrapePromProducts($url);
//        dump($data);
//        foreach ($data as $productData) {
//
//            $price = str_replace(' ', '',$productData['price']);
//
//            preg_match('/[\d,]+/',  $price , $matches);
//        dump($matches[0]);
//
//        }
//
//        exit();

        $brochures_directory = $this->getParameter('brochures_directory');
        $user = $this->getUser();

        foreach ($data as $productData) {
            $product = new Product();
            $product->setName($productData['name']);
            if ($productData['price']){
                $price = str_replace(' ', '',$productData['price']);

                preg_match('/[\d,]+/',  $price , $matches);
                $product->setPrice($matches[0]);
            }else {
                $product->setPrice(0);

            }
            if ($productData['discount']) {
                $discount = str_replace(' ', '',$productData['discount']);

                preg_match('/[\d,]+/',  $discount , $matches);
                $product->setDiscount($matches[0]);

            }
            $bruchureName = $this->scraper->downloadImage($brochures_directory, $productData['image_url']);
            $product->setBrochureFilename($bruchureName);
            $product->setUser($user);
            $productRepository->save($product, true);
        }

        return $this->redirectToRoute('sonata_admin_dashboard');
    }
}

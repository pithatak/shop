<?php

namespace App\Service;

use Goutte\Client;

class WebScraper
{
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }


    public function scrapeDashumiaocoinProducts($url)
    {

        $crawler = $this->client->request('GET', $url);

        $products = [];

        // Найти все элементы <li> внутри <ul class="pro-list clearfix ">
        //главна страница ul.pro-list li'
        $crawler->filter('ul.common_pro_list1 li')->each(function ($node) use (&$products) {
            if ($node->filter('a.name')->count()){
                $name = $node->filter('a.name')->text();
                $price = $node->filter('div.price')->text();
                $imageUrl = $node->filter('a img')->attr('src');
//            $productUrl = $node->filter('div.pic a')->count() ? 'https://www.dashumiaocoin.com' . $node->filter('div.pic a')->attr('href') : null;

                $products[] = [
                    'name' => $name,
                    'price' => $price,
                    'image_url' => $imageUrl,
//                'product_url' => 'https://www.dashumiaocoin.com' . $productUrl,
                ];
            }
        });

        return $products;
    }

    public function scrapeAmazonProducts($url)
    {

        $crawler = $this->client->request('GET', $url);

        $products = [];

        // Найти все элементы <li> внутри <ul class="pro-list clearfix ">
        //главна страница ul.pro-list li'
        $crawler->filter('ol.a-carousel li')->each(function ($node) use (&$products) {
            if ($node->filter('div.p13n-sc-truncate-desktop-type2')->count()){
                $name = $node->filter('div.p13n-sc-truncate-desktop-type2')->text();
                $price = $node->filter('span._cDEzb_p13n-sc-price_3mJ9Z')->count() ? $node->filter('span._cDEzb_p13n-sc-price_3mJ9Z')->text() : 0;
                $imageUrl = $node->filter('div.a-spacing-mini img')->attr('src');
//            $productUrl = $node->filter('div.pic a')->count() ? 'https://www.dashumiaocoin.com' . $node->filter('div.pic a')->attr('href') : null;

                $products[] = [
                    'name' => $name,
                    'price' => $price,
                    'image_url' => $imageUrl,
//                'product_url' => 'https://www.dashumiaocoin.com' . $productUrl,
                ];
            }
        });

        return $products;
    }

    public function scrapePromProducts($url)
    {

        $crawler = $this->client->request('GET', $url);

        $products = [];

        // Найти все элементы <li> внутри <ul class="pro-list clearfix ">
        //главна страница ul.pro-list li'
        $crawler->filter('div.JicYY')->each(function ($node) use (&$products) {
            if ($node->filter('picture img')->count()){
                $name = $node->filter('picture img')->attr('alt');
                //іногда чогось бувають пустими
                $discount = $node->filter('.AJ5TG [data-qaid="product_price"]')->count() ? $node->filter('.AJ5TG [data-qaid="product_price"]')->text() : NULL;
                $price = $node->filter('.AJ5TG [data-qaid="old_price"]')->count() ? $node->filter('.AJ5TG [data-qaid="old_price"]')->text() : NULL;
                $imageUrl = $node->filter('picture img')->attr('src');
//            $productUrl = $node->filter('div.pic a')->count() ? 'https://www.dashumiaocoin.com' . $node->filter('div.pic a')->attr('href') : null;

                $products[] = [
                    'name' => $name,
                    'price' => $price,
                    'discount' => $discount,
                    'image_url' => $imageUrl,
//                'product_url' => 'https://www.dashumiaocoin.com' . $productUrl,
                ];
            }
        });

        return $products;
    }

    public function downloadImage($brochures_directory, $imageUrl )
    {
        // Получить содержимое изображения
        $imageContent = file_get_contents($imageUrl);

        if ($imageContent === false) {
            throw new \Exception("Не удалось скачать изображение по URL: $imageUrl");
        }

        // Если имя файла не указано, используем имя из URL
//        if (!$fileName) {
//            $fileName = basename($imageUrl); // Извлекает имя файла из URL
//        }

        $slug = strtolower($imageUrl);

        $slug = preg_replace('/[\s_]+/', '-', $slug);

        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);

        $safeFilename = trim($slug, '-');

        $newFilename = $safeFilename . '-' . uniqid() . '.jpg';

        // Полный путь для сохранения изображения
        $fullPath = $brochures_directory . DIRECTORY_SEPARATOR . $newFilename;

        // Сохранение изображения на сервере
        $result = file_put_contents($fullPath, $imageContent);

        if ($result === false) {
            throw new \Exception("Не удалось сохранить изображение: $fullPath");
        }

        return $newFilename; // Возвращает полный путь к сохраненному файлу
    }

}
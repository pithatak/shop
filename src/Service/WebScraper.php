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


    public function scrapeProducts($url)
    {

        $crawler = $this->client->request('GET', $url);

        $products = [];

        // Найти все элементы <li> внутри <ul class="pro-list clearfix ">
        $crawler->filter('ul.pro-list li')->each(function ($node) use (&$products) {
            $name = $node->filter('a.name')->count() ? $node->filter('a.name')->text() : 'Название отсутствует';
            $price = $node->filter('span.price')->count() ? $node->filter('span.price')->text() : 'Цена отсутствует';
            $imageUrl = $node->filter('div.pic img')->count() ? $node->filter('div.pic img')->attr('src') : null;
            $productUrl = $node->filter('div.pic a')->count() ? 'https://www.dashumiaocoin.com' . $node->filter('div.pic a')->attr('href') : null;

            $products[] = [
                'name' => $name,
                'price' => $price,
                'image_url' => $imageUrl,
                'product_url' => 'https://www.dashumiaocoin.com' . $productUrl,
            ];
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
        dump('$full path = ');
        dump($fullPath);

        // Сохранение изображения на сервере
        $result = file_put_contents($fullPath, $imageContent);

        if ($result === false) {
            throw new \Exception("Не удалось сохранить изображение: $fullPath");
        }

        return $fullPath; // Возвращает полный путь к сохраненному файлу
    }

}
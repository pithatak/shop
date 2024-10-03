<?php

namespace App\Controller;

use App\Service\WebScraper;
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
     * @Route("/scrape", name="scrape")
     */
    public function scrape(): Response
    {
        $url = 'https://www.dashumiaocoin.com';
        $data = $this->scraper->scrapeProducts($url);
        $brochures_directory = $this->getParameter('brochures_directory');
        foreach ($data as $d){
            $this->scraper->downloadImage($brochures_directory, $d);//ще не готове і непровірене

        }
        return $this->json($data);
    }
}

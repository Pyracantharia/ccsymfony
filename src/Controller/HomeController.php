<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ArticleRepository;
use App\Service\WeatherService;
class HomeController extends AbstractController
{

    private $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    #[Route('/', name: 'app_home')]
    public function index(ArticleRepository $articleRepository, WeatherService $weatherService): Response
    {
        // Récupérer tous les articles
        $articles = $articleRepository->findAll();
        $weatherData = $weatherService->getWeatherData('Paris');
    
        return $this->render('home/index.html.twig', [
            'articles' => $articles, // Passer les articles à la vue
            'weather' => $weatherData,
        ]);
    }
    
}

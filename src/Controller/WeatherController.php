<?php



namespace App\Controller;

use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WeatherController extends AbstractController
{
    private $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    #[Route('/weather', name: 'app_weather')]
    public function index(Request $request): Response
    {
        // Récupérer la ville de la query string, par défaut Paris
        $city = $request->query->get('city', 'Paris');  

        try {
            // Récupérer les données météo pour la ville
            $weatherData = $this->weatherService->getWeatherData($city);


            return $this->render('weather/index.html.twig', [
                'city' => $city,
                'weather' => $weatherData,  // Passer les données météo à la vue
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Could not fetch weather data.');
            return $this->redirectToRoute('app_home');  // Redirige vers la page d'accueil si erreur
        }
    }
}

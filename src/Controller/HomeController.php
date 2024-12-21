<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ArticleRepository;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ArticleRepository $articleRepository): Response
    {
        // Récupérer tous les articles
        $articles = $articleRepository->findAll();
    
        return $this->render('home/index.html.twig', [
            'articles' => $articles, // Passer les articles à la vue
        ]);
    }
    
}

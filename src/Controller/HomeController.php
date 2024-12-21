<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ArticleRepository;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findAll(); // Récupère tous les articles
    
        return $this->render('home/index.html.twig', [
            'articles' => $articles,
        ]);
    }
    
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Article;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function profile(EntityManagerInterface $entityManager): Response
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();

        // Récupère les articles de l'utilisateur
        $articles = $entityManager->getRepository(Article::class)->findBy(['author' => $user]);

        return $this->render('profile/index.html.twig', [
            'articles' => $articles, // Passe les articles au template
        ]);
    }
}
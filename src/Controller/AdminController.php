<?php


namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        // Récupère tous les articles
        $articles = $entityManager->getRepository(Article::class)->findAll();

        return $this->render('admin/dashboard.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/admin/article/{id}/delete', name: 'admin_article_delete')]
    public function delete(Article $article, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($article);
        $entityManager->flush();

        $this->addFlash('success', 'Article supprimé avec succès!');
        return $this->redirectToRoute('admin_dashboard');
    }
}

<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;



final class ArticleController extends AbstractController
{
    #[Route('/article/create', name: 'app_article_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Attribuer l'utilisateur connecté comme auteur de l'article
            $article->setAuthor($this->getUser());
            $article->setPublishedAt(new \DateTimeImmutable());

            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash('success', 'Article créé avec succès!');
            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }

        return $this->render('article/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/article/{id}', name: 'app_article_show')]
    public function show(Article $article, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Vérifie si l'utilisateur est banni
        if ($this->getUser() && in_array('ROLE_BANNED', $this->getUser()->getRoles())) {
            $this->addFlash('error', 'You are banned and cannot leave a comment.');
            return $this->redirectToRoute('app_home'); // Rediriger vers la page d'accueil ou autre
        }

        // Si l'utilisateur n'est pas banni, permettre de laisser un commentaire
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ajouter le commentaire à l'article et à l'utilisateur
            $comment->setArticle($article);
            $comment->setAuthor($this->getUser());
            $comment->setPublishedAt(new \DateTimeImmutable());

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Your comment has been posted!');
            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
            'commentForm' => $form->createView(),
        ]);
    }




    #[Route('/article/{id}/comment', name: 'app_article_comment', methods: ['POST'])]
    public function comment(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        // Si l'utilisateur est banni, redirige avec un message d'erreur
        if ($this->getUser() && in_array('ROLE_BANNED', $this->getUser()->getRoles())) {
            $this->addFlash('error', 'You are banned and cannot leave a comment.');
            return $this->redirectToRoute('app_home');
        }
    
        // Crée un nouveau commentaire
        $comment = new Comment();
        $comment->setArticle($article);
        $comment->setAuthor($this->getUser());
        $comment->setContent($request->request->get('comment_content'));
        $comment->setPublishedAt(new \DateTimeImmutable());
    
        $entityManager->persist($comment);
        $entityManager->flush();
    
        $this->addFlash('success', 'Your comment has been posted!');
        return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
    }
    




#[Route('/article/{articleId}/comment/{commentId}/delete', name: 'app_comment_delete')]
public function deleteComment(int $articleId, int $commentId, EntityManagerInterface $entityManager): Response
{
    // Récupère l'article et le commentaire en fonction des IDs
    $article = $entityManager->getRepository(Article::class)->find($articleId);
    $comment = $entityManager->getRepository(Comment::class)->find($commentId);

    // Vérifie si l'article et le commentaire existent
    if (!$article || !$comment) {
        throw $this->createNotFoundException('Article ou commentaire non trouvé');
    }

    // Vérifie que l'utilisateur est administrateur
    if (!$this->isGranted('ROLE_ADMIN')) {
        throw new AccessDeniedException('Vous n\'êtes pas autorisé à supprimer ce commentaire.');
    }

    // Supprimer le commentaire
    $entityManager->remove($comment);
    $entityManager->flush();

    $this->addFlash('success', 'Le commentaire a été supprimé avec succès.');

    // Redirige vers l'article
    return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
}

    #[Route('/article/{id}/edit', name: 'app_article_edit')]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        // Vérifie si l'utilisateur peut modifier cet article
        if ($article->getAuthor() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à modifier cet article.');
        }
    
        // Créer le formulaire pour l'article
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarde les modifications dans la base de données
            $entityManager->flush();
            // Message de succès
            $this->addFlash('success', 'Article mis à jour avec succès!');
            // Redirige vers la page de l'article
            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }
    
        // Affiche le formulaire d'édition et passe l'article
        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article, // Assure-toi de passer l'article à la vue
        ]);
    }

    #[Route('/article/{id}/delete', name: 'app_article_delete')]
    public function delete(Article $article, EntityManagerInterface $entityManager): Response
    {
        // Vérifie si l'utilisateur peut supprimer cet article
        if ($article->getAuthor() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à supprimer cet article.');
        }

        $entityManager->remove($article);
        $entityManager->flush();

        $this->addFlash('success', 'Article supprimé avec succès!');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/articles', name: 'app_article_index')]
    public function index(ArticleRepository $articleRepository): Response
    {
        // Récupérer tous les articles
        $articles = $articleRepository->findAll();

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }
}

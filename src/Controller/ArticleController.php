<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/article')]
class ArticleController extends AbstractController
{
    #[Route('/', name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request,
    EntityManagerInterface $entityManager,
    SluggerInterface $slugger): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('picture')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move(
                        $this->getParameter('article_picture_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $article->setPicture($newFilename);
            }

            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Votre article a bien été ajouté'
            );

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_show', methods: ['GET', 'POST'])]
    public function show(Article $article, Request $request, EntityManagerInterface $entityManager): Response
    {

        $comment = new Comment;
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        // dd($form->getErrors());

        if($form->isSubmitted() && $form->isValid()) {

            $comment->setUser($this->getUser());
            $comment->setArticle($article);
            $comment->setDate(new \DateTime);
            $comment->setIsVerified(false);

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Votre commentaire a bien été ajouté !'
            );

        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
            'commentForm' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();


            $this->addFlash(
                'success',
                'Votre article a bien été modifié !'
            );

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        $this->addFlash(
            'success',
            'Votre article a bien été supprimé !'
        );

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/category/{id_category}', name: 'app_get_article_by_category', methods: ['GET'])]
    public function getArticleByCategory(EntityManagerInterface $entityManager, int $id_category): Response
    {

        $articles = $entityManager->getRepository(Article::class)->findBy(array("category" => $id_category));

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

}

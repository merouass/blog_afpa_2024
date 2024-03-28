<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Translation\TranslatableMessage;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ArticleRepository $articleRepository,
    CategoryRepository $categoryRepository,
    PaginatorInterface $paginator,
    Request $request): Response
    {
        $message    = new TranslatableMessage('symfony is great');

        $articles = $paginator->paginate(
            $articleRepository->findAll(), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            2 /*limit per page*/
        );

        return $this->render('home/index.html.twig', [
            'message' =>$message,
            'categories' => $categoryRepository->findAll(),
            'articles' => $articles,

        ]);
    }

    #[Route('/filter/{filter}', name: 'app_home_filter')]
    public function getArticlesByFilter(
    CategoryRepository $categoryRepository,
    ArticleRepository $articleRepository,
    Request $request,
    string $filter,
    SerializerInterface $serializer
    ): JsonResponse
    {

        $articlesData = [];

        foreach ($articleRepository->findArticlesByFilter($filter) as $article) {

            $articleData = [
                'id' => $article->getId(),
                'title' => $article->getTitle(),
                'description' => $article->getDescription(),
                'picture' => $article->getPicture(),
                'date' => $article->getDate()->format('Y-m-d'),
                'category_id' => $article->getCategory() ? $article->getCategory()->getId() : null,
                'category_name' => $article->getCategory() ? $article->getCategory()->getTitle() : null,
            ];
        
            // Ajoutez le tableau simplifié de l'article au tableau des articles
            $articlesData[] = $articleData;
        }
        
        // Utilisez JsonResponse pour retourner le tableau d'articles en JSON
        return new JsonResponse($articlesData);

    }

    #[Route('/search', name: 'app_search_articles', methods: ['GET'])]
    public function getArticlesBySearch(ArticleRepository $articleRepository,
    Request $request,
    PaginatorInterface $paginator): Response
    {

        // si j'ai un paramètre GET search
        if($request->query->has("search")) {

            $search = strtolower($request->query->get("search"));

            $articles = $paginator->paginate(
                $articleRepository->findArticlesBySearch($search), /* query NOT result */
                $request->query->getInt('page', 1), /*page number*/
                2 /*limit per page*/
            );

            
            return $this->render('article/index.html.twig', [
                'articles' => $articles,
            ]);

        } else {
            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }


    }
}

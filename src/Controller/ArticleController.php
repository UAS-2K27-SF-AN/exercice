<?php
namespace App\Controller;
use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
class ArticleController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->redirectToRoute('article_index');
    }
    #[Route('/articles/', name: 'article_index', methods: ['GET'])]
    public function index(ArticleRepository $articles): Response
    {
        return $this->render('articles/index.html.twig', [
            'articles' => $articles->findAll(),
        ]);
    }
    #[Route('/articles/add', name: 'article_add', methods: ['GET', 'POST'])]
    public function add(Request $request, ArticleRepository $articles): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $articles->save($article);
            return $this->redirectToRoute('article_index');
        }
        return $this->render('articles/form.html.twig', [
            'form' => $form->createView(),
            'page_title' => 'Ajouter un article',
            'button_label' => 'Créer',
        ]);
    }
    #[Route('/articles/{id<\d+>}', name: 'article_show', methods: ['GET'])]
    public function show(int $id, ArticleRepository $articles): Response
    {
        $article = $articles->find($id);
        if (!$article) {
            throw $this->createNotFoundException('Article introuvable.');
        }
        return $this->render('articles/show.html.twig', [
            'article' => $article,
        ]);
    }
    #[Route('/articles/edit/{id<\d+>}', name: 'article_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, ArticleRepository $articles): Response
    {
        $article = $articles->find($id);
        if (!$article) {
            throw $this->createNotFoundException('Article introuvable.');
        }
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $articles->save($article);
            return $this->redirectToRoute('article_index');
        }
        return $this->render('articles/form.html.twig', [
            'form' => $form->createView(),
            'page_title' => 'Modifier un article',
            'button_label' => 'Enregistrer',
        ]);
    }
    #[Route('/articles/delete/{id<\d+>}', name: 'article_delete', methods: ['GET', 'POST'])]
    public function delete(int $id, ArticleRepository $articles): Response
    {
        $article = $articles->find($id);
        if ($article) {
            $articles->delete($article);
        }
        return $this->redirectToRoute('article_index');
    }
}

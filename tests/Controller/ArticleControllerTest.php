<?php

namespace App\Tests\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ArticleControllerTest extends WebTestCase
{
    private ArticleRepository $repository;
    private string $tempFile;
    private $client;

    protected function setUp(): void
    {
        $this->tempFile = sys_get_temp_dir().'/controller_test_'.uniqid('', true).'.json';
        file_put_contents($this->tempFile, '[]');
        $this->client = self::createClient();
        $this->repository = $this->client->getContainer()->get(ArticleRepository::class);
        $ref = new \ReflectionProperty(ArticleRepository::class, 'file');
        $ref->setAccessible(true);
        $ref->setValue($this->repository, $this->tempFile);
    }

    protected function tearDown(): void
    {
        if (is_file($this->tempFile)) {
            unlink($this->tempFile);
        }
        parent::tearDown();
    }

    public function testIndexRendersList(): void
    {
        $this->repository->save(new Article(null, 'Titre', 'Contenu', 'Auteur'));

        $this->client->request('GET', '/articles/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Articles');
        $this->assertSelectorTextContains('table', 'Titre');
    }

    public function testAddGetRendersForm(): void
    {
        $this->client->request('GET', '/articles/add');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Ajouter un article');
        $this->assertSelectorExists('form[name="article"]');
    }

    public function testAddPostCreatesArticle(): void
    {
        $crawler = $this->client->request('GET', '/articles/add');
        $form = $crawler->selectButton('Créer')->form();
        $form['article[title]'] = 'Nouvel article';
        $form['article[content]'] = 'Contenu du nouvel article.';
        $form['article[author]'] = 'Auteur';
        $this->client->submit($form);

        $this->assertResponseRedirects('/articles/');
        $this->client->followRedirect();
        $this->assertSame('Nouvel article', $this->repository->find(1)->title);
    }

    public function testShowRendersArticle(): void
    {
        $article = new Article(null, 'Article démo', 'Contenu démo', 'Auteur démo');
        $this->repository->save($article);

        $this->client->request('GET', '/articles/'.(int) $article->id);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Détails de l\'article');
        $this->assertSelectorTextContains('body', 'Article démo');
    }

    public function testShowReturns404ForUnknownId(): void
    {
        $this->client->request('GET', '/articles/99999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testEditGetRendersForm(): void
    {
        $article = new Article(null, 'À modifier', 'Contenu', 'Auteur');
        $this->repository->save($article);

        $this->client->request('GET', '/articles/edit/'.(int) $article->id);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifier un article');
        $this->assertSelectorExists('input[name="article[title]"]');
    }

    public function testEditPostUpdatesArticle(): void
    {
        $article = new Article(null, 'Titre', 'Contenu', 'Auteur');
        $this->repository->save($article);
        $id = (int) $article->id;

        $crawler = $this->client->request('GET', '/articles/edit/'.$id);
        $form = $crawler->selectButton('Enregistrer')->form();
        $form['article[title]'] = 'Titre modifié';
        $this->client->submit($form);

        $this->assertResponseRedirects('/articles/');
        $this->client->followRedirect();
        $this->assertSame('Titre modifié', $this->repository->find($id)->title);
    }

    public function testDeleteRemovesArticle(): void
    {
        $article = new Article(null, 'À supprimer', 'Contenu', 'Auteur');
        $this->repository->save($article);
        $id = (int) $article->id;

        $this->client->request('GET', '/articles/delete/'.$id);

        $this->assertResponseRedirects('/articles/');
        $this->assertNull($this->repository->find($id));
    }
}

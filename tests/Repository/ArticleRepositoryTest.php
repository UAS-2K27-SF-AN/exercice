<?php
namespace App\Tests\Repository;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use PHPUnit\Framework\TestCase;
class ArticleRepositoryTest extends TestCase
{
    private ArticleRepository $repository;
    private string $tempFile;
    protected function setUp(): void
    {
        $this->tempFile = sys_get_temp_dir().'/articles_test_'.uniqid('', true).'.json';
        file_put_contents($this->tempFile, '[]');
        $this->repository = new ArticleRepository($this->tempFile);
    }
    protected function tearDown(): void
    {
        if (is_file($this->tempFile)) {
            unlink($this->tempFile);
        }
    }
    public function testFindAllReturnsDefaultArticlesWhenFileEmpty(): void
    {
        $all = $this->repository->findAll();
        $this->assertCount(3, $all);
        $this->assertSame(1, $all[0]->id);
        $this->assertSame('Marwen', $all[0]->title);
    }
    public function testSaveAssignsIdAndFindAllReturnsIt(): void
    {
        $article = new Article(null, 'Titre', 'Contenu', 'Auteur');
        $this->repository->save($article);
        $this->assertNotNull($article->id);
        $this->assertSame(4, $article->id);
        $found = $this->repository->find(4);
        $this->assertNotNull($found);
        $this->assertSame('Titre', $found->title);
    }
    public function testSaveIncrementsId(): void
    {
        $a1 = new Article(null, 'A', 'B', 'C');
        $a2 = new Article(null, 'D', 'E', 'F');
        $this->repository->save($a1);
        $this->repository->save($a2);
        $this->assertSame(4, $a1->id);
        $this->assertSame(5, $a2->id);
    }
    public function testFindReturnsArticleById(): void
    {
        $found = $this->repository->find(1);
        $this->assertNotNull($found);
        $this->assertSame('Marwen', $found->title);
    }
    public function testFindReturnsNullForUnknownId(): void
    {
        $this->assertNull($this->repository->find(999));
    }
    public function testSaveUpdatesExistingArticle(): void
    {
        $article = $this->repository->find(1);
        $this->assertNotNull($article);
        $article->title = 'Titre modifié';
        $this->repository->save($article);
        $updated = $this->repository->find(1);
        $this->assertSame('Titre modifié', $updated->title);
    }
    public function testDeleteRemovesArticle(): void
    {
        $article = $this->repository->find(1);
        $this->assertNotNull($article);
        $this->repository->delete($article);
        $this->assertNull($this->repository->find(1));
    }
    public function testFindAllIsSortedByIdAscending(): void
    {
        $all = $this->repository->findAll();
        $this->assertSame(1, $all[0]->id);
        $this->assertSame(2, $all[1]->id);
        $this->assertSame(3, $all[2]->id);
    }
}

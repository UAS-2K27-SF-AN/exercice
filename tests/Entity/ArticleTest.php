<?php

namespace App\Tests\Entity;

use App\Entity\Article;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    public function testConstructWithDefaults(): void
    {
        $article = new Article();
        $this->assertNull($article->id);
        $this->assertSame('', $article->title);
        $this->assertSame('', $article->content);
        $this->assertSame('', $article->author);
        $this->assertInstanceOf(\DateTimeImmutable::class, $article->createdAt);
    }

    public function testConstructWithProvidedValues(): void
    {
        $createdAt = new \DateTimeImmutable('2025-01-01');
        $article = new Article(5, 'Titre', 'Contenu', 'Auteur', $createdAt);
        $this->assertSame(5, $article->id);
        $this->assertSame('Titre', $article->title);
        $this->assertSame('Contenu', $article->content);
        $this->assertSame('Auteur', $article->author);
        $this->assertSame($createdAt, $article->createdAt);
    }
}
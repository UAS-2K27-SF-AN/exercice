<?php
namespace App\Repository;
use App\Entity\Article;
class ArticleRepository
{
    public const DEFAULT_DATA = [
        [
            'id' => 1,
            'title' => 'Marwen',
            'content' => 'Premier article de test.',
            'author' => 'MBA',
            'createdAt' => '2025-11-01T09:00:00+01:00',
        ],
        [
            'id' => 2,
            'title' => 'Introduction à Twig',
            'content' => 'Twig est un moteur de templates utilisé par Symfony.',
            'author' => 'Bob',
            'createdAt' => '2025-11-02T10:00:00+01:00',
        ],
        [
            'id' => 3,
            'title' => 'Comprendre les routes Symfony',
            'content' => 'Comprendre les routes et les contrôleurs dans Symfony.',
            'author' => 'Charlie',
            'createdAt' => '2025-11-03T11:00:00+01:00',
        ],
    ];

    private string $file;
    public function __construct(string $articlesFile)
    {
        $this->file = $articlesFile;
    }
    /** @return Article[] */
    public function findAll(): array
    {
        $rows = $this->readRows();
        $articles = array_map(fn (array $row) => $this->hydrate($row), $rows);
        usort($articles, fn (Article $a, Article $b) => $a->id <=> $b->id);
        return $articles;
    }
    public function find(int $id): ?Article
    {
        foreach ($this->readRows() as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return $this->hydrate($row);
            }
        }

        return null;
    }
    public function save(Article $article): void
    {
        $rows = $this->readRows();
        if ($article->id === null) {
            $article->id = $rows === [] ? 1 : max(array_map(fn (array $r) => (int) $r['id'], $rows)) + 1;
            $article->createdAt ??= new \DateTimeImmutable();
            $rows[] = $this->dehydrate($article);
        } else {
            $found = false;
            foreach ($rows as $index => $row) {
                if ((int) $row['id'] === $article->id) {
                    $article->createdAt ??= new \DateTimeImmutable((string) ($row['createdAt'] ?? 'now'));
                    $rows[$index] = $this->dehydrate($article);
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $rows[] = $this->dehydrate($article);
            }
        }
        $this->writeRows($rows);
    }
    public function delete(Article $article): void
    {
        $rows = array_values(array_filter(
            $this->readRows(),
            fn (array $row) => (int) $row['id'] !== $article->id,
        ));
        $this->writeRows($rows);
    }
    /** @return array<int, array<string, mixed>> */
    private function readRows(): array
    {
        if (!is_file($this->file)) {
            $this->writeRows(self::DEFAULT_DATA);
            return self::DEFAULT_DATA;
        }
        $json = @file_get_contents($this->file);
        if ($json === false || trim($json) === '' || trim($json) === '[]') {
            $this->writeRows(self::DEFAULT_DATA);
            return self::DEFAULT_DATA;
        }

        try {
            $rows = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            return is_array($rows) ? $rows : [];
        } catch (\Throwable) {
            return self::DEFAULT_DATA;
        }
    }
    /** @param array<int, array<string, mixed>> $rows */
    private function writeRows(array $rows): void
    {
        $directory = dirname($this->file);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
        $json = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        if (file_put_contents($this->file, $json.PHP_EOL, LOCK_EX) === false) {
            throw new \RuntimeException('Impossible d’enregistrer les articles dans le fichier JSON.');
        }
    }
    private function hydrate(array $row): Article
    {
        return new Article(
            (int) $row['id'],
            (string) $row['title'],
            (string) $row['content'],
            (string) $row['author'],
            new \DateTimeImmutable((string) $row['createdAt']),
        );
    }
    private function dehydrate(Article $article): array
    {
        return [
            'id' => $article->id,
            'title' => $article->title,
            'content' => $article->content,
            'author' => $article->author,
            'createdAt' => $article->createdAt->format(DATE_ATOM),
        ];
    }
}

<?php
namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;
class Article
{
    public function __construct(
        public ?int $id = null,
        #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
        #[Assert\Length(max: 150, maxMessage: 'Le titre ne doit pas dépasser {{ limit }} caractères.')]
        public string $title = '',
        #[Assert\NotBlank(message: 'Le contenu est obligatoire.')]
        public string $content = '',
        #[Assert\NotBlank(message: 'Le nom de l’auteur est obligatoire.')]
        #[Assert\Length(max: 100, maxMessage: 'Le nom ne doit pas dépasser {{ limit }} caractères.')]
        public string $author = '',
        public ?\DateTimeImmutable $createdAt = null,
    ) {
        $this->createdAt ??= new \DateTimeImmutable();
    }
}

<?php
namespace App\Models;

class Article
{

    private string $title;
    private string $description;
    private ?string $id=null;
    private ?string $userId=null;

    public function __construct(string $title, string $description, ?string $id=null, ?string $userId=null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->userId = $userId;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }
}
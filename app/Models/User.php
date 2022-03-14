<?php

namespace App\Models;

class User
{
    private string $email;
    private string $password;
    private string $name;
    private string $surname;
    private string $birthday;
    private ?string $createdAt=null;
    private ?string $id = null;

    public function __construct(string $email, string $password, string $name, string $surname,
                                string $birthday, ?string $createdAt=null, ?string $id=null)
    {

        $this->name = $name;
        $this->surname = $surname;
        $this->birthday = $birthday;
        $this->email = $email;
        $this->password = $password;
        $this->createdAt = $createdAt;
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function getBirthday(): string
    {
        return $this->birthday;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getId(): ?string
    {
        return $this->id;
    }
}
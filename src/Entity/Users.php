<?php

declare(strict_types=1);

namespace CSL\Entity;

use CSL\Repository\UsersRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'users')]
#[Entity(repositoryClass: UsersRepository::class)]
class Users
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue]
    private int $id;

    #[Column(name: 'name', type: Types::STRING, length: 100)]
    private string $name;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}

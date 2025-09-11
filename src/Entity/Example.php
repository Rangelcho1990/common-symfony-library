<?php

declare(strict_types=1);

namespace CSL\Entity;

use CSL\Repository\ExampleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'examples')]
#[Entity(repositoryClass: ExampleRepository::class)]
class Example
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

    public function setId(int $id): Example
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Example
    {
        $this->name = $name;

        return $this;
    }
}

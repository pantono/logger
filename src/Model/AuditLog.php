<?php

namespace Pantono\Logger\Model;

use Pantono\Contracts\Attributes\NoSave;
use Pantono\Contracts\Attributes\Filter;
use Pantono\Database\Traits\SavableModel;

class AuditLog
{
    use SavableModel;

    private ?int $id = null;
    private string $model;
    private string $modelId;
    private \DateTimeInterface $date;
    private int $userId;
    #[NoSave]
    private ?string $userName = null;
    private string $entry;
    /**
     * @var array<string, mixed>|null
     */
    #[Filter('json_decode')]
    private ?array $previousState = null;
    /**
     * @var array<string, mixed>|null
     */
    #[Filter('json_decode')]
    private ?array $newState = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    public function getModelId(): string
    {
        return $this->modelId;
    }

    public function setModelId(string $modelId): void
    {
        $this->modelId = $modelId;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): void
    {
        $this->date = $date;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(?string $userName): void
    {
        $this->userName = $userName;
    }

    public function getEntry(): string
    {
        return $this->entry;
    }

    public function setEntry(string $entry): void
    {
        $this->entry = $entry;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPreviousState(): ?array
    {
        return $this->previousState;
    }

    /**
     * @param array<string, mixed>|null $previousState
     */
    public function setPreviousState(?array $previousState): void
    {
        $this->previousState = $previousState;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getNewState(): ?array
    {
        return $this->newState;
    }

    /**
     * @param array<string, mixed>|null $newState
     */
    public function setNewState(?array $newState): void
    {
        $this->newState = $newState;
    }
}

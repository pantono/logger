<?php

namespace Pantono\Logger\Filter;

use Pantono\Contracts\Filter\PageableInterface;
use Pantono\Database\Traits\Pageable;

class AuditLogFilter implements PageableInterface
{
    use Pageable;

    private ?\DateTimeInterface $dateFrom = null;
    private ?\DateTimeInterface $dateTo = null;
    private ?string $model = null;
    private ?string $modelId = null;
    private ?int $userId = null;

    public function getDateFrom(): ?\DateTimeInterface
    {
        return $this->dateFrom;
    }

    public function setDateFrom(?\DateTimeInterface $dateFrom): void
    {
        $this->dateFrom = $dateFrom;
    }

    public function getDateTo(): ?\DateTimeInterface
    {
        return $this->dateTo;
    }

    public function setDateTo(?\DateTimeInterface $dateTo): void
    {
        $this->dateTo = $dateTo;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): void
    {
        $this->model = $model;
    }

    public function getModelId(): ?string
    {
        return $this->modelId;
    }

    public function setModelId(?string $modelId): void
    {
        $this->modelId = $modelId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }
}

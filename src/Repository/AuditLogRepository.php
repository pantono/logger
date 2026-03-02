<?php

namespace Pantono\Logger\Repository;

use Pantono\Database\Repository\DefaultRepository;
use Pantono\Logger\Model\AuditLog;
use Pantono\Logger\Filter\AuditLogFilter;

class AuditLogRepository extends DefaultRepository
{
    /**
     * @return array<string,mixed>|null
     */
    public function getLogById(int $id): ?array
    {
        return $this->selectSingleRow('audit_log', 'id', $id);
    }

    public function saveLog(AuditLog $log): void
    {
        $id = $this->insertOrUpdate('audit_log', 'id', $log->getId(), $log->getAllData());
        if ($id) {
            $log->setId($id);
        }
    }


    /**
     * @return array<int,array<string, mixed>>
     */
    public function getAuditLogByFilter(AuditLogFilter $filter): array
    {
        $select = $this->getDb()->select('l.*', "CONCAT(u.forename, ' ', u.surname) AS user_name")->from('audit_log', 'l')
            ->leftJoin('l', 'user', 'u', 'u.id=l.user_id');

        if ($filter->getDateFrom() !== null) {
            $select->where('l.date >= :date_from')
                ->setParameter('date_from', $filter->getDateFrom()->format('Y-m-d H:i:s'));
        }
        if ($filter->getDateTo() !== null) {
            $select->where('l.date <= :date_to')
                ->setParameter('date_to', $filter->getDateTo()->format('Y-m-d H:i:s'));
        }

        if ($filter->getModel() !== null) {
            $select->where('l.model = :model')
                ->setParameter('model', $filter->getModel());
        }
        if ($filter->getModelId() !== null) {
            $select->where('l.model_id = :model_id')
                ->setParameter('model_id', $filter->getModelId());
        }
        if ($filter->getUserId() !== null) {
            $select->where('l.user_id=:user_id')
                ->setParameter('user_id', $filter->getUserId());
        }
        $this->applyCountAndLimit($select, $filter);

        return $this->getDb()->fetchAll($select);
    }
}

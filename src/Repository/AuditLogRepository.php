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
        $select = $this->getDb()->select()->from('audit_log')
            ->joinLeft('user', 'audit_log.user_id = user.id', ['CONCAT(user.forename, \' \', user.surname) as user_name']);

        if ($filter->getDateFrom() !== null) {
            $select->where('date >= ?', $filter->getDateFrom()->format('Y-m-d H:i:s'));
        }
        if ($filter->getDateTo() !== null) {
            $select->where('date <= ?', $filter->getDateFrom()->format('Y-m-d H:i:s'));
        }

        if ($filter->getModel() !== null) {
            $select->where('model = ?', $filter->getModel());
        }
        if ($filter->getModelId() !== null) {
            $select->where('model_id = ?', $filter->getModelId());
        }
        if ($filter->getUserId() !== null) {
            $select->where('user_id=?', $filter->getUserId());
        }
        $filter->setTotalResults($this->getCount($select));
        $select->limitPage($filter->getPage(), $filter->getPerPage());
        return $this->fetchAll($select);
    }
}

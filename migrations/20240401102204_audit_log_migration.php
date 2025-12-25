<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AuditLogMigration extends AbstractMigration
{
    public function change(): void
    {
        $this->table('audit_log')
            ->addColumn('model', 'string')
            ->addColumn('model_id', 'string')
            ->addColumn('date', 'datetime')
            ->addColumn('user_id', 'integer')
            ->addColumn('entry', 'text')
            ->addColumn('previous_state', 'json', ['null' => true])
            ->addColumn('new_state', 'json', ['null' => true])
            ->addIndex('model')
            ->addIndex('model_id')
            ->create();
    }
}

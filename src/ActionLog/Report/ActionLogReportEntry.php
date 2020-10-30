<?php

namespace App\ActionLog\Report;

use App\Entity\ActionLog;

/**
 * Collection for ActionLog::class grouped by action in a given datetime period.
 */
class ActionLogReportEntry
{
    private \DateTimeInterface $dateFrom;
    private \DateTimeInterface $dateTo;
    /**
     * @var array<ActionLog>
     */
    private array $actionLogsCreate;
    /**
     * @var array<ActionLog>
     */
    private array $actionLogsEdit;
    /**
     * @var array<ActionLog>
     */
    private array $actionLogsDelete;

    /**
     * @param array<ActionLog> $actionLogsCreate
     * @param array<ActionLog> $actionLogsEdit
     * @param array<ActionLog> $actionLogsDelete
     */
    public function __construct(
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo,
        array $actionLogsCreate,
        array $actionLogsEdit,
        array $actionLogsDelete
    ) {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->actionLogsCreate = $actionLogsCreate;
        $this->actionLogsEdit = $actionLogsEdit;
        $this->actionLogsDelete = $actionLogsDelete;
    }

    public function getDateFrom(): \DateTimeInterface
    {
        return $this->dateFrom;
    }

    public function getDateTo(): \DateTimeInterface
    {
        return $this->dateTo;
    }

    /**
     * @return array<ActionLog>
     */
    public function getActionLogsCreate(): array
    {
        return $this->actionLogsCreate;
    }

    /**
     * @return array<ActionLog>
     */
    public function getActionLogsEdit(): array
    {
        return $this->actionLogsEdit;
    }

    /**
     * @return array<ActionLog>
     */
    public function getActionLogsDelete(): array
    {
        return $this->actionLogsDelete;
    }
}

<?php

namespace App\ActionLog\Report;

use App\Entity\ActionLog;
use App\Entity\Admin\User;

/**
 * A report about all action logs of the user in the given \DatePeriod::class.
 */
class ActionLogReport
{
    /**
     * @var \DatePeriod<\DateTimeImmutable>
     */
    private \DatePeriod $datePeriod;
    /**
     * @var array<ActionLog>
     */
    private array $actionLogs;
    /**
     * @var array<ActionLogReportEntry>
     */
    private array $entries;
    private User $user;

    /**
     * @param \DatePeriod<\DateTimeImmutable> $datePeriod
     * @param array<ActionLog>                $actionLogs
     * @param array<ActionLogReportEntry>     $entries
     */
    public function __construct(
        \DatePeriod $datePeriod,
        array $actionLogs,
        array $entries,
        User $user
    ) {
        $this->datePeriod = $datePeriod;
        $this->actionLogs = $actionLogs;
        $this->entries = $entries;
        $this->user = $user;
    }

    /**
     * @return \DatePeriod<\DateTimeImmutable>
     */
    public function getDatePeriod(): \DatePeriod
    {
        return $this->datePeriod;
    }

    /**
     * @return array<ActionLog>
     */
    public function getActionLogs(): array
    {
        return $this->actionLogs;
    }

    /**
     * @return array<ActionLogReportEntry>
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}

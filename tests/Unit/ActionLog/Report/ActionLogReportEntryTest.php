<?php

namespace App\Tests\Unit\ActionLog\Report;

use App\ActionLog\Report\ActionLogReportEntry;
use App\Entity\ActionLog;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\ActionLog\Report\ActionLogReportEntry
 */
class ActionLogReportEntryTest extends TestCase
{
    public function testConstruct(): void
    {
        $dateFrom = new \DateTimeImmutable('11-11-2011');
        $dateTo = new \DateTimeImmutable('11-12-2011');
        $actionLog1 = new ActionLog('action', 'class', 1, null);
        $actionLog2 = new ActionLog('action', 'other_class', 2, null);
        $actionLog3 = new ActionLog('action', 'some_other_class', 3, null);
        $actionLogsCreate = [$actionLog1];
        $actionLogsEdit = [$actionLog2];
        $actionLogsDelete = [$actionLog3];

        $entry = new ActionLogReportEntry($dateFrom, $dateTo, $actionLogsCreate, $actionLogsEdit, $actionLogsDelete);

        $this->assertSame($dateFrom, $entry->getDateFrom());
        $this->assertSame($dateTo, $entry->getDateTo());
        $this->assertSame($actionLogsCreate, $entry->getActionLogsCreate());
        $this->assertSame($actionLogsEdit, $entry->getActionLogsEdit());
        $this->assertSame($actionLogsDelete, $entry->getActionLogsDelete());
    }
}

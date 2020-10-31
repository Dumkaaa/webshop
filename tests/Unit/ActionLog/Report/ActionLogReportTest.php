<?php

namespace App\Tests\Unit\ActionLog\Report;

use App\ActionLog\Report\ActionLogReport;
use App\ActionLog\Report\ActionLogReportEntry;
use App\Entity\ActionLog;
use App\Entity\Admin\User;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\ActionLog\Report\ActionLogReport
 */
class ActionLogReportTest extends TestCase
{
    public function testConstruct(): void
    {
        $dateFrom = new \DateTimeImmutable('11-11-2011');
        $dateTo = new \DateTimeImmutable('11-12-2011');
        $interval = new \DateInterval('P1D');
        $datePeriod = new \DatePeriod($dateFrom, $interval, $dateTo);

        $actionLog1 = new ActionLog('action', 'class', 1, null);
        $actionLog2 = new ActionLog('action', 'other_class', 2, null);
        $actionLogs = [$actionLog1, $actionLog2];

        $entry1 = new ActionLogReportEntry($dateFrom, $dateTo, [], [], []);
        $entry2 = new ActionLogReportEntry($dateFrom, $dateTo, [], [], []);
        $entries = [$entry1, $entry2];

        $user = new User();

        $report = new ActionLogReport($datePeriod, $actionLogs, $entries, $user);

        $this->assertSame($datePeriod, $report->getDatePeriod());
        $this->assertSame($actionLogs, $report->getActionLogs());
        $this->assertSame($entries, $report->getEntries());
        $this->assertSame($user, $report->getUser());
    }
}

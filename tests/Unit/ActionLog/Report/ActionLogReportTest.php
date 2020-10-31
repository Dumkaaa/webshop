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

    /**
     * @covers \App\ActionLog\Report\ActionLogReport::getBarChart
     */
    public function testGetBarChart(): void
    {
        $dateFrom = new \DateTimeImmutable('11-11-2011');
        $dateTo = new \DateTimeImmutable('11-12-2011');
        $interval = new \DateInterval('P1D');
        $datePeriod = new \DatePeriod($dateFrom, $interval, $dateTo);

        $actionLog = new ActionLog('action', 'class', 1, null);
        $entry1 = new ActionLogReportEntry($dateFrom, $dateFrom, [
            $actionLog, $actionLog, $actionLog,
        ], [
            $actionLog, $actionLog,
        ], [
            $actionLog,
        ]);
        $entry2 = new ActionLogReportEntry($dateTo, $dateTo, [
            $actionLog,
        ], [], [
            $actionLog, $actionLog,
        ]);
        $entries = [$entry1, $entry2];

        $report = new ActionLogReport($datePeriod, [], $entries, new User());
        $barChart = $report->getBarChart();

        // Make sure caching works.
        $this->assertSame($barChart, $report->getBarChart());

        $labels = $barChart->getLabels();
        $this->assertCount(2, $labels);
        $this->assertSame('11-11-2011', $labels[0]);
        $this->assertSame('11-12-2011', $labels[1]);

        $bars = $barChart->getBars();
        $this->assertCount(3, $bars);

        $bar1 = $bars[0];
        $this->assertCount(2, $bar1);
        $this->assertSame(3, $bar1[0]);
        $this->assertSame(1, $bar1[1]);

        $bar2 = $bars[1];
        $this->assertCount(2, $bar2);
        $this->assertSame(2, $bar2[0]);
        $this->assertSame(0, $bar2[1]);

        $bar3 = $bars[2];
        $this->assertCount(2, $bar3);
        $this->assertSame(1, $bar3[0]);
        $this->assertSame(2, $bar3[1]);
    }
}

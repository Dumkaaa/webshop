<?php

namespace App\Tests\Unit\ActionLog\Report;

use App\ActionLog\Report\ActionLogReportFactory;
use App\Entity\ActionLog;
use App\Entity\Admin\User;
use App\Repository\ActionLogRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @covers \App\ActionLog\Report\ActionLogReportFactory
 */
class ActionLogReportFactoryTest extends TestCase
{
    /**
     * @covers \App\ActionLog\Report\ActionLogReportFactory::createForUserLastMonth
     */
    public function testCreateForUserLastMonth(): void
    {
        $user = new User();

        $actionLogRepositoryProphecy = $this->prophesize(ActionLogRepository::class);
        $actionLogRepositoryProphecy->findGroupedForUserBetween($user, Argument::type('\DateTimeImmutable'), Argument::type('\DateTimeImmutable'))
            ->shouldBeCalledTimes(1)->willReturn([]);

        $actionLogRepository = $actionLogRepositoryProphecy->reveal();
        $factory = new ActionLogReportFactory($actionLogRepository);

        $report = $factory->createForUserLastMonth($user);

        $expectedDateFrom = new \DateTimeImmutable('midnight 1 month ago');
        $expectedDateTo = new \DateTimeImmutable('now');

        $period = $report->getDatePeriod();
        $this->assertSame($expectedDateFrom->format('d-m-Y H:i:s'), $period->getStartDate()->format('d-m-Y H:i:s'));
        $this->assertSame($expectedDateTo->format('d-m-Y H:i:s'), $period->getEndDate()->format('d-m-Y H:i:s'));
        $this->assertSame(1, $period->getDateInterval()->d);
    }

    /**
     * @covers \App\ActionLog\Report\ActionLogReportFactory::createForUser
     */
    public function testCreateForUser(): void
    {
        $user = new User();
        $dateFrom = new \DateTimeImmutable('11-11-2011');
        $dateTo = new \DateTimeImmutable('11-12-2011');
        $interval = new \DateInterval('P1D');
        $datePeriod = new \DatePeriod($dateFrom, $interval, $dateTo);

        // Create some action logs that should be included in the report.
        $actionLog1 = new ActionLog(ActionLog::ACTION_CREATE, 'object', 1, $user);
        $actionLog1->setCreatedAt(new \DateTimeImmutable('13-11-2011'));
        $actionLog2 = new ActionLog(ActionLog::ACTION_EDIT, 'object', 2, $user);
        $actionLog2->setCreatedAt(new \DateTimeImmutable('19-11-2011'));
        $actionLog3 = new ActionLog(ActionLog::ACTION_DELETE, 'object', 3, $user);
        $actionLog3->setCreatedAt(new \DateTimeImmutable('25-11-2011'));

        // Also add a log that should be excluded because its out of the date period.
        $actionLog4 = new ActionLog(ActionLog::ACTION_CREATE, 'object', 4, $user);
        $actionLog4->setCreatedAt(new \DateTimeImmutable('12-12-2011'));

        $actionLogRepositoryProphecy = $this->prophesize(ActionLogRepository::class);
        $actionLogRepositoryProphecy->findGroupedForUserBetween($user, $dateFrom, $dateTo)->shouldBeCalledTimes(1)->willReturn([
            $actionLog1,
            $actionLog2,
            $actionLog3,
            $actionLog4,
        ]);

        $actionLogRepository = $actionLogRepositoryProphecy->reveal();
        $factory = new ActionLogReportFactory($actionLogRepository);

        $report = $factory->createForUser($user, $datePeriod);

        $this->assertSame($datePeriod, $report->getDatePeriod());
        $this->assertSame([
            $actionLog1,
            $actionLog2,
            $actionLog3,
        ], $report->getActionLogs());
        $this->assertCount(30, $report->getEntries());
        $this->assertSame($user, $report->getUser());

        // Check the entries.
        foreach ($report->getEntries() as $index => $entry) {
            $createdCount = 0;
            $editedCount = 0;
            $deletedCount = 0;
            if (2 === $index) { // 13-11-2011
                $createdCount = 1;
                $this->assertSame([$actionLog1], $entry->getActionLogsCreate());
            } elseif (8 === $index) { // 19-11-2011
                $editedCount = 1;
                $this->assertSame([$actionLog2], $entry->getActionLogsEdit());
            } elseif (14 === $index) { // 25-11-2011
                $deletedCount = 1;
                $this->assertSame([$actionLog3], $entry->getActionLogsDelete());
            }

            $this->assertCount($createdCount, $entry->getActionLogsCreate());
            $this->assertCount($editedCount, $entry->getActionLogsEdit());
            $this->assertCount($deletedCount, $entry->getActionLogsDelete());
        }
    }
}

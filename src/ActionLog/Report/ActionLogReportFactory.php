<?php

namespace App\ActionLog\Report;

use App\Entity\ActionLog;
use App\Entity\Admin\User;
use App\Repository\ActionLogRepository;

/**
 * Factory for ActionLogReport::class instances.
 */
class ActionLogReportFactory
{
    private ActionLogRepository $actionLogRepository;

    public function __construct(ActionLogRepository $actionLogRepository)
    {
        $this->actionLogRepository = $actionLogRepository;
    }

    /**
     * Helper method to call createForUser with a date period for the last month with a daily interval.
     */
    public function createForUserLastMonth(User $user): ActionLogReport
    {
        $dateFrom = new \DateTimeImmutable('midnight 1 month ago');
        $dateTo = new \DateTimeImmutable('now');
        $interval = new \DateInterval('P1D');

        return $this->createForUser($user, new \DatePeriod($dateFrom, $interval, $dateTo));
    }

    /**
     * Creates a report for the given user in the given date period.
     *
     * @param \DatePeriod<\DateTimeImmutable> $period
     */
    public function createForUser(User $user, \DatePeriod $period): ActionLogReport
    {
        $startDate = $period->getStartDate();
        $endDate = $period->getEndDate();
        $interval = $period->getDateInterval();

        /**
         * Find all action logs for the user in this period grouped by the action, user, object class & createdAt.
         * This way bulk actions won't be seen as loads of different actions and distort the report data.
         */
        $actionLogs = $this->actionLogRepository->createQueryBuilder('a')
            ->where('IDENTITY(a.user) = :userId')
            ->andWhere('a.createdAt >= :startDate')
            ->andWhere('a.createdAt < :endDate')
            ->setParameters([
                'userId' => $user->getId(),
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->groupBy('a.action, a.user, a.objectClass, a.createdAt')
            ->orderBy('a.createdAt')
            ->getQuery()
            ->getResult();

        // Generate report entries.
        $entries = [];
        $actionLogCount = count($actionLogs);
        $actionLogIndex = 0; // Use this to keep track of the action log index in the period foreach loop.

        /** @var \DateTimeImmutable $dateFrom */
        foreach ($period as $dateFrom) {
            // Calculate the to date.
            $dateTo = $dateFrom->add($interval);

            // Group the action logs in this date period by the action.
            $actionLogsCreate = [];
            $actionLogsEdit = [];
            $actionLogsDelete = [];

            while ($actionLogIndex < $actionLogCount) {
                /** @var ActionLog $actionLog */
                $actionLog = $actionLogs[$actionLogIndex];
                if ($actionLog->getCreatedAt() >= $dateTo) {
                    // The latest action log is after this date period, break.
                    break;
                }

                // Add the action log to the correct action array.
                switch ($actionLog->getAction()) {
                    case ActionLog::ACTION_CREATE:
                        $actionLogsCreate[] = $actionLog;
                        break;
                    case ActionLog::ACTION_EDIT:
                        $actionLogsEdit[] = $actionLog;
                        break;
                    case ActionLog::ACTION_DELETE:
                        $actionLogsDelete[] = $actionLog;
                        break;
                }

                // Continue to the next action log.
                ++$actionLogIndex;
            }

            // Add the entry.
            $entries[] = new ActionLogReportEntry($dateFrom, $dateTo, $actionLogsCreate, $actionLogsEdit, $actionLogsDelete);
        }

        // Create the report itself.
        return new ActionLogReport($period, $actionLogs, $entries, $user);
    }
}

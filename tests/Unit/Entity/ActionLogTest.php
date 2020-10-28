<?php

namespace App\Tests\Unit\Entity;

use App\Entity\ActionLog;
use App\Entity\ActionLogChange;
use App\Entity\Admin\User;
use App\Timestampable\TimestampableInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Entity\ActionLog
 */
class ActionLogTest extends TestCase
{
    public function testConstruct(): void
    {
        $actionLog = new ActionLog(ActionLog::ACTION_CREATE, 'class', 11, null);

        $this->assertNull($actionLog->getId());
        $this->assertSame(ActionLog::ACTION_CREATE, $actionLog->getAction());
        $this->assertSame('class', $actionLog->getObjectClass());
        $this->assertSame(11, $actionLog->getObjectId());
        $this->assertNull($actionLog->getUser());
        $this->assertEmpty($actionLog->getChanges());
        $this->assertInstanceOf(TimestampableInterface::class, $actionLog);
    }

    public function testConstructWithUser(): void
    {
        $user = new User();
        $actionLogWithUser = new ActionLog(ActionLog::ACTION_EDIT, 'class', 1, $user);
        $this->assertSame(ActionLog::ACTION_EDIT, $actionLogWithUser->getAction());
        $this->assertSame($user, $actionLogWithUser->getUser());
    }

    /**
     * @covers \App\Entity\ActionLog::getChanges
     * @covers \App\Entity\ActionLog::addChange
     */
    public function testChanges(): void
    {
        $actionLog = new ActionLog(ActionLog::ACTION_DELETE, 'class', 22, null);
        $this->assertEmpty($actionLog->getChanges());
        
        $change = new ActionLogChange($actionLog, 'property', 'foo', 'bar');
        // Make sure constructing a change with the action log does not add it to the action log yet.
        $this->assertEmpty($actionLog->getChanges());
        
        $actionLog->addChange($change);
        $this->assertNotEmpty($actionLog->getChanges());
        $this->assertContains($change, $actionLog->getChanges());
    }
}
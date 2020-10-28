<?php

namespace App\Tests\Unit\Entity;

use App\Entity\ActionLog;
use App\Entity\ActionLogChange;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Entity\ActionLogChange
 */
class ActionLogChangeTest extends TestCase
{
    public function testConstruct(): void
    {
        $actionLog = new ActionLog(ActionLog::ACTION_CREATE, 'class', 11, null);
        $change = new ActionLogChange($actionLog, 'property', 'foo', 'bar');

        $this->assertNull($change->getId());
        $this->assertSame($change->getActionLog(), $actionLog);
        $this->assertSame('property', $change->getProperty());
        $this->assertSame('foo', $change->getOldValue());
        $this->assertSame('bar', $change->getNewValue());
    }

    public function testConstructNullOldValue(): void
    {
        $actionLog = new ActionLog(ActionLog::ACTION_CREATE, 'class', 11, null);
        $change = new ActionLogChange($actionLog, 'property', null, 'bar');

        $this->assertNull($change->getOldValue());
    }

    public function testConstructNullNewValue(): void
    {
        $actionLog = new ActionLog(ActionLog::ACTION_CREATE, 'class', 11, null);
        $change = new ActionLogChange($actionLog, 'property', 'foo', null);

        $this->assertNull($change->getNewValue());
    }
}
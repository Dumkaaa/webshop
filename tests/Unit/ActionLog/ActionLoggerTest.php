<?php

namespace App\Tests\Unit\ActionLog;

use App\ActionLog\ActionLogger;
use App\ActionLog\LoggableObjectInterface;
use App\Entity\ActionLog;
use App\Entity\Admin\User;
use App\Exception\InvalidActionLogActionException;
use App\Exception\NonPersistedActionLogObjectException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @covers \App\ActionLog\ActionLogger
 */
class ActionLoggerTest extends TestCase
{
    /**
     * @covers \App\ActionLog\ActionLogger::createLog
     * @covers \App\ActionLog\LoggableObjectInterface::getNonLoggableProperties
     */
    public function testCreateLog(): void
    {
        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(3)->willReturn(null);

        $objectProphecy = $this->prophesize(LoggableObjectInterface::class);
        $objectProphecy->getId()->shouldBeCalledTimes(3)->willReturn(11);
        $objectProphecy->getNonLoggableProperties()->shouldBeCalledTimes(3)->willReturn([
            'nonLoggable',
        ]);
        $object = $objectProphecy->reveal();

        $logger = new ActionLogger($securityProphecy->reveal());

        $createLog = $logger->createLog(ActionLog::ACTION_CREATE, $object);
        $this->assertSame($createLog->getAction(), ActionLog::ACTION_CREATE);
        $this->assertSame($createLog->getObjectClass(), get_class($object));
        $this->assertSame($createLog->getObjectId(), 11);
        $this->assertNull($createLog->getUser());
        $this->assertEmpty($createLog->getChanges());

        $editLog = $logger->createLog(ActionLog::ACTION_CREATE, $object, [
            'nonLoggable' => ['Foo', 'Bar'],
            'name' => ['Old', 'New'],
            'country' => ['the', 'Netherlands'],
        ]);
        $this->assertSame($editLog->getAction(), ActionLog::ACTION_CREATE);
        $this->assertSame($editLog->getObjectClass(), get_class($object));
        $this->assertSame($editLog->getObjectId(), 11);
        $this->assertNull($editLog->getUser());
        $this->assertCount(2, $editLog->getChanges());
        $changes = $editLog->getChanges()->toArray();
        $this->assertSame($editLog, $changes[0]->getActionLog());
        $this->assertSame('name', $changes[0]->getProperty());
        $this->assertSame('Old', $changes[0]->getOldValue());
        $this->assertSame('New', $changes[0]->getNewValue());
        $this->assertSame($editLog, $changes[1]->getActionLog());
        $this->assertSame('country', $changes[1]->getProperty());
        $this->assertSame('the', $changes[1]->getOldValue());
        $this->assertSame('Netherlands', $changes[1]->getNewValue());

        $deleteLog = $logger->createLog(ActionLog::ACTION_DELETE, $object);
        $this->assertSame($deleteLog->getAction(), ActionLog::ACTION_DELETE);
        $this->assertSame($deleteLog->getObjectClass(), get_class($object));
        $this->assertSame($deleteLog->getObjectId(), 11);
        $this->assertNull($deleteLog->getUser());
        $this->assertEmpty($deleteLog->getChanges());
    }

    /**
     * @covers \App\ActionLog\ActionLogger::createLog
     */
    public function testCreateLogWithUser(): void
    {
        $user = new User();
        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($user);

        $objectProphecy = $this->prophesize(LoggableObjectInterface::class);
        $objectProphecy->getId()->shouldBeCalledTimes(1)->willReturn(11);
        $objectProphecy->getNonLoggableProperties()->shouldBeCalledTimes(1)->willReturn([]);
        $object = $objectProphecy->reveal();

        $logger = new ActionLogger($securityProphecy->reveal());

        $createLog = $logger->createLog(ActionLog::ACTION_CREATE, $object);
        $this->assertSame($user, $createLog->getUser());
    }

    /**
     * @covers \App\ActionLog\ActionLogger::createLog
     */
    public function testCreateLogWithInvalidUser(): void
    {
        $user = $this->prophesize(UserInterface::class)->reveal();
        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($user);

        $objectProphecy = $this->prophesize(LoggableObjectInterface::class);
        $objectProphecy->getId()->shouldBeCalledTimes(1)->willReturn(11);
        $objectProphecy->getNonLoggableProperties()->shouldBeCalledTimes(1)->willReturn([]);
        $object = $objectProphecy->reveal();

        $logger = new ActionLogger($securityProphecy->reveal());

        $createLog = $logger->createLog(ActionLog::ACTION_CREATE, $object);
        $this->assertNull($createLog->getUser());
    }

    /**
     * @covers \App\ActionLog\ActionLogger::createLog
     * @covers \App\Exception\InvalidActionLogActionException
     */
    public function testCreateLogInvalidAction(): void
    {
        $security = $this->prophesize(Security::class)->reveal();
        $logger = new ActionLogger($security);

        $this->expectExceptionMessage(InvalidActionLogActionException::class);
        $this->expectExceptionMessage('Invalid action "foo".');

        $object = $this->prophesize(LoggableObjectInterface::class)->reveal();
        $logger->createLog('foo', $object);
    }

    /**
     * @covers \App\ActionLog\ActionLogger::createLog
     * @covers \App\Exception\NonPersistedActionLogObjectException
     */
    public function testCreateLogNonPersistedObject(): void
    {
        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn(null);

        $objectProphecy = $this->prophesize(LoggableObjectInterface::class);
        $objectProphecy->getId()->shouldBeCalledTimes(1)->willReturn(null);
        $object = $objectProphecy->reveal();
        $logger = new ActionLogger($securityProphecy->reveal());

        $this->expectException(NonPersistedActionLogObjectException::class);
        $this->expectExceptionMessage('To log an object it must have an id before persisting, consider using Uuid.');

        $logger->createLog(ActionLog::ACTION_CREATE, $object);
    }

    /**
     * @covers \App\ActionLog\ActionLogger::transformValue
     */
    public function testTransformValue(): void
    {
        $security = $this->prophesize(Security::class)->reveal();
        $logger = new ActionLogger($security);

        $this->assertSame('foo', $logger->transformValue('foo'));
        $this->assertNull($logger->transformValue(null));
        $this->assertSame('11', $logger->transformValue(11));
        $this->assertSame('01-11-2011 23:24:25', $logger->transformValue(new \DateTimeImmutable('01-11-2011 23:24:25')));
        $this->assertSame('true', $logger->transformValue(true));
        $this->assertSame('false', $logger->transformValue(false));
        $this->assertSame('{"foo":"bar"}', $logger->transformValue([
            'foo' => 'bar',
        ]));
    }
}

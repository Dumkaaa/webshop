<?php

namespace App\Tests\Unit\Admin\EventSubscriber;

use App\Admin\EventSubscriber\RequestSubscriber;
use App\Entity\Admin\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Security;

class RequestSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $this->assertSame([
            RequestEvent::class => 'onKernelRequest',
        ], RequestSubscriber::getSubscribedEvents());
    }

    public function testOnKernelRequest(): void
    {
        $eventProphecy = $this->prophesize(RequestEvent::class);
        $eventProphecy->isMasterRequest()->shouldBeCalledTimes(1)->willReturn(true);

        $securityProphecy = $this->prophesize(Security::class);

        $entityManager = $this->prophesize(EntityManagerInterface::class)->reveal();

        $subscriber = new RequestSubscriber($securityProphecy->reveal(), $entityManager);

        // Test if the user's activity will get updated.
        $user = new User();
        $this->assertNull($user->getLastActiveAt());
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($user);
        $subscriber->onKernelRequest($eventProphecy->reveal());

        $this->assertNotNull($user->getLastActiveAt());
        $this->assertSame((new \DateTimeImmutable())->format('d-m-Y'), $user->getLastActiveAt()->format('d-m-Y'));
    }
}

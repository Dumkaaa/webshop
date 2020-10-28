<?php

namespace App\Tests\Unit\Timestampable;

use App\Entity\Admin\User;
use App\Timestampable\TimestampableSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Timestampable\TimestampableSubscriber
 */
class TimestampableSubscriberTest extends TestCase
{
    /**
     * @covers \App\Timestampable\TimestampableSubscriber::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        $subscriber = new TimestampableSubscriber();

        $this->assertSame($subscriber->getSubscribedEvents(), [
            Events::prePersist,
            Events::preUpdate,
        ]);
    }

    /**
     * @covers \App\Timestampable\TimestampableSubscriber::prePersist
     */
    public function testPrePersist(): void
    {
        // $entity can be any entity that implements TimestampableInterface.
        $entity = new User();

        $subscriber = new TimestampableSubscriber();
        $entityManager = $this->prophesize(EntityManagerInterface::class)->reveal();
        $args = new LifecycleEventArgs($entity, $entityManager);

        // Set the createdAt and lastUpdatedAt to a value before today to make sure they get updated by the subscriber.
        $entity->setCreatedAt(new \DateTimeImmutable('11-11-2011'));
        $entity->setLastUpdatedAt(new \DateTimeImmutable('11-11-2011'));

        $subscriber->prePersist($args);

        // Both dates should be updated after persisting.
        $currentDate = new \DateTimeImmutable();
        $this->assertSame($entity->getCreatedAt()->format('d-m-Y'), $currentDate->format('d-m-Y'));
        $this->assertSame($entity->getLastUpdatedAt()->format('d-m-Y'), $currentDate->format('d-m-Y'));
    }

    /**
     * @covers \App\Timestampable\TimestampableSubscriber::preUpdate
     */
    public function testPreUpdate(): void
    {
        // $entity can be any entity that implements TimestampableInterface.
        $entity = new User();

        $subscriber = new TimestampableSubscriber();
        $entityManager = $this->prophesize(EntityManagerInterface::class)->reveal();
        $args = new LifecycleEventArgs($entity, $entityManager);

        // Set the createdAt and lastUpdatedAt to a value before today to make sure they get updated by the subscriber.
        $entity->setCreatedAt(new \DateTimeImmutable('11-11-2011'));
        $entity->setLastUpdatedAt(new \DateTimeImmutable('11-11-2011'));

        $subscriber->preUpdate($args);

        // Only lastUpdatedAt should be updated after updating.
        $currentDate = new \DateTimeImmutable();
        $this->assertSame($entity->getCreatedAt()->format('d-m-Y'), '11-11-2011');
        $this->assertSame($entity->getLastUpdatedAt()->format('d-m-Y'), $currentDate->format('d-m-Y'));
    }
}
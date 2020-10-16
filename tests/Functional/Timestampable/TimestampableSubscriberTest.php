<?php

namespace App\Tests\Functional\Timestampable;

use App\Entity\Admin\User;
use App\Tests\Functional\EntityManagerTest;
use App\Timestampable\TimestampableSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class TimestampableSubscriberTest extends EntityManagerTest
{
    public function testGetSubscribedEvents(): void
    {
        $subscriber = new TimestampableSubscriber();

        $this->assertSame($subscriber->getSubscribedEvents(), [
            Events::prePersist,
            Events::preUpdate,
        ]);
    }

    public function testPrePersist(): void
    {
        // $entity can be any entity that implements TimestampableInterface.
        $entity = new User();

        $subscriber = new TimestampableSubscriber();
        $args = new LifecycleEventArgs($entity, $this->entityManager);

        // Set the createdAt and lastUpdatedAt to a value before today to make sure they get updated by the subscriber.
        $entity->setCreatedAt(new \DateTimeImmutable('11-11-2011'));
        $entity->setLastUpdatedAt(new \DateTimeImmutable('11-11-2011'));

        $subscriber->prePersist($args);

        // Both dates should be updated after persisting.
        $currentDate = new \DateTimeImmutable();
        $this->assertSame($entity->getCreatedAt()->format('d-m-Y'), $currentDate->format('d-m-Y'));
        $this->assertSame($entity->getLastUpdatedAt()->format('d-m-Y'), $currentDate->format('d-m-Y'));
    }

    public function testPreUpdate(): void
    {
        // $entity can be any entity that implements TimestampableInterface.
        $entity = new User();

        $subscriber = new TimestampableSubscriber();
        $args = new LifecycleEventArgs($entity, $this->entityManager);

        // Set the createdAt and lastUpdatedAt to a value before today to make sure they get updated by the subscriber.
        $entity->setCreatedAt(new \DateTimeImmutable('11-11-2011'));
        $entity->setLastUpdatedAt(new \DateTimeImmutable('11-11-2011'));

        $subscriber->preUpdate($args);

        // Only lastUpdatedAt should be updated after updating.
        $currentDate = new \DateTimeImmutable();
        $this->assertSame($entity->getCreatedAt()->format('d-m-Y'), '11-11-2011');
        $this->assertSame($entity->getLastUpdatedAt()->format('d-m-Y'), $currentDate->format('d-m-Y'));
    }

    /**
     * Test if the subscriber is subscribed to the doctrine events.
     */
    public function testEvent(): void
    {
        // $entity can be any entity that implements TimestampableInterface.
        $entity = new User();

        // Set the createdAt and lastUpdatedAt to a value before today to make sure they get updated by the subscriber.
        $entity->setCreatedAt(new \DateTimeImmutable('11-11-2011'));
        $entity->setLastUpdatedAt(new \DateTimeImmutable('11-11-2011'));

        $this->entityManager->persist($entity);

        // Both dates should be updated after persisting.
        $currentDate = new \DateTimeImmutable();
        $this->assertSame($entity->getCreatedAt()->format('d-m-Y'), $currentDate->format('d-m-Y'));
        $this->assertSame($entity->getLastUpdatedAt()->format('d-m-Y'), $currentDate->format('d-m-Y'));
    }
}
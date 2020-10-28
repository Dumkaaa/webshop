<?php

namespace App\Tests\Functional\ActionLog;

use App\Entity\ActionLog;
use App\Entity\Admin\User;
use App\Repository\ActionLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @covers \App\ActionLog\ActionLogSubscriber
 */
class ActionLogSubscriberTest extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $kernel->getContainer()->get('doctrine');

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();

        $this->entityManager = $entityManager;

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
    }

    /**
     * Test if the subscriber is subscribed to the doctrine events.
     *
     * @covers \App\ActionLog\ActionLogSubscriber::onFlush
     * @covers \App\ActionLog\ActionLogSubscriber::postFlush
     */
    public function testEvent(): void
    {
        // $entity can be any entity that implements LoggableInterface.
        $entity = new User();
        $entity->setFirstName('Action')
            ->setLastName('Log')
            ->setEmailAddress('action@log.com')
            ->setPassword('test');

        /** @var ActionLogRepository $actionLogRepository */
        $actionLogRepository = $this->entityManager->getRepository(ActionLog::class);
        $actionLogCount = $actionLogRepository->count([]);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        // One new event should be created upon creation.
        $this->assertSame($actionLogCount + 1, $actionLogRepository->count([]));

        $entity->setFirstName('Foo');
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        // One new event should be created upon editing.
        $this->assertSame($actionLogCount + 2, $actionLogRepository->count([]));

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        // One new event should be created upon deleting.
        $this->assertSame($actionLogCount + 3, $actionLogRepository->count([]));
    }
}

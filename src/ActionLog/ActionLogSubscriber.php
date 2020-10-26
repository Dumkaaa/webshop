<?php

namespace App\ActionLog;

use App\Entity\ActionLog;
use App\Entity\ActionLogChange;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;

/**
 * Logs the create, edit and delete actions of \App\ActionLog\LoggableObjectInterface::class instances.
 */
class ActionLogSubscriber implements EventSubscriber
{
    private ActionLogger $actionLogger;
    /** @var array<LoggableObjectInterface> */
    private array $loggableInsertions = [];

    public function __construct(ActionLogger $actionLogger)
    {
        $this->actionLogger = $actionLogger;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
            Events::postFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $entityManager = $args->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $logClassMetadata = $entityManager->getClassMetadata(ActionLog::class);
        $changeClassMetadata = $entityManager->getClassMetadata(ActionLogChange::class);

        // Log insertions for post flush.
        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof LoggableObjectInterface) {
                $this->loggableInsertions[] = $entity;
            }
        }

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof LoggableObjectInterface) {
                // Remove non loggable properties from the change set that's being passed to the action logger.
                $changeSet = $unitOfWork->getEntityChangeSet($entity);
                foreach ($entity->getNonLoggableProperties() as $property) {
                    unset($changeSet[$property]);
                }

                // Make sure the entity still has loggable changes left after removing the non loggable properties.
                if (count($changeSet) > 0) {
                    $actionLog = $this->actionLogger->createLog(ActionLog::ACTION_EDIT, $entity, $changeSet);
                    $entityManager->persist($actionLog);
                    $unitOfWork->computeChangeSet($logClassMetadata, $actionLog);

                    foreach ($actionLog->getChanges() as $change) {
                        $unitOfWork->computeChangeSet($changeClassMetadata, $change);
                    }
                }
            }
        }

        foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof LoggableObjectInterface) {
                $actionLog = $this->actionLogger->createLog(ActionLog::ACTION_DELETE, $entity);
                $entityManager->persist($actionLog);
                $unitOfWork->computeChangeSet($logClassMetadata, $actionLog);
            }
        }
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        if (0 === count($this->loggableInsertions)) {
            return;
        }

        // Log insertions post flush so the id is known.
        $entityManager = $args->getEntityManager();
        foreach ($this->loggableInsertions as $entity) {
            $actionLog = $this->actionLogger->createLog(ActionLog::ACTION_CREATE, $entity);
            $entityManager->persist($actionLog);
        }

        // Reset the loggable insertions to prevent an infinite loop.
        $this->loggableInsertions = [];

        $entityManager->flush();
    }
}

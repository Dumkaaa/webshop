<?php

namespace App\ActionLog;

use App\Entity\ActionLog;
use App\Entity\ActionLogChange;
use App\Entity\Admin\User;
use App\Exception\InvalidActionLogActionException;
use App\Exception\NonPersistedActionLogObjectException;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Security\Core\Security;

/**
 * Logs actions and their changes of given objects.
 */
class ActionLogger
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * Creates an action log for the action for the given object.
     *
     * @param array<mixed> $changeSet
     *
     * @throws InvalidActionLogActionException
     * @throws NonPersistedActionLogObjectException
     */
    public function createLog(string $action, LoggableObjectInterface $object, array $changeSet = []): ActionLog
    {
        // Validate the action.
        if (!in_array($action, [
            ActionLog::ACTION_CREATE,
            ActionLog::ACTION_EDIT,
            ActionLog::ACTION_DELETE,
        ])) {
            throw new InvalidActionLogActionException($action);
        }

        // Get the user that performed the action.
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            // Ignore other user instances.
            $user = null;
        }

        // Retrieve the id.
        $id = $object->getId();
        if (null === $id) {
            throw new NonPersistedActionLogObjectException('To log an object it must have an id before persisting, consider using Uuid.');
        }

        // Create the action.
        $actionLog = new ActionLog($action, ClassUtils::getClass($object), $id, $user);

        // Create the changes.
        foreach ($changeSet as $property => $values) {
            $oldValue = $this->transformValue($values[0]);
            $newValue = $this->transformValue($values[1]);
            $actionLogChange = new ActionLogChange($actionLog, $property, $oldValue, $newValue);
            $actionLog->addChange($actionLogChange);
        }

        return $actionLog;
    }

    /**
     * Transforms the given value to null or a string.
     *
     * @param mixed $value
     */
    private function transformValue($value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('d-m-Y H:i:s');
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_array($value)) {
            return (string) json_encode($value);
        }

        return null !== $value ? (string) $value : null;
    }
}

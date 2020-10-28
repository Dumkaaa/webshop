<?php

namespace App\DataFixtures;

use App\ActionLog\LoggableObjectInterface;
use App\DataFixtures\Admin\UserFixtures;
use App\Entity\ActionLog;
use App\Entity\ActionLogChange;
use App\Entity\Admin\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures for \App\Entity\Admin\ActionLog::class and \App\Entity\Admin\ActionLogChange::class.
 */
class ActionLogFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return [
            FixtureGroupInterface::ADMIN,
            FixtureGroupInterface::ADMIN_LOGS,
        ];
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var User $admin */
        $admin = $this->getReference(UserFixtures::REFERENCE_ADMIN);

        /** @var User $user */
        $user = $this->getReference(UserFixtures::REFERENCE_USER);
        $manager->persist($this->generateActionLog(ActionLog::ACTION_CREATE, $user, $admin));
        $manager->persist($this->generateActionLog(ActionLog::ACTION_EDIT, $user, $admin, [
            [
                'firstName' => ['Foo', 'Bar'],
                'lastName' => ['Last', 'Example'],
            ],
        ]));

        /** @var User $disabledUser */
        $disabledUser = $this->getReference(UserFixtures::REFERENCE_DISABLED);
        $manager->persist($this->generateActionLog(ActionLog::ACTION_CREATE, $disabledUser, $admin));
        $manager->persist($this->generateActionLog(ActionLog::ACTION_EDIT, $disabledUser, $admin, [
            [
                'isEnabled' => ['true', 'false'],
            ],
        ]));

        $manager->flush();
    }

    /**
     * @param array<array<array<mixed>>> $changeSetCollection
     */
    private function generateActionLog(string $action, LoggableObjectInterface $object, ?User $user = null, array $changeSetCollection = []): ActionLog
    {
        $actionLog = new ActionLog($action, ClassUtils::getClass($object), $object->getId(), $user);

        foreach ($changeSetCollection as $changeSet) {
            foreach ($changeSet as $property => $values) {
                $actionLog->addChange(new ActionLogChange($actionLog, $action, $values[0], $values[1]));
            }
        }

        return $actionLog;
    }
}

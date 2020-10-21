<?php

namespace App\Admin\EventSubscriber;

use App\Entity\Admin\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Security;

class RequestSubscriber implements EventSubscriberInterface
{
    private Security $security;
    private EntityManagerInterface $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->isMasterRequest()) {
            $user = $this->security->getUser();
            if ($user && $user instanceof User) {
                // Update the user's lastActiveAt if longer than 1 minute ago (to prevent a db update on multiple requests in the same minute).
                if (!$user->getLastActiveAt() || $user->getLastActiveAt() < new \DateTimeImmutable('1 minute ago')) {
                    $user->setLastActiveAt(new \DateTimeImmutable());
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                }
            }
        }
    }
}

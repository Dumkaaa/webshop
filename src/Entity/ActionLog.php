<?php

namespace App\Entity;

use App\Entity\Admin\User;
use App\Repository\ActionLogRepository;
use App\Timestampable\TimestampableEntity;
use App\Timestampable\TimestampableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Log of an action in the system.
 *
 * @ORM\Entity(repositoryClass=ActionLogRepository::class)
 */
class ActionLog implements TimestampableInterface
{
    use TimestampableEntity;

    const ACTION_CREATE = 'create';
    const ACTION_EDIT = 'edit';
    const ACTION_DELETE = 'delete';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private string $action;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private string $objectClass;

    /**
     * @ORM\Column(type="integer")
     */
    private int $objectId;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private ?User $user;

    /**
     * @ORM\OneToMany(targetEntity=ActionLogChange::class, mappedBy="actionLog", cascade={"persist"})
     *
     * @var Collection<ActionLogChange>
     */
    private Collection $changes;

    public function __construct(string $action, string $objectClass, int $objectId, ?User $user)
    {
        $this->action = $action;
        $this->objectClass = $objectClass;
        $this->objectId = $objectId;
        $this->user = $user;

        $this->changes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getObjectClass(): string
    {
        return $this->objectClass;
    }

    public function getObjectId(): int
    {
        return $this->objectId;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @return Collection<ActionLogChange>
     */
    public function getChanges(): Collection
    {
        return $this->changes;
    }

    public function addChange(ActionLogChange $change): self
    {
        if (!$this->changes->contains($change)) {
            $this->changes[] = $change;
        }

        return $this;
    }
}

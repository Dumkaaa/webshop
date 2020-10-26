<?php

namespace App\Entity;

use App\Repository\ActionLogChangeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * A change caused by an \App\Entity\ActionLog::class.
 *
 * @ORM\Entity(repositoryClass=ActionLogChangeRepository::class)
 */
class ActionLogChange
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity=ActionLog::class, inversedBy="changes")
     * @ORM\JoinColumn(nullable=false)
     */
    private ActionLog $actionLog;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private string $property;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $oldValue;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $newValue;

    public function __construct(ActionLog $actionLog, string $property, ?string $oldValue, ?string $newValue)
    {
        $this->actionLog = $actionLog;
        $this->property = $property;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActionLog(): ActionLog
    {
        return $this->actionLog;
    }

    public function setActionLog(ActionLog $actionLog): self
    {
        $this->actionLog = $actionLog;

        return $this;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function setProperty(string $property): self
    {
        $this->property = $property;

        return $this;
    }

    public function getOldValue(): ?string
    {
        return $this->oldValue;
    }

    public function setOldValue(?string $oldValue): self
    {
        $this->oldValue = $oldValue;

        return $this;
    }

    public function getNewValue(): ?string
    {
        return $this->newValue;
    }

    public function setNewValue(?string $newValue): self
    {
        $this->newValue = $newValue;

        return $this;
    }
}

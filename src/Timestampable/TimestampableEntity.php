<?php

namespace App\Timestampable;

use Doctrine\ORM\Mapping as ORM;

/**
 * When used together with TimestampableInterface::class on a doctrine entity, the createdAt and lastUpdatedAt properties will be automatically updated
 * by the TimestampableSubscriber::class.
 */
trait TimestampableEntity
{
    /**
     * @ORM\Column(type="datetime")
     */
    protected \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    protected \DateTimeInterface $lastUpdatedAt;

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getLastUpdatedAt(): \DateTimeInterface
    {
        return $this->lastUpdatedAt;
    }

    public function setLastUpdatedAt(\DateTimeInterface $lastUpdatedAt): void
    {
        $this->lastUpdatedAt = $lastUpdatedAt;
    }
}

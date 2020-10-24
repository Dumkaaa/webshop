<?php

namespace App\Timestampable;

/**
 * Keep track of the creation and update timestamps of the instance.
 */
interface TimestampableInterface
{
    public function getCreatedAt(): \DateTimeInterface;

    public function setCreatedAt(\DateTimeInterface $createdAt): void;

    public function getLastUpdatedAt(): \DateTimeInterface;

    public function setLastUpdatedAt(\DateTimeInterface $lastUpdatedAt): void;
}

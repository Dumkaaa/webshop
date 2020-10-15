<?php

namespace App\Timestampable;

interface TimestampableInterface
{
    public function getCreatedAt(): ?\DateTimeInterface;

    public function setCreatedAt(\DateTimeInterface $createdAt): void;

    public function getLastUpdatedAt(): ?\DateTimeInterface;

    public function setLastUpdatedAt(\DateTimeInterface $lastUpdatedAt): void;
}

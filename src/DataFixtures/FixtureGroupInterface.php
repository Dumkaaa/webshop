<?php

namespace App\DataFixtures;

interface FixtureGroupInterface extends \Doctrine\Bundle\FixturesBundle\FixtureGroupInterface
{
    const ADMIN = 'admin';
    const ADMIN_USER = 'admin_user';
    const ADMIN_LOGS = 'admin_logs';
}
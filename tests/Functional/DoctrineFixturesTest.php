<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;

abstract class DoctrineFixturesTest extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->loadFixtures();

        parent::setUp();
    }

    protected function loadFixtures(): void
    {
        $application = new Application($this->client->getKernel());
        $application->setAutoExit(false);

        $command = 'doctrine:fixtures:load -n --quiet';

        foreach ($this->getFixtureGroups() as $group) {
            $command .= ' --group='.$group;
        }

        $application->run(new StringInput($command));
    }

    /**
     * @return array<string>
     */
    abstract protected function getFixtureGroups(): array;
}

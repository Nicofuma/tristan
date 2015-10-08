<?php

namespace SensioLabs\JobBoardBundle\TestsFunctional\Command;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use SensioLabs\JobBoardBundle\Command\DeleteJobsCommand;
use SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM\MixedStatusData;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class DeleteJobsCommandTest extends WebTestCase
{
    public function testExecute()
    {
        $this->loadFixtures([MixedStatusData::class]);

        $kernel = self::createKernel();
        $kernel->boot();

        $application = new Application($kernel);
        $application->add(new DeleteJobsCommand());

        $command = $application->find('jobs:delete');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        self::assertContains('[OK] 1 jobs have been deleted.', $commandTester->getDisplay());
    }
}

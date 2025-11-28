<?php

namespace Beerfranz\DynamicScheduleBundle\Tests\Functional;

use Beerfranz\DynamicScheduleBundle\Entity\Task;
use Beerfranz\DynamicScheduleBundle\Repository\TaskRepository;
use Beerfranz\DynamicScheduleBundle\Service\DynamicScheduleProvider;
use Beerfranz\DynamicScheduleBundle\Tests\TestKernel;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Scheduler\SchedulerInterface;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class StandaloneTest extends KernelTestCase
{
    protected $em = null;

    use ClockSensitiveTrait;
    use InteractsWithMessenger;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->em = $kernel->getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);

        $schemaTool->dropDatabase();
        $schemaTool->createSchema(
            $this->em->getMetadataFactory()->getAllMetadata()
        );

        $this->loadFixtures();

        parent::setUp();
    }

    public function tearDown(): void
    {
        try {
            $this->em->close();
            $this->em = null;
            parent::tearDown();

        } catch(\Exception $e) {
            echo $e->getMessage();
        } finally {
            restore_error_handler();
            restore_exception_handler();
        }
    }

    private function loadFixtures(): void
    {
        $task = (new Task())
            ->setScheduleName('test_schedule')
            ->setMessageClass('Beerfranz\DynamicScheduleBundle\Tests\Message\TestMessage')
            ->setTrigger('every')
            ->setFrequency('2 seconds')
            ->setTransport('sync');
        $this->em->persist($task);

        $disabledTask = (new Task())
            ->setScheduleName('test_schedule')
            ->setMessageClass('Beerfranz\DynamicScheduleBundle\Tests\Message\TestMessage')
            ->setTrigger('every')
            ->setFrequency('1 month')
            ->setTransport('async')
            ->setJitter(5)
            ->hasEnabled(false);
        $this->em->persist($disabledTask);

        $asyncTask = (new Task())
            ->setScheduleName('test_schedule')
            ->setMessageClass('Beerfranz\DynamicScheduleBundle\Tests\Message\TestMessage')
            ->setTrigger('every')
            ->setFrequency('2 seconds')
            ->setTransport('async');
        $this->em->persist($asyncTask);

        $taskOtherSchedule = (new Task())
            ->setScheduleName('test_other_schedule')
            ->setMessageClass('Beerfranz\DynamicScheduleBundle\Tests\Message\TestMessage')
            ->setTrigger('every')
            ->setFrequency('2 seconds')
            ->setTransport('other')
            ->setJitter(0);
        $this->em->persist($taskOtherSchedule);

        $taskBadTransportSchedule = (new Task())
            ->setScheduleName('bad_transport')
            ->setMessageClass('Beerfranz\DynamicScheduleBundle\Tests\Message\TestBadTransportMessage')
            ->setTrigger('every')
            ->setFrequency('1 seconds')
            ->setTransport('not_exists');
        $this->em->persist($taskBadTransportSchedule);

        $farAwayTask = (new Task())
            ->setScheduleName('test_schedule_far_away')
            ->setMessageClass('Beerfranz\DynamicScheduleBundle\Tests\Message\TestFarAwayMessage')
            ->setTrigger('every')
            ->setFrequency('3 seconds')
            ->setTransport('far_away');
        $this->em->persist($farAwayTask);

        $this->em->flush();
    }

    public function testRepository(): void
    {
        $repo = self::getContainer()->get(TaskRepository::class);
        $this->assertEquals(6, count($repo->findAll()));
        $this->assertEquals(2, count($repo->findTasksForSchedule('test_schedule')));
    }

    public function testDynamicScheduleProvider(): void
    {

        $scheduleProvider = self::getContainer()->get('SimpleScheduleProvider');

        $schedule = $scheduleProvider->getSchedule();

        $messages = $schedule->getRecurringMessages();
        $this->assertNotEmpty($messages);
        $this->assertEquals(count($messages), 2);

        // dd($messages);
        // $this->assertEquals('Beerfranz\DynamicScheduleBundle\Tests\Message\TestMessage', $messages[0]->getMessageName());

    }

    private function consumeSchedule(string $name, int $maxTime = 5)
    {
        $application = new Application(self::$kernel);
        $command = $application->find('messenger:consume');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'receivers' => ['scheduler_' . $name],
            '--time-limit' => $maxTime,
        ], ['capture_stderr_separately' => true]);
        return $commandTester;
    }

    public function testDynamicScheduleExecution(): void
    {
        $commandTester = $this->consumeSchedule('dynamic');
        $commandTester->assertCommandIsSuccessful();
        $this->transport('async')->queue()->assertCount(2);
        $this->transport('sync')->queue()->assertCount(2);
        $this->assertStringContainsString('Consuming messages from transport "scheduler_dynamic"', $commandTester->getDisplay());
    }

    public function testEmptyDynamicSchedule(): void
    {
        $commandTester = $this->consumeSchedule('empty', 1);
        $commandTester->assertCommandIsSuccessful();
        $this->transport('async')->queue()->assertCount(0);
        $this->transport('sync')->queue()->assertCount(0);
    }

    public function testBadScheduleName(): void
    {
        $this->expectException('\Symfony\Component\Console\Exception\RuntimeException');
        $commandTester = $this->consumeSchedule('not_exists', 1);
    }

    public function testBadMessageTransport(): void
    {
        $commandTester = $this->consumeSchedule('bad_transport', 1);
        $commandTester->assertCommandIsSuccessful();
    }

    // Test that last message is send after a pause of the scheduler
    public function testFarAwaySchedule(): void
    {
        $this->transport('far_away')->queue()->assertEmpty();
        $this->consumeSchedule('far_away', 1);
        $this->transport('far_away')->queue()->assertCount(0);

        sleep(20);
        $this->consumeSchedule('far_away', 1);
        $this->transport('far_away')->queue()->assertCount(1);

        
    }

    // Test that all messages are send when provider param onlyLastMissedRun=false.
    // Use provider NoMissScheduleProvider
    public function testNoMiss(): void
    {
        $this->transport('other')->queue()->assertEmpty();
        $this->consumeSchedule('no_miss', 1);
        $this->transport('other')->queue()->assertCount(1);
        sleep(20);
        $this->consumeSchedule('no_miss', 1);
        $this->transport('other')->queue()->assertCount(12);   
    }

}
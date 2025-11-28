<?php

namespace Beerfranz\DynamicScheduleBundle\Service;

use Beerfranz\DynamicScheduleBundle\Entity\Task;
use Beerfranz\DynamicScheduleBundle\Repository\TaskRepository;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Message\RedispatchMessage;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

class DynamicScheduleProvider implements ScheduleProviderInterface
{
    public function __construct(
        private TaskRepository $taskRepository,
        private string $scheduleName,
        private bool $onlyLastMissedRun = true,
        private ?CacheInterface $cache = null,
        private ?LockFactory $lockFactory = null,
    ) {
    }

    public function getSchedule(): Schedule
    {
        $schedule = new Schedule();
        $tasks = $this->taskRepository->findTasksForSchedule($this->scheduleName);

        foreach ($tasks as $task) {
            $recurring = $this->generateRecurring($task);
            $schedule->add($recurring);
        }

        if ($this->cache !== null)
            $schedule->stateful($this->cache);

        $schedule->processOnlyLastMissedRun($this->onlyLastMissedRun);
        
        if ($this->lockFactory !== null)
            $schedule->lock($this->lockFactory->createLock('dynamic_schedule_' . $this->scheduleName));

        return $schedule;
    }

    private function generateRecurring(Task $task)
    {
        $allowedTriggers = [ 'cron', 'every' ];
        $trigger = $task->getTrigger();

        if (!in_array($trigger, $allowedTriggers))
        {
            throw new \Exception('Can not add RecurringMessage with trigger type ' . $trigger . ', allowed values are '. implode(',', $allowedTriggers) . '.');
        }

        $messageClass = $task->getMessageClass();
        if ($task->getMessageArgs() == null)
            $message = new $messageClass();
        else
            $message = new $messageClass($task->getMessageArgs());
        $freq = $task->getFrequency();
        $from = $task->getStartFrom();
        $until = $task->getUntil();
        $jitter = $task->getJitter();
        $redispatchMessage = new RedispatchMessage($message, $task->getTransport());

        if ($until !== null) {
            $recurring = RecurringMessage::$trigger($freq, $redispatchMessage, $from, $until);
        } else {
            $recurring = RecurringMessage::$trigger($freq, $redispatchMessage, $from);
        }

        $recurring->withJitter($jitter);
        return $recurring;
    }
}

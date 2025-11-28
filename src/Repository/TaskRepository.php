<?php

namespace Beerfranz\DynamicScheduleBundle\Repository;

use Beerfranz\DynamicScheduleBundle\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findTasksForSchedule($scheduleName): array
    {
        return $this->findBy(['enabled' => true, 'scheduleName' => $scheduleName]);
    }
}

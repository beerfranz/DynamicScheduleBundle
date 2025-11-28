<?php

namespace Beerfranz\DynamicScheduleBundle\Tests\Message;

use Beerfranz\DynamicScheduleBundle\Tests\Message\TestMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TestMessageHandler
{

	public function __invoke(TestMessage $message)
    {
        echo 'Consumed';
    }

}

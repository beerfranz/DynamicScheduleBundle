<?php

namespace Beerfranz\DynamicScheduleBundle\Tests\Message;

class TestMessage
{
	protected ?string $content = null;

	public function __construct(?string $content = null) {
		$this->content = $content;
	}

	public function getContent(): ?string
	{
		return $this->content;
	}
}

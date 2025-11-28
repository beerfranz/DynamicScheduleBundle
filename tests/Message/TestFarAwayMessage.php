<?php

namespace Beerfranz\DynamicScheduleBundle\Tests\Message;

class TestFarAwayMessage
{
	protected ?string $content = null;
	public ?\DateTimeImmutable $time = null;

	public function __construct(?string $content = null) {
		$this->content = $content;
		$this->time = new \DateTimeImmutable();
	}

	public function getContent(): ?string
	{
		return $this->content;
	}
}

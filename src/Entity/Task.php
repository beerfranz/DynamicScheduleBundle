<?php

namespace Beerfranz\DynamicScheduleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "Beerfranz\DynamicScheduleBundle\Repository\TaskRepository")]
#[ORM\Table(name: "dynamic_schedule_task")]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $scheduleName;

    #[ORM\Column(type: "string", length: 255)]
    private string $messageClass;

    #[ORM\Column(type: 'json', options: ['jsonb' => true], nullable: true)]
    private ?array $messageArgs = null;

    #[ORM\Column(type: "string", length: 20)]
    private string $trigger; // 'cron' or 'every'

    #[ORM\Column(type: "string", length: 255)]
    private string $frequency;

    #[ORM\Column(type: "string", length: 255)]
    private string $transport;

    #[ORM\Column(type: "integer", nullable: false)]
    private int $jitter = 0;

    #[ORM\Column(type: "boolean", nullable: false)]
    private ?bool $enabled = true;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startFrom = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $until = null;

    public function __construct()
    {
        $this->startFrom = new \DateTimeImmutable();
    }

    // Getters & Setters
    public function getId(): ?int { return $this->id; }
    public function getScheduleName(): string { return $this->scheduleName; }
    public function setScheduleName(string $scheduleName): self { $this->scheduleName = $scheduleName; return $this; }
    public function getMessageClass(): string { return $this->messageClass; }
    public function setMessageClass(string $messageClass): self { $this->messageClass = $messageClass; return $this; }
    public function getMessageArgs(): ?array { return $this->messageArgs; }
    public function setMessageArgs(?array $messageArgs): self { $this->messageArgs = $messageArgs; return $this; }
    public function getTrigger(): string { return $this->trigger; }
    public function setTrigger(string $trigger): self { $this->trigger = $trigger; return $this; }
    public function getFrequency(): string { return $this->frequency; }
    public function setFrequency(string $frequency): self { $this->frequency = $frequency; return $this; }
    public function getTransport(): string { return $this->transport; }
    public function setTransport(string $transport): self { $this->transport = $transport; return $this; }
    public function getJitter(): ?int { return $this->jitter; }
    public function setJitter(?int $jitter): self { $this->jitter = $jitter; return $this; }
    public function isEnabled(): bool { return $this->enabled; }
    public function hasEnabled(bool $enabled): self { $this->enabled = $enabled; return $this; }
    public function getStartFrom(): ?\DateTimeImmutable { return $this->startFrom; }
    public function setStartFrom(?\DateTimeImmutable $startFrom): self { $this->startFrom = $startFrom; return $this; }
    public function getUntil(): ?\DateTimeImmutable { return $this->until; }
    public function setUntil(?\DateTimeImmutable $until): self { $this->until = $until; return $this; }
}

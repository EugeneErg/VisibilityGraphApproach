<?php

declare(strict_types = 1);

namespace EugeneErg\src\ValueObjects;

final class Part
{
    private ?self $previous;
    private ?self $next;
    private bool $nextConnected = false;

    public function __construct(
        public readonly Point $point,
        public readonly Angle $angle,
        public readonly float $distance,
    ) {
    }

    public function getPreviousConnected(): bool
    {
        return $this->previous?->getNextConnected() ?? false;
    }

    public function getNextConnected(): bool
    {
        return $this->nextConnected;
    }

    public function unsetPrevious(): void
    {
        $this->previous?->unsetNext();
    }

    public function setPrevious(self $previous, bool $connected): void
    {
        $previous->setNext($this, $connected);
    }

    public function unsetNext(): void
    {
        if ($this->next !== null) {
            $this->next->previous = null;
        }

        $this->next = null;
    }

    public function setNext(self $next, bool $connected): void
    {
        $this->next?->unsetPrevious();
        $this->next = $next;
        $this->nextConnected = $connected;
        $next->unsetPrevious();
        $next->previous = $this;
    }

    public function addPrevious(self $previous, bool $connected): void
    {
        $previous->addNext($this, $connected);
    }

    public function addNext(self $next, bool $connected): void
    {
        $next->setNext($this->next, $this->nextConnected);
        $this->setNext($next, $connected);
    }

    public function getNext(): ?self
    {
        return $this->next;
    }
}
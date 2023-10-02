<?php

namespace MorningMedley\Route\Classes\Rest;

use MorningMedley\Route\Abstracts\AbstractGroup;
use MorningMedley\Route\Route;

class Group extends AbstractGroup
{
    protected ?string $namespace = null;
    protected ?bool $public = null;
    protected ?bool $exposed = null;

    protected function open(): void
    {
        $this->app->make('rest-router')->setCurrentGroup($this);
    }

    protected function close(): void
    {
        $this->app->make('rest-router')->setCurrentGroup($this->getGroup());
    }

    public function namespace(string $namespace): static
    {
        $this->namespace = trim($namespace, '/');

        return $this;
    }

    public function getNamespace(): ?string
    {
        if ($this->namespace !== null) {
            return $this->namespace;
        }

        return $this->group?->getNamespace();
    }

    public function public(bool $public = true): static
    {
        $this->public = $public;

        return $this;
    }

    public function isPublic(): bool
    {
        if ($this->public !== null) {
            return $this->public;
        }

        return $this->group?->isPublic() ?? false;
    }

    public function expose(bool $expose): static
    {
        $this->exposed = $expose;

        return $this;
    }

    public function isExposed(): bool
    {
        if ($this->exposed !== null) {
            return $this->exposed;
        }

        return $this->group?->isExposed() ?? false;
    }
}

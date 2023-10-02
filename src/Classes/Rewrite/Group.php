<?php

namespace MorningMedley\Route\Classes\Rewrite;

use MorningMedley\Route\Abstracts\AbstractGroup;
use MorningMedley\Route\Route;

class Group extends AbstractGroup
{
    protected function open(): void
    {
        $this->app->make('rewrite-router')->setCurrentGroup($this);
    }

    protected function close(): void
    {
        $this->app->make('rewrite-router')->setCurrentGroup($this->getGroup());
    }
}

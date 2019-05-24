<?php

namespace App\Telegrambot\Commands;

abstract class BaseUserCommand extends BaseCommand
{
    public function isUserCommand(): bool
    {
        return true;
    }
}

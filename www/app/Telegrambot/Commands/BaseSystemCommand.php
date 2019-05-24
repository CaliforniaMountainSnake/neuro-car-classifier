<?php

namespace App\Telegrambot\Commands;

abstract class BaseSystemCommand extends BaseCommand
{
    public function isSystemCommand(): bool
    {
        return true;
    }
}

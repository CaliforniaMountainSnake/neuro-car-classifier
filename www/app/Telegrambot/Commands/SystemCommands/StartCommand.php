<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use App\Telegrambot\Commands\BaseSystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

/**
 * Команда, выполняемая при первом запуске бота.
 */
class StartCommand extends BaseSystemCommand
{
    /**
     * @var string
     */
    protected $name = 'start';

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * Вернуть описание команды.
     * @return string
     */
    protected function makeDescription(): string
    {
        return __('telegrambot/start.description');
    }

    /**
     * Вернуть способ использования команды.
     * @return string|null
     */
    protected function makeUsage(): ?string
    {
        return '/start';
    }

    /**
     * Вернуть имя команды.
     * @return string
     */
    protected static function getCommandName(): string
    {
        return 'start';
    }

    /**
     * Command execute method
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        return $this->sendTextMessage(__('telegrambot/start.maintext'));
    }
}

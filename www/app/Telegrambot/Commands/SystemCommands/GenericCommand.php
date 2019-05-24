<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use App\Telegrambot\Commands\BaseSystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

/**
 * Команда, которая выполняется по-умолчанию, когда целевая команда не найдена.
 */
class GenericCommand extends BaseSystemCommand
{
    /**
     * @var string
     */
    protected $name = 'generic';

    /**
     * Вернуть описание команды.
     * @return string
     */
    protected function makeDescription(): string
    {
        return __('telegrambot/generic.description');
    }

    /**
     * Вернуть способ использования команды.
     * @return string|null
     */
    protected function makeUsage(): ?string
    {
        return null;
    }

    /**
     * Вернуть имя команды.
     * @return string
     */
    protected static function getCommandName(): string
    {
        return 'generic';
    }

    /**
     * Command execute method
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        $message = $this->getMessage();

        //You can use $command as param
        $user_id = $message->getFrom()->getId();
        $command = $message->getCommand();

        //If the user is an admin and the command is in the format "/whoisXYZ", call the /whois command
        if (stripos($command, 'whois') === 0 && $this->telegram->isAdmin($user_id)) {
            return $this->telegram->executeCommand('whois');
        }

        return $this->sendTextMessage(__('telegrambot/generic.maintext', ['command_name' => $command]));
    }
}

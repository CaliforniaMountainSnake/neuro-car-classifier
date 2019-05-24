<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use App\Telegrambot\Commands\BaseSystemCommand;
use Longman\TelegramBot\Commands\UserCommands\PredictCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

/**
 * Команда, выполняемая при присылании сообщения, если никакая другая команда не активна в данный момент.
 */
class GenericmessageCommand extends BaseSystemCommand
{
    /**
     * @var string
     */
    protected $name = 'genericmessage';

    /**
     * Вернуть описание команды.
     * @return string
     */
    protected function makeDescription(): string
    {
        return __('telegrambot/genericmessage.description');
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
        return 'genericmessage';
    }

    /**
     * Command execute method
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        $conversation = new Conversation(
            $this->getMessage()->getFrom()->getId(),
            $this->getMessage()->getChat()->getId()
        );

        // Если запущена какая-либо команда, продолжаем ее выполнение.
        if ($conversation->exists()) {
            return $this->telegram->executeCommand($conversation->getCommand());
        }

        // Если никакой команды не запущено, выполняем команду распознвания изображения.
        return $this->telegram->executeCommand(PredictCommand::getCommandName());
    }
}

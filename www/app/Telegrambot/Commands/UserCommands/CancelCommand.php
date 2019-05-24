<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use App\Telegrambot\Commands\BaseUserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

/**
 * Завершить активную команду.
 */
class CancelCommand extends BaseUserCommand
{
    /**
     * @var string
     */
    protected $name = 'cancel';

    /**
     * Вернуть описание команды.
     * @return string
     */
    protected function makeDescription(): string
    {
        return __('telegrambot/cancel.description');
    }

    protected function makeUsage(): string
    {
        return '/cancel';
    }

    /**
     * Вернуть имя команды.
     * @return string
     */
    protected static function getCommandName(): string
    {
        return 'cancel';
    }

    /**
     * Command execute method
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        $text = __('telegrambot/cancel.no_active_command');

        // Завершить активную команду, если она имеется.
        $conversation = new Conversation(
            $this->getMessage()->getFrom()->getId(),
            $this->getMessage()->getChat()->getId()
        );

        if ($conversation_command = $conversation->getCommand()) {
            $conversation->cancel();
            $text = __('telegrambot/cancel.command_has_been_stopped', ['command_name' => $conversation_command]);
        }

        return $this->removeKeyboard($text);
    }

    /**
     * Command execute method if MySQL is required but not available
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function executeNoDb(): ServerResponse
    {
        return $this->sendTextMessage(__('telegrambot/cancel.no_active_command'));
    }
}

<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use App\Telegrambot\Commands\BaseUserCommand;
use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

/**
 * Команда помощи.
 * Отобразить все доступные команды, разделенные на пользовательскую и администраторскую секцию.
 */
class HelpCommand extends BaseUserCommand
{
    /**
     * @var string
     */
    protected $name = 'help';

    protected function makeDescription(): string
    {
        return __('telegrambot/help.description');
    }

    protected function makeUsage(): string
    {
        return __('telegrambot/help.usage', [
            'command_name' => $this->name
        ]);
    }

    /**
     * Вернуть имя команды.
     * @return string
     */
    protected static function getCommandName(): string
    {
        return 'help';
    }

    /**
     * @inheritdoc
     */
    public function execute(): ServerResponse
    {
        // Admin commands shouldn't be shown in group chats
        $safe_to_show = $this->message->getChat()->isPrivateChat();
        [$all_commands, $user_commands, $admin_commands] = $this->getUserAdminCommands();
        $resultText = '';

        // If no command parameter is passed, show the list.
        if ($this->text === '') {
            $resultText .= __('telegrambot/help.commands_list_title');
            foreach ($user_commands as $user_command) {
                $resultText .= '/' . $user_command->getName() . ' - ' . $user_command->getDescription() . PHP_EOL;
            }

            if ($safe_to_show && count($admin_commands) > 0) {
                $resultText .= PHP_EOL . __('telegrambot/help.library_admin_commands_list_title');
                foreach ($admin_commands as $admin_command) {
                    $resultText .= '/' . $admin_command->getName() . ' - ' . $admin_command->getDescription() . PHP_EOL;
                }
            }
        }

        return $this->sendTextMessage($resultText);
    }

    /**
     * Получить все доступные пользовательские и администраторские команды.
     *
     * @return Command[][]
     * @throws TelegramException
     */
    protected function getUserAdminCommands(): array
    {
        // Получить только команды, которые разрешено показывать.
        /** @var Command[] $commands */
        $commands = array_filter($this->telegram->getCommandsList(), static function ($command) {
            /** @var Command $command */
            return !$command->isSystemCommand() && $command->showInHelp() && $command->isEnabled();
        });

        $user_commands = array_filter($commands, static function ($command) {
            /** @var Command $command */
            return $command->isUserCommand();
        });

        $admin_commands = array_filter($commands, static function ($command) {
            /** @var Command $command */
            return $command->isAdminCommand();
        });

        ksort($commands);
        ksort($user_commands);
        ksort($admin_commands);

        return [$commands, $user_commands, $admin_commands];
    }
}

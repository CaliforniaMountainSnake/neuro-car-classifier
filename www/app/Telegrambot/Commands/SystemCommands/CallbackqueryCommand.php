<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use App\Telegrambot\Commands\BaseSystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

/**
 * Команда обработки callback-запросов.
 * Обрабатывает все нажатия на кнопки inline-клавиатуры.
 */
class CallbackqueryCommand extends BaseSystemCommand
{
    /**
     * @var string
     */
    protected $name = 'callbackquery';

    /**
     * Вернуть описание команды.
     * @return string
     */
    protected function makeDescription(): string
    {
        return __('telegrambot/callbackquery.description');
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
        return 'callbackquery';
    }

    /**
     * Command execute method
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     */
    public function execute(): ServerResponse
    {
        $callback_query    = $this->getCallbackQuery();
        $callback_query_id = $callback_query->getId();
        $callback_data     = $callback_query->getData();

        $data = [
            'callback_query_id' => $callback_query_id,
            'text' => 'Hello World!',
            'show_alert' => $callback_data === 'thumb up',
            'cache_time' => 5,
        ];

        return Request::answerCallbackQuery($data);
    }
}

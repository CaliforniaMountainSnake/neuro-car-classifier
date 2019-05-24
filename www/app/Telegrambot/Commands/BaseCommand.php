<?php

namespace App\Telegrambot\Commands;

use App\Telegrambot\CommandUtils\ConversationUtils;
use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

abstract class BaseCommand extends Command
{
    use ConversationUtils;

    /**
     * Режим парсинга по-умолчанию.
     */
    public const DEFAULT_PARSE_MODE = 'html';

    /**
     * Текущее сообщение.
     * @var Message|null
     */
    protected $message;

    /**
     * От кого получено текущее сообщение.
     * @var User
     */
    protected $telegramUser;

    /**
     * Текущий id чата.
     * @var int
     */
    protected $chatId;

    /**
     * Текущий текст. (without cmd).
     * @var string
     */
    protected $text;

    /**
     * @var Conversation|null
     */
    protected $conversation;

    /**
     * @var bool
     */
    protected $need_mysql = true;

    /**
     * Вернуть описание команды.
     * @return string
     */
    abstract protected function makeDescription(): string;

    /**
     * Вернуть способ использования команды.
     * @return string|null
     */
    abstract protected function makeUsage(): ?string;

    /**
     * Вернуть имя команды.
     * @return string
     */
    abstract protected static function getCommandName(): string;

    /**
     * BaseCommand constructor.
     * @param Telegram $telegram
     * @param Update|null $update
     */
    public function __construct(Telegram $telegram, Update $update = null)
    {
        $this->description = $this->makeDescription();
        $this->usage       = $this->makeUsage();
        parent::__construct($telegram, $update);
    }

    /**
     * @return ServerResponse
     * @throws TelegramException
     */
    public function preExecute(): ServerResponse
    {
        $this->initTelegramParams();
        return parent::preExecute();
    }

    protected function initTelegramParams(): void
    {
        $this->message  = $this->getTelegramMessage();
        $callback_query = $this->getUpdate()->getCallbackQuery();
        if ($this->message !== null) {
            $this->telegramUser = $this->message->getFrom();
            $this->chatId       = $this->message->getChat()->getId();
            $this->text         = $this->message->getText(true) ?? '';
        } elseif ($callback_query) {
            $this->telegramUser = $callback_query->getFrom();
            $this->chatId       = $callback_query->getMessage()->getChat()->getId();
            $this->text         = $callback_query->getData() ?? '';
        }
    }

    /**
     * Отослать текстовое сообщение.
     * (Режим парсинга: HTML).
     *
     * @param string $_msg Текст сообщения.
     * @param Keyboard|null $_reply_markup Клавиатура. Не обязательно.
     * @param string ...$_errors Ошибки, которые следует отобразить.
     *
     * @return ServerResponse                  Ответ Telegram.
     * @throws TelegramException
     */
    protected function sendTextMessage(
        string $_msg,
        ?Keyboard $_reply_markup = null,
        string ...$_errors
    ): ServerResponse {
        $errorsStr = empty($_errors) ? '' : \implode("\n", $_errors) . "\n\n";
        $data      = [
            'chat_id' => $this->getChatId(),
            'parse_mode' => self::DEFAULT_PARSE_MODE,
            'text' => $errorsStr . $_msg,
        ];

        if ($_reply_markup !== null) {
            $data['reply_markup'] = $_reply_markup;
        }

        return Request::sendMessage($data);
    }

    /**
     * Завершить команду и отправить сообщение об ошибке.
     *
     * @param string ...$_errors
     * @return ServerResponse
     * @throws TelegramException
     */
    protected function sendFatalError(string ...$_errors): ServerResponse
    {
        $conversation = $this->getConversation();
        if ($conversation !== null) {
            $conversation->stop();
        }
        return $this->sendTextMessage('', null, ...$_errors);
    }

    /**
     * Отправить уведомление, что бот сейчас печатает.
     * @return ServerResponse
     */
    protected function sendTypingAction(): ServerResponse
    {
        // Send typing action.
        return Request::sendChatAction([
            'chat_id' => $this->getChatId(),
            'action' => 'typing',
        ]);
    }

    /**
     * @param string $_text
     * @return ServerResponse
     * @throws TelegramException
     */
    protected function removeKeyboard(string $_text): ServerResponse
    {
        $params = [
            'chat_id' => $this->getChatId(),
            'parse_mode' => self::DEFAULT_PARSE_MODE,
            'reply_markup' => Keyboard::remove(['selective' => true]),
            'text' => $_text
        ];

        return Request::sendMessage($params);
    }

    protected function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    protected function setConversation(?Conversation $_new_conversation_state): void
    {
        $this->conversation = $_new_conversation_state;
    }

    protected function getStateNoteName(): string
    {
        return 'state';
    }

    protected function getTelegramUser(): User
    {
        return $this->telegramUser;
    }

    protected function getChatId(): int
    {
        return $this->chatId;
    }

    public function getTelegramMessage(): ?Message
    {
        return $this->getMessage();
    }

    /** @noinspection SenselessMethodDuplicationInspection */
    public function getName(): string
    {
        return $this->name;
    }
}

<?php

namespace App\Telegrambot\CommandUtils;

use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\User;

trait ConversationUtils
{
    abstract protected function getConversation(): ?Conversation;

    abstract protected function setConversation(?Conversation $_new_conversation_state): void;

    /**
     * Идентификатор текущего состояния conversation.
     * @return string
     */
    abstract protected function getStateNoteName(): string;

    /**
     * @return User
     */
    abstract protected function getTelegramUser(): User;

    /**
     * @return int
     */
    abstract protected function getChatId(): int;

    /**
     * Get command name.
     * @return string
     */
    abstract public function getName(): string;

    /**
     * @param string $_new_state
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    protected function setConversationState(string $_new_state): void
    {
        if ($this->getNote($this->getStateNoteName()) === $_new_state) {
            return;
        }

        $this->setConversationNotes([$this->getStateNoteName() => $_new_state]);
    }

    /**
     * Set permanent variables for the current conversation.
     *
     * @param array $_notes_arr Array with notes.
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    protected function setConversationNotes(array $_notes_arr): void
    {
        foreach ($_notes_arr as $key => $value) {
            $this->getConversation()->notes[$key] = $value;
        }
        $this->getConversation()->update();
    }

    /**
     * Unset permanent variables for the current conversation.
     *
     * @param array $_note_keys_arr Array of the notes's KEYS.
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    protected function deleteConversationNotes(array $_note_keys_arr): void
    {
        foreach ($_note_keys_arr as $value) {
            unset ($this->getConversation()->notes[$value]);
        }
        $this->getConversation()->update();
    }

    /**
     * Вернуть значение переменной conversation->notes.
     * Алиас для более быстрого обращения.
     *
     * @param string $_note Ключ массива notes.
     *
     * @return mixed|null Значение переменной или null, если она не существует.
     */
    protected function getNote(string $_note)
    {
        return ($this->getConversation()->notes[$_note] ?? null);
    }

    /**
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    protected function startConversation(): void
    {
        $this->setConversation(new Conversation ($this->getTelegramUser()->getId(), $this->getChatId(),
            $this->getName()));
    }

    /**
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    protected function stopConversation(): void
    {
        if ($this->getConversation() !== null) {
            $this->getConversation()->stop();
        }
    }
}

<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use App\ApplicationStorageService;
use App\FileDownloader\StorageService\StorageServiceInterface;
use App\FileDownloader\TelegramFileDownloader;
use App\Telegrambot\Commands\BaseUserCommand;
use CaliforniaMountainSnake\UtilTraits\Curl\CurlUtils;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

/**
 * Команда распознавания присланного пользователем изображения.
 */
class PredictCommand extends BaseUserCommand
{
    use CurlUtils;

    /**
     * Метка о показе первого сообщения.
     */
    protected const NOTES_FIRST_MESSAGE_SHOWN = 'first_message_shown';

    /**
     * Метка с именем присланного файла.
     */
    protected const NOTES_INPUT_IMAGE_FILENAME = 'input_image_filename';

    /**
     * Максимальный размер файла изображения в мегабайтах.
     */
    protected const MAX_IMAGE_FILESIZE_MB = 5;

    /**
     * Чувствительны ли расширения файлов изображений к регистру?
     */
    protected const IS_IMAGE_EXTENSION_CASE_SENSITIVE = false;

    /**
     * @var string
     */
    protected $name = 'predict';

    /**
     * @var StorageServiceInterface
     */
    protected $storageService;

    /**
     * Получить поддерживаемые расширения изображений.
     * @return array
     */
    protected function getAvailableImageExtensions(): array
    {
        return [
            'jpg',
            'jpeg',
            'png'
        ];
    }

    /**
     * Вернуть описание команды.
     * @return string
     */
    protected function makeDescription(): string
    {
        return __('telegrambot/predict.description');
    }

    /**
     * Вернуть способ использования команды.
     * @return string|null
     */
    protected function makeUsage(): ?string
    {
        return '/predict';
    }

    /**
     * Вернуть имя команды.
     * @return string
     */
    protected static function getCommandName(): string
    {
        return 'predict';
    }

    /**
     * PredictCommand constructor.
     * @param Telegram $telegram
     * @param Update|null $update
     * @throws \LogicException
     */
    public function __construct(Telegram $telegram, ?Update $update = null)
    {
        $this->storageService = app()->make(ApplicationStorageService::class);
        parent::__construct($telegram, $update);
    }

    /**
     * Execute command
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        // Conversation start
        $this->startConversation();

        // Call needed state method
        $stateMethod = $this->conversation->notes[$this->getStateNoteName()] ?? 'state_1_getFile';
        return $this->$stateMethod ($this->message, $this->text);
    }

    /**
     * Получить файл от пользователя.
     *
     * @param Message $_user_message
     * @param string $_text
     * @return ServerResponse
     * @throws TelegramException
     */
    public function state_1_getFile(Message $_user_message, string $_text = ''): ServerResponse
    {
        $this->setConversationState('state_1_getFile');
        $messageType         = $this->message->getType();
        $isFirstMessageShown = $this->getNote(self::NOTES_FIRST_MESSAGE_SHOWN) !== null;
        $isGenericLaunch     = $messageType !== 'command' && !$isFirstMessageShown;

        // Валидация присланных данных.
        $errors = [];
        if ($isFirstMessageShown || $isGenericLaunch) {
            if ($messageType !== 'photo') {
                $errors[] = __('telegrambot/predict.error_wrong_message_type');
            } else {
                $downloadErrors = $this->downloadImage($this->message);
                $errors         = \array_merge($errors, $downloadErrors);
            }
        }

        // Показать ошибки и завершить команду, если она запущена автоматически.
        if (!empty($errors) && $isGenericLaunch) {
            return $this->sendFatalError(...$errors);
        }

        // Показ начального сообщения и ошибок, если они есть.
        if (!$isGenericLaunch && (!$isFirstMessageShown || !empty($errors))) {
            $this->setConversationNotes([self::NOTES_FIRST_MESSAGE_SHOWN => true]);
            return $this->sendTextMessage(__('telegrambot/predict.maintext'), null, ...$errors);
        }

        // Если ошибок нет, переходим к следующей стадии.
        return $this->state_2_showPrediction($_user_message);
    }

    /**
     * Показать сообщение с предсказанием.
     *
     * @param Message $_user_message
     * @param string $_text
     * @return ServerResponse
     * @throws TelegramException
     */
    public function state_2_showPrediction(Message $_user_message, string $_text = ''): ServerResponse
    {
        $this->sendTypingAction();

        $imageTempFilename = $this->getNote(self::NOTES_INPUT_IMAGE_FILENAME);
        $prediction        = $this->getPrediction($imageTempFilename);
        \unlink($imageTempFilename);

        $this->stopConversation();
        return $this->sendTextMessage($this->getPredictionFormattedMessage($prediction));
    }


    /**
     * Загрузить файл с серверов мессенжера на сервер бота.
     *
     * @param Message $_user_message
     * @return array Массив с ошибками, если они есть.
     * @throws TelegramException
     */
    protected function downloadImage(Message $_user_message): array
    {
        $errors = [];
        $photos = $_user_message->getPhoto();
        $photo  = \end($photos);

        $downloader = new TelegramFileDownloader ($photo->getFileId(), $this->telegram,
            $this->storageService, true);

        if (!$downloader->downloadToTemp()) {
            $errors[] = __('file_downloader.error_file_download', [
                'error_code' => $downloader->getErrorCode(),
                'error_description' => $downloader->getErrorDescription(),
            ]);
        } elseif (!$downloader->checkFileExtensions(self::IS_IMAGE_EXTENSION_CASE_SENSITIVE,
            ...$this->getAvailableImageExtensions())) {
            $errors[] = __('file_downloader.error_file_bad_extension', [
                'current_bad_extension' => $downloader->getExtensionFilenameTemp(),
                'supported_extensions' => \implode(', ', $this->getAvailableImageExtensions())
            ]);
        } elseif (!$downloader->checkFileSize(1024 * 1024 * self::MAX_IMAGE_FILESIZE_MB)) {
            $errors[] = __('file_downloader.error_file_is_too_big', [
                'current_human_readable_bad_size' => $downloader->getLastKnownFilesizeHumanReadable(),
                'max_size_in_mb' => self::MAX_IMAGE_FILESIZE_MB,
            ]);
        } else {
            $this->setConversationNotes([
                self::NOTES_INPUT_IMAGE_FILENAME => $downloader->getFilenameTemp()
            ]);
        }

        return $errors;
    }

    /**
     * Получить предсказание от нейросети.
     *
     * @param string $_image_filename Имя файла изображения
     * @return array Декодированный из JSON массив предсказаний.
     */
    protected function getPrediction(string $_image_filename): array
    {
        $url = config('app.url_http') . ':' . config('values.python_container_port') . '/predict';
        return $this->postQuery($url, [
            'image' => new \CURLFile($_image_filename)
        ])->jsonDecode();
    }

    /**
     * Получить форматированное текстовое сообщение с предсказаниями.
     *
     * @param array $_predictions
     * @return string
     */
    protected function getPredictionFormattedMessage(array $_predictions): string
    {
        $rows = '';
        foreach ($_predictions['predictions'] as $prediction) {
            $rows .= __('telegrambot/predict.prediction_row', [
                'label' => $prediction['label'],
                'probability_percent' => \round((float)$prediction['probability'] * 100, 2),
            ]);
        }

        return __('telegrambot/predict.result_text', [
            'predictions_rows' => $rows
        ]);
    }
}

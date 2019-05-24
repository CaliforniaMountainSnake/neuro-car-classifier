<?php

namespace App\FileDownloader;

use App\FileDownloader\StorageService\StorageServiceInterface;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

/**
 * Класс для скачивания файла от юзера и проверки его на ошибки.
 */
class TelegramFileDownloader extends BaseFileDownloader
{
    /**
     * @var string
     */
    protected $fileId;

    /**
     * @var Telegram
     */
    protected $telegram;

    public function __construct(
        string $_file_id,
        Telegram $_telegram,
        StorageServiceInterface $_storage_service,
        bool $_is_auto_delete_if_bad = true
    ) {
        $this->fileId            = $_file_id;
        $this->telegram          = $_telegram;
        $this->storageService    = $_storage_service;
        $this->isAutoDeleteIfBad = $_is_auto_delete_if_bad;

        parent::__construct($_storage_service, $_is_auto_delete_if_bad);
    }

    /**
     * @return bool
     * @throws TelegramException
     */
    public function downloadToTemp(): bool
    {
        // А теперь скачиваем то что нам прислали в папку.
        $file                   = Request::getFile(['file_id' => $this->fileId]);
        $this->errorCode        = $file->getErrorCode();
        $this->errorDescription = $file->getDescription();

        if ($file->isOk() && Request::downloadFile($file->getResult())) {
            // Переместим в TEMP загруженный файл.
            $downloadedFilename = $this->telegram->getDownloadPath() . '/' . $file->getResult()->getFilePath();
            $extension          = \pathinfo($downloadedFilename, \PATHINFO_EXTENSION);

            $this->filenameTemp = $this->storageService->saveToNewTempFile(\file_get_contents($downloadedFilename),
                $extension);
            \unlink($downloadedFilename);

            // Запишем последний известный размер файла, чтобы вывести его в сообщении об ошибке.
            $this->lastKnownFilesizeHumanReadable = $this->getHumanReadableFileSize($this->getFilesize());
            return true;
        }

        // Ошибка скачивания с Telegram.
        return false;
    }
}

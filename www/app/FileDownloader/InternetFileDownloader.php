<?php

namespace App\FileDownloader;

use App\FileDownloader\StorageService\StorageServiceInterface;
use CaliforniaMountainSnake\UtilTraits\Curl\CurlUtils;

/**
 * Класс загрузчика для скачивания файлов из интернета.
 */
class InternetFileDownloader extends BaseFileDownloader
{
    use CurlUtils;

    /**
     * @var string
     */
    protected $fileUrl;

    public function __construct(
        string $_file_url,
        StorageServiceInterface $_storage_service,
        bool $_is_auto_delete_if_bad = true
    ) {
        $this->fileUrl           = $_file_url;
        $this->storageService    = $_storage_service;
        $this->isAutoDeleteIfBad = $_is_auto_delete_if_bad;

        parent::__construct($_storage_service, $_is_auto_delete_if_bad);
    }

    public function downloadToTemp(): bool
    {
        // Создадим имя сохраняемому файлу в папке TEMP на основе его имени в переданном URL.
        $this->filenameTemp = $this->storageService->generateNewTempFilename(\basename($this->fileUrl));

        // Скачаем файл с удаленного сервера.
        if (!$this->downloadFile($this->fileUrl, $this->filenameTemp)) {
            return false;
        }

        // Запишем последний известный размер файла, чтобы вывести его в сообщении об ошибке.
        $this->lastKnownFilesizeHumanReadable = $this->getHumanReadableFileSize($this->getFilesize());

        return true;
    }
}

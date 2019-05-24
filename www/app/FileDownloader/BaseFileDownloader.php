<?php

namespace App\FileDownloader;

use App\FileDownloader\StorageService\StorageServiceInterface;
use CaliforniaMountainSnake\UtilTraits\StringUtils;

/**
 * Базовый загрузчик-проверяльщик файлов.
 */
abstract class BaseFileDownloader implements FileDownloaderInterface
{
    use StringUtils;

    /**
     * @var StorageServiceInterface
     */
    protected $storageService;

    /**
     * @var bool
     */
    protected $isAutoDeleteIfBad;

    /**
     * @var string
     */
    protected $filenameTemp;

    /**
     * @var int
     */
    protected $lastKnownFilesizeHumanReadable = -1;

    /**
     * @var int
     */
    protected $errorCode = -1;

    /**
     * @var string
     */
    protected $errorDescription = 'NO_ERRORS';

    public function __construct(StorageServiceInterface $_storage_service, bool $_is_auto_delete_if_bad = true)
    {
        $this->storageService    = $_storage_service;
        $this->isAutoDeleteIfBad = $_is_auto_delete_if_bad;
    }

    /**
     * Проверить расширения.
     * @param bool $_is_case_sensitive
     * @param string[] $_supported_file_extensions
     * @return bool
     */
    public function checkFileExtensions(bool $_is_case_sensitive, string ...$_supported_file_extensions): bool
    {
        $isGoodExt = $this->checkFileExtensionsBase($this->filenameTemp, $_is_case_sensitive,
            ...$_supported_file_extensions);

        // Удалим некорректный файл.
        if (!$isGoodExt && $this->isAutoDeleteIfBad) {
            $this->delete();
        }

        return $isGoodExt;
    }

    /**
     * Проверить, соответствует ли расширение файла заданному списку расширений.
     *
     * @param string $_filename Имя файла.
     * @param bool $_is_case_sensitive Проверять с учетом регистра?
     * @param string ...$_supported_file_extensions Массив расширений.
     *
     * @return bool Соответствует ли расширение файла одному из заданных?
     */
    public function checkFileExtensionsBase(
        string $_filename,
        bool $_is_case_sensitive,
        string ...$_supported_file_extensions
    ): bool {
        // Получим оригинальное расширение файла и поддерживаемые расширения.
        $fileExtOriginal       = $this->storageService->getFileExtension($_filename);
        $supportedExtsOriginal = $_supported_file_extensions;

        $fileExt       = $fileExtOriginal;
        $supportedExts = $supportedExtsOriginal;
        if (!$_is_case_sensitive) {
            $fileExt       = \mb_strtolower($fileExtOriginal);
            $supportedExts = \array_map('\mb_strtolower', $supportedExtsOriginal);
        }

        if (\in_array($fileExt, $supportedExts, true)) {
            return true;
        }

        return false;
    }

    public function checkFileSize(int $_max_size_in_bytes): bool
    {
        if ($this->getFilesize() > $_max_size_in_bytes) {
            // Удалим некорректный файл.
            if ($this->isAutoDeleteIfBad) {
                $this->delete();
            }
            return false;
        }

        return true;
    }

    public function getFilenameTemp(): string
    {
        return $this->filenameTemp;
    }

    public function getExtensionFilenameTemp(): string
    {
        return $this->storageService->getFileExtension($this->filenameTemp);
    }

    public function getFilesize(): int
    {
        return $this->storageService->getFileSize($this->filenameTemp);
    }

    public function getLastKnownFilesizeHumanReadable(): string
    {
        return $this->lastKnownFilesizeHumanReadable;
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    public function getErrorDescription(): string
    {
        return $this->errorDescription;
    }

    public function delete(): bool
    {
        return $this->storageService->delete($this->filenameTemp);
    }
}

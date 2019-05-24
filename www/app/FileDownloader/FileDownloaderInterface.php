<?php

namespace App\FileDownloader;

/**
 * Интерфейс объекта загрузки файлов и их удобной проверки.
 */
interface FileDownloaderInterface
{
    public function downloadToTemp(): bool;

    public function checkFileExtensions(bool $_is_case_sensitive, string ...$_supported_file_extensions): bool;

    public function checkFileSize(int $_max_size_in_bytes): bool;

    public function getFilenameTemp(): string;

    public function getExtensionFilenameTemp(): string;

    public function getFilesize(): int;

    public function getLastKnownFilesizeHumanReadable(): string;

    public function getErrorCode(): int;

    public function getErrorDescription(): string;

    public function delete(): bool;
}

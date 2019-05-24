<?php

namespace App;

use App\FileDownloader\StorageService\StorageServiceInterface;

class ApplicationStorageService implements StorageServiceInterface
{
    /**
     * Сохранить файл во временную папку с рандомным именем.
     *
     * @param string $_content Содержимое файла.
     * @param string $_extension Расширение файла.
     * @param string $_name_prefix Префикс имени нового файла.
     *
     * @return string Абсолютное имя нового файла.
     */
    public function saveToNewTempFile(string $_content, string $_extension = '', string $_name_prefix = ''): string
    {
        $extension = empty($_extension) ? '' : '.' . $_extension;
        $newName   = $this->generateNewTempFilename($_name_prefix, $extension);

        \file_put_contents($newName, $_content);
        return $newName;
    }

    /**
     * Сгенерировать случайное имя для файла.
     *
     * @param string $_name_prefix Префикс имени нового файла.
     * @param string $_name_suffix Суффикс файла. Здесь можно задать имя и расширение.
     * @param bool $_is_create_file Создать пустой файл с новым именем?
     *
     * @return string Случайное сгенерированное имя файла.
     */
    public function generateNewTempFilename(
        string $_name_prefix = '',
        string $_name_suffix = '',
        bool $_is_create_file = false
    ): string {
        $filename = storage_path('app/temp/' . $_name_prefix . \microtime(true) . $_name_suffix);
        if ($_is_create_file && !\file_exists($filename)) {
            \file_put_contents($filename, '');
        }

        return $filename;
    }

    /**
     * Получить расширение файла.
     *
     * @param string $_filename
     * @return string
     */
    public function getFileExtension(string $_filename): string
    {
        return \pathinfo($_filename, \PATHINFO_EXTENSION);
    }

    /**
     * Получить размер файла в байтах.
     *
     * @param string $_filename
     * @return int
     */
    public function getFileSize(string $_filename): int
    {
        return \filesize($_filename);
    }

    /**
     * Удалить файл.
     *
     * @param string $_filename
     * @return bool
     */
    public function delete(string $_filename): bool
    {
        return \unlink($_filename);
    }
}

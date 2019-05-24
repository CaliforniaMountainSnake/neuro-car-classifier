<?php

namespace App\FileDownloader\StorageService;

interface StorageServiceInterface
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
    public function saveToNewTempFile(
        string $_content,
        string $_extension = '',
        string $_name_prefix = ''
    ): string;

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
    ): string;

    /**
     * Получить расширение файла.
     *
     * @param string $_filename
     * @return string
     */
    public function getFileExtension(string $_filename): string;


    /**
     * Получить размер файла в байтах.
     *
     * @param string $_filename
     * @return int
     */
    public function getFileSize(string $_filename): int;

    /**
     * Удалить файл.
     *
     * @param string $_filename
     * @return bool
     */
    public function delete(string $_filename): bool;
}

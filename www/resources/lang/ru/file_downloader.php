<?php

return [
    'error_file_download' => "<b>Ошибка загрузки файла!</b>\n"
        . 'Описание ошибки: <i>Code :error_code, :error_description.</i>',

    'error_file_bad_extension' => "<b>Ошибка! Неверное расширение файла (:current_bad_extension)!</b>\n"
        . "Доступны только следующие расширения:\n"
        . ':supported_extensions',

    'error_file_is_too_big' => '<b>Ошибка! Слишком большой размер файла'
        . ' (:current_human_readable_bad_size/:max_size_in_mb Mb)!</b>',
];

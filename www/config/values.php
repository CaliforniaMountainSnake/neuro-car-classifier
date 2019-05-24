<?php

/**
 * Custom application configs.
 */

$botIdExploded = \explode(':', env('TELEGRAMBOT_BOT_TOKEN'));
return [
    // Get-параметр с админским паролем. Необходимо для системных команд вроде установки вебхука.
    'secret_admin_password_query_param' => 'secret_admin_password',
    'secret_admin_password' => env('SECRET_ADMIN_PASSWORD'),
    'telegrambot_bot_token' => env('TELEGRAMBOT_BOT_TOKEN'),
    'telegrambot_username' => env('TELEGRAMBOT_USERNAME'),
    'telegrambot_id' => $botIdExploded[0],
    'telegrambot_admin_id' => env('TELEGRAMBOT_ADMIN_ID'),

    // Must be compatible with LangsEnum.
    'default_new_user_lang' => 'ru',
    'ssl_phpfpm_crt_file' => env('SSL_PHPFPM_CRT_FILE'),
    'python_container_port' => env('PYTHON_CONTAINER_PORT')
];

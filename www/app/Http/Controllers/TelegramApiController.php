<?php

namespace App\Http\Controllers;

use App\Http\Middleware\WithSecretAdminPasswordMiddleware;
use CaliforniaMountainSnake\SocialNetworksAPI\Telegram\SimpleTelegramApi;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Exception\TelegramLogException;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\TelegramLog;
use Symfony\Component\HttpFoundation\Response;

class TelegramApiController extends Controller
{
    public function __construct()
    {
        $this->middleware(WithSecretAdminPasswordMiddleware::class)->only([
            'setWebhook',
        ]);
    }

    /**
     * Установить обработчик адрес обработчика запросов для Telegram BOT API.
     *
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function setWebhook(): Response
    {
        $simple   = new SimpleTelegramApi();
        $hook_url = action('TelegramApiController@webhook');

        $response = $simple->setWebhookSelfSigned(
            config('values.telegrambot_bot_token'),
            $hook_url,
            config('values.ssl_phpfpm_crt_file')
        );

        return \response('Webhook URL: ' . $hook_url . "<br>\n"
            . '<pre>' . \print_r($response, true) . '</pre>');
    }

    /**
     * Обработать входящий запрос от Telegram BOT API.
     *
     * @return Response
     * @throws \LogicException
     */
    public function webhook(): Response
    {
        try {
            // Add you bot's API key and name
            $bot_api_key  = config('values.telegrambot_bot_token');
            $bot_username = config('values.telegrambot_username');

            // Define all IDs of admin users in this array (leave as empty array if not used)
            // In MUST be ints!
            $admin_users = [
                (int)config('values.telegrambot_admin_id')
            ];

            // Define all paths for your custom commands in this array (leave as empty array if not used)
            $commands_paths = [
                //    __DIR__ . '/Commands/',
                app_path('Telegrambot/Commands/UserCommands/'),
                app_path('Telegrambot/Commands/SystemCommands/')
            ];

            // Enter your MySQL database credentials
            $mysql_credentials = [
                'host' => config('database.connections.mysql_app.host'),
                'user' => config('database.connections.mysql_app.username'),
                'password' => config('database.connections.mysql_app.password'),
                'database' => config('database.connections.mysql_app.database'),
            ];

            // Create Telegram API object
            $telegram = new Telegram($bot_api_key, $bot_username);

            // Add commands paths containing your custom commands
            $telegram->addCommandsPaths($commands_paths);

            // Enable admin users
            $telegram->enableAdmins($admin_users);

            // Enable MySQL
            $telegram->enableMySql($mysql_credentials);

            // Set custom Upload and Download paths
            $telegram->setDownloadPath(storage_path('app/bot-download'));
            $telegram->setUploadPath(storage_path('app/bot-upload'));

            // Logging
            TelegramLog::initErrorLog(storage_path('logs/telegram_bot_raw/bot_errors.log'));

            // Requests Limiter (tries to prevent reaching Telegram API limits)
            $telegram->enableLimiter();

            // Handle telegram webhook request
            $telegram->handle();
        } catch (TelegramException $e) {
            log_telegram_bot()->error('LongmanTelegramBot webhook exception: ' . $e->getMessage());
            TelegramLog::error($e);
        } catch (TelegramLogException $e) {
            log_telegram_bot()->error('LongmanTelegramBot webhook exception: ' . $e->getMessage());
        }

        return response('Ok!');
    }
}

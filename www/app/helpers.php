<?php

/**
 * @return \Illuminate\Log\LogManager
 */
function log_laravel_main()
{
    return Log::channel(config('logging.default'));
}

/**
 * @return \Illuminate\Log\LogManager
 */
function log_telegram_bot()
{
    return Log::channel('telegram_bot');
}

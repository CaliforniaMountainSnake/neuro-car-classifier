<?php

return [
    'description' => 'Распознать изображение автомобиля',

    'error_wrong_message_type' => '<b>Неверный тип сообщения! Пришлите изображение.</b>',
    'maintext' => 'Пришлите изображение автомобиля и бот попытается предсказать его модель!',

    'result_text' => "Вероятно, на изображении:\n"
        . ':predictions_rows',

    'prediction_row' => "<b>:label</b> - :probability_percent%\n"
];

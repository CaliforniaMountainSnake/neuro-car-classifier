"""Flask-приложение по предсказанию класса изображения с помощью нейросети"""
import io
import logging
import os
from pathlib import Path

import PIL
import flask
from PIL import Image
from dotenv import load_dotenv
from flask import Flask

from mobilenetv2predictor import MobileNetV2Predictor

# Инициализируем переменные среды из .env-файла:
load_dotenv(Path('.') / '.env.docker')

# Инициализируем приложение Flask.
app = Flask(__name__)
model_filename = '../keras_models/' + os.getenv("NEURAL_NETWORK_MODEL_FILE")


def load_model():
    """Загрузить модель нейронной сети"""
    global predictor
    predictor = MobileNetV2Predictor(model_filename=model_filename)
    return


@app.errorhandler(405)
def method_not_allowed(e):
    return flask.jsonify(
        {'error': 405, 'description': 'Этот http-метод недоступен для выполнения данного запроса.'}), 405


@app.errorhandler(404)
def method_not_allowed(e):
    return flask.jsonify(
        {'error': 404, 'description': 'Указанный URL не найден.'}), 404


@app.route('/predict', methods=["POST"])
def predict():
    """Предсказать класс изображения, полученного методом POST, и выдать результат в виде JSON"""
    image_key = "image"
    result = {'success': False}
    if not flask.request.files.get(image_key):
        return flask.jsonify(
            {'error': 400, 'description': 'Вы не задали файл изображения в параметре "' + image_key + '"'}), 400

    # Получим изображение из запроса:
    image = flask.request.files[image_key].read()

    # Конвертируем изображение в формат PIL:
    original_pil_image: PIL.Image.Image = Image.open(io.BytesIO(image))
    preprocessed_pil_image = predictor.preprocess_pil_image(original_pil_image)

    # Выполняем предсказание:
    predictions = predictor.predict(preprocessed_pil_image)
    decoded_predictions = predictor.decode_predictions(predictions=predictions, top=5)
    print('decoded_predictions: ', decoded_predictions)

    # Получаем тепловую карту:
    # predictor.get_heat_map(last_conv_layer_name=predictor.get_last_conv_layer_name(),
    #                        original_pil_image=original_pil_image,
    #                        preprocessed_pil_image=preprocessed_pil_image,
    #                        result_heat_map_filename='heat_map.jpg')

    # Формируем выходной json-массив:
    result['predictions'] = []
    for (class_id, label, probability) in decoded_predictions[0]:
        row = {"class_id": class_id, "label": label, "probability": float(probability)}
        result["predictions"].append(row)
    result['success'] = True

    return flask.jsonify(result)


if __name__ == "__main__":
    """В случае использования модуля как программы, загрузить модель нейросети и запустить web-сервер"""
    logging.basicConfig(filename='/var/log/flask_error.log', level=logging.DEBUG)
    load_model()
    app.run(host="0.0.0.0", debug=True)

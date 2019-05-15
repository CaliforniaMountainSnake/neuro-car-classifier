import operator
from typing import Callable, List

import PIL
import numpy as np
from PIL import Image
from keras.engine.training import Model
from keras.preprocessing import image


class BasePredictor:
    """Предсказатель класса изображения на основе заданной нейросети"""

    def __init__(self, model: Model, preprocess_input: Callable, image_size_x: int, image_size_y: int,
                 labels_list: List[str]):
        """Инициализация параметров"""
        self.model: Model = model
        self.preprocess_input = preprocess_input
        self.image_size_x = image_size_x
        self.image_size_y = image_size_y
        self.labels_list = labels_list

        # Инициализируем функцию предсказания
        self.model._make_predict_function()

    def predict(self, np_array: np.ndarray):
        """Предсказать класс изображения с помощью нейросети"""
        return self.model.predict(np_array)

    def decode_predictions(self, predictions, top: int = 5):
        """Декодировать предсказания сети в список кортежей (класс, название класса, вероятность)"""
        result = []
        # Обходим пакеты предсказаний.
        for pred_index, prediction in enumerate(predictions):
            pred_list = []
            # Обходим лейблы.
            for index, label in enumerate(self.labels_list):
                pred_list.append((index, label, prediction[index]))
            result.append(sorted(pred_list, key=operator.itemgetter(2), reverse=True)[:top])
        return result

    def __str__(self) -> str:
        return str(self.model.to_json())

    def print_model(self):
        """Напечатать информацию о моделе"""
        print('MODEL TRAINABLE LAYERS:')
        for i, layer in enumerate(self.model.layers):
            print(i, ')', layer, layer.trainable)

        print('MODEL.SUMMARY:')
        self.model.summary()
        return

    def preprocess_pil_image(self, pil_image: PIL.Image.Image):
        """Дообработка изображения в формате PIL"""
        # Конвертировать изображение в формат RGB, если оно еще не в этом формате.
        if pil_image.mode != "RGB":
            pil_image = pil_image.convert("RGB")

        # Изменить размер изображения на подходящий нейросети.
        pil_image = pil_image.resize((self.image_size_x, self.image_size_y))

        # Преобразовать изображения из формата PIL в трехмерный массив Numpy.
        np_array = image.img_to_array(pil_image)

        # Выполнить обработку изображения собственной функцией препроцессинга нейросети.
        return self.preprocess_input(np.expand_dims(np_array, axis=0))

    def preprocess_image_from_file(self, image_filename: str):
        """Открыть изображение из файла и выполнить препроцессинг"""
        pil_image: PIL.Image.Image = image.load_img(image_filename)
        return self.preprocess_pil_image(pil_image)

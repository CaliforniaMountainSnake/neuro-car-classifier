import operator
from typing import Callable, List
import cv2
import PIL
import keras.backend as K
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
        self.predictions = None
        self.predicted_class = None

        # Инициализируем функцию предсказания
        self.model._make_predict_function()

    def predict(self, np_array: np.ndarray):
        """Предсказать класс изображения с помощью нейросети"""
        self.predictions = self.model.predict(np_array)
        self.predicted_class = np.argmax(self.predictions[0])
        return self.predictions

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

    def get_heat_map(self, last_conv_layer_name: str, original_pil_image: PIL.Image.Image,
                     preprocessed_pil_image: PIL.Image.Image, result_heat_map_filename: str,
                     heatmap_intensity_factor: float = 0.6):
        """Получить тепловую карту активации класса. Перед выполнение метода необходимо выполнить метод predict()."""
        # This is the "african elephant" entry in the prediction vector
        predicted_class_output = self.model.output[:, self.predicted_class]

        # The is the output feature map of the `block5_conv3` layer,
        # the last convolutional layer in VGG16
        last_conv_layer = self.model.get_layer(last_conv_layer_name)

        # This is the gradient of the "african elephant" class with regard to
        # the output feature map of `block5_conv3`
        grads = K.gradients(predicted_class_output, last_conv_layer.output)[0]

        # This is a vector of shape (512,), where each entry
        # is the mean intensity of the gradient over a specific feature map channel
        pooled_grads = K.mean(grads, axis=(0, 1, 2))
        channels_count = pooled_grads.shape[0]
        print('channels_count: ', channels_count)

        # This function allows us to access the values of the quantities we just defined:
        # `pooled_grads` and the output feature map of `block5_conv3`,
        # given a sample image
        iterate = K.function([self.model.input], [pooled_grads, last_conv_layer.output[0]])

        # These are the values of these two quantities, as Numpy arrays,
        # given our sample image of two elephants
        pooled_grads_value, conv_layer_output_value = iterate([preprocessed_pil_image])

        # We multiply each channel in the feature map array
        # by "how important this channel is" with regard to the elephant class
        for i in range(channels_count):
            conv_layer_output_value[:, :, i] *= pooled_grads_value[i]

        # The channel-wise mean of the resulting feature map
        # is our heatmap of class activation
        heatmap = np.mean(conv_layer_output_value, axis=-1)

        heatmap = np.maximum(heatmap, 0)
        heatmap /= np.max(heatmap)
        # plt.matshow(heatmap)
        # plt.show()

        # We use cv2 to load the original image
        # img = cv2.imread(original_image_filename)
        img = self.__convert_pil_image_to_cv_image(original_pil_image)

        # We resize the heatmap to have the same size as the original image
        heatmap = cv2.resize(heatmap, (img.shape[1], img.shape[0]))

        # We convert the heatmap to RGB
        heatmap = np.uint8(255 * heatmap)

        # We apply the heatmap to the original image
        heatmap = cv2.applyColorMap(heatmap, cv2.COLORMAP_JET)

        # 0.4 here is a heatmap intensity factor
        superimposed_img = heatmap * heatmap_intensity_factor + img

        # Save the image to disk
        cv2.imwrite(result_heat_map_filename, superimposed_img)
        return

    @staticmethod
    def __convert_pil_image_to_cv_image(pil_image):
        # Конвертировать изображение в формат RGB, если оно еще не в этом формате.
        if pil_image.mode != "RGB":
            pil_image = pil_image.convert("RGB")

        return cv2.cvtColor(np.array(pil_image), cv2.COLOR_RGB2BGR)

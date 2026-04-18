from flask import Flask, request, jsonify
import sys
import traceback
import numpy as np
import cv2
import tensorflow as tf
from keras.applications import MobileNetV2
from sklearn.metrics.pairwise import cosine_similarity
from livereload import Server
import pickle
import os

app = Flask(__name__)

# Ghi log vào file
log_file = "./app/logs/image_search.log"
model = MobileNetV2(weights="imagenet", include_top=False, pooling="avg")

def write_log(message):
    with open(log_file, "a", encoding="utf-8") as f:
        f.write(message + "\n")

@app.route('/api/image_search', methods=['POST'])
def image_search():
    try:
        write_log("=== Bắt đầu tìm kiếm ảnh ===")
        image_file = request.files['image']
        upload_dir = "/app/upload"
        os.makedirs(upload_dir, exist_ok=True)  # Tạo thư mục nếu chưa có

        image_path = os.path.join(upload_dir, image_file.filename)
        image_file.save(image_path)
        write_log(f"Ảnh tải lên: {image_path}")

        image = cv2.imread(image_path)
        if image is None:
            raise ValueError(f"Không thể đọc ảnh: {image_path}")

        image = cv2.resize(image, (224, 224))
        image = np.expand_dims(image, axis=0)
        image = tf.keras.applications.mobilenet_v2.preprocess_input(image)
        write_log("Xử lý ảnh thành công.")

        query_vector = model.predict(image)
        write_log("Trích xuất đặc trưng thành công.")

        with open("./app/data/features.pkl", "rb") as f:
            image_vectors, image_paths = pickle.load(f)
        write_log("Tải vector ảnh thành công.")
        
        # Tính toán độ tương đồng cosine
        similarities = cosine_similarity(query_vector, image_vectors)
        
        # Số lượng ảnh tương đồng cao nhất
        i = 5
        top_matches = np.argsort(similarities[0])[-i:][::-1]
        best_match_names = [os.path.basename(image_paths[i]) for i in top_matches]

        write_log(f"Tên ảnh kết quả: {best_match_names}")

        # Xóa ảnh sau khi xử lý xong
        if os.path.exists(image_path):
            os.remove(image_path)
            write_log(f"Đã xóa ảnh: {image_path}")

        return jsonify({'success': True, 'image_names': best_match_names})

    except Exception as e:
        error_message = traceback.format_exc()
        write_log(f"❌ Lỗi xảy ra: {error_message}")
        return jsonify({'success': False, 'message': 'Lỗi xảy ra trong quá trình xử lý ảnh', 'error': str(e)})
    
@app.route('/api/add_images', methods=['POST'])
def add_images():
    try:
        write_log("=== Bắt đầu thêm ảnh mới ===")
        write_log(f"Files nhận được: {request.files}")
        
        # Lấy danh sách các file ảnh từ request
        image_files = [file for key, file in request.files.items() if key.startswith('images[')]
        if not image_files:
            write_log("Không có ảnh nào được tải lên.")
            return jsonify({'success': False, 'message': 'Không có ảnh nào được tải lên'})

        upload_dir = "/app/upload"
        os.makedirs(upload_dir, exist_ok=True)  # Tạo thư mục nếu chưa có

        # Tải vector ảnh hiện tại
        with open("./app/data/features.pkl", "rb") as f:
            image_vectors, image_paths = pickle.load(f)
        write_log("Tải vector ảnh hiện tại thành công.")
        image_vectors = image_vectors.tolist()  # Chuyển đổi về danh sách để thêm vector mới
        image_paths = list(image_paths)  # Chuyển đổi về danh sách để thêm đường dẫn mới

        # Xử lý từng ảnh
        for image_file in image_files:
            image_path = os.path.join(upload_dir, image_file.filename)
            image_file.save(image_path)
            write_log(f"Ảnh tải lên: {image_path}")
            image_name = os.path.basename(image_path)

            # Xử lý ảnh
            image = cv2.imread(image_path)
            if image is None:
                write_log(f"Không thể đọc ảnh: {image_path}")
                continue  # Bỏ qua ảnh không hợp lệ

            image = cv2.resize(image, (224, 224))
            image = np.expand_dims(image, axis=0)
            image = tf.keras.applications.mobilenet_v2.preprocess_input(image)
            feature_vector = model.predict(image)[0]

            # Thêm vector vào danh sách
            image_vectors.append(feature_vector)
            image_paths.append(image_name)

            write_log(f"Đã thêm ảnh: {image_name}")

            # Xóa ảnh sau khi xử lý xong
            if os.path.exists(image_path):
                os.remove(image_path)
                write_log(f"Đã xóa ảnh: {image_path}")

        # Lưu lại vector và đường dẫn ảnh
        with open("./app/data/features.pkl", "wb") as f:
            pickle.dump((np.array(image_vectors), image_paths), f)
        write_log("Lưu vector ảnh thành công.")

        return jsonify({'success': True, 'message': 'Tất cả ảnh đã được thêm thành công'})

    except Exception as e:
        error_message = traceback.format_exc()
        write_log(f"❌ Lỗi xảy ra: {error_message}")
        return jsonify({'success': False, 'message': 'Lỗi xảy ra trong quá trình thêm ảnh', 'error': str(e)})

@app.route('/')
def home():
    weather_data = {
        "city": "HN",
        "temperature": 30,
        "humidity": 70,
        "description": "Partly Cloudy"
    }
    return jsonify(weather_data)

if __name__ == '__main__':
    server = Server(app.wsgi_app)
    server.serve(host='0.0.0.0', port=5000, debug=True)
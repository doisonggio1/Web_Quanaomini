import sys
import traceback
import numpy as np
import cv2
import tensorflow as tf
from keras.applications import MobileNetV2
from sklearn.metrics.pairwise import cosine_similarity
import pickle
import os

# Ghi log vào file
log_file = "webquanao/application/logs/image_search.log"

def write_log(message):
    with open(log_file, "a", encoding="utf-8") as f:
        f.write(message + "\n")

try:
    write_log("=== Bắt đầu tìm kiếm ảnh ===")

    write_log("Đang tải mô hình MobileNetV2...")
    model = MobileNetV2(weights="imagenet", include_top=False, pooling="avg")
    write_log("Tải mô hình thành công.")

    write_log("Đang tải dữ liệu vector sản phẩm...")
    with open("webquanao/application/data/features.pkl", "rb") as f:
        image_vectors, image_paths = pickle.load(f)
    write_log(f"Tải thành công {len(image_paths)} ảnh.")

    image_path = sys.argv[1]
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

    similarities = cosine_similarity(query_vector, image_vectors)
    
    # Số lượng ảnh tương đồng cao nhất
    i = 10
    top_matches = np.argsort(similarities[0])[-i:][::-1]
    best_match_names = [os.path.basename(image_paths[i]) for i in top_matches]

    write_log(f"Tên ảnh kết quả: {best_match_names}")

    # In ra tên ảnh cách nhau bởi dấu phẩy
    print(",".join(best_match_names))

    # Xóa ảnh sau khi xử lý xong
    if os.path.exists(image_path):
        os.remove(image_path)
        write_log(f"Đã xóa ảnh: {image_path}")

except Exception as e:
    error_message = traceback.format_exc()
    write_log(f"❌ Lỗi xảy ra: {error_message}")
    print("ERROR")
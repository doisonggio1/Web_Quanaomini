import os
import pickle
import numpy as np
import cv2
import tensorflow as tf
from keras.applications import MobileNetV2

# Định nghĩa thư mục chứa ảnh sản phẩm
IMAGE_DIR = "webquanao/upload/product"
FEATURES_FILE = "webquanao/application/data/features.pkl"

# Kiểm tra thư mục ảnh có tồn tại không
if not os.path.exists(IMAGE_DIR):
    print(f"❌ Lỗi: Thư mục ảnh '{IMAGE_DIR}' không tồn tại!")
    exit()

# Tạo mô hình trích xuất đặc trưng
print("🔄 Đang tải mô hình MobileNetV2...")
model = MobileNetV2(weights="imagenet", include_top=False, pooling="avg")
print("✅ Mô hình đã tải thành công.")

# Danh sách lưu vector đặc trưng và đường dẫn ảnh
image_vectors = []
image_paths = []

# Duyệt qua từng ảnh trong thư mục
print(f"📸 Đang xử lý ảnh trong '{IMAGE_DIR}'...")
for filename in os.listdir(IMAGE_DIR):
    img_path = os.path.join(IMAGE_DIR, filename)
    image = cv2.imread(img_path)

    if image is None:
        print(f"⚠ Không thể đọc ảnh: {img_path}")
        continue

    # Xử lý ảnh
    image = cv2.resize(image, (224, 224))
    image = np.expand_dims(image, axis=0)
    image = tf.keras.applications.mobilenet_v2.preprocess_input(image)

    # Trích xuất đặc trưng
    feature_vector = model.predict(image)[0]
    image_vectors.append(feature_vector)
    image_paths.append(filename)

# Kiểm tra có ảnh nào được xử lý không
if len(image_vectors) == 0:
    print("❌ Không có ảnh nào được xử lý!")
    exit()

# Lưu dữ liệu
os.makedirs(os.path.dirname(FEATURES_FILE), exist_ok=True)  # Đảm bảo thư mục tồn tại
with open(FEATURES_FILE, "wb") as f:
    pickle.dump((np.array(image_vectors), image_paths), f)

print(f"✅ Đã lưu {len(image_paths)} vector đặc trưng vào '{FEATURES_FILE}'")

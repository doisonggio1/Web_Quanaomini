# Sử dụng image Python chính thức
FROM python:3.10

# Cài đặt thư viện hệ thống cần thiết
RUN apt-get update && apt-get install -y libgl1 libglib2.0-0
# Thiết lập thư mục làm việc trong container
WORKDIR /app

# Copy file requirements.txt vào container
COPY requirements.txt requirements.txt

# Cài đặt các thư viện cần thiết
RUN pip install --no-cache-dir -r requirements.txt

# Copy toàn bộ mã nguồn vào container
COPY . .

# Chạy ứng dụng Flask
CMD ["python", "app.py"]
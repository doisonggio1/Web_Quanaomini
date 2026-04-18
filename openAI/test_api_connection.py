import os
import requests
import json
import logging
from dotenv import load_dotenv

# Tải biến môi trường từ file .env
load_dotenv()

# Cấu hình logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)

# Lấy API key từ biến môi trường
api_key = os.environ.get('DEEPSEEK_API_KEY')
model_name = os.environ.get('DEEPSEEK_MODEL', 'deepseek/deepseek-chat-v3-0324:free')

def test_api_key_validity():
    """Kiểm tra tính hợp lệ của API key"""
    try:
        logging.info("Kiểm tra tính hợp lệ của API key...")
        headers = {"Authorization": f"Bearer {api_key}"}
        response = requests.head("https://openrouter.ai/api/v1/auth/key", headers=headers, timeout=10)
        
        if response.status_code == 200:
            logging.info("✅ API key hợp lệ và đang hoạt động")
            return True
        else:
            logging.error(f"❌ API key không hợp lệ hoặc hết hạn. Mã trạng thái: {response.status_code}")
            if response.text:
                logging.error(f"Chi tiết lỗi: {response.text}")
            return False
    except Exception as e:
        logging.error(f"❌ Lỗi khi kiểm tra API key: {str(e)}")
        return False

def test_api_connection():
    """Kiểm tra kết nối đến API và gửi một request đơn giản"""
    try:
        logging.info("Kiểm tra kết nối đến OpenRouter API...")
        url = "https://openrouter.ai/api/v1/chat/completions"
        headers = {
            "Authorization": f"Bearer {api_key}",
            "HTTP-Referer": "http://localhost:5002",
            "X-Title": "API Connection Test",
            "Content-Type": "application/json"
        }
        
        # Tạo một prompt đơn giản để kiểm tra
        data = {
            "model": model_name,
            "messages": [{"role": "user", "content": "Xin chào, đây là tin nhắn kiểm tra kết nối. Hãy trả lời ngắn gọn."}],
            "temperature": 0.7,
            "max_tokens": 50
        }
        
        logging.info(f"Gửi request đến OpenRouter API với model: {model_name}")
        response = requests.post(url, headers=headers, json=data, timeout=30)
        
        if response.status_code == 200:
            response_json = response.json()
            logging.info("✅ Kết nối thành công đến OpenRouter API")
            if 'choices' in response_json and len(response_json['choices']) > 0:
                content = response_json['choices'][0]['message']['content']
                logging.info(f"Phản hồi từ API: {content}")
                return True, content
            else:
                logging.warning("⚠️ Nhận được phản hồi nhưng không có nội dung")
                return True, "Không có nội dung"
        else:
            error_detail = ""
            try:
                error_json = response.json()
                if 'error' in error_json:
                    if isinstance(error_json['error'], dict) and 'message' in error_json['error']:
                        error_detail = f" - Chi tiết: {error_json['error']['message']}"
                    else:
                        error_detail = f" - Chi tiết: {error_json['error']}"
            except:
                error_detail = f" - Nội dung phản hồi: {response.text[:200]}"
            
            logging.error(f"❌ Lỗi khi gọi OpenRouter API: Mã trạng thái {response.status_code}{error_detail}")
            return False, f"Lỗi: {response.status_code}{error_detail}"
    except Exception as e:
        logging.error(f"❌ Lỗi khi kết nối đến OpenRouter API: {str(e)}")
        return False, str(e)

def main():
    logging.info("=== BẮT ĐẦU KIỂM TRA KẾT NỐI API ===")
    logging.info(f"API Key: {api_key[:8]}...")
    logging.info(f"Model: {model_name}")
    
    # Kiểm tra tính hợp lệ của API key
    key_valid = test_api_key_validity()
    
    if key_valid:
        # Kiểm tra kết nối và gửi request
        connection_success, response = test_api_connection()
        
        if connection_success:
            logging.info("✅ Tất cả kiểm tra đều thành công!")
            logging.info("=== KẾT THÚC KIỂM TRA KẾT NỐI API ===")
            return True
    
    logging.error("❌ Kiểm tra kết nối API thất bại!")
    logging.info("=== KẾT THÚC KIỂM TRA KẾT NỐI API ===")
    return False

if __name__ == "__main__":
    main()
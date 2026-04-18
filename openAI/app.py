import os
import json
import logging
from PIL import Image
from google.cloud import vision
from openai import OpenAI
from flask import Flask, request, jsonify
import requests
from werkzeug.utils import secure_filename
from flask_cors import CORS  # Thêm CORS để giải quyết vấn đề kết nối từ frontend
from dotenv import load_dotenv

# Tải biến môi trường từ file .env
load_dotenv()

# Cấu hình logging
import os
log_dir = 'logs'
os.makedirs(log_dir, exist_ok=True)
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler(os.path.join(log_dir, 'openai_service.log')),
        logging.StreamHandler()
    ]
)

# Khởi tạo Flask app
app = Flask(__name__)

class ProductDescriber:
    model_name = os.environ.get('DEEPSEEK_MODEL', 'deepseek/deepseek-chat-v3-0324:free')
    api_key = None
    api_initialized = False
    vision_client = None
    vision_api_key = None

    def __init__(self):
        pass

    def analyze_image(self, image_path):
        """
        Phân tích ảnh sản phẩm bằng Google Vision AI
        Trả về các đặc điểm nhận dạng được từ ảnh
        """
        if not self.vision_client:
            # Chế độ mô phỏng khi không có Vision API
            logging.info("Đang sử dụng chế độ mô phỏng cho Vision API")
            # Trả về một số đặc điểm mẫu cho mục đích phát triển
            return "quần áo, thời trang, chất liệu cao cấp, thiết kế hiện đại, màu sắc tươi sáng"

        try:
            with open(image_path, 'rb') as img_file:
                content = img_file.read()
            
            # Tạo request trực tiếp đến Vision API với API key
            image = vision.Image(content=content)
            
            # Sử dụng client đã được cấu hình với API key
            response = self.vision_client.label_detection(image=image)
            labels = [label.description for label in response.label_annotations]
            
            return ", ".join(labels)
        except Exception as e:
            logging.error(f'Lỗi phân tích ảnh: {str(e)}')
            # Trả về đặc điểm mẫu nếu có lỗi
            return "quần áo, thời trang, chất liệu, thiết kế"

    def _validate_api_key(self, api_key):
        """Kiểm tra tính hợp lệ của OpenRouter API key"""
        try:
            import requests
            headers = {"Authorization": f"Bearer {api_key}"}
            # Thêm timeout để tránh chờ quá lâu khi mạng không ổn định
            response = requests.head("https://openrouter.ai/api/v1/auth/key", headers=headers, timeout=10)
            
            if response.status_code == 200:
                logging.info("API key hợp lệ và đang hoạt động")
                return True
            else:
                logging.error(f"API key không hợp lệ hoặc hết hạn. Mã trạng thái: {response.status_code}")
                if response.text:
                    logging.error(f"Chi tiết lỗi: {response.text}")
                return False
        except requests.exceptions.Timeout:
            logging.error("Timeout khi kiểm tra API key: Kết nối đến OpenRouter quá chậm hoặc không khả dụng")
            return False
        except requests.exceptions.ConnectionError:
            logging.error("Lỗi kết nối khi kiểm tra API key: Không thể kết nối đến OpenRouter")
            return False
        except Exception as e:
            logging.error(f"Lỗi khi kiểm tra API key: {str(e)}")
            return False
            
    def _validate_vision_api_key(self, api_key):
        """Kiểm tra tính hợp lệ của Google Vision API key"""
        try:
            import requests
            # Thử gọi một request đơn giản đến Vision API để kiểm tra key
            url = f"https://vision.googleapis.com/v1/images:annotate?key={api_key}"
            headers = {"Content-Type": "application/json"}
            # Tạo một request đơn giản
            data = {
                "requests": [{
                    "image": {"content": ""},
                    "features": [{"type": "LABEL_DETECTION", "maxResults": 1}]
                }]
            }
            # Thêm timeout để tránh chờ quá lâu
            response = requests.post(url, headers=headers, json=data, timeout=10)
            
            # Kiểm tra response, nếu lỗi là do content trống thì key hợp lệ
            if response.status_code == 400:
                error_json = response.json()
                if 'error' in error_json and 'Invalid image' in str(error_json):
                    logging.info("Google Vision API key hợp lệ và đang hoạt động")
                    return True
                    
            # Nếu là lỗi xác thực thì key không hợp lệ
            if response.status_code == 403:
                logging.error(f"Google Vision API key không hợp lệ hoặc hết hạn. Mã trạng thái: {response.status_code}")
                if response.text:
                    logging.error(f"Chi tiết lỗi: {response.text}")
                return False
                
            # Trường hợp khác, giả định key có thể hợp lệ
            logging.warning(f"Không thể xác định chính xác tính hợp lệ của Google Vision API key. Mã trạng thái: {response.status_code}")
            return True
            
        except requests.exceptions.Timeout:
            logging.error("Timeout khi kiểm tra Google Vision API key: Kết nối quá chậm hoặc không khả dụng")
            return False
        except requests.exceptions.ConnectionError:
            logging.error("Lỗi kết nối khi kiểm tra Google Vision API key: Không thể kết nối đến Google")
            return False
        except Exception as e:
            logging.error(f"Lỗi khi kiểm tra Google Vision API key: {str(e)}")
            return False
            
    def call_openrouter(self, prompt, temperature=0.7, max_tokens=300):
        """Gọi API OpenRouter để tạo nội dung"""
        try:
            # Kiểm tra kết nối internet trước khi gọi API
            try:
                # Kiểm tra kết nối đến Google (trang web đáng tin cậy)
                logging.info("Kiểm tra kết nối internet trước khi gọi OpenRouter API")
                requests.head("https://www.google.com", timeout=5)
            except requests.exceptions.RequestException as e:
                logging.error(f"Không thể kết nối internet trước khi gọi OpenRouter API: {str(e)}")
                raise ConnectionError("Không thể kết nối internet. Vui lòng kiểm tra kết nối mạng của bạn.")
                
            # Kiểm tra kết nối đến OpenRouter trước khi gọi API
            try:
                logging.info("Kiểm tra kết nối đến OpenRouter...")
                requests.head("https://openrouter.ai", timeout=5)
            except requests.exceptions.RequestException as e:
                logging.error(f"Không thể kết nối đến OpenRouter: {str(e)}")
                raise ConnectionError("Không thể kết nối đến dịch vụ OpenRouter. Có thể do tường lửa hoặc cài đặt mạng.")
                
            url = "https://openrouter.ai/api/v1/chat/completions"
            headers = {
                "Authorization": f"Bearer {self.api_key}",
                "HTTP-Referer": "http://localhost:5002",
                "X-Title": "Product Description Generator",
                "Content-Type": "application/json",
                "Origin": "http://localhost:5002"  # Thêm Origin header để giúp với CORS
            }
            data = {
                "model": self.model_name,
                "messages": [{"role": "user", "content": prompt}],
                "temperature": temperature,
                "max_tokens": max_tokens
            }
            
            logging.info(f"Gửi request đến OpenRouter API với model: {self.model_name}")
            # Thêm timeout để tránh chờ quá lâu khi mạng không ổn định
            response = requests.post(url, headers=headers, json=data, timeout=30)
            
            if response.status_code == 200:
                logging.info("Nhận phản hồi thành công từ OpenRouter API")
                return response.json()
            else:
                error_detail = ""
                try:
                    error_json = response.json()
                    if 'error' in error_json:
                        if isinstance(error_json['error'], dict) and 'message' in error_json['error']:
                            error_detail = f" - Chi tiết: {error_json['error']['message']}"
                        else:
                            error_detail = f" - Chi tiết: {error_json['error']}"
                except Exception as parse_error:
                    logging.error(f"Không thể phân tích phản hồi lỗi: {str(parse_error)}")
                    error_detail = f" - Nội dung phản hồi: {response.text[:200]}"
                
                error_message = f"Lỗi khi gọi OpenRouter API: Mã trạng thái {response.status_code}{error_detail}"
                logging.error(error_message)
                
                if response.status_code == 401 or response.status_code == 403:
                    raise ValueError("API key không hợp lệ hoặc hết hạn")
                elif response.status_code == 429:
                    raise ValueError("Đã vượt quá giới hạn tốc độ (rate limit) của API")
                elif response.status_code >= 500:
                    raise ConnectionError("Lỗi máy chủ OpenRouter, vui lòng thử lại sau")
                else:
                    raise ValueError(f"Lỗi không xác định: {error_message}")
        except requests.exceptions.Timeout:
            error_msg = "Timeout khi gọi OpenRouter API: Kết nối quá chậm hoặc không khả dụng"
            logging.error(error_msg)
            raise TimeoutError(error_msg)
        except requests.exceptions.ConnectionError as conn_error:
            error_msg = f"Lỗi kết nối khi gọi OpenRouter API: {str(conn_error)}"
            logging.error(error_msg)
            # Thêm thông tin chi tiết hơn về lỗi kết nối
            if "ProxyError" in str(conn_error):
                error_msg += ". Có thể do cài đặt proxy không chính xác."
            elif "SSLError" in str(conn_error):
                error_msg += ". Có thể do vấn đề với chứng chỉ SSL."
            elif "ConnectionRefusedError" in str(conn_error):
                error_msg += ". Kết nối bị từ chối, có thể do tường lửa hoặc dịch vụ không khả dụng."
            logging.error(f"Chi tiết lỗi kết nối: {error_msg}")
            raise ConnectionError(error_msg)
        except (ValueError, ConnectionError, TimeoutError) as known_error:
            # Chuyển tiếp các lỗi đã xử lý
            raise
        except Exception as e:
            error_msg = f"Lỗi không xác định khi gọi OpenRouter API: {str(e)}"
            logging.error(error_msg)
            raise RuntimeError(error_msg)
            
    def _ensure_ai_client(self):
        """Đảm bảo OpenRouter API đã được khởi tạo"""
        if not self.api_initialized:
            logging.warning("API chưa được khởi tạo, đang thử khởi tạo lại...")
            try:
                # Thử lấy API key từ biến môi trường, nếu không có thì dùng giá trị mặc định
                self.api_key = os.environ.get('DEEPSEEK_API_KEY')
                logging.info(f"Đang kiểm tra API key: {self.api_key[:10]}...")
                
                # Kiểm tra kết nối internet trước khi xác thực API key
                try:
                    # Kiểm tra kết nối đến Google (trang web đáng tin cậy)
                    requests.head("https://www.google.com", timeout=5)
                except requests.exceptions.RequestException as e:
                    logging.error(f"Không thể kết nối internet. Vui lòng kiểm tra kết nối mạng: {str(e)}")
                    return False
                
                # Kiểm tra kết nối đến OpenRouter trước khi xác thực API key
                try:
                    logging.info("Kiểm tra kết nối đến OpenRouter...")
                    requests.head("https://openrouter.ai", timeout=5)
                except requests.exceptions.RequestException as e:
                    logging.error(f"Không thể kết nối đến OpenRouter: {str(e)}")
                    # Kiểm tra các vấn đề cụ thể
                    if "ProxyError" in str(e):
                        logging.error("Có thể do cài đặt proxy không chính xác.")
                    elif "SSLError" in str(e):
                        logging.error("Có thể do vấn đề với chứng chỉ SSL.")
                    elif "ConnectionRefusedError" in str(e):
                        logging.error("Kết nối bị từ chối, có thể do tường lửa.")
                    return False
                
                # Kiểm tra tính hợp lệ của API key
                if self._validate_api_key(self.api_key):
                    self.api_initialized = True
                    logging.info("Khởi tạo lại API thành công")
                    return True
                else:
                    logging.error("API key không hợp lệ hoặc dịch vụ OpenRouter không khả dụng")
                    return False
            except Exception as e:
                logging.error(f"Không thể khởi tạo lại API: {str(e)}")
                return False
        return True
        
        # Khởi tạo Google Vision Client
        try:
            # Sử dụng biến môi trường cho Google Vision API key
            self.vision_api_key = os.environ.get('GOOGLE_VISION_API_KEY')
            if not self.vision_api_key:
                logging.warning('GOOGLE_VISION_API_KEY không được cấu hình trong biến môi trường')
                logging.info('Sử dụng chế độ không có Vision API')
                self.vision_client = None
            else:
                # Kiểm tra tính hợp lệ của API key
                if self._validate_vision_api_key(self.vision_api_key):
                    try:
                        # Khởi tạo client với API key
                        logging.info('Đang khởi tạo Vision API client với API key')
                        # Tạo credentials từ API key
                        from google.oauth2.service_account import Credentials
                        from google.auth import credentials
                        
                        # Sử dụng API key để xác thực
                        self.vision_client = vision.ImageAnnotatorClient(credentials=credentials.AnonymousCredentials())
                        # Cấu hình endpoint với API key
                        self.vision_client._transport.channel._credentials = credentials.AnonymousCredentials()
                        self.vision_client._transport._host = f"https://vision.googleapis.com/v1?key={self.vision_api_key}"
                        
                        logging.info('Khởi tạo Vision API client thành công')
                    except Exception as e:
                        logging.error(f'Lỗi khi khởi tạo Vision API: {str(e)}')
                        print(f"Lỗi khi khởi tạo Vision API: {str(e)}. Sử dụng chế độ không có Vision API.")
                        self.vision_client = None
                else:
                    logging.error('Google Vision API key không hợp lệ')
                    print("Google Vision API key không hợp lệ. Sử dụng chế độ không có Vision API.")
                    self.vision_client = None
        except Exception as e:
            logging.error(f'Lỗi khởi tạo Vision Client: {str(e)}')
            self.vision_client = None

    def analyze_image(self, image_path):
        """
        Phân tích ảnh sản phẩm bằng Google Vision AI
        Trả về các đặc điểm nhận dạng được từ ảnh
        """
        if not self.vision_client:
            # Chế độ mô phỏng khi không có Vision API
            logging.info("Đang sử dụng chế độ mô phỏng cho Vision API")
            # Trả về một số đặc điểm mẫu cho mục đích phát triển
            return "quần áo, thời trang, chất liệu cao cấp, thiết kế hiện đại, màu sắc tươi sáng"

        try:
            with open(image_path, 'rb') as img_file:
                content = img_file.read()
            
            # Tạo request trực tiếp đến Vision API với API key
            image = vision.Image(content=content)
            
            # Sử dụng client đã được cấu hình với API key
            response = self.vision_client.label_detection(image=image)
            labels = [label.description for label in response.label_annotations]
            
            return ", ".join(labels)
        except Exception as e:
            logging.error(f'Lỗi phân tích ảnh: {str(e)}')
            # Trả về đặc điểm mẫu nếu có lỗi
            return "quần áo, thời trang, chất liệu, thiết kế"

    def _validate_api_key(self, api_key):
        """Kiểm tra tính hợp lệ của OpenRouter API key"""
        try:
            import requests
            headers = {"Authorization": f"Bearer {api_key}"}
            # Thêm timeout để tránh chờ quá lâu khi mạng không ổn định
            response = requests.head("https://openrouter.ai/api/v1/auth/key", headers=headers, timeout=10)
            
            if response.status_code == 200:
                logging.info("API key hợp lệ và đang hoạt động")
                return True
            else:
                logging.error(f"API key không hợp lệ hoặc hết hạn. Mã trạng thái: {response.status_code}")
                if response.text:
                    logging.error(f"Chi tiết lỗi: {response.text}")
                return False
        except requests.exceptions.Timeout:
            logging.error("Timeout khi kiểm tra API key: Kết nối đến OpenRouter quá chậm hoặc không khả dụng")
            return False
        except requests.exceptions.ConnectionError:
            logging.error("Lỗi kết nối khi kiểm tra API key: Không thể kết nối đến OpenRouter")
            return False
        except Exception as e:
            logging.error(f"Lỗi khi kiểm tra API key: {str(e)}")
            return False
            
    def _validate_vision_api_key(self, api_key):
        """Kiểm tra tính hợp lệ của Google Vision API key"""
        try:
            import requests
            # Thử gọi một request đơn giản đến Vision API để kiểm tra key
            url = f"https://vision.googleapis.com/v1/images:annotate?key={api_key}"
            headers = {"Content-Type": "application/json"}
            # Tạo một request đơn giản
            data = {
                "requests": [{
                    "image": {"content": ""},
                    "features": [{"type": "LABEL_DETECTION", "maxResults": 1}]
                }]
            }
            # Thêm timeout để tránh chờ quá lâu
            response = requests.post(url, headers=headers, json=data, timeout=10)
            
            # Kiểm tra response, nếu lỗi là do content trống thì key hợp lệ
            if response.status_code == 400:
                error_json = response.json()
                if 'error' in error_json and 'Invalid image' in str(error_json):
                    logging.info("Google Vision API key hợp lệ và đang hoạt động")
                    return True
                    
            # Nếu là lỗi xác thực thì key không hợp lệ
            if response.status_code == 403:
                logging.error(f"Google Vision API key không hợp lệ hoặc hết hạn. Mã trạng thái: {response.status_code}")
                if response.text:
                    logging.error(f"Chi tiết lỗi: {response.text}")
                return False
                
            # Trường hợp khác, giả định key có thể hợp lệ
            logging.warning(f"Không thể xác định chính xác tính hợp lệ của Google Vision API key. Mã trạng thái: {response.status_code}")
            return True
            
        except requests.exceptions.Timeout:
            logging.error("Timeout khi kiểm tra Google Vision API key: Kết nối quá chậm hoặc không khả dụng")
            return False
        except requests.exceptions.ConnectionError:
            logging.error("Lỗi kết nối khi kiểm tra Google Vision API key: Không thể kết nối đến Google")
            return False
        except Exception as e:
            logging.error(f"Lỗi khi kiểm tra Google Vision API key: {str(e)}")
            return False
            
    def call_openrouter(self, prompt, temperature=0.7, max_tokens=300):
        """Gọi API OpenRouter để tạo nội dung"""
        try:
            # Kiểm tra kết nối internet trước khi gọi API
            try:
                # Kiểm tra kết nối đến Google (trang web đáng tin cậy)
                logging.info("Kiểm tra kết nối internet trước khi gọi OpenRouter API")
                requests.head("https://www.google.com", timeout=5)
            except requests.exceptions.RequestException as e:
                logging.error(f"Không thể kết nối internet trước khi gọi OpenRouter API: {str(e)}")
                raise ConnectionError("Không thể kết nối internet. Vui lòng kiểm tra kết nối mạng của bạn.")
                
            # Kiểm tra kết nối đến OpenRouter trước khi gọi API
            try:
                logging.info("Kiểm tra kết nối đến OpenRouter...")
                requests.head("https://openrouter.ai", timeout=5)
            except requests.exceptions.RequestException as e:
                logging.error(f"Không thể kết nối đến OpenRouter: {str(e)}")
                raise ConnectionError("Không thể kết nối đến dịch vụ OpenRouter. Có thể do tường lửa hoặc cài đặt mạng.")
                
            url = "https://openrouter.ai/api/v1/chat/completions"
            headers = {
                "Authorization": f"Bearer {self.api_key}",
                "HTTP-Referer": "http://localhost:5002",
                "X-Title": "Product Description Generator",
                "Content-Type": "application/json",
                "Origin": "http://localhost:5002"  # Thêm Origin header để giúp với CORS
            }
            data = {
                "model": self.model_name,
                "messages": [{"role": "user", "content": prompt}],
                "temperature": temperature,
                "max_tokens": max_tokens
            }
            
            logging.info(f"Gửi request đến OpenRouter API với model: {self.model_name}")
            # Thêm timeout để tránh chờ quá lâu khi mạng không ổn định
            response = requests.post(url, headers=headers, json=data, timeout=30)
            
            if response.status_code == 200:
                logging.info("Nhận phản hồi thành công từ OpenRouter API")
                return response.json()
            else:
                error_detail = ""
                try:
                    error_json = response.json()
                    if 'error' in error_json:
                        if isinstance(error_json['error'], dict) and 'message' in error_json['error']:
                            error_detail = f" - Chi tiết: {error_json['error']['message']}"
                        else:
                            error_detail = f" - Chi tiết: {error_json['error']}"
                except Exception as parse_error:
                    logging.error(f"Không thể phân tích phản hồi lỗi: {str(parse_error)}")
                    error_detail = f" - Nội dung phản hồi: {response.text[:200]}"
                
                error_message = f"Lỗi khi gọi OpenRouter API: Mã trạng thái {response.status_code}{error_detail}"
                logging.error(error_message)
                
                if response.status_code == 401 or response.status_code == 403:
                    raise ValueError("API key không hợp lệ hoặc hết hạn")
                elif response.status_code == 429:
                    raise ValueError("Đã vượt quá giới hạn tốc độ (rate limit) của API")
                elif response.status_code >= 500:
                    raise ConnectionError("Lỗi máy chủ OpenRouter, vui lòng thử lại sau")
                else:
                    raise ValueError(f"Lỗi không xác định: {error_message}")
        except requests.exceptions.Timeout:
            error_msg = "Timeout khi gọi OpenRouter API: Kết nối quá chậm hoặc không khả dụng"
            logging.error(error_msg)
            raise TimeoutError(error_msg)
        except requests.exceptions.ConnectionError as conn_error:
            error_msg = f"Lỗi kết nối khi gọi OpenRouter API: {str(conn_error)}"
            logging.error(error_msg)
            # Thêm thông tin chi tiết hơn về lỗi kết nối
            if "ProxyError" in str(conn_error):
                error_msg += ". Có thể do cài đặt proxy không chính xác."
            elif "SSLError" in str(conn_error):
                error_msg += ". Có thể do vấn đề với chứng chỉ SSL."
            elif "ConnectionRefusedError" in str(conn_error):
                error_msg += ". Kết nối bị từ chối, có thể do tường lửa hoặc dịch vụ không khả dụng."
            logging.error(f"Chi tiết lỗi kết nối: {error_msg}")
            raise ConnectionError(error_msg)
        except (ValueError, ConnectionError, TimeoutError) as known_error:
            # Chuyển tiếp các lỗi đã xử lý
            raise
        except Exception as e:
            error_msg = f"Lỗi không xác định khi gọi OpenRouter API: {str(e)}"
            logging.error(error_msg)
            raise RuntimeError(error_msg)
            
    def _ensure_ai_client(self):
        """Đảm bảo OpenRouter API đã được khởi tạo"""
        if not self.api_initialized:
            logging.warning("API chưa được khởi tạo, đang thử khởi tạo lại...")
            try:
                # Thử lấy API key từ biến môi trường, nếu không có thì dùng giá trị mặc định
                self.api_key = os.environ.get('DEEPSEEK_API_KEY')
                logging.info(f"Đang kiểm tra API key: {self.api_key[:10]}...")
                
                # Kiểm tra kết nối internet trước khi xác thực API key
                try:
                    # Kiểm tra kết nối đến Google (trang web đáng tin cậy)
                    requests.head("https://www.google.com", timeout=5)
                except requests.exceptions.RequestException as e:
                    logging.error(f"Không thể kết nối internet. Vui lòng kiểm tra kết nối mạng: {str(e)}")
                    return False
                
                # Kiểm tra kết nối đến OpenRouter trước khi xác thực API key
                try:
                    logging.info("Kiểm tra kết nối đến OpenRouter...")
                    requests.head("https://openrouter.ai", timeout=5)
                except requests.exceptions.RequestException as e:
                    logging.error(f"Không thể kết nối đến OpenRouter: {str(e)}")
                    # Kiểm tra các vấn đề cụ thể
                    if "ProxyError" in str(e):
                        logging.error("Có thể do cài đặt proxy không chính xác.")
                    elif "SSLError" in str(e):
                        logging.error("Có thể do vấn đề với chứng chỉ SSL.")
                    elif "ConnectionRefusedError" in str(e):
                        logging.error("Kết nối bị từ chối, có thể do tường lửa.")
                    return False
                
                # Kiểm tra tính hợp lệ của API key
                if self._validate_api_key(self.api_key):
                    self.api_initialized = True
                    logging.info("Khởi tạo lại API thành công")
                    return True
                else:
                    logging.error("API key không hợp lệ hoặc dịch vụ OpenRouter không khả dụng")
                    return False
            except Exception as e:
                logging.error(f"Không thể khởi tạo lại API: {str(e)}")
                return False
        return True
        
    def _ensure_vision_client(self):
        """Đảm bảo Google Vision API client đã được khởi tạo"""
        if not self.vision_client:
            logging.warning("Google Vision API client chưa được khởi tạo, đang thử khởi tạo lại...")
            try:
                # Thử lấy API key từ biến môi trường
                self.vision_api_key = os.environ.get('GOOGLE_VISION_API_KEY')
                if not self.vision_api_key:
                    logging.error("GOOGLE_VISION_API_KEY không được cấu hình trong biến môi trường")
                    return False
                
                # Kiểm tra kết nối internet trước khi xác thực API key
                try:
                    # Kiểm tra kết nối đến Google (trang web đáng tin cậy)
                    requests.head("https://www.google.com", timeout=5)
                except requests.exceptions.RequestException:
                    logging.error("Không thể kết nối internet. Vui lòng kiểm tra kết nối mạng.")
                    return False
                
                # Kiểm tra tính hợp lệ của API key
                if self._validate_vision_api_key(self.vision_api_key):
                    try:
                        # Khởi tạo client với API key
                        logging.info('Đang khởi tạo Vision API client với API key')
                        # Tạo credentials từ API key
                        from google.oauth2.service_account import Credentials
                        from google.auth import credentials
                        
                        # Sử dụng API key để xác thực
                        self.vision_client = vision.ImageAnnotatorClient(credentials=credentials.AnonymousCredentials())
                        # Cấu hình endpoint với API key
                        self.vision_client._transport.channel._credentials = credentials.AnonymousCredentials()
                        self.vision_client._transport._host = f"https://vision.googleapis.com/v1?key={self.vision_api_key}"
                        
                        logging.info('Khởi tạo Vision API client thành công')
                        return True
                    except Exception as e:
                        logging.error(f'Lỗi khi khởi tạo Vision API: {str(e)}')
                        return False
                else:
                    logging.error("Google Vision API key không hợp lệ hoặc dịch vụ Google Vision không khả dụng")
                    return False
            except Exception as e:
                logging.error(f"Không thể khởi tạo lại Google Vision API client: {str(e)}")
                return False
        return True
            
    def generate_description(self, product_name, image_features, category, price):
        """
        Tạo mô tả sản phẩm bằng AI
        Tham số:
        - product_name: Tên sản phẩm
        - image_features: Đặc điểm từ ảnh
        - category: Danh mục sản phẩm
        - price: Giá bán
        """
        prompt = f"""Hãy viết mô tả chuyên nghiệp cho sản phẩm '{product_name}' với các thông tin sau:
        - Đặc điểm nhận dạng từ ảnh: {image_features}
        - Danh mục sản phẩm: {category}
        - Giá bán: {price} VNĐ
        Yêu cầu:
        - Giọng văn hấp dẫn, thu hút khách hàng
        - Độ dài khoảng 100-150 từ
        - Nhấn mạnh vào lợi ích khách hàng
        - Phù hợp với danh mục sản phẩm đã cung cấp"""
        
        # Kiểm tra kết nối internet trước khi gọi API
        try:
            # Kiểm tra kết nối đến Google (trang web đáng tin cậy)
            requests.head("https://www.google.com", timeout=5)
        except requests.exceptions.RequestException as e:
            logging.error(f"Không thể kết nối internet: {str(e)}")
            return "Xin lỗi, không thể tạo mô tả lúc này. Vui lòng kiểm tra kết nối mạng của bạn."
        
        # Đảm bảo API đã được khởi tạo
        if not self._ensure_ai_client():
            logging.error('Không thể kết nối đến dịch vụ AI: API không thể khởi tạo')
            return "Xin lỗi, không thể tạo mô tả lúc này. Dịch vụ AI không khả dụng hoặc API key không hợp lệ."
            
        # Thử kết nối tối đa 3 lần
        max_retries = 3
        retry_count = 0
        last_error = None
        
        while retry_count < max_retries:
            try:
                logging.info(f'Đang gọi OpenRouter API (lần thử {retry_count + 1}/{max_retries})')
                response = self.call_openrouter(
                    prompt=prompt,
                    temperature=0.7,
                    max_tokens=500
                )
                
                if response and 'choices' in response and len(response['choices']) > 0:
                    return response['choices'][0]['message']['content']
                else:
                    error_msg = "Không nhận được phản hồi hợp lệ từ API"
                    if response and isinstance(response, dict):
                        if 'error' in response:
                            error_msg += f": {response['error']}"
                    raise Exception(error_msg)
                    
            except Exception as e:
                retry_count += 1
                last_error = str(e)
                logging.error(f'Lỗi khi gọi OpenRouter API (lần {retry_count}/{max_retries}): {str(e)}')
                
                if retry_count < max_retries:
                    # Tăng thời gian chờ giữa các lần thử
                    import time
                    wait_time = retry_count * 2  # Tăng thời gian chờ theo số lần thử
                    logging.info(f'Chờ {wait_time} giây trước khi thử lại...')
                    time.sleep(wait_time)
                    
                    # Thử khởi tạo lại API nếu có lỗi
                    if retry_count == 1:
                        logging.info('Đang thử khởi tạo lại API client...')
                        self._ensure_ai_client()
        
        # Nếu đã thử hết số lần mà vẫn lỗi
        logging.error(f'Không thể kết nối đến dịch vụ AI sau {max_retries} lần thử: {last_error}')
        
        # Trả về thông báo lỗi chi tiết hơn cho người dùng
        error_message = "Xin lỗi, không thể tạo mô tả lúc này."
        
        if "timeout" in last_error.lower() or "connection" in last_error.lower():
            error_message += " Có vấn đề về kết nối mạng, vui lòng kiểm tra và thử lại sau."
        elif "api key" in last_error.lower() or "authorization" in last_error.lower():
            error_message += " Có vấn đề với xác thực API, vui lòng liên hệ quản trị viên."
        elif "rate limit" in last_error.lower():
            error_message += " Dịch vụ AI đang quá tải, vui lòng thử lại sau ít phút."
        else:
            error_message += " Vui lòng thử lại sau."
            
        return error_message

# Khởi tạo bộ tạo mô tả
describer = ProductDescriber()

# Route mặc định
@app.route('/')
def index():
    return jsonify({
        "status": "success",
        "message": "API đang hoạt động",
        "endpoints": [
            "/api/product/description",
            "/api/product/review-summary"
        ]
    })

# Route tạo mô tả sản phẩm
@app.route('/api/product/description', methods=['POST'])
def create_product_description():
    try:
        # Kiểm tra kết nối internet trước khi xử lý yêu cầu
        try:
            # Kiểm tra kết nối đến Google (trang web đáng tin cậy)
            requests.head("https://www.google.com", timeout=5)
        except requests.exceptions.RequestException:
            logging.error("Không thể kết nối internet khi xử lý yêu cầu tạo mô tả")
            return jsonify({
                "status": "error",
                "message": "Không thể kết nối internet. Vui lòng kiểm tra kết nối mạng của bạn."
            }), 503
            
        # Kiểm tra xem có file ảnh được gửi lên không
        if 'image' not in request.files:
            return jsonify({
                "status": "error",
                "message": "Không tìm thấy file ảnh"
            }), 400
        
        # Lấy thông tin từ form data
        file = request.files['image']
        product_name = request.form.get('product_name', '')
        category = request.form.get('category', '')  # Thay product_features bằng category
        price = request.form.get('price', '')
        
        # Kiểm tra tên file
        if file.filename == '':
            return jsonify({
                "status": "error",
                "message": "Không có file nào được chọn"
            }), 400
        
        # Lưu file ảnh
        filename = secure_filename(file.filename)
        file_path = os.path.join(app.config['UPLOAD_FOLDER'], filename)
        file.save(file_path)
        
        # Phân tích ảnh
        image_features = describer.analyze_image(file_path)
        
        # Tạo mô tả sản phẩm
        description = describer.generate_description(
            product_name=product_name,
            image_features=image_features,
            category=category,  # Sử dụng category thay vì product_features
            price=price
        )
        
        return jsonify({
            "status": "success",
            "data": {
                "product_name": product_name,
                "category": category,
                "image_features": image_features,
                "description": description
            }
        })
    except Exception as e:
        logging.error(f'Lỗi xử lý yêu cầu: {str(e)}')
        return jsonify({
            "status": "error",
            "message": f"Lỗi xử lý yêu cầu: {str(e)}"
        }), 500

# Thêm phương thức phân tích thị trường vào lớp ProductDescriber
def analyze_market_data(self, product_data):
    """
    Phân tích dữ liệu thị trường từ các sản phẩm
    Tham số:
    - product_data: Dữ liệu về các sản phẩm và yêu cầu phân tích
    """
     # LẤY DỮ LIỆU TỪ DICTIONARY
    best_selling = product_data.get('best_selling', [])
    worst_selling = product_data.get('worst_selling', [])
    best_rated = product_data.get('best_rated', [])
    worst_rated = product_data.get('worst_rated', [])
    most_carted = product_data.get('most_carted', [])
    stats = product_data.get('stats', {})
    
    # Kiểm tra xem có dữ liệu sản phẩm chi tiết không
    has_detailed_data = ('best_selling' in product_data or 'worst_selling' in product_data or 
                        'best_rated' in product_data or 'worst_rated' in product_data or 
                        'most_carted' in product_data)
    
    # Tạo prompt phân tích thị trường
    summary = "PHÂN TÍCH DỮ LIỆU KINH DOANH\n\n"
    summary += "📈 5 SẢN PHẨM BÁN CHẠY NHẤT:\n" + self._format_product_list(best_selling) + "\n"
    summary += "📉 5 SẢN PHẨM BÁN ÍT NHẤT:\n" + self._format_product_list(worst_selling) + "\n"
    summary += "🌟 5 SẢN PHẨM ĐƯỢC ĐÁNH GIÁ TỐT NHẤT:\n" + self._format_product_list(best_rated) + "\n"
    summary += "💔 5 SẢN PHẨM ĐƯỢC ĐÁNH GIÁ KÉM NHẤT:\n" + self._format_product_list(worst_rated) + "\n"
    summary += "🛒 5 SẢN PHẨM ĐƯỢC CHO VÀO GIỎ NHIỀU NHẤT:\n" + self._format_product_list(most_carted) + "\n"
    
    summary += "📊 THỐNG KÊ TỔNG HỢP:\n"
    summary += f"- Đơn hàng mới: {stats.get('orders', 0)}\n"
    summary += f"- Bình luận: {stats.get('comments', 0)}\n"
    summary += f"- Khách hàng mới: {stats.get('new_customers', 0)}\n"
    summary += f"- Tổng lượt xem: {stats.get('total_views', 0)}\n"
    if has_detailed_data:
        # Nếu có dữ liệu chi tiết, sử dụng prompt chi tiết
        prompt = f"""Hãy phân tích dữ liệu thị trường dựa trên thông tin sau và đưa ra nhận định, gợi ý chiến lược kinh doanh:

1. Sản phẩm bán chạy nhất:
{self._format_product_list(product_data.get('best_selling', []))}

2. Sản phẩm bán ít nhất:
{self._format_product_list(product_data.get('worst_selling', []))}

3. Sản phẩm được đánh giá tốt nhất:
{self._format_product_list(product_data.get('best_rated', []))}

4. Sản phẩm được đánh giá kém nhất:
{self._format_product_list(product_data.get('worst_rated', []))}

5. Sản phẩm được thêm vào giỏ hàng nhiều nhất:
{self._format_product_list(product_data.get('most_carted', []))}

Yêu cầu:
1. Phân tích xu hướng thị trường dựa trên dữ liệu trên
2. Nhận định về điểm mạnh, điểm yếu trong danh mục sản phẩm
3. Đề xuất 3-5 chiến lược kinh doanh cụ thể để cải thiện doanh số
4. Gợi ý các sản phẩm tiềm năng cần đẩy mạnh hoặc cải thiện
5. Phân tích mối tương quan giữa đánh giá và doanh số (nếu có)
6. Đề xuất chiến lược giá và khuyến mãi phù hợp
7. Trình bày kết quả rõ ràng, có cấu trúc với các mục rõ ràng"""
    else:
        # Nếu không có dữ liệu chi tiết, sử dụng prompt tổng quát
        prompt = """Hãy phân tích thị trường thời trang và đưa ra nhận định, gợi ý chiến lược kinh doanh cho cửa hàng quần áo online:

Yêu cầu:
1. Phân tích xu hướng thị trường thời trang hiện nay
2. Nhận định về các cơ hội và thách thức trong kinh doanh thời trang online
3. Đề xuất 3-5 chiến lược kinh doanh cụ thể để cải thiện doanh số
4. Gợi ý các loại sản phẩm tiềm năng cần đẩy mạnh theo mùa và xu hướng
5. Đề xuất chiến lược giá và khuyến mãi phù hợp
6. Gợi ý về cách tối ưu hóa trải nghiệm người dùng và tăng tỷ lệ chuyển đổi
7. Trình bày kết quả rõ ràng, có cấu trúc với các mục rõ ràng"""
        
        # Thêm các yêu cầu phân tích cụ thể nếu có
        if product_data.get('recent_sales'):
            prompt += "\n\n- Tập trung phân tích xu hướng doanh số gần đây và đề xuất cách tăng doanh thu"
        if product_data.get('customer_behavior'):
            prompt += "\n\n- Phân tích hành vi khách hàng và đề xuất cách tăng tỷ lệ chuyển đổi"
        if product_data.get('market_trends'):
            prompt += "\n\n- Phân tích chi tiết xu hướng thị trường và cách nắm bắt cơ hội mới"
        if product_data.get('strategy_focus'):
            prompt += "\n\n- Đề xuất chiến lược cụ thể để nắm bắt cơ hội mới và tăng trưởng dài hạn"
    
    # Kiểm tra kết nối internet trước khi gọi API
    try:
        requests.head("https://www.google.com", timeout=5)
    except requests.exceptions.RequestException as e:
        logging.error(f"Không thể kết nối internet: {str(e)}")
        return "Xin lỗi, không thể phân tích dữ liệu lúc này. Vui lòng kiểm tra kết nối mạng của bạn."
    
    # Đảm bảo API đã được khởi tạo
    if not self._ensure_ai_client():
        logging.error('Không thể kết nối đến dịch vụ AI: API không thể khởi tạo')
        return {
            "success": False,
            "error": True,
            "message": "Xin lỗi, không thể phân tích dữ liệu lúc này. Dịch vụ AI không khả dụng hoặc API key không hợp lệ."
        }
        
    # Thử kết nối tối đa 3 lần
    max_retries = 3
    retry_count = 0
    last_error = None
    
    while retry_count < max_retries:
        try:
            logging.info(f'Đang gọi OpenRouter API (lần thử {retry_count + 1}/{max_retries})')
            response = self.call_openrouter(
                prompt=prompt,
                temperature=0.7,
                max_tokens=1000  # Tăng max_tokens vì phân tích thị trường cần nhiều nội dung hơn
            )
            
            if response and 'choices' in response and len(response['choices']) > 0:
                # Trả về kết quả dạng JSON thay vì chuỗi văn bản
                return {
                    "success": True,
                    "analysis": response['choices'][0]['message']['content']
                }
            else:
                error_msg = "Không nhận được phản hồi hợp lệ từ API"
                if response and isinstance(response, dict):
                    if 'error' in response:
                        error_msg += f": {response['error']}"
                raise Exception(error_msg)
                
        except Exception as e:
            retry_count += 1
            last_error = str(e)
            logging.error(f'Lỗi khi gọi OpenRouter API (lần {retry_count}/{max_retries}): {str(e)}')
            
            if retry_count < max_retries:
                # Tăng thời gian chờ giữa các lần thử
                import time
                wait_time = retry_count * 2
                logging.info(f'Chờ {wait_time} giây trước khi thử lại...')
                time.sleep(wait_time)
                
                # Thử khởi tạo lại API nếu có lỗi
                if retry_count == 1:
                    logging.info('Đang thử khởi tạo lại API client...')
                    self._ensure_ai_client()
    
    # Nếu đã thử hết số lần mà vẫn lỗi
    logging.error(f'Không thể kết nối đến dịch vụ AI sau {max_retries} lần thử: {last_error}')
    
    # Tạo thông báo lỗi chi tiết hơn cho người dùng
    error_message = "Xin lỗi, không thể phân tích dữ liệu thị trường lúc này."
    
    if "timeout" in last_error.lower() or "connection" in last_error.lower():
        error_message += " Có vấn đề về kết nối mạng, vui lòng kiểm tra và thử lại sau."
    elif "api key" in last_error.lower() or "authorization" in last_error.lower():
        error_message += " Có vấn đề với xác thực API, vui lòng liên hệ quản trị viên."
    elif "rate limit" in last_error.lower():
        error_message += " Dịch vụ AI đang quá tải, vui lòng thử lại sau ít phút."
    else:
        error_message += " Vui lòng thử lại sau."
    
    # Trả về JSON thay vì chuỗi văn bản để đảm bảo định dạng nhất quán
    return {
        "success": False,
        "error": True,
        "message": error_message
    }

def _format_product_list(self, products):
    """
    Định dạng danh sách sản phẩm thành chuỗi văn bản cho prompt
    """
    if not products:
        return "Không có dữ liệu"
    
    # Kiểm tra nếu products không phải là list (có thể là boolean hoặc giá trị khác)
    if not isinstance(products, list):
        return "Không có dữ liệu chi tiết"
        
    result = ""
    for product in products:
        # Kiểm tra xem product là object hay dictionary
        if isinstance(product, dict):
            # Xử lý nếu product là dictionary
            name = product.get('name', 'Không có tên')
            price = product.get('price', 0)
            view = product.get('view', 0)
            discount_id = product.get('discount_id', None)
            sold_count = product.get('sold_count', 0)
            avg_rating = product.get('avg_rating', 0)
            rating_count = product.get('rating_count', 0)
            cart_count = product.get('cart_count', 0)
        else:
            # Xử lý các thuộc tính có thể không tồn tại nếu product là object
            name = getattr(product, 'name', 'Không có tên')
            price = getattr(product, 'price', 0)
            view = getattr(product, 'view', 0)
            discount_id = getattr(product, 'discount_id', None)
            
            # Các thuộc tính đặc biệt cho từng loại sản phẩm
            sold_count = getattr(product, 'sold_count', 0) if hasattr(product, 'sold_count') else 0
            avg_rating = getattr(product, 'avg_rating', 0) if hasattr(product, 'avg_rating') else 0
            rating_count = getattr(product, 'rating_count', 0) if hasattr(product, 'rating_count') else 0
            cart_count = getattr(product, 'cart_count', 0) if hasattr(product, 'cart_count') else 0
        
        # Tạo chuỗi mô tả sản phẩm
        product_info = f"- {name}: Giá {price:,}đ, Lượt xem: {view}"
        
        # Thêm thông tin giảm giá nếu có
        if discount_id:
            discount_amount = price * discount_id / 100
            product_info += f", Giảm giá: {discount_amount:,}đ ({discount_id}%)"
        
        # Thêm thông tin đặc biệt tùy theo loại sản phẩm
        if sold_count:
            product_info += f", Đã bán: {sold_count}"
        if avg_rating:
            product_info += f", Đánh giá: {avg_rating:.1f}/5 ({rating_count} đánh giá)"
        if cart_count:
            product_info += f", Số lần thêm vào giỏ: {cart_count}"
            
        result += product_info + "\n"
        
    return result

# Thêm phương thức analyze_market_data vào lớp ProductDescriber
ProductDescriber.analyze_market_data = analyze_market_data
ProductDescriber._format_product_list = _format_product_list

# Route tổng hợp đánh giá từ bình luận sản phẩm
@app.route('/api/product/review-summary', methods=['POST'])
def summarize_product_reviews():
    try:
        # Lấy dữ liệu từ request
        data = request.get_json()
        
        # Kiểm tra dữ liệu đầu vào
        if not data or 'reviews' not in data or not data['reviews']:
            return jsonify({
                "status": "error",
                "message": "Không tìm thấy dữ liệu bình luận"
            }), 400
        
        # Lấy thông tin sản phẩm và danh sách bình luận
        product_name = data.get('product_name', 'Sản phẩm')
        reviews = data['reviews']
        
        # Kiểm tra định dạng của reviews
        if not isinstance(reviews, list):
            return jsonify({
                "status": "error",
                "message": "Dữ liệu bình luận phải là một mảng"
            }), 400
        
        # Tạo prompt cho OpenAI
        reviews_text = "\n".join([f"- {review}" for review in reviews])
        prompt = f"""Hãy tổng hợp TẤT CẢ các bình luận sau đây về sản phẩm '{product_name}' thành MỘT câu duy nhất:

{reviews_text}

Yêu cầu:
1. Tạo MỘT câu duy nhất tổng hợp tất cả các bình luận trên, ngắn gọn, xúc tích nhưng đủ ý
2. Câu tổng hợp phải phản ánh chính xác nội dung từ TẤT CẢ các bình luận của khách hàng
3. Câu tổng hợp phải bao gồm cả điểm mạnh và điểm yếu (nếu có) của sản phẩm
4. Không thêm thông tin không có trong bình luận
5. Giữ nguyên ngôn ngữ của bình luận (tiếng Việt)
6. Chỉ có phần tổng hợp bình luận , không có chú thích
7. Độ dài tối đa 100 từ"""
        
        # Gọi OpenRouter API để tổng hợp đánh giá
        if not describer._ensure_ai_client():
            logging.error('Không thể kết nối đến dịch vụ AI: API không thể khởi tạo')
            return jsonify({
                "status": "error",
                "message": "Không thể kết nối đến dịch vụ AI. Vui lòng thử lại sau."
            }), 503  # Service Unavailable
            
        # Thử kết nối tối đa 3 lần
        max_retries = 3
        retry_count = 0
        last_error = None
        
        while retry_count < max_retries:
            try:
                logging.info(f'Đang gọi OpenRouter API (lần thử {retry_count + 1}/{max_retries})')
                response = describer.call_openrouter(
                    prompt=prompt,
                    temperature=0.7,
                    max_tokens=500
                )
                
                if response and 'choices' in response and len(response['choices']) > 0:
                    summary = response['choices'][0]['message']['content']
                    
                    # Trả về kết quả
                    return jsonify({
                        "status": "success",
                        "data": {
                            "product_name": product_name,
                            "review_count": len(reviews),
                            "summary": summary
                        }
                    })
                else:
                    raise Exception("Không nhận được phản hồi hợp lệ từ API")
            except Exception as e:
                retry_count += 1
                last_error = str(e)
                logging.error(f'Lỗi khi gọi OpenRouter API (lần {retry_count}/{max_retries}): {str(e)}')
                
                if retry_count < max_retries:
                    # Chờ một chút trước khi thử lại
                    import time
                    time.sleep(1)  # Chờ 1 giây
                    
                    # Thử khởi tạo lại API nếu có lỗi
                    if retry_count == 1:
                        describer._ensure_ai_client()
                    
        # Nếu đã thử hết số lần mà vẫn lỗi
        logging.error(f'Không thể kết nối đến dịch vụ AI sau {max_retries} lần thử: {last_error}')
        return jsonify({
            "status": "error",
            "message": f"Không thể tổng hợp đánh giá. Lỗi kết nối đến dịch vụ AI: {last_error}"
        }), 503  # Service Unavailable
    except Exception as e:
        logging.error(f'Lỗi xử lý yêu cầu: {str(e)}')
        return jsonify({
            "status": "error",
            "message": f"Lỗi xử lý yêu cầu: {str(e)}"
        }), 500


# Route phân tích thị trường và đề xuất chiến lược kinh doanh
@app.route('/api/market/analyze', methods=['POST'])
def analyze_market():
    try:
        data = request.get_json()
        # Lấy dữ liệu thực tế từ panel_data và stats_data
        panel_data = data.get('panel_data', [])
        stats_data = data.get('stats_data', {})
        
        # Khởi tạo đối tượng ProductDescriber để sử dụng phương thức phân tích
        describer = ProductDescriber()
        
        # Chuẩn bị dữ liệu cho phân tích thị trường
        market_data = {
            'data_analysis': data.get('data_analysis', True),
            'brief_strategy': data.get('brief_strategy', True)
        }
        
        # Khởi tạo các biến trước khi sử dụng
        best_selling_products = []
        worst_selling_products = []
        best_rated_products = []
        worst_rated_products = []
        most_carted_products = []
        
        # Duyệt qua từng panel để phân loại dữ liệu
        for panel in panel_data:
            title = panel.get('title', '').lower()
            table_data = panel.get('table_data', [])
            
            # Phân loại dữ liệu dựa trên tiêu đề panel
            if 'bán chạy' in title or 'best selling' in title:
                best_selling_products = table_data[:5]  # Lấy tối đa 5 sản phẩm
            elif 'bán ít' in title or 'worst selling' in title:
                worst_selling_products = table_data[:5]
            elif 'đánh giá cao' in title or 'best rated' in title:
                best_rated_products = table_data[:5]
            elif 'đánh giá thấp' in title or 'worst rated' in title:
                worst_rated_products = table_data[:5]
            elif 'giỏ hàng' in title or 'cart' in title:
                most_carted_products = table_data[:5]
        
        # Thêm dữ liệu đã phân loại vào market_data
        if best_selling_products:
            market_data['best_selling'] = best_selling_products
        if worst_selling_products:
            market_data['worst_selling'] = worst_selling_products
        if best_rated_products:
            market_data['best_rated'] = best_rated_products
        if worst_rated_products:
            market_data['worst_rated'] = worst_rated_products
        if most_carted_products:
            market_data['most_carted'] = most_carted_products
        
        # Thêm dữ liệu thống kê nếu có
        if stats_data:
            market_data['stats'] = stats_data
        
        # Gọi phương thức phân tích thị trường
        analysis_result = describer.analyze_market_data(market_data)
        
        # Kiểm tra kết quả phân tích
        if isinstance(analysis_result, dict):
            if analysis_result.get('success', False):
                # Nếu phân tích thành công, trả về kết quả
                return jsonify({
                    "status": "success",
                    "data": {
                        "analysis": analysis_result.get('analysis', 'Không có dữ liệu phân tích')
                    }
                })
            else:
                # Nếu có lỗi trong quá trình phân tích
                error_message = analysis_result.get('message', 'Lỗi không xác định khi phân tích dữ liệu')
                return jsonify({
                    "status": "error",
                    "message": error_message
                }), 500
        else:
            # Nếu kết quả là chuỗi (thường là thông báo lỗi)
            return jsonify({
                "status": "success",
                "data": {
                    "analysis": analysis_result
                }
            })
    except Exception as e:
        logging.error(f"Lỗi khi phân tích thị trường: {str(e)}")
        return jsonify({
            "status": "error",
            "message": f"Lỗi phân tích dữ liệu: {str(e)}"
        }), 500
        
# Chạy ứng dụng
if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5002, debug=True)
    
    
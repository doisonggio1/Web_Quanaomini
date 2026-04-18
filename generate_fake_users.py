import pymysql
from faker import Faker
import random
from datetime import datetime
import time
from decimal import Decimal

# Kết nối database
conn = pymysql.connect(
    host='sql3.freesqldatabase.com',
    user='sql3769289',
    password='laUmWFHXPe',  
    database='sql3769289',
    charset='utf8mb4',
    cursorclass=pymysql.cursors.DictCursor
)
cursor = conn.cursor()

fake = Faker('vi_VN')

def generate_phone():
    return "0" + "".join([str(random.randint(0, 9)) for _ in range(9)])

def generate_address_parts():
    address = fake.street_address()
    cities = ["Hà Nội", "Hồ Chí Minh", "Đà Nẵng", "Hải Phòng", "Cần Thơ", "Huế", "Biên Hòa", "Nha Trang", "Vũng Tàu", "Buôn Ma Thuột"]
    districts = ["Quận 1", "Quận 2", "Quận 3", "Quận 4", "Quận 5", "Quận 7", "Quận 10", "Quận 12", "Thanh Xuân", "Cầu Giấy"]
    wards = [f"Phường {i}" for i in range(1, 21)]

    city = random.choice(cities)
    district = random.choice(districts)
    ward = random.choice(wards)

    return address, city, district, ward

def hash_password(raw_password):
    return fake.sha256(raw_password)

def get_random_coupon():
    cursor.execute("SELECT * FROM coupon WHERE status = 1")
    coupons = cursor.fetchall()
    if coupons and random.random() < 0.1:  # 10% giao dịch có coupon
        return random.choice(coupons)
    return None

def calculate_discount(coupon, total_amount):
    value = Decimal(coupon['value'])
    min_price = Decimal(coupon['min_price'])
    if total_amount < min_price:
        return 0.0
    if coupon['measure'] == 1:
        return value
    elif coupon['measure'] == 2:
        discount = total_amount * (value / Decimal(100))
        return discount
    return 0.0

# Số lượng user cần tạo
num_users = 100
payment_methods = ['vnpay', 'vietqr', 'cash']
statuses = [0, 1, 2, 3]

for _ in range(num_users):
    name = fake.name()
    email = fake.unique.email()
    password = hash_password("123456")
    phone = generate_phone()
    address, city, district, ward = generate_address_parts()
    created_dt = datetime.now()
    created = int(time.mktime(created_dt.timetuple()))
    is_verified = random.choice([0, 1])
    date_modified = None if random.random() < 0.3 else created_dt.strftime('%Y-%m-%d %H:%M:%S')

    # Thêm user
    sql_user = """
        INSERT INTO user (name, email, password, phone, address, city, district, ward, created, is_verified, date_modified)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
    """
    cursor.execute(sql_user, (
        name, email, password, phone,
        address, city, district, ward,
        created_dt.strftime('%Y-%m-%d %H:%M:%S'),
        is_verified, date_modified
    ))
    user_id = cursor.lastrowid

    # Tạo giao dịch cho user này
    status = random.choice(statuses)
    message = fake.sentence()
    payment = random.choice(payment_methods)
    amount = round(random.uniform(100000, 1000000), 2)

    # Coupon ngẫu nhiên (10% có)
    coupon = get_random_coupon()
    if coupon:
        coupon_id = coupon['id']
        discount_amount = calculate_discount(coupon, Decimal(amount))
    else:
        coupon_id = None
        discount_amount = 0.0

    sql_transaction = """
        INSERT INTO transaction (
            status, user_id, delivery_name, delivery_email, delivery_phone, 
            delivery_address, delivery_city, delivery_district, delivery_ward,
            message, coupon_id, discount_amount, amount, payment, created
        )
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
    """

    cursor.execute(sql_transaction, (
        status, user_id, name, email, phone,
        address, city, district, ward,
        message, coupon_id, discount_amount, amount, payment, created
    ))

conn.commit()
cursor.close()
conn.close()

print(f"✅ Đã tạo thành công {num_users} user và giao dịch.")

import random
import pymysql
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

# Lấy danh sách transaction và product hiện có
cursor.execute("SELECT id FROM transaction")
transactions = cursor.fetchall()

cursor.execute("SELECT id, price FROM product")
products = cursor.fetchall()

# Kiểm tra
if not transactions or not products:
    print("Cần có dữ liệu trong bảng transaction và product trước khi tạo order.")
    exit()

# Hàm tạo orders cho mỗi transaction
def generate_orders_for_transaction(transaction_id):
    num_orders = random.randint(1, 3)  # Mỗi transaction có từ 1 đến 5 đơn hàng
    selected_products = random.sample(products, min(num_orders, len(products)))

    for product in selected_products:
        qty = random.randint(1, 5)
        amount = Decimal(product["price"]) * qty
        status = random.choice([0, 1, 2])  # Trạng thái random

        cursor.execute("""
            INSERT INTO `order` (transaction_id, product_id, qty, amount, status)
            VALUES (%s, %s, %s, %s, %s)
        """, (transaction_id, product["id"], qty, amount, status))

# Tạo order cho mỗi transaction
for txn in transactions:
    generate_orders_for_transaction(txn["id"])

conn.commit()
print(f"Đã tạo dữ liệu order cho {len(transactions)} giao dịch.")
cursor.close()
conn.close()

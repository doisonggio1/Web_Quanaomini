// Logic chọn địa chỉ
var citis = document.getElementById("city");
var districts = document.getElementById("district");
var wards = document.getElementById("ward");
// Lấy thẻ chứa thông tin người dùng
var userInfoElement = document.getElementById("userInfo");
var userData = JSON.parse(userInfoElement.getAttribute("data-user"));

// Điền thông tin vào các trường input
document.getElementById("name").value = userData.name;
document.getElementById("email").value = userData.email;
document.getElementById("phone").value = userData.phone;
document.getElementById("address").value = userData.address;

var Parameter = {
	url: "http://localhost:8080/api/read-json",
	method: "GET",
	responseType: "application/json",
};
function selectOptionByText(selectId, textToFind) {
	let select = document.getElementById(selectId);
	for (let i = 0; i < select.options.length; i++) {
		if (select.options[i].text === textToFind) {
			select.selectedIndex = i;
			break;
		}
	}
}
var promise = axios(Parameter);
promise.then(function (result) {
	renderCity(result.data);

	// Điền thông tin sau khi dữ liệu được load
	let cityElement = document.getElementById("city");

	for (let i = 0; i < cityElement.options.length; i++) {
		if (cityElement.options[i].text === userData.city) {
			cityElement.selectedIndex = i;
			break;
		}
	}

	// Gửi sự kiện onchange
	let eventCity = new Event("change");
	cityElement.dispatchEvent(eventCity);

	let districtElement = document.getElementById("district");
	for (let i = 0; i < districtElement.options.length; i++) {
		if (districtElement.options[i].text === userData.district) {
			districtElement.selectedIndex = i;
			break;
		}
	}

	// Gửi sự kiện onchange
	let eventDistrict = new Event("change");
	districtElement.dispatchEvent(eventDistrict);

	// ====== Dành cho WARD ======
	let wardElement = document.getElementById("ward");
	for (let i = 0; i < wardElement.options.length; i++) {
		if (wardElement.options[i].text === userData.ward) {
			wardElement.selectedIndex = i;
			break;
		}
	}

	// Gửi sự kiện onchange
	let eventWard = new Event("change");
	wardElement.dispatchEvent(eventWard);
});

function renderCity(data) {
	for (const x of data) {
		citis.options[citis.options.length] = new Option(x.Name, x.Id);
	}
	citis.onchange = function () {
		district.length = 1;
		ward.length = 1;
		if (this.value != "") {
			const result = data.filter((n) => n.Id === this.value);

			for (const k of result[0].Districts) {
				district.options[district.options.length] = new Option(k.Name, k.Id);
			}
		} else {
			resetShipping();
			resetVoucherGiftcode();
		}
	};
	district.onchange = function () {
		ward.length = 1;
		const dataCity = data.filter((n) => n.Id === citis.value);
		if (this.value != "") {
			const dataWards = dataCity[0].Districts.filter(
				(n) => n.Id === this.value
			)[0].Wards;

			for (const w of dataWards) {
				wards.options[wards.options.length] = new Option(w.Name, w.Id);
			}
		} else {
			resetShipping();
			resetVoucherGiftcode();
		}
	};
}

// Bắt sự kiện chọn phường xã để tính tiền vận chuyển
// Lấy toạ độ
function matchWords(inputStr, targetStr) {
	const inputWords = inputStr.toLowerCase().split(/\s+/);
	const targetWords = targetStr.toLowerCase().split(/\s+/);

	return inputWords.every((word) => targetWords.includes(word));
}

function checkMatchInList(inputStr, list) {
	for (let i = 0; i < list.length; i++) {
		if (matchWords(inputStr, list[i].properties.region)) {
			return list[i].geometry.coordinates; // Dừng duyệt nếu match
		}
	}
	return null; // Không có phần tử nào match
}

function getLastTwoWords(str) {
	const words = str.trim().split(/\s+/);
	return words.slice(-2).join(" ");
}

async function getCoordinates(city, district, ward) {
	const el = document.getElementById("openroute");
	const apiKey = el.getAttribute("data-key");
	const location = `${ward}, ${district}, ${city}, Vietnam`;

	const url = `https://api.openrouteservice.org/geocode/search?api_key=${apiKey}&text=${encodeURIComponent(
		location
	)}`;

	try {
		const response = await fetch(url);
		const data = await response.json();
		let coordinates = null;
		if (data.features && data.features.length > 0) {
			coordinates = checkMatchInList(getLastTwoWords(city), data.features);
			let longitude;
			let latitude;
			if (coordinates != null) {
				longitude = coordinates[0]; // Kinh độ
				latitude = coordinates[1]; // Vĩ độ
			} else {
				return false;
			}
			return { longitude, latitude };
		} else {
			throw new Error("Không tìm thấy tọa độ cho địa điểm này.");
		}
	} catch (error) {
		console.error("Lỗi khi lấy tọa độ:", error);
	}
}

function toRadians(degrees) {
	return (degrees * Math.PI) / 180;
}

function haversine(coord1, coord2) {
	const R = 6371; // Bán kính Trái Đất (km)

	const [lon1, lat1] = coord1;
	const [lon2, lat2] = coord2;

	const φ1 = toRadians(lat1);
	const φ2 = toRadians(lat2);
	const Δφ = toRadians(lat2 - lat1);
	const Δλ = toRadians(lon2 - lon1);

	const a =
		Math.sin(Δφ / 2) ** 2 + Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) ** 2;

	const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

	const distance = R * c;
	return distance * 1000; // Trả về đơn vị m
}
function resetShipping() {
	document.querySelector(".shipping-detail").style.display = "none";
	document.querySelector(".shipping").style.display = "none";
}
let checkResetGiftcode = false;
function resetVoucherGiftcode() {
	if (document.querySelector(".giftcode").style.display == "flex") {
		voucher_type = JSON.parse(
			document.querySelector(".giftcode").getAttribute("data-gift-code")
		).type;

		if (voucher_type == 0) {
			removeGiftCodeCalc();
		}
	}

	voucher_type = null;
	document.querySelectorAll(".voucher-card").forEach((card) => {
		if (card.classList.contains("border-blue-500")) {
			let voucher = JSON.parse(card.getAttribute("data-card"));
			voucher_type = voucher.type;
			return;
		}
	});
	if (
		voucher_type == 0 &&
		document.querySelector(".cart-voucher").style.display == "flex"
	) {
		removeVoucherCalc();
		document.querySelectorAll(".voucher-card").forEach((card) => {
			if (card.classList.contains("border-blue-500")) {
				card.classList.remove("border-blue-500");
				return;
			}
		});
		// selectedVoucherId = null;
		document.getElementById("selected-voucher").classList.add("hidden");
	}
	checkResetGiftcode = true;
}

async function getDistance(fromCoords, toCoords) {
	const url = "https://api.openrouteservice.org/v2/directions/driving-car";
	const el = document.getElementById("openroute");
	const apiKey = el.getAttribute("data-key");
	const body = {
		coordinates: [fromCoords, toCoords],
	};

	const response = await fetch(url, {
		method: "POST",
		headers: {
			Authorization: apiKey,
			"Content-Type": "application/json",
		},
		body: JSON.stringify(body),
	});

	const data = await response.json();
	if (data.routes && data.routes.length > 0) {
		const distance = data.routes[0].summary.distance; // đơn vị: mét
		const duration = data.routes[0].summary.duration; // đơn vị: giây
		return { distance, duration };
	} else if (data.error.message != null) {
		const distance = haversine(fromCoords, toCoords); // đơn vị: mét
		const duration = undefined; // đơn vị: giây
		return { distance, duration };
	} else {
		throw new Error("Không thể tính khoảng cách.");
	}
}

// đổi đơn vị tiền
function formatCurrency(number) {
	if (isNaN(number)) {
		return "0 VND";
	}

	return Number(number).toLocaleString("vi-VN") + " VND";
}

wards.addEventListener("change", function () {
	cityText = citis.options[citis.selectedIndex].text;
	districtText = district.options[district.selectedIndex].text;
	wardText = ward.options[ward.selectedIndex].text;

	if (!cityText || !districtText || !wardText) {
		resetShipping();
		resetVoucherGiftcode();
		return;
	}

	// HV bưu chính viễn thông - ngọc trực
	let from = [105.7684188, 20.9847744];
	let to = [];
	if (this.value) {
		getCoordinates(cityText, districtText, wardText)
			.then((tocoords) => {
				if (tocoords) {
					to.push(tocoords.longitude);
					to.push(tocoords.latitude);
					return getDistance(from, to);
				} else {
					document.querySelector(".shipping-detail").style.display = "block";
					document.querySelector(".distance").textContent = `Chưa xác định`;
					document.querySelector(".fee").textContent = `Thông báo sau`;
					document.querySelector(".shipping").style.display = "none";
					return Promise.reject("Không tìm thấy tọa độ đích.");
				}
			})
			.then(async (result) => {
				// 1. Hiển thị div với class 'shipping-detail' dưới dạng block
				document.querySelector(".shipping-detail").style.display = "block";
				// 2. Thay đổi nội dung của distance và fee
				const itemprice = document.getElementById("totalPrice").dataset.price;
				const shipfee = await getShippingFee(
					Math.round(result.distance / 1000),
					Number(itemprice)
				);
				let total = Math.round(Number(shipfee) + Number(itemprice));
				document.querySelector(".distance").textContent = `${Math.round(
					result.distance / 1000
				)} km`;
				document.querySelector(".fee").textContent = `${formatCurrency(
					shipfee
				)}`;

				// Hiển thị dòng phí ship
				document.querySelector(".shipping").style.display = "flex";
				document.getElementById("shippingFee").textContent = `${formatCurrency(
					shipfee
				)}`;
				if (document.querySelector(".cart-voucher").style.display == "flex") {
					total -= parseVNDString(
						document.getElementById("cartVoucher").textContent
					);
				}
				if (document.querySelector(".giftcode").style.display == "flex") {
					total -= parseVNDString(
						document.getElementById("giftcode").textContent
					);
				}
				document.getElementById("totalPrice").textContent = `${formatCurrency(
					total
				)}`;
			})
			.catch((err) => {
				console.error("Lỗi:", err);
			});
	}
});

async function getShippingFee(distance, totalAmount) {
	try {
		const response = await fetch("http://localhost:8080/shipping-fee", {
			method: "GET",
			headers: {
				"Content-Type": "application/json",
			},
		});

		const text = await response.text();
		const data = JSON.parse(text);

		if (data.status !== "success") {
			console.error("Lỗi lấy dữ liệu shipping rules");
			return null;
		}

		const rules = data.data;
		let selectedRule = null;

		for (const rule of rules) {
			const minDist = parseFloat(rule.min_distance_km);
			const maxDist = parseFloat(rule.max_distance_km);
			const minAmount = parseFloat(rule.min_order_amount);
			const maxAmount = parseFloat(rule.max_order_amount);

			const matchDistance =
				(isNaN(minDist) || distance >= minDist) &&
				(isNaN(maxDist) || distance <= maxDist);

			const matchAmount =
				(isNaN(minAmount) || totalAmount >= minAmount) &&
				(isNaN(maxAmount) || totalAmount <= maxAmount);

			if (matchDistance && matchAmount) {
				selectedRule = rule;
				break;
			}
		}

		if (selectedRule) {
			let fee = parseFloat(selectedRule.shipping_fee);
			if (selectedRule.unit === "MUL") {
				fee = fee * distance;
			}
			return fee;
		} else {
			return null; // hoặc 1 giá trị mặc định như 50000
		}
	} catch (error) {
		console.error("Lỗi fetch:", error);
		return null;
	}
}

// Hiệu ứng hiển thị chi tiết phương thức thanh toán
function highlightPayment(selectedInput) {
	// Xóa in đậm của tất cả tiêu đề thanh toán
	document.querySelectorAll(".payment-title").forEach((title) => {
		title.classList.remove("font-bold");
	});

	// Lấy phần tử `span` kế bên input radio và in đậm nó
	selectedInput.nextElementSibling.nextElementSibling.classList.add(
		"font-bold"
	);
}

document.addEventListener("DOMContentLoaded", function () {
	const paymentOptions = document.querySelectorAll(".payment-option");
	const detailsSections = {
		bank: document.getElementById("bank-details"),
		pos: document.getElementById("pos-details"),
		vnpay: document.getElementById("vnpay-details"),
		vietqr: document.getElementById("vietqr-details"),
	};

	paymentOptions.forEach((option) => {
		option.addEventListener("change", function () {
			highlightPayment(option);
			// Ẩn tất cả nội dung thanh toán
			Object.values(detailsSections).forEach((section) =>
				section.classList.add("hidden")
			);

			// Hiển thị nội dung tương ứng với lựa chọn
			if (detailsSections[this.value]) {
				detailsSections[this.value].classList.remove("hidden");
			}
		});
	});
});

// Kiểm tra tính hợp lệ của form
document.addEventListener("DOMContentLoaded", function () {
	document
		.getElementById("submitBtn")
		.addEventListener("click", function (event) {
			let isValid = true;

			function showError(input, message) {
				clearError(input);
				let errorMsg = document.createElement("p");
				errorMsg.className = "text-red-500 text-xl mt-1";
				errorMsg.innerText = message;
				input.classList.add("border-red-500");
				input.parentNode.appendChild(errorMsg);
			}

			function clearError(input) {
				input.classList.remove("border-red-500");
				let errorMsg = input.parentNode.querySelector(".text-red-500");
				if (errorMsg) {
					errorMsg.remove();
				}
			}

			// Kiểm tra Họ và Tên
			let nameInput = document.getElementById("name");
			if (nameInput.value.trim() === "") {
				showError(nameInput, "Họ và tên không được để trống");
				isValid = false;
			} else {
				clearError(nameInput);
			}

			// Kiểm tra Email
			let emailInput = document.getElementById("email");
			let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			if (!emailRegex.test(emailInput.value.trim())) {
				showError(emailInput, "Email không hợp lệ");
				isValid = false;
			} else {
				clearError(emailInput);
			}

			// Kiểm tra Số điện thoại
			let phoneInput = document.getElementById("phone");
			let phoneRegex = /^[0-9]{8,11}$/;
			if (!phoneRegex.test(phoneInput.value.trim())) {
				showError(phoneInput, "Số điện thoại không hợp lệ (8-11 chữ số)");
				isValid = false;
			} else {
				clearError(phoneInput);
			}

			// Kiểm tra Địa chỉ
			let addressInput = document.getElementById("address");
			if (addressInput.value.trim() === "") {
				showError(addressInput, "Địa chỉ không được để trống");
				isValid = false;
			} else {
				clearError(addressInput);
			}

			// Kiểm tra Tỉnh/Thành
			let citySelect = document.getElementById("city");
			if (citySelect.value === "") {
				showError(citySelect, "Vui lòng chọn Tỉnh/Thành");
				isValid = false;
			} else {
				clearError(citySelect);
			}

			// Kiểm tra Quận/Huyện
			let districtSelect = document.getElementById("district");
			if (districtSelect.value === "") {
				showError(districtSelect, "Vui lòng chọn Quận/Huyện");
				isValid = false;
			} else {
				clearError(districtSelect);
			}

			// Kiểm tra Phường/Xã
			let wardSelect = document.getElementById("ward");
			if (wardSelect.value === "") {
				showError(wardSelect, "Vui lòng chọn Phường/Xã");
				isValid = false;
			} else {
				clearError(wardSelect);
			}

			let selectedPayment = document.querySelector(
				'input[name="payment"]:checked'
			);
			let paymentError = document.getElementById("payment-error");
			if (!selectedPayment) {
				paymentError.classList.remove("hidden"); // Hiển thị cảnh báo
				isValid = false;
			} else {
				paymentError.classList.add("hidden"); // Ẩn cảnh báo nếu đã chọn
			}

			// Là tuỳ chọn
			let message = document.getElementById("message").value;

			// Nếu có lỗi, không gửi API
			if (!isValid) return;
			let discount_amount = 0;
			if (document.querySelector(".cart-voucher").style.display == "flex") {
				discount_amount = parseVNDString(
					document.getElementById("cartVoucher").textContent
				);
			}
			if (document.querySelector(".giftcode").style.display == "flex") {
				discount_amount =
					discount_amount +
					parseVNDString(document.getElementById("giftcode").textContent);
			}
			let shipping_fee = 0;
			if (
				document.querySelector(".shipping-detail").style.display == "block" &&
				document.querySelector(".fee").textContent != `Thông báo sau`
			) {
				shipping_fee = parseVNDString(
					document.querySelector(".fee").textContent
				);
			}
			let giftcode_id = null;
			if (document.querySelector(".giftcode").style.display == "flex") {
				giftcode_id = JSON.parse(
					document.querySelector(".giftcode").getAttribute("data-gift-code")
				).code;
			}

			let coupon_id = null;
			document.querySelectorAll(".voucher-card").forEach((card) => {
				if (card.classList.contains("border-blue-500")) {
					let voucher = JSON.parse(card.getAttribute("data-card"));
					coupon_id = voucher.id;
					return;
				}
			});
			// Tạo object chứa dữ liệu cần gửi
			let formData = {
				name: nameInput.value.trim(),
				email: emailInput.value.trim(),
				phone: phoneInput.value.trim(),
				address: addressInput.value.trim(),
				city: citySelect.value,
				district: districtSelect.value,
				ward: wardSelect.value,
				message: message,
				discount_amount: discount_amount,
				shipping_fee: shipping_fee,
				coupon_id: coupon_id,
				giftcode_id: giftcode_id,
				payment: selectedPayment.value,
			};

			if (
				selectedPayment.value == "cash" ||
				selectedPayment.value == "vietqr" ||
				selectedPayment.value == "pos"
			) {
				// Gửi dữ liệu lên server qua fetch API (Fake API endpoint)
				fetch("http://localhost:8080/order/complete", {
					method: "POST",
					headers: {
						"Content-Type": "application/json",
					},
					body: JSON.stringify(formData),
				})
					.then((response) => response.text())
					.then((text) => {
						return JSON.parse(text); // Chuyển thành JSON thủ công
					})
					.then((data) => {
						// Hiển thị popup đặt hàng thành công
						if (data.status == "null_user") {
							window.location.href = "/dang-nhap";
						} else if (data.status == "success") {
							Swal.fire({
								icon: "success",
								title: "Đặt hàng thành công!",
								text: data.message,
								customClass: {
									confirmButton: "my-custom-button",
								},
								confirmButtonText: "OK",
							}).then(() => {
								window.location.href = "/";
							});
						} else {
							Swal.fire({
								icon: "error",
								title: "Đặt hàng thất bại!",
								text: data.message,
								customClass: {
									confirmButton: "my-custom-button",
								},
								confirmButtonText: "Thử lại",
							});
						}
					})
					.catch((error) => {
						Swal.fire({
							icon: "error",
							title: "Lỗi!",
							customClass: {
								confirmButton: "my-custom-button",
							},
							text: "Đã có lỗi xảy ra, vui lòng thử lại.",
						});
						console.error(error);
					});
			} else if (selectedPayment.value == "vnpay") {
				fetch("http://localhost:8080/vnpay/payment", {
					method: "POST",
					headers: { "Content-Type": "application/json" },
					body: JSON.stringify(formData),
				})
					.then((response) => response.json())
					.then((data) => {
						if (data.status == "error") {
							Swal.fire({
								icon: "error",
								title: "Thanh toán VNPAY chưa sẵn sàng",
								text: data.message || "Vui lòng kiểm tra cấu hình VNPAY.",
								customClass: {
									confirmButton: "my-custom-button",
								},
								confirmButtonText: "Đã hiểu",
							});
							return;
						}
						if (data.payment_url) {
							// Chuyển hướng người dùng đến VNPAY
							window.location.href = data.payment_url;
						}
					})
					.catch((error) => console.error("Error:", error));
			}
		});
});

function getVoucherScore(voucher) {
	const value = Number(voucher.value ?? 0);
	const maxValue = Number(voucher.max_value ?? 0);
	const minPrice = Number(voucher.min_price ?? 0);

	let score = 0;

	if (value <= 100) {
		// Giảm theo phần trăm
		score += value * 2; // Nhân đôi để làm nổi bật % ưu đãi
	} else {
		// Giảm theo số tiền (VND)
		score += value / 1000; // Mỗi 1.000đ tương đương 1 điểm
	}

	// Trừ điểm nếu bị giới hạn giá trị tối đa (max_value)
	if (maxValue > 0) {
		// Nếu max_value nhỏ hơn value thì bị giới hạn
		const limit = Math.max(0, value - maxValue);
		score -= limit / 1000;
	} else {
		// Không có giới hạn => cộng thêm điểm
		score += 5;
	}

	// Cộng điểm nếu không yêu cầu giá trị đơn hàng tối thiểu
	if (minPrice === 0) {
		score += 5;
	} else {
		// Nếu có yêu cầu thì trừ nhẹ tùy theo mức độ
		score -= minPrice / 100000; // Ví dụ: 100.000đ => -1 điểm
	}

	return Math.round(score * 100) / 100; // Làm tròn 2 chữ số thập phân
}

function sortVouchersByPriority(voucherList, type = 0) {
	// Bước 1: Lọc theo type_coupon nếu type được truyền vào
	const filteredVouchers =
		typeof type === "number"
			? voucherList.filter((v) => v.type_coupon == type)
			: voucherList;

	// Bước 2: Sắp xếp giảm dần theo điểm ưu tiên
	return filteredVouchers.sort((a, b) => {
		const scoreA = getVoucherScore(a);
		const scoreB = getVoucherScore(b);
		return scoreB - scoreA;
	});
}

function parseVNDString(vndString) {
	// Loại bỏ "VND", dấu cách và dấu chấm
	const numericStr = vndString.replace(/[^\d]/g, "");
	return Number(numericStr);
}

function calculateDiscount(
	orderTotal,
	minValueNum,
	maxValueNum,
	valueNum,
	shippingFeeValue = null
) {
	// Nếu miễn phí hoàn toàn (valueNum == 0), giảm toàn bộ shippingFeeValue nếu có, hoặc orderTotal
	if (orderTotal == 0) {
		return -1;
	}

	// 1. Kiểm tra điều kiện tối thiểu
	if (minValueNum != null && minValueNum > 0) {
		const minThreshold =
			minValueNum <= 100 ? (orderTotal * minValueNum) / 100 : minValueNum;

		if (orderTotal < minThreshold) {
			return -2; // Không đủ điều kiện
		}
	}

	// 2. Xác định phần áp dụng giảm giá: phí ship hoặc tổng đơn
	const baseAmount = shippingFeeValue != null ? shippingFeeValue : orderTotal;

	// 3. Tính mức giảm
	let discount = 0;
	if (valueNum != null) {
		discount = valueNum <= 100 ? (baseAmount * valueNum) / 100 : valueNum;
	}

	// 4. Giới hạn giảm tối đa nếu có
	if (maxValueNum != null && maxValueNum > 0) {
		const maxAllowed =
			maxValueNum <= 100 ? (baseAmount * maxValueNum) / 100 : maxValueNum;

		discount = Math.min(discount, maxAllowed);
	}

	// 5. Không giảm quá giá trị của phần được áp dụng
	discount = Math.min(discount, baseAmount);

	return formatCurrency(discount);
}

function calculateCartTotalByCatalog(cartList, catalogId) {
	let total = 0;

	cartList.forEach((item) => {
		const itemCatalogId =
			item.catalog_id != null ? parseInt(item.catalog_id) : null;
		const itemParentId =
			item.parent_id != null ? parseInt(item.parent_id) : null;
		const price = parseFloat(item.price || 0);
		const qty = parseInt(item.qty || 1);

		if (
			catalogId == null ||
			itemCatalogId == parseInt(catalogId) ||
			itemParentId == parseInt(catalogId)
		) {
			total += price * qty;
		}
	});

	return total;
}

function addVoucherCalc(card) {
	// Lấy thông tin voucher
	let voucher = JSON.parse(card.getAttribute("data-card"));

	// Xác định kiểu giảm: Phí ship hay giảm giá hàng
	// Số tiền giảm hoặc phần trăm giảm
	const valueNum = voucher.value !== null ? parseInt(voucher.value) : null;

	// Áp dụng từ đơn giá tối thiểu nào?
	const minPriceNum =
		voucher.min_price !== null ? parseInt(voucher.min_price) : null;

	// Giới hạn giảm tối đa
	const maxValueNum =
		voucher.max_value !== null ? parseInt(voucher.max_value) : null;

	// Tổng tiền gốc của đơn hàng
	const itemprice = document.getElementById("totalPrice").dataset.price;

	// Danh sách cart
	const carts = document.getElementById("cartInfo");
	var cartData = JSON.parse(carts.getAttribute("data-cart"));

	// Tổng tiền các sản phẩm thoả mãn ngành hàng trong cart
	const itemValid = calculateCartTotalByCatalog(cartData, voucher.catalog_id);

	if (voucher.type == 0) {
		if (
			document.querySelector(".shipping-detail").style.display != "block" ||
			document.querySelector(".fee").textContent == `Thông báo sau`
		) {
			Swal.fire({
				icon: "warning",
				title: "Áp mã không thành công",
				text: "Chưa chọn địa điểm hoặc phí ship sẽ được thông báo sau",
				customClass: {
					confirmButton: "my-custom-button",
				},
				confirmButtonText: "OK",
			});
			return false;
		}

		// Kiểm tra nếu đã áp giftcode freeship thì không được áp giftcode ship tiếp nữa
		let voucher_type = null;
		if (document.querySelector(".giftcode").style.display == "flex") {
			voucher_type = JSON.parse(
				document.querySelector(".giftcode").getAttribute("data-gift-code")
			).type;
		}

		if (voucher_type == 0) {
			Swal.fire({
				icon: "warning",
				title: "Áp mã không thành công",
				text: "Bạn đã áp mã gift code giảm tiền SHIP rồi",
				customClass: {
					confirmButton: "my-custom-button",
				},
				confirmButtonText: "OK",
			});
			return false;
		}

		let shippingFeeValue = parseVNDString(
			document.querySelector(".fee").textContent
		);
		let shippingDiscount = calculateDiscount(
			itemValid,
			minPriceNum,
			maxValueNum,
			valueNum,
			shippingFeeValue
		);

		if (shippingDiscount == -1) {
			Swal.fire({
				icon: "warning",
				title: "Áp mã không thành công",
				text: "Giỏ hàng không có sản phẩm nào nằm trong ngành hàng được áp mã.",
				customClass: {
					confirmButton: "my-custom-button",
				},
				confirmButtonText: "OK",
			});
			return false;
		} else if (shippingDiscount == -2) {
			Swal.fire({
				icon: "warning",
				title: "Áp mã không thành công",
				text: "Tổng đơn hàng nhỏ hơn mức tối thiểu cho phép",
				customClass: {
					confirmButton: "my-custom-button",
				},
				confirmButtonText: "OK",
			});
			return false;
		}

		const giftCodeInfo = document.querySelector(".giftcode");
		let discountAmount = 0;
		if (giftCodeInfo && giftCodeInfo.style.display == "flex") {
			discountAmount = parseVNDString(
				document.getElementById("giftcode").textContent
			);
		}

		document.getElementById("cartVoucher").textContent =
			"- " + shippingDiscount;
		let totalBill =
			Number(itemprice) +
			Number(shippingFeeValue) -
			discountAmount -
			parseVNDString(shippingDiscount);
		if (totalBill < 0) {
			totalBill = 0;
		}
		document.getElementById("totalPrice").textContent =
			formatCurrency(totalBill);
		document.querySelector(".cart-voucher").style.display = "flex";
	} else if (voucher.type == 1) {
		let shippingFeeValue = 0;
		if (
			document.querySelector(".shipping-detail").style.display == "block" &&
			document.querySelector(".fee").textContent != `Thông báo sau`
		) {
			shippingFeeValue = parseVNDString(
				document.querySelector(".fee").textContent
			);
		}

		// Kiểm tra nếu đã áp giftcode freeship thì không được áp giftcode ship tiếp nữa
		let voucher_type = null;
		if (document.querySelector(".giftcode").style.display == "flex") {
			voucher_type = JSON.parse(
				document.querySelector(".giftcode").getAttribute("data-gift-code")
			).type;
		}

		if (voucher_type == 1) {
			Swal.fire({
				icon: "warning",
				title: "Áp mã không thành công",
				text: "Bạn đã áp mã gift code giảm tiền hàng rồi",
				customClass: {
					confirmButton: "my-custom-button",
				},
				confirmButtonText: "OK",
			});
			return false;
		}

		if (itemprice == null) {
			return false;
		}
		let itemDiscount = calculateDiscount(
			itemValid,
			minPriceNum,
			maxValueNum,
			valueNum
		);
		if (itemDiscount == -1) {
			Swal.fire({
				icon: "warning",
				title: "Áp mã không thành công",
				text: "Giỏ hàng không có sản phẩm nào nằm trong ngành hàng được áp mã.",
				customClass: {
					confirmButton: "my-custom-button",
				},
				confirmButtonText: "OK",
			});
			return false;
		} else if (itemDiscount == -2) {
			Swal.fire({
				icon: "warning",
				title: "Áp mã không thành công",
				text: "Tổng đơn hàng nhỏ hơn mức tối thiểu cho phép",
				customClass: {
					confirmButton: "my-custom-button",
				},
				confirmButtonText: "OK",
			});
			return false;
		}

		const giftCodeInfo = document.querySelector(".giftcode");
		let discountAmount = 0;
		if (giftCodeInfo && giftCodeInfo.style.display == "flex") {
			discountAmount = parseVNDString(
				document.getElementById("giftcode").textContent
			);
		}

		document.getElementById("cartVoucher").textContent = "- " + itemDiscount;
		let totalBill =
			Number(itemprice) +
			Number(shippingFeeValue) -
			discountAmount -
			parseVNDString(itemDiscount);
		if (totalBill < 0) {
			totalBill = 0;
		}
		document.getElementById("totalPrice").textContent =
			formatCurrency(totalBill);
		document.querySelector(".cart-voucher").style.display = "flex";
	}
	return true;
}
function removeVoucherCalc() {
	const itemprice = document.getElementById("totalPrice").dataset.price;
	if (itemprice == null) {
		return false;
	}
	let shippingFeeValue = 0;
	if (
		document.querySelector(".shipping-detail").style.display == "block" &&
		document.querySelector(".fee").textContent != `Thông báo sau`
	) {
		shippingFeeValue = parseVNDString(
			document.querySelector(".fee").textContent
		);
	}
	if (document.querySelector(".cart-voucher").style.display == "flex") {
		document.getElementById("cartVoucher").textContent = "";
		document.querySelector(".cart-voucher").style.display = "none";
		let totalBill = Number(itemprice) + Number(shippingFeeValue);
		document.getElementById("totalPrice").textContent =
			formatCurrency(totalBill);
	}
}

function removeGiftCodeCalc() {
	const itemprice = document.getElementById("totalPrice").dataset.price;
	if (itemprice == null) {
		return false;
	}
	let shippingFeeValue = 0;
	if (
		document.querySelector(".shipping-detail").style.display == "block" &&
		document.querySelector(".fee").textContent != `Thông báo sau`
	) {
		shippingFeeValue = parseVNDString(
			document.querySelector(".fee").textContent
		);
	}
	let voucherBill = 0;
	if (document.querySelector(".cart-voucher").style.display == "flex") {
		voucherBill = parseVNDString(
			document.getElementById("cartVoucher").textContent
		);
	}
	if (document.querySelector(".giftcode").style.display == "flex") {
		document.getElementById("giftcode").textContent = "";
		document.querySelector(".giftcode").style.display = "none";
		let totalBill = Number(itemprice) + Number(shippingFeeValue) - voucherBill;
		document.getElementById("totalPrice").textContent =
			formatCurrency(totalBill);
	}

	// Reset lại các thành phần
	document.getElementById("gift_code").value = "";
	document.getElementById("gift_code").disabled = false;
	document.getElementById("gift-message").classList.add("hidden");
	document.getElementById("gift-message").textContent = "";
	document.getElementById("cancel-gift").classList.add("hidden");
}

function initVoucherSelection() {
	let selectedVoucherId = null;
	// Chọn voucher
	document.querySelectorAll(".voucher-card").forEach((card) => {
		card.addEventListener("click", function () {
			if (checkResetGiftcode) {
				selectedVoucherId = null;
				checkResetGiftcode = false;
			}
			// Nếu đã chọn voucher khác, huỷ chọn cũ
			if (selectedVoucherId && selectedVoucherId !== this.dataset.id) {
				document
					.querySelectorAll(".voucher-card")
					.forEach((c) => c.classList.remove("border-blue-500"));
				removeVoucherCalc();
			}

			// Nếu chọn lại chính nó thì bỏ chọn
			if (selectedVoucherId === this.dataset.id) {
				selectedVoucherId = null;
				this.classList.remove("border-blue-500");
				document.getElementById("selected-voucher").classList.add("hidden");
				removeVoucherCalc();
				return;
			}

			// Cập nhật giá trị hoá đơn sau khi chọn
			if (!addVoucherCalc(card)) {
				if (
					!document
						.getElementById("selected-voucher")
						.classList.contains("hidden")
				) {
					selectedVoucherId = null;
					document.getElementById("selected-voucher").classList.add("hidden");
				}
				return;
			}

			// Gán ID voucher đã chọn
			selectedVoucherId = this.dataset.id;
			this.classList.add("border-blue-500");

			// Cập nhật UI
			document.getElementById("selected-voucher-name").textContent =
				this.nextElementSibling.querySelector(".endow-content").textContent;
			document.getElementById("selected-voucher").classList.remove("hidden");
		});

		// Khi di chuột vào
		const tooltip = card.nextElementSibling;
		card.addEventListener("mouseenter", () => {
			const rect = card.getBoundingClientRect(); // lấy vị trí thẻ voucher

			// Đặt vị trí tooltip bên dưới thẻ
			tooltip.style.top = `${rect.bottom}px`;
			tooltip.classList.add("show"); // thêm hiệu ứng sau 1 frame

			// Tính chiều rộng tooltip sau khi nó hiển thị
			const tooltipWidth = tooltip.offsetWidth;
			const viewportWidth = window.innerWidth;

			// Mặc định căn trái theo card
			let left = rect.left;
			// Nếu tooltip tràn phải
			if (rect.left + 30 + tooltipWidth > viewportWidth) {
				tooltip.style.left = `${viewportWidth - tooltipWidth - 10}px`;
				return;
			}

			// Nếu tooltip tràn trái
			if (left < 0) {
				tooltip.style.left = `8px`;
				return;
			}
			tooltip.style.left = `${rect.left + 30}px`;
		});

		// Khi rời chuột ra
		card.addEventListener("mouseleave", () => {
			tooltip.classList.remove("show");
		});

		// Khi scroll
		window.addEventListener("scroll", () => {
			if (tooltip.classList.contains("show")) {
				tooltip.classList.remove("show");
			}
		});
	});

	// Bỏ chọn voucher
	document
		.getElementById("remove-selected-voucher")
		.addEventListener("click", function () {
			selectedVoucherId = null;
			document.getElementById("selected-voucher").classList.add("hidden");
			document
				.querySelectorAll(".voucher-card")
				.forEach((c) => c.classList.remove("border-blue-500"));
			removeVoucherCalc();
		});
}

function formatDateTime(datetimeStr) {
	const date = new Date(datetimeStr.replace(" ", "T")); // đảm bảo định dạng chuẩn ISO

	const hours = date.getHours().toString().padStart(2, "0");
	const minutes = date.getMinutes().toString().padStart(2, "0");
	const day = date.getDate().toString().padStart(2, "0");
	const month = (date.getMonth() + 1).toString().padStart(2, "0"); // tháng bắt đầu từ 0
	const year = date.getFullYear();

	return `${hours}:${minutes} ${day}/${month}/${year}`;
}

document.addEventListener("DOMContentLoaded", function () {
	function renderVoucherText(voucher) {
		let valueText = "";
		let targetText = "";
		let limitText = "";
		let catalogText = "";
		let quantityText = "";
		let expiryText = "";
		// Xác định kiểu giảm: Phí ship hay giảm giá hàng
		if (voucher.type == 0) {
			// Phí ship
			const valueNum = Number(voucher.value);
			if (valueNum < 0) {
				valueText = [-1, "Giá trị không hợp lệ"];
			} else if (valueNum == 100) {
				valueText = [0, "Miễn phí"];
			} else if (valueNum < 100) {
				valueText = [1, `Giảm ${valueNum}%`];
			} else {
				valueText = [2, `Giảm ${valueNum.toLocaleString("vi-VN")}đ`];
			}
		} else if (voucher.type == 1) {
			// Giảm giá sản phẩm
			const valueNum = Number(voucher.value);
			if (valueNum < 0) {
				valueText = [-1, "Giá trị không hợp lệ"];
			} else if (valueNum == 100) {
				valueText = [0, "Miễn phí"];
			} else if (valueNum < 100) {
				valueText = [1, `Giảm ${valueNum}%`];
			} else {
				valueText = [2, `Giảm ${valueNum.toLocaleString("vi-VN")}đ`];
			}
		}

		// Áp dụng từ đơn giá tối thiểu nào?
		const minPriceNum = Number(voucher.min_price);
		if (voucher.min_price != null && minPriceNum < 0) {
			targetText = [
				-1,
				"Giá trị không hợp lệ",
				"Đơn giá tối thiểu không hợp lệ",
			];
		} else if (voucher.min_price != null && minPriceNum > 0) {
			targetText = [
				1,
				`Đơn từ ${minPriceNum.toLocaleString("vi-VN")}đ`,
				`Áp dụng cho đơn hàng tối thiểu từ ${minPriceNum.toLocaleString(
					"vi-VN"
				)}đ trở lên`,
			];
		} else {
			targetText = [
				0,
				"Toàn bộ mức giá",
				"Áp dụng cho toàn bộ các mức giá của đơn hàng",
			];
		}

		// Giới hạn giảm tối đa
		const maxValueNum = Number(voucher.max_value);
		if (voucher.max_value != null && maxValueNum < 0) {
			limitText = [-1, "Giá trị không hợp lệ"];
		} else if (voucher.max_value != null && maxValueNum > 0) {
			limitText = [1, `Giảm tối đa ${maxValueNum.toLocaleString("vi-VN")}đ`];
		} else {
			limitText = [0, "Không giới hạn mức giảm tối đa cho đơn hàng"];
		}

		// Ngành hàng áp dụng
		if (voucher.catalog_id != null && Number(voucher.catalog_id) < 0) {
			catalogText = [-1, "Ngành hàng không hợp lệ"];
		} else if (voucher.catalog_id == null) {
			catalogText = [0, "Áp dụng tất cả ngành hàng"];
		} else {
			catalogText = [1, `Áp dụng cho ngành hàng ${voucher.catalog_name}`];
		}

		// Tổng số lượng voucher
		const quantityNum = Number(voucher.total_quantity);
		if (voucher.total_quantity != null && quantityNum < 0) {
			quantityText = [-1, "Số lượng không hợp lệ"];
		} else if (voucher.total_quantity == null) {
			quantityText = [0, "Số lượng phát hành không giới hạn"];
		} else if (quantityNum === 0) {
			quantityText = [1, "Đã hết số lượng phát hành"];
		} else {
			quantityText = [2, `Số lượng còn lại: ${quantityNum}`];
		}

		// Tính thời gian còn lại trước khi hết hạn
		const now = new Date();
		const endDate = new Date(voucher.end_date);
		const diffTime = endDate - now;
		const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

		if (diffDays > 0) {
			expiryText = [2, `Còn ${diffDays} ngày`];
		} else if (diffDays === 0) {
			expiryText = [1, `Hết hạn trong hôm nay`];
		} else {
			expiryText = [0, `Đã hết hạn`];
		}

		return [
			valueText,
			targetText,
			limitText,
			catalogText,
			quantityText,
			expiryText,
		];
	}
	async function renderVoucherCard(
		slider,
		valueText,
		targetText,
		limitText,
		catalogText,
		quantityText,
		expiryText,
		voucher
	) {
		const base_url = "http://localhost:8080/public/upload/order";
		const iconSrc =
			voucher.type == 0 ? `${base_url}/shipping.png` : `${base_url}/sale.png`;
		const expiryClass =
			expiryText[1] == "Hết hạn trong hôm nay"
				? "text-red-600"
				: "text-gray-500";
		const used_total = voucher.used_total;
		const used_left = voucher.usage_limit - voucher.used_count;
		const html = `
					<div data-id="${voucher.id}" data-card='${JSON.stringify(
			voucher
		)}' class="voucher-card min-w-[200px] p-4 bg-white border rounded-lg shadow cursor-pointer hover:border-blue-500 flex justify-between items-center">
						${
							valueText[0] == -1 || targetText[0] == -1 || expiryText[0] == 0
								? ""
								: `
							<div class="voucher-text">
								${valueText[1] ? `<p class="text-lg font-semibold">${valueText[1]}</p>` : ""}
								${targetText[1] ? `<p class="text-sm text-gray-800">${targetText[1]}</p>` : ""}
								${
									expiryText[1]
										? `<p class="text-sm ${expiryClass}">HSD: ${expiryText[1]}</p>`
										: ""
								}
							</div>
						`
						}

						${
							iconSrc
								? `<img src="${iconSrc}" class="sale-icon w-8 h-auto" alt="giá trị đơn hàng">`
								: ""
						}
					</div>
					<div class="voucher-tooltip">
						<div class="max-w-xl mx-auto mt-10 p-6 bg-white rounded-lg shadow-md font-sans text-base leading-relaxed">
							<!-- Thanh tiến trình -->
							<div class="mb-6">
								${
									quantityText[0] == -1
										? ""
										: quantityText[0] == 0
										? `
								<div class="w-[300px] text-sm text-orange-600 mb-2 font-medium">
									<strong>Số lượng phát hành:</strong> Không giới hạn
								</div>`
										: quantityText[0] == 1
										? `
								<div class="text-sm text-orange-600 mb-2 font-medium">
									<strong>Số lượng phát hành:</strong> Đã phát hết
								</div>`
										: quantityText[0] == 2 && used_total != null
										? `
								<div class="text-sm text-orange-600 mb-2 font-medium">
									<strong>Số lượng đã sử dụng:</strong> ${used_total} / ${voucher.total_quantity}
								</div>
								<div class="w-[300px] h-2 bg-gray-300 rounded-full overflow-hidden">
									<div
										class="h-full bg-gradient-to-r from-red-500 to-yellow-400 text-white text-[8px] text-right leading-[8px] pr-1"
										style="width: ${(used_total / voucher.total_quantity) * 100}%"
									></div>
								</div>`
										: ""
								}

								${
									used_left != null && used_left < 0
										? ""
										: used_left != null
										? `
									<div class="inline-block mt-5 float-right bg-red-600 text-white text-sm font-bold px-3 py-1 rounded">
										Còn ${used_left} lần sử dụng
									</div>
									`
										: `
									<div class="inline-block mt-5 float-right bg-green-600 text-white text-sm font-bold px-3 py-1 rounded">
										Không giới hạn
									</div>
									`
								}
							</div>

							<!-- Hạn sử dụng -->
							${
								voucher.start_date && voucher.end_date
									? `
							<h2 class="text-lg font-semibold text-black mt-6">Hạn sử dụng mã</h2>
							<p class="text-base text-gray-700 leading-loose whitespace-nowrap">
							${formatDateTime(voucher.start_date)} - ${formatDateTime(voucher.end_date)}
							</p>
							`
									: ""
							}

							<!-- Ưu đãi -->
							${
								voucher.description && voucher.description.trim()
									? `
								<h2 class="text-lg font-semibold text-black mt-6">Ưu đãi</h2>
								<p class="text-base text-gray-700 leading-loose endow-content">
									${voucher.description}
								</p>
								`
									: ""
							}

							<!-- Điều kiện -->
							${
								(catalogText?.[0] !== -1 && catalogText?.[1]) ||
								(limitText?.[0] !== -1 && limitText?.[1]) ||
								(targetText?.[0] !== -1 && targetText?.[2]) ||
								(voucher.usage_limit != null && voucher.usage_limit >= 0)
									? `
									<h2 class="text-lg font-semibold text-black mt-6">Điều kiện</h2>
									<ul class="list-disc pl-5 text-base text-gray-700 leading-loose">
										${
											catalogText?.[0] !== -1 && catalogText?.[1]
												? `<li>${catalogText[1]}</li>`
												: ""
										}
										${limitText?.[0] !== -1 && limitText?.[1] ? `<li>${limitText[1]}</li>` : ""}
										${targetText?.[0] !== -1 && targetText?.[2] ? `<li>${targetText[2]}</li>` : ""}
										${
											voucher.usage_limit != null && voucher.usage_limit >= 0
												? `<li>Mỗi người dùng được phép sử dụng tối đa ${voucher.usage_limit} lượt</li>`
												: ""
										}
									</ul>
								`
									: ""
							}
						</div>
					</div>
					`;

		slider.insertAdjacentHTML("beforeend", html);
	}

	async function fetchVouchers() {
		try {
			const response = await fetch("http://localhost:8080/get-voucher", {
				method: "GET",
				headers: { "Content-Type": "application/json" },
			});
			const text = await response.text();
			const data = JSON.parse(text);
			if (data.status === "success" && Array.isArray(data.data.coupon_info)) {
				const slider = document.getElementById("voucher-slider");
				slider.innerHTML = ""; // Xóa nội dung cũ
				const sortCoupon = sortVouchersByPriority(data.data.coupon_info);
				sortCoupon.forEach(async (voucher) => {
					const [
						valueText,
						targetText,
						limitText,
						catalogText,
						quantityText,
						expiryText,
					] = renderVoucherText(voucher);
					await renderVoucherCard(
						slider,
						valueText,
						targetText,
						limitText,
						catalogText,
						quantityText,
						expiryText,
						voucher
					);
				});
				initVoucherSelection();
			} else {
				console.warn("Không có dữ liệu voucher.");
			}
		} catch (error) {
			console.error("Lỗi khi lấy voucher:", error);
		}
	}

	fetchVouchers();

	function addGiftCodeCalc(voucher) {
		// Xác định kiểu giảm: Phí ship hay giảm giá hàng
		// Số tiền giảm hoặc phần trăm giảm
		const valueNum = voucher.value !== null ? parseInt(voucher.value) : null;

		// Áp dụng từ đơn giá tối thiểu nào?
		const minPriceNum =
			voucher.min_price !== null ? parseInt(voucher.min_price) : null;

		// Giới hạn giảm tối đa
		const maxValueNum =
			voucher.max_value !== null ? parseInt(voucher.max_value) : null;

		// Tổng tiền gốc của đơn hàng
		const itemprice = document.getElementById("totalPrice").dataset.price;

		// Danh sách cart
		const carts = document.getElementById("cartInfo");
		var cartData = JSON.parse(carts.getAttribute("data-cart"));

		// Tổng tiền các sản phẩm thoả mãn ngành hàng trong cart
		const itemValid = calculateCartTotalByCatalog(cartData, voucher.catalog_id);
		if (voucher.type == 0) {
			if (
				document.querySelector(".shipping-detail").style.display != "block" ||
				document.querySelector(".fee").textContent == `Thông báo sau`
			) {
				Swal.fire({
					icon: "warning",
					title: "Áp mã không thành công",
					text: "Chưa chọn địa điểm hoặc phí ship sẽ được thông báo sau",
					customClass: {
						confirmButton: "my-custom-button",
					},
					confirmButtonText: "OK",
				});
				return false;
			}
			// Kiểm tra nếu đã áp voucher freeship thì không được áp voucher ship tiếp nữa
			let voucher_type = null;
			document.querySelectorAll(".voucher-card").forEach((card) => {
				if (card.classList.contains("border-blue-500")) {
					let voucher = JSON.parse(card.getAttribute("data-card"));
					voucher_type = voucher.type;
					return;
				}
			});
			if (voucher_type == 0) {
				Swal.fire({
					icon: "warning",
					title: "Áp mã không thành công",
					text: "Bạn đã áp mã voucher giảm giá SHIP rồi",
					customClass: {
						confirmButton: "my-custom-button",
					},
					confirmButtonText: "OK",
				});
				return false;
			}

			let shippingFeeValue = parseVNDString(
				document.querySelector(".fee").textContent
			);
			let shippingDiscount = calculateDiscount(
				itemValid,
				minPriceNum,
				maxValueNum,
				valueNum,
				shippingFeeValue
			);

			if (shippingDiscount == -1) {
				Swal.fire({
					icon: "warning",
					title: "Áp mã không thành công",
					text: "Giỏ hàng không có sản phẩm nào nằm trong ngành hàng được áp mã.",
					customClass: {
						confirmButton: "my-custom-button",
					},
					confirmButtonText: "OK",
				});
				return false;
			} else if (shippingDiscount == -2) {
				Swal.fire({
					icon: "warning",
					title: "Áp mã không thành công",
					text: "Tổng đơn hàng nhỏ hơn mức tối thiểu cho phép",
					customClass: {
						confirmButton: "my-custom-button",
					},
					confirmButtonText: "OK",
				});
				return false;
			}

			const cartVoucher = document.querySelector(".cart-voucher");
			let discountAmount = 0;
			if (cartVoucher && cartVoucher.style.display == "flex") {
				discountAmount = parseVNDString(
					document.getElementById("cartVoucher").textContent
				);
			}
			document.getElementById("giftcode").textContent = "- " + shippingDiscount;

			let totalBill =
				Number(itemprice) +
				Number(shippingFeeValue) -
				discountAmount -
				parseVNDString(shippingDiscount);
			if (totalBill < 0) {
				totalBill = 0;
			}
			document.getElementById("totalPrice").textContent =
				formatCurrency(totalBill);
			document.querySelector(".giftcode").style.display = "flex";
		} else if (voucher.type == 1) {
			let shippingFeeValue = 0;
			if (
				document.querySelector(".shipping-detail").style.display == "block" &&
				document.querySelector(".fee").textContent != `Thông báo sau`
			) {
				shippingFeeValue = parseVNDString(
					document.querySelector(".fee").textContent
				);
			}

			// Kiểm tra nếu đã áp voucher freeship thì không được áp voucher ship tiếp nữa
			let voucher_type = null;
			document.querySelectorAll(".voucher-card").forEach((card) => {
				if (card.classList.contains("border-blue-500")) {
					let voucher = JSON.parse(card.getAttribute("data-card"));
					voucher_type = voucher.type;
					return;
				}
			});
			if (voucher_type == 1) {
				Swal.fire({
					icon: "warning",
					title: "Áp mã không thành công",
					text: "Bạn đã áp mã voucher giảm tiền hàng rồi",
					customClass: {
						confirmButton: "my-custom-button",
					},
					confirmButtonText: "OK",
				});
				return false;
			}

			if (itemprice == null) {
				return false;
			}
			let itemDiscount = calculateDiscount(
				itemValid,
				minPriceNum,
				maxValueNum,
				valueNum
			);
			if (itemDiscount == -1) {
				Swal.fire({
					icon: "warning",
					title: "Áp mã không thành công",
					text: "Giỏ hàng không có sản phẩm nào nằm trong ngành hàng được áp mã.",
					customClass: {
						confirmButton: "my-custom-button",
					},
					confirmButtonText: "OK",
				});
				return false;
			} else if (itemDiscount == -2) {
				Swal.fire({
					icon: "warning",
					title: "Áp mã không thành công",
					text: "Tổng đơn hàng nhỏ hơn mức tối thiểu cho phép",
					customClass: {
						confirmButton: "my-custom-button",
					},
					confirmButtonText: "OK",
				});
				return false;
			}

			const cartVoucher = document.querySelector(".cart-voucher");
			let discountAmount = 0;
			if (cartVoucher && cartVoucher.style.display == "flex") {
				discountAmount = parseVNDString(
					document.getElementById("cartVoucher").textContent
				);
			}

			document.getElementById("giftcode").textContent = "- " + itemDiscount;
			let totalBill =
				Number(itemprice) +
				Number(shippingFeeValue) -
				discountAmount -
				parseVNDString(itemDiscount);
			if (totalBill < 0) {
				totalBill = 0;
			}
			document.getElementById("totalPrice").textContent =
				formatCurrency(totalBill);
			document.querySelector(".giftcode").style.display = "flex";
		}
		return true;
	}

	// Áp dụng gift code
	document
		.getElementById("apply-gift")
		.addEventListener("click", async function () {
			const code = document.getElementById("gift_code").value.trim();
			if (!code) {
				alert("Vui lòng nhập gift code!");
				return;
			}

			const data = {
				// Thêm các trường cần gửi, ví dụ:
				userId: 123,
				giftCode: code,
			};

			const response = await fetch("http://localhost:8080/check-gift-code", {
				method: "POST",
				headers: {
					"Content-Type": "application/json",
				},
				body: JSON.stringify(data),
			});

			const card = await response.json();

			const giftcode = document.querySelector(".giftcode");
			giftcode.setAttribute("data-gift-code", JSON.stringify(card.data));

			if (!addGiftCodeCalc(card.data)) {
				return;
			}

			// Hiển thị thông báo và nút huỷ
			document.getElementById(
				"gift-message"
			).textContent = `Đã áp dụng Gift Code: ${code}`;
			document.getElementById("gift-message").classList.remove("hidden");
			document.getElementById("cancel-gift").classList.remove("hidden");

			// Disable input nếu muốn không cho sửa code
			document.getElementById("gift_code").disabled = true;
		});

	document.getElementById("cancel-gift").addEventListener("click", function () {
		removeGiftCodeCalc();
	});
});

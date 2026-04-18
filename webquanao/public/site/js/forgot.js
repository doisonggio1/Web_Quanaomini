let recaptchaToken = "";
function onCaptchaSuccess(token) {
	recaptchaToken = token;
}

function validateRecaptcha() {
	if (!recaptchaToken) {
		// Loại thông báo: 'success' hoặc 'error'
		const type = "error"; // hoặc 'error'

		Swal.fire({
			toast: true,
			position: "top",
			icon: type,
			title:
				type === "success"
					? "Thành công! Dữ liệu đã được lưu."
					: "Vui lòng xác nhận reCAPTCHA trước khi gửi.",
			showConfirmButton: false,
			showCloseButton: true,
			timer: 2500,
			timerProgressBar: true,
			customClass: {
				popup: `custom-toast ${
					type === "success" ? "swal2-success-toast" : "swal2-error-toast"
				}`,
			},
			didOpen: (toast) => {
				toast.addEventListener("mouseenter", Swal.stopTimer);
				toast.addEventListener("mouseleave", Swal.resumeTimer);
			},
		});
		return false;
	}
	return true;
}

let isFetching = false;
document
	.getElementById("submitBtn")
	.addEventListener("click", async function () {
		if (isFetching) {
			// Loại thông báo: 'success' hoặc 'error'
			const type = "error"; // hoặc 'error'

			Swal.fire({
				toast: true,
				position: "top",
				icon: type,
				title: "Yêu cầu đang được thực hiện, vui lòng đợi...",
				showConfirmButton: false,
				showCloseButton: true,
				timer: 2500,
				timerProgressBar: true,
				customClass: {
					popup: `custom-toast ${
						type === "success" ? "swal2-success-toast" : "swal2-error-toast"
					}`,
				},
				didOpen: (toast) => {
					toast.addEventListener("mouseenter", Swal.stopTimer);
					toast.addEventListener("mouseleave", Swal.resumeTimer);
				},
			});
			return; // Nếu đang fetch, không làm gì cả
		}
		isFetching = true;
		let isValid = true;

		const response = grecaptcha.getResponse();

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

		// Kiểm tra Email
		let emailInput = document.getElementById("email");
		let emailValue = emailInput.value.trim();
		let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		if (emailInput.value === "") {
			showError(emailInput, "Email không được rỗng");
			isValid = false;
		} else if (!emailRegex.test(emailValue)) {
			showError(emailInput, "Email không hợp lệ");
			isValid = false;
		} else {
			clearError(emailInput);
		}

		// Kiểm tra mật khẩu
		let passwordInput = document.getElementById("password");
		let re_passwordInput = document.getElementById("repassword");
		let passwordValue = passwordInput.value.trim();

		//Password phải từ 8 kí tự trở lên
		if (passwordInput.value === "") {
			showError(passwordInput, "Mật khẩu không được rỗng");
			isValid = false;
		} else if (passwordValue.length < 8) {
			showError(passwordInput, "Password phải từ 8 kí tự trở lên");
			isValid = false;
		} else {
			clearError(passwordInput);
		}

		// Mật khẩu nhập lại không khớp
		if (re_passwordInput.value === "") {
			showError(re_passwordInput, "Mật khẩu nhập lại không được rỗng");
			isValid = false;
		} else if (passwordInput.value !== re_passwordInput.value) {
			showError(re_passwordInput, "Mật khẩu nhập lại không khớp");
			isValid = false;
		} else {
			clearError(re_passwordInput);
		}

		// Nếu có lỗi, không gửi API
		if (!isValid) {
			isFetching = false;
			return;
		}

		// Gọi hàm kiểm tra recaptcha trước khi gửi
		if (!validateRecaptcha() || !response) {
			isFetching = false;
			return;
		}

		const formData = {
			email: emailValue,
			password: passwordValue,
			recaptcha: recaptchaToken,
		};
		// Gửi dữ liệu lên server qua fetch API (Fake API endpoint)
		// Cập nhật thời gian đếm ngược mỗi giây
		let countdown = document.getElementById("hiddenData").dataset.expire; //ENV
		// Định nghĩa hàm ngoài
		async function myExternalFunction(
			url,
			password = null,
			email = null,
			interval = 1000
		) {
			let passwordInfo = {
				password: password,
				email: email,
			};
			while (true) {
				try {
					const response = await fetch(url, {
						method: "POST",
						headers: {
							"Content-Type": "application/json",
						},
						body: JSON.stringify(passwordInfo),
					});
					const data = await response.json();

					if (data.status === "success") {
						return data;
					}
				} catch (error) {
					console.error("Fetch error:", error);
				}

				await new Promise((resolve) => setTimeout(resolve, interval));
			}
		}
		await fetch("http://localhost:8080/quen-mat-khau", {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
			},
			body: JSON.stringify(formData),
		})
			.then((res) => res.text()) // Read the response as text first
			.then((text) => {
				try {
					// Try parsing the response text as JSON
					const data = JSON.parse(text);
					// Hiển thị popup đổi mật khẩu thành công
					if (data.status == "success") {
						Swal.fire({
							icon: "info", // Biểu tượng thông tin
							title: "Sắp xong rồi...", // Tiêu đề
							text: data.message,
							customClass: {
								confirmButton: "my-custom-button", // Tùy chỉnh lớp nút xác nhận
							},
							confirmButtonText: "OK", // Văn bản của nút xác nhận
							showConfirmButton: false, // Ẩn nút xác nhận
							timer: countdown * 1000, // Thời gian hiển thị popup (10 giây)
							timerProgressBar: true, // Hiển thị thanh tiến trình đếm ngược
						}).then((result) => {
							// Sau khi popup đóng hoặc khi thời gian hết, chuyển hướng
							if (result.dismiss === Swal.DismissReason.timer) {
								// Popup bị đóng do hết thời gian
								window.location.href = "/";
							}
						});
						myExternalFunction(
							"http://localhost:8080/user/check_forgot_password_mail",
							passwordValue,
							emailValue
						)
							.then((data) => {
								if (data.status === "success") {
									const countdown = 3; // tổng thời gian
									let remaining = countdown;
									Swal.fire({
										icon: "success",
										title: "Đổi mật khẩu thành công",
										html: `
										<p><strong>Chuyển hướng về đăng nhập sau <span id="countdown-timer">${remaining}</span> giây...</strong></p>
									`,
										customClass: {
											confirmButton: "my-custom-button",
										},
										confirmButtonText: "Về trang chủ",
										showConfirmButton: true,
										timer: countdown * 1000,
										timerProgressBar: true,
										didOpen: () => {
											const timerEl =
												document.getElementById("countdown-timer");
											const interval = setInterval(() => {
												remaining--;
												if (timerEl) timerEl.textContent = remaining;
												if (remaining <= 0) clearInterval(interval);
											}, 1000);
										},
									}).then((result) => {
										if (result.isConfirmed) {
											// Chờ thêm 3 giây nữa sau khi popup đóng
											window.location.href = "/";
										} else {
											window.location.href = "/dang-nhap";
										}
									});
								} else {
									Swal.fire({
										icon: "error",
										title: "Xác thực mail thất bại",
										text: data.message,
										customClass: {
											confirmButton: "my-custom-button",
										},
										confirmButtonText: "Thử lại",
									});

									// Reset reCAPTCHA for the next submission
									grecaptcha.reset();
									recaptchaToken = "";
								}
							})
							.catch((error) => {
								console.error("Error:", error);
								// Reset reCAPTCHA for the next submission
								grecaptcha.reset();
								recaptchaToken = "";
							});
					} else {
						if (data.status === "error") {
							let detailHTML = "";
							if (data.errors) {
								detailHTML +=
									'<ul style="text-align: left; font-size: larger;">';
								for (const key in data.errors) {
									detailHTML += `<li><strong>${key}:</strong> ${data.errors[key]}</li>`;
								}
								detailHTML += "</ul>";
							}

							Swal.fire({
								icon: "error",
								title: "Đổi mật khẩu thất bại",
								text: data.message,
								showCancelButton: true,
								confirmButtonText: "Thử lại",
								cancelButtonText: "Xem chi tiết",
								customClass: {
									confirmButton: "my-custom-button",
									cancelButton: "my-custom-button",
								},
							}).then((result) => {
								if (result.dismiss === Swal.DismissReason.cancel) {
									// Mở popup chi tiết lỗi
									Swal.fire({
										icon: "info",
										title: "Chi tiết lỗi",
										html: detailHTML,
										confirmButtonText: "Đã hiểu",
										customClass: {
											confirmButton: "my-custom-button",
										},
									});
								}
							});
						}

						// Reset reCAPTCHA for the next submission
						grecaptcha.reset();
						recaptchaToken = "";
					}
				} catch (err) {
					// Handle error if JSON parsing fails
					console.error("Lỗi:", err);

					// Reset reCAPTCHA for the next submission
					grecaptcha.reset();
					recaptchaToken = "";
				}
			})
			.catch((err) => {
				console.error("Lỗi:", err);

				// Reset reCAPTCHA for the next submission
				grecaptcha.reset();
				recaptchaToken = "";
			});
		isFetching = false;
	});

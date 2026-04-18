/**
 * Script xử lý chức năng phân tích AI trên trang quản trị
 */

// Thêm thư viện Markdown.js
if (typeof window.marked === "undefined") {
	const script = document.createElement("script");
	script.src = "https://cdn.jsdelivr.net/npm/marked/marked.min.js";
	script.async = false;
	document.head.appendChild(script);
}

$(document).ready(function () {
	// Xử lý sự kiện click cho nút phân tích AI chính
	$(".btn-analyze-ai").on("click", function () {
		// Sử dụng panel kết quả đầu tiên trên trang
		var resultPanel = $(".analysis-result-panel").first();

		// Hiển thị loading
		resultPanel.show();
		resultPanel.find(".loading-analysis").show();
		resultPanel.find(".analysis-result").hide();

		// Thay đổi tiêu đề phân tích
		resultPanel
			.find(".panel-title")
			.text("Phân tích số liệu kinh doanh");

		// Khởi tạo dữ liệu phân tích
		var productData = {
			data_analysis: true, // Yêu cầu phân tích dữ liệu
			brief_strategy: true, // Yêu cầu đề xuất chiến lược ngắn gọn
			panel_title: "Phân tích số liệu", // Tiêu đề phân tích
			panel_data: [], // Mảng chứa dữ liệu từ tất cả các panel
		};

		// Thu thập dữ liệu từ tất cả các panel có bảng dữ liệu
		$(".panel").each(function () {
			var panelElement = $(this);
			var panelTitle = panelElement.find(".panel-heading").text().trim();

			// Chỉ xử lý các panel có bảng dữ liệu
			if (panelElement.find("table").length > 0) {
				var panelData = {
					title: panelTitle,
					table_data: [],
				};

				// Thu thập dữ liệu từ bảng
				panelElement.find("table tbody tr").each(function () {
					var rowData = {};
					$(this)
						.find("td")
						.each(function (index) {
							var headerText = $(this)
								.closest("table")
								.find("th")
								.eq(index)
								.text()
								.trim();
							rowData[headerText] = $(this).text().trim();
						});
					panelData.table_data.push(rowData);
				});

				// Thêm dữ liệu panel vào mảng tổng hợp
				if (panelData.table_data.length > 0) {
					// Đã tổng hợp tất cả panel vào productData.panel_data
					productData.panel_data.push(panelData);
				}
			}
		});

		// Thu thập thêm dữ liệu từ các widget thống kê (nếu có)
		var statsData = {};
		$(".panel-widget").each(function () {
			var widgetTitle = $(this).find(".text-muted").text().trim();
			var widgetValue = $(this).find(".large").text().trim();
			if (widgetTitle && widgetValue) {
				statsData[widgetTitle] = widgetValue;
			}
		});

		// Thêm dữ liệu thống kê vào dữ liệu phân tích
		if (Object.keys(statsData).length > 0) {
			productData.stats_data = statsData;
		}

		// Gọi API thông qua proxy PHP thay vì trực tiếp đến container openAI
		fetch("/proxy/analyze_proxy.php", {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
			},
			body: JSON.stringify(productData),
		})
			.then((response) => {
				// Kiểm tra nếu response không phải là JSON
				const contentType = response.headers.get("content-type");
				if (!contentType || !contentType.includes("application/json")) {
					throw new Error(
						"Phản hồi không phải định dạng JSON. Có thể server đang gặp sự cố."
					);
				}
				return response.json();
			})
			.then((response) => {
				// Ẩn loading và hiển thị kết quả
				resultPanel.find(".loading-analysis").hide();
				resultPanel.find(".analysis-result").show();

				if (response.status === "success") {
					// Chuyển đổi kết quả phân tích từ text sang Markdown nếu thư viện đã được tải
					let analysisContent = response.data.analysis;
					if (typeof window.marked !== "undefined") {
						// Sử dụng marked để chuyển đổi Markdown thành HTML
						analysisContent = window.marked.parse(analysisContent);
					}
					// Hiển thị kết quả phân tích đã được định dạng
					resultPanel.find(".analysis-result").html(analysisContent);
				} else {
					// Hiển thị thông báo lỗi với định dạng tốt hơn
					let errorMessage = response.message || "Không thể phân tích dữ liệu";

					// Nếu lỗi liên quan đến API key, hiển thị thông báo thân thiện hơn
					if (
						errorMessage.includes("API key") ||
						errorMessage.includes("không khả dụng")
					) {
						errorMessage =
							"Xin lỗi, không thể phân tích dữ liệu lúc này. Dịch vụ AI không khả dụng hoặc cần cấu hình lại. Vui lòng liên hệ quản trị viên.";
					}

					resultPanel
						.find(".analysis-result")
						.html("<div class='alert alert-danger'>" + errorMessage + "</div>");
				}
			})
			.catch((error) => {
				// Ẩn loading và hiển thị thông báo lỗi
				resultPanel.find(".loading-analysis").hide();
				resultPanel.find(".analysis-result").show();

				// Tạo thông báo lỗi chi tiết và dễ hiểu hơn
				let errorMessage = "Lỗi kết nối đến service AI";

				// Kiểm tra loại lỗi để hiển thị thông báo phù hợp
				if (error.message && error.message.includes("JSON")) {
					errorMessage = "Lỗi định dạng dữ liệu: " + error.message;
				} else if (error.message && error.message.includes("NetworkError")) {
					errorMessage =
						"Lỗi kết nối mạng: Không thể kết nối đến máy chủ phân tích";
				} else if (error.message && error.message.includes("Failed to fetch")) {
					errorMessage =
						"Không thể kết nối đến máy chủ phân tích AI. Vui lòng kiểm tra kết nối mạng và thử lại sau.";
				} else if (error.message) {
					errorMessage = error.message;
				}

				resultPanel
					.find(".analysis-result")
					.html("<div class='alert alert-danger'>" + errorMessage + "</div>");
				console.error("Lỗi khi gọi phân tích AI:", error);

				// Ghi log chi tiết hơn để debug
				console.debug("Chi tiết lỗi:", {
					message: error.message,
					stack: error.stack,
					name: error.name,
				});
			});
	});
});

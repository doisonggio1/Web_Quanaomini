/**
 * JavaScript để xử lý chức năng tạo mô tả sản phẩm bằng AI
 */
$(document).ready(function () {
	// Xử lý khi nhấn nút tạo mô tả AI
	$("#generate-ai-description").on("click", function (e) {
		e.preventDefault();

		// Hiển thị thông báo đang xử lý
		$("#ai-status").html(
			'<div class="alert alert-info">Đang tạo mô tả AI, vui lòng đợi...</div>'
		);

		// Lấy dữ liệu từ form
		var formData = new FormData();

		// Lấy file ảnh
		var imageFile = $("#image")[0].files[0];
		if (!imageFile) {
			$("#ai-status").html(
				'<div class="alert alert-danger">Vui lòng chọn ảnh sản phẩm trước!</div>'
			);
			return;
		}

		// Lấy thông tin sản phẩm
		var productName = $('input[name="name"]').val();
		var catalogId = $('select[name="catalog_id"]').val();
		var catalogText = $('select[name="catalog_id"] option:selected').text();
		var price = $('input[name="price"]').val();

		// Kiểm tra dữ liệu
		if (
			!productName ||
			catalogId === "--- Chọn danh mục sản phẩm ---" ||
			!price
		) {
			$("#ai-status").html(
				'<div class="alert alert-danger">Vui lòng điền đầy đủ thông tin sản phẩm (tên, danh mục, giá)!</div>'
			);
			return;
		}

		// Thêm dữ liệu vào FormData
		formData.append("image", imageFile);
		formData.append("product_name", productName);
		formData.append("product_features", "Danh mục: " + catalogText);
		formData.append("price", price);

		// Gửi request đến service OpenAI
		$.ajax({
			url: "http://openai:5002/api/product/description",
			type: "POST",
			data: formData,
			processData: false,
			contentType: false,
			success: function (response) {
				if (response.status === "success") {
					// Cập nhật trường mô tả với nội dung AI tạo ra
					CKEDITOR.instances.content.setData(response.data.description);
					$("#ai-status").html(
						'<div class="alert alert-success">Đã tạo mô tả AI thành công!</div>'
					);
				} else {
					$("#ai-status").html(
						'<div class="alert alert-danger">Lỗi: ' +
							response.message +
							"</div>"
					);
				}
			},
			error: function (xhr, status, error) {
				$("#ai-status").html(
					'<div class="alert alert-danger">Lỗi kết nối đến service AI: ' +
						(xhr.responseJSON && xhr.responseJSON.message
							? xhr.responseJSON.message
							: error) +
						"</div>"
				);
				console.error("Lỗi kết nối đến service AI:", xhr, status, error);
			},
		});
	});
});

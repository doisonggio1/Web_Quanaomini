$(document).ready(function () {
   //ajax them san pham
   $(document).on("click", ".add-to-cart", function (e) {
      e.preventDefault();
      let productId = $(this).data("id");
  
      $.ajax({
          url: "/cart/add",
          type: "POST",
          data: { id: productId },
          dataType: "json",
          success: function (response) {
              if (response.status === "success") {
                  $("#cart-total").text(response.cart_total);
                  alert(response.message);
                  loadCart(); // Tải lại giỏ hàng
              } else {
                  alert(response.message);
              }
          }
      });
  });

  //AJAX cập nhật số lượng
  $(document).on("click", ".update-qty", function () {
   let productId = $(this).data("id");
   let action = $(this).data("action"); // 'sum' hoặc 'sub'

   $.ajax({
       url: "/cart/update",
       type: "POST",
       data: { id: productId, action: action },
       dataType: "json",
       success: function (response) {
           if (response.status === "success") {
               $("#cart-total").text(response.cart_total);
               loadCart();
           } else {
               alert(response.message);
           }
       }
   });
   });

   //AJAX xóa sản phẩm
   $(document).on("click", ".delete-item", function () {
      let productId = $(this).data("id");
  
      $.ajax({
          url: "/cart/del",
          type: "POST",
          data: { id: productId },
          dataType: "json",
          success: function (response) {
              if (response.status === "success") {
                  $("#cart-total").text(response.cart_total);
                  loadCart();
              } else {
                  alert(response.message);
              }
          }
      });
  });

  //Hàm tải lại giỏ hàng
  function loadCart() {
   $.ajax({
       url: "/cart",
       type: "GET",
       success: function (data) {
           $("#cart-content").html($(data).find("#cart-content").html());
       }
   });
   }


});

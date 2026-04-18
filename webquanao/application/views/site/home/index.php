<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<?php 
$product_categories = [
    'product/news' => ['title' => 'Sản phẩm mới', 'icon' => 'new.gif', 'products' => $new_product],
    'ban-chay' => ['title' => 'Sản phẩm bán chạy', 'icon' => 'hot.gif', 'products' => $hot_product],
    'product/views' => ['title' => 'Sản phẩm xem nhiều', 'icon' => 'hot.gif', 'products' => $view_product]
];
?>

<?php foreach ($product_categories as $link => $category) { 
    $swiper_id = str_replace(' ', '-', strtolower($category['title']));
?>
<div class="row">
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title text-center">
                <img src="<?php echo base_url(); ?>upload/icon/<?php echo $category['icon']; ?>" alt="">
                <a href="<?php echo base_url($link); ?>" class='product_title'><?php echo $category['title']; ?></a>
                <img src="<?php echo base_url(); ?>upload/icon/<?php echo $category['icon']; ?>" alt="">
            </h3>
        </div>		
        <div class="panel-body">
            <div class="swiper-container <?php echo $swiper_id; ?>">
                <div class="swiper-wrapper">
                    <?php foreach ($category['products'] as $value) { 
                        $name = covert_vi_to_en($value->name);
                        $name = strtolower($name);
                    ?>
                        <div class="swiper-slide">
                            <div class="product_item">
                                <p class="product_name">
                                    <a href="<?php echo base_url($name.'-p'.$value->id); ?>">
                                        <?php echo $value->name; ?>
                                    </a>
                                </p>
                                <div class="product-image">
                                    <a href="<?php echo base_url($name.'-p'.$value->id); ?>">
                                        <img src="<?php echo base_url(); ?>upload/product/<?php echo $value->image_link; ?>" alt="">
                                    </a>
                                </div>
                                <?php if ($value->discount > 0 || $value->price < $value->origin_price) { 
                                    $new_price = $value->price - $value->discount; ?>
                                    <p>
                                        <span class='price'><?php echo number_format($new_price); ?> VNĐ</span>
                                        <del class="product-discount"><?php echo number_format($value->origin_price); ?> VNĐ</del>
                                    </p>
                                <?php } else { ?>
                                    <p><span class='price'><?php echo number_format($value->origin_price); ?> VNĐ</span></p>
                                <?php } ?>
                                <p>
                                    <span class="glyphicon glyphicon-eye-open"></span> <?php echo $value->view; ?>
                                    <span class="glyphicon glyphicon-shopping-cart"></span> <?php echo $value->buyed; ?>
                                </p>
                                <a href="<?php echo base_url('cart/add/'.$value->id); ?>">
                                    <button class='btn btn-info'><span class="glyphicon glyphicon-shopping-cart"></span> Thêm giỏ hàng</button>
                                </a>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="swiper-button-next <?php echo $swiper_id; ?>-next"></div>
            <div class="swiper-button-prev <?php echo $swiper_id; ?>-prev"></div>
            <div class="swiper-pagination <?php echo $swiper_id; ?>-pagination"></div>
        </div>
    </div>
</div>
<?php } ?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var swiperCategories = [
            "sản-phẩm-mới",
            "sản-phẩm-bán-chạy",
            "sản-phẩm-xem-nhiều"
        ];
        swiperCategories.forEach(function(category) {
            new Swiper("." + category, {
				spaceBetween: 30,
				loop: true, // Lặp lại khi hết trang
				autoplay: {
					delay: 10000, // 10 giây chuyển trang 1 lần
					disableOnInteraction: false // Dừng autoplay khi người dùng tương tác
				},
				navigation: {
					nextEl: "." + category + "-next",
					prevEl: "." + category + "-prev",
				},
				pagination: {
					el: "." + category + "-pagination",
					clickable: true,
				},
				breakpoints: {
					1024: { slidesPerView: 4, slidesPerGroup: 4 },
					768: { slidesPerView: 2, slidesPerGroup: 2 },
					480: { slidesPerView: 1, slidesPerGroup: 1 }
				}
			});
        });
    });
</script>

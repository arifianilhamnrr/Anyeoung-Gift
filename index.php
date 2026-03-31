<?php
// Mengarahkan user ke folder users/
header("Location: users/");
exit();
?>

INSERT INTO `product_images` (`product_id`, `image_path`, `is_primary`, `sort_order`) VALUES
(2, 'public/uploads/products/bouquet-satin-utama.jpg', 1, 1),
(2, 'public/uploads/products/bouquet-satin-detail.jpg', 0, 2),
(3, 'public/uploads/products/bouquet-uang-utama.jpg', 1, 1),
(4, 'public/uploads/products/hampers-hijab-a-utama.jpg', 1, 1),
(5, 'public/uploads/products/hampers-hijab-b-utama.jpg', 1, 1),
(6, 'public/uploads/products/hampers-boneka-utama.jpg', 1, 1),
(6, 'public/uploads/products/hampers-boneka-detail.jpg', 0, 2),
(7, 'public/uploads/products/custom-hampers-utama.jpg', 1, 1);
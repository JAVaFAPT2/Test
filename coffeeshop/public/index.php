<?php
$page_title = "VelVet Coffee - Chiu cùng Cà phê";
include_once "includes/header.php";
include_once "config/database.php";
include_once "classes/Product.php";
include_once "classes/Category.php";

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);
$category = new Category($db);

// Lấy 6 sản phẩm mới nhất
$stmt = $product->readAll();
$products = [];
$counter = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($counter < 6) {
        $products[] = $row;
    }
    $counter++;
}

$cat_stmt = $category->readAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css/trangchu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>

    </style>
</head>
<body>

<section class="hero">
    <div class="hero-content" style="position:relative;">
        <div class="hero-image">
            <img src="image/background_trangchu.jpg" alt="VelVet Coffee Banner">
        </div>
        <div class="hero-text">
            <h1>Welcome to VelVet Coffee</h1>
            <p>Trải nghiệm những dòng cà phê tinh tế, được pha chế bằng đam mê và sự tỉ mỉ.</p>
        </div>
    </div>
</section>

<section class="menu-section">
    <div class="menu-container">
        <div class="menu-layout">
            <div class="menu-sidebar">
                <h1 class="velvet-text">VelVet's</h1>
                <h2 class="menu-heading">Menu</h2>
                <p class="menu-description">
                    "Mỗi tách cà phê đề được
pha chế cán thận bởi đội
ngũ pha chế lành nghề của
chúng tôi, sử dụng nguồn
cà phê cao cấp có nguồn
gốc tại địa phương và trên
toàn cầu để đảm bảo
mang đến trải nghiệm cà
phê độc đáo và thoa mãn."
                </p>
            </div>
            <div class="menu-content">
                <div class="menu-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:30px;">
                    <?php foreach ($products as $item): ?>
                    <div class="menu-item">
                        <div class="menu-item-image">
                            <img src="get_image.php?id=<?php echo $item['id']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>
                        <h3 class="menu-item-title"><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p class="menu-item-price">$<?php echo number_format($item['price'], 2); ?></p>
                        <button class="add-to-cart" data-id="<?php echo $item['id']; ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>" data-price="<?php echo $item['price']; ?>">Thêm vào giỏ</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="menu-footer">
                    <a href="menu.php"><button class="view-all-btn">XEM TẤT CẢ</button></a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="categories

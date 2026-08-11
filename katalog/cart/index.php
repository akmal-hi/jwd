<?php
include '../config.php';
// PERBAIKAN 1: Ganti include ke file functions yang baru
include '../includes/cart_functions.php';

// Ambil semua item dari cart
$cart_items = getCartItems();
$total_price = getCartTotal();

// PERBAIKAN 2: Gunakan fungsi getWaNumber() yang sudah aman (Prepared Statement)
$wa_default = getWaNumber();

// Buat pesan untuk WhatsApp
$wa_message = "Halo, saya tertarik dengan produk berikut:%0A%0A";
$no = 1;
foreach ($cart_items as $item) {
    $wa_message .= $no . ". " . $item['name'] . " - " . $item['price'] . "%0A";
    $no++;
}
$wa_message .= "%0ATotal: Rp " . number_format($total_price, 0, ',', '.');
$wa_message .= "%0A%0ATerima kasih.";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Keranjang Belanja - LSP COACHPRO INDONESIA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fa;
            padding: 2rem;
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(31, 36, 98, 0.08);
            animation: fadeIn 0.4s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .cart-header {
            padding: 1.5rem 2rem;
            background: white;
            border-bottom: 1px solid #eef2f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .cart-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2462;
        }
        .cart-header h1 i {
            color: #e8b830;
            margin-right: 0.5rem;
        }
        .btn-back {
            background: #f8f9fc;
            color: #1a2a3a;
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.85rem;
            transition: all 0.2s;
            border: 1px solid #eef2f0;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-back:hover {
            background: #eef1ff;
            border-color: #e8b830;
            color: #1f2462;
            transform: translateY(-2px);
        }
        .cart-empty {
            text-align: center;
            padding: 4rem 2rem;
        }
        .cart-empty i {
            font-size: 5rem;
            color: #e8b830;
            margin-bottom: 1rem;
            opacity: 0.7;
        }
        .cart-empty h3 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            color: #1f2462;
        }
        .cart-empty p {
            color: #6c7a8a;
            margin-bottom: 1.5rem;
        }
        .btn-shop {
            background: #1f2462;
            color: white;
            padding: 0.7rem 1.8rem;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            display: inline-block;
        }
        .btn-shop:hover {
            background: #141844;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(31, 36, 98, 0.3);
        }
        .cart-items {
            padding: 1rem 2rem;
        }
        .cart-items-header {
            display: grid;
            grid-template-columns: 3fr 1fr 1fr 0.5fr;
            padding: 0.8rem 0;
            border-bottom: 2px solid #eef2f0;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #8ba0ae;
        }
        .cart-item {
            display: grid;
            grid-template-columns: 3fr 1fr 1fr 0.5fr;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #eef2f0;
            gap: 1rem;
        }
        .cart-item-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .cart-item-name {
            font-weight: 700;
            color: #1a2a3a;
            font-size: 1rem;
        }
        .cart-item-category {
            font-size: 0.7rem;
            color: #e8b830;
            background: #eef1ff;
            display: inline-block;
            padding: 2px 8px;
            border-radius: 30px;
            width: fit-content;
        }
        .cart-item-price {
            font-weight: 600;
            color: #1f2462;
            font-size: 1rem;
        }
        .cart-item-quantity {
            text-align: center;
        }
        .quantity-badge {
            background: #eef1ff;
            color: #1f2462;
            padding: 0.25rem 1rem;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-block;
        }
        .cart-item-total {
            font-weight: 800;
            color: #1a2a3a;
            font-size: 1rem;
        }
        .cart-item-remove {
            background: none;
            border: none;
            color: #ef4444;
            cursor: pointer;
            font-size: 1.1rem;
            transition: all 0.2s;
            text-align: center;
            width: 32px;
            height: 32px;
            border-radius: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .cart-item-remove:hover {
            background: #fef2f2;
            color: #dc2626;
            transform: scale(1.05);
        }
        .cart-summary {
            background: #f8f9fc;
            padding: 1.5rem 2rem;
            border-top: 1px solid #eef2f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 0.5rem;
        }
        .cart-total {
            font-size: 1rem;
        }
        .cart-total span {
            font-weight: 800;
            color: #e8b830;
            font-size: 1.6rem;
            margin-left: 0.5rem;
        }
        .btn-checkout {
            background: linear-gradient(135deg, #25D366, #1da85e);
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.25);
        }
        .btn-checkout:hover {
            background: linear-gradient(135deg, #1da85e, #158a4d);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(37, 211, 102, 0.35);
        }
        .btn-clear {
            background: transparent;
            border: 1px solid #ef4444;
            color: #ef4444;
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-clear:hover {
            background: #ef4444;
            color: white;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            .cart-header {
                padding: 1rem 1.2rem;
            }
            .cart-header h1 {
                font-size: 1.2rem;
            }
            .cart-items-header {
                display: none;
            }
            .cart-item {
                grid-template-columns: 1fr;
                gap: 0.5rem;
                padding: 1rem 0;
                position: relative;
                padding-right: 40px;
            }
            .cart-item-info {
                gap: 0.3rem;
            }
            .cart-item-price {
                font-size: 0.9rem;
            }
            .cart-item-quantity {
                text-align: left;
            }
            .cart-item-total {
                font-size: 0.9rem;
            }
            .cart-item-remove {
                position: absolute;
                top: 1rem;
                right: 0;
            }
            .cart-summary {
                flex-direction: column;
                align-items: stretch;
                padding: 1.2rem;
            }
            .cart-total {
                text-align: center;
            }
            .cart-total span {
                font-size: 1.3rem;
            }
            .btn-checkout, .btn-clear {
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .cart-items {
                padding: 0.5rem 1rem;
            }
        }
        
        .toast-notification {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #1f2462;
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(31, 36, 98, 0.3);
            animation: slideIn 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .toast-notification i {
            color: #e8b830;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="cart-header">
            <h1><i class="fas fa-shopping-cart"></i> Keranjang Belanja</h1>
            <a href="../index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali Belanja</a>
        </div>
        
        <?php if (empty($cart_items)): ?>
            <div class="cart-empty">
                <i class="fas fa-shopping-cart"></i>
                <h3>Keranjang Anda kosong</h3>
                <p>Belum ada produk yang disimpan. Yuk, mulai belanja!</p>
                <a href="../index.php" class="btn-shop"><i class="fas fa-store"></i> Lihat Produk</a>
            </div>
        <?php else: ?>
            <div class="cart-items-header">
                <span>Produk</span>
                <span>Harga</span>
                <span>Jumlah</span>
                <span></span>
            </div>
            
            <div class="cart-items" id="cartItemsContainer">
                <?php foreach ($cart_items as $id => $item): ?>
                <div class="cart-item" data-id="<?= $id ?>">
                    <div class="cart-item-info">
                        <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
                        <span class="cart-item-category"><i class="fas fa-tag"></i> Digital Product</span>
                    </div>
                    <div class="cart-item-price" id="price-<?= $id ?>">
                        <?= htmlspecialchars($item['price']) ?>
                    </div>
                    <div class="cart-item-quantity">
                        <span class="quantity-badge">
                            <i class="fas fa-check-circle"></i> 1 item
                        </span>
                    </div>
                    <div class="cart-item-total" id="total-<?= $id ?>">
                        <?= htmlspecialchars($item['price']) ?>
                    </div>
                    <button class="cart-item-remove" onclick="removeFromCart(<?= $id ?>)">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <button class="btn-clear" onclick="clearCart()">
                    <i class="fas fa-trash-alt"></i> Kosongkan Keranjang
                </button>
                <div class="cart-total">
                    Total: <span id="cartTotal">Rp <?= number_format($total_price, 0, ',', '.') ?></span>
                </div>
                <a href="../checkout.php" class="btn-checkout" style="background: linear-gradient(135deg, #e8b830, #d4a020); color: #1f2462;">
                    <i class="fas fa-shopping-cart"></i> Checkout Sekarang
                </a>
                <a href="https://wa.me/<?= $wa_default ?>?text=<?= rawurlencode($wa_message) ?>" class="btn-checkout" target="_blank">
                    <i class="fab fa-whatsapp"></i> Order via WhatsApp
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function showToast(message, isError = false) {
            const existingToast = document.querySelector('.toast-notification');
            if (existingToast) existingToast.remove();
            
            const toast = document.createElement('div');
            toast.className = 'toast-notification';
            toast.style.background = isError ? '#ef4444' : '#1f2462';
            toast.innerHTML = '<i class="fas ' + (isError ? 'fa-exclamation-circle' : 'fa-shopping-cart') + '" style="color: #e8b830;"></i> ' + message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                if (toast && toast.remove) toast.remove();
            }, 2500);
        }
        
        function updateCartDisplay() {
            // PERBAIKAN 3: Ubah URL AJAX ke file ajax/cart_ajax.php yang baru
            fetch('../ajax/cart_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'action=get'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (window.opener && window.opener.updateCartBadge) {
                        window.opener.updateCartBadge();
                    }
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        function removeFromCart(productId) {
            if (confirm('Hapus produk ini dari keranjang?')) {
                // PERBAIKAN 3: Ubah URL AJAX ke file ajax/cart_ajax.php yang baru
                fetch('../ajax/cart_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `action=remove&product_id=${productId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Produk dihapus dari keranjang');
                        updateCartDisplay();
                    } else {
                        showToast('Gagal menghapus produk', true);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan', true);
                });
            }
        }
        
        function clearCart() {
            if (confirm('Kosongkan semua item di keranjang?')) {
                // PERBAIKAN 3: Ubah URL AJAX ke file ajax/cart_ajax.php yang baru
                fetch('../ajax/cart_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'action=clear'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Keranjang berhasil dikosongkan');
                        updateCartDisplay();
                    } else {
                        showToast('Gagal mengosongkan keranjang', true);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan', true);
                });
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            if (window.opener && window.opener.updateCartBadge) {
                window.opener.updateCartBadge();
            }
        });
    </script>
</body>
</html>
<?php
// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Checkout - DKV ROOM';

// Path ke config (di root)
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/cart_functions.php';

// Mulai session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil semua item dari cart
$cart_items = getCartItems();
$total_price = getCartTotal();

// Proses Checkout
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($cart_items)) {
    // Ambil data form
    $client_name = trim($_POST['client_name'] ?? '');
    $client_email = trim($_POST['client_email'] ?? '');
    $client_phone = trim($_POST['client_phone'] ?? '');
    $client_address = trim($_POST['client_address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'bank_transfer';
    
    // Validasi
    $errors = [];
    if (empty($client_name)) $errors[] = 'Nama lengkap wajib diisi!';
    if (empty($client_phone)) $errors[] = 'Nomor telepon wajib diisi!';
    
    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    } else {
        // Generate invoice number
        $invoice_number = 'DKV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
        $unique_link = generate_invoice_link(12);
        
        // Buat deskripsi produk dari cart
        $product_names = [];
        $total_amount = 0;
        $product_details = [];
        $product_ids = [];
        
        foreach ($cart_items as $id => $item) {
            $product_names[] = $item['name'];
            $price_clean = preg_replace('/[^0-9]/', '', $item['price']);
            $item_price = floatval($price_clean);
            $total_amount += $item_price;
            $product_ids[] = $id;
            
            $product_details[] = "• " . $item['name'] . " - Rp " . number_format($item_price, 0, ',', '.');
        }
        
        $service_name = implode(', ', $product_names);
        $service_description = "Pembelian produk: " . implode(', ', $product_names);
        $amount = $total_amount;
        $tax = 0;
        $discount = 0;
        $total = $amount;
        
        // Ambil product_id pertama (jika ada)
        $product_id = !empty($product_ids) ? $product_ids[0] : 0;
        
        // Cek unique link
        $link_exists = true;
        while ($link_exists) {
            $stmt_cek = mysqli_prepare($conn, "SELECT id FROM invoices WHERE unique_link = ?");
            if ($stmt_cek) {
                mysqli_stmt_bind_param($stmt_cek, "s", $unique_link);
                mysqli_stmt_execute($stmt_cek);
                $result_cek = mysqli_stmt_get_result($stmt_cek);
                if (mysqli_num_rows($result_cek) == 0) {
                    $link_exists = false;
                } else {
                    $unique_link = generate_invoice_link(12);
                }
                mysqli_stmt_close($stmt_cek);
            } else {
                break;
            }
        }
        
        // Insert invoice
        $stmt_insert = mysqli_prepare($conn, "INSERT INTO invoices (
            invoice_number, client_name, client_email, client_phone, client_address,
            service_name, service_description, guide_content, schedule,
            amount, tax, discount, total,
            status, issue_date, due_date, notes, unique_link, product_id, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        if ($stmt_insert) {
            mysqli_stmt_bind_param($stmt_insert, 
                "sssssssssdddssssssi",
                $invoice_number, 
                $client_name, 
                $client_email, 
                $client_phone, 
                $client_address,
                $service_name, 
                $service_description,
                '',  // guide_content (kosong)
                '',  // schedule (kosong)
                $amount,
                $tax,
                $discount,
                $total,
                'sent',
                date('Y-m-d'),
                date('Y-m-d', strtotime('+30 days')),
                $notes, 
                $unique_link,
                $product_id
            );
            
            if (mysqli_stmt_execute($stmt_insert)) {
                $invoice_id = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt_insert);
                
                // Ambil data invoice yang baru dibuat
                $stmt_invoice = mysqli_prepare($conn, "SELECT * FROM invoices WHERE id = ?");
                mysqli_stmt_bind_param($stmt_invoice, "i", $invoice_id);
                mysqli_stmt_execute($stmt_invoice);
                $result_invoice = mysqli_stmt_get_result($stmt_invoice);
                $invoice_data = mysqli_fetch_assoc($result_invoice);
                mysqli_stmt_close($stmt_invoice);
                
                // Kosongkan cart setelah checkout
                clearCart();
                
                // Buat pesan WhatsApp
                $wa_message = generate_wa_message($invoice_data, $product_details, $payment_method);
                $phone = $client_phone;
                $phone = preg_replace('/[^0-9]/', '', $phone);
                if (substr($phone, 0, 1) === '0') {
                    $phone = '62' . substr($phone, 1);
                } elseif (substr($phone, 0, 2) !== '62') {
                    $phone = '62' . $phone;
                }
                
                // Buat URL WhatsApp
                $wa_url = "https://api.whatsapp.com/send?phone=" . $phone . "&text=" . urlencode($wa_message);
                
                // Redirect langsung ke WhatsApp
                header("Location: " . $wa_url);
                exit;
                
            } else {
                $error = 'Gagal menyimpan invoice: ' . mysqli_error($conn);
                mysqli_stmt_close($stmt_insert);
            }
        } else {
            $error = 'Gagal menyiapkan query: ' . mysqli_error($conn);
        }
    }
}

// Fungsi untuk generate invoice link
function generate_invoice_link($length = 12) {
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
}

// Fungsi generate pesan WA dengan template lengkap
function generate_wa_message($invoice, $product_details, $payment_method) {
    $date = date('d/m/Y', strtotime($invoice['issue_date']));
    $due_date = date('d/m/Y', strtotime($invoice['due_date']));
    
    $payment_label = $payment_method == 'bank_transfer' ? 'Transfer Bank (Mandiri)' : 'QRIS (Scan Barcode)';
    
    $message = "📋 *INVOICE PEMESANAN*\n";
    $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    $message .= "👤 *Data Pemesan*\n";
    $message .= "──────────────────\n";
    $message .= "Nama      : " . htmlspecialchars($invoice['client_name']) . "\n";
    $message .= "Telepon   : " . htmlspecialchars($invoice['client_phone']) . "\n";
    if (!empty($invoice['client_email'])) {
        $message .= "Email     : " . htmlspecialchars($invoice['client_email']) . "\n";
    }
    if (!empty($invoice['client_address'])) {
        $message .= "Alamat    : " . htmlspecialchars($invoice['client_address']) . "\n";
    }
    $message .= "\n";
    
    $message .= "📄 *Detail Invoice*\n";
    $message .= "──────────────────\n";
    $message .= "No. Invoice : " . htmlspecialchars($invoice['invoice_number']) . "\n";
    $message .= "Tanggal     : " . $date . "\n";
    $message .= "Jatuh Tempo : " . $due_date . "\n";
    $message .= "\n";
    
    $message .= "🛒 *Produk yang Dipesan*\n";
    $message .= "──────────────────\n";
    foreach ($product_details as $detail) {
        $message .= $detail . "\n";
    }
    $message .= "\n";
    
    $message .= "💰 *Rincian Pembayaran*\n";
    $message .= "──────────────────\n";
    if ($invoice['amount'] > 0) {
        $message .= "Subtotal    : Rp " . number_format($invoice['amount'], 0, ',', '.') . "\n";
    }
    if ($invoice['tax'] > 0) {
        $message .= "Pajak       : Rp " . number_format($invoice['tax'], 0, ',', '.') . "\n";
    }
    if ($invoice['discount'] > 0) {
        $message .= "Diskon      : -Rp " . number_format($invoice['discount'], 0, ',', '.') . "\n";
    }
    $message .= "──────────────────\n";
    $message .= "💎 *TOTAL*    : Rp " . number_format($invoice['total'], 0, ',', '.') . "\n";
    $message .= "\n";
    
    $message .= "💳 *Metode Pembayaran*\n";
    $message .= "──────────────────\n";
    $message .= $payment_label . "\n";
    $message .= "\n";
    
    $message .= "🏦 *Rekening Pembayaran*\n";
    $message .= "──────────────────\n";
    $message .= "Bank        : Mandiri\n";
    $message .= "No. Rekening: 123-45-6789012\n";
    $message .= "Atas Nama   : DKV ROOM\n";
    $message .= "\n";
    
    if (!empty($invoice['notes'])) {
        $message .= "📝 *Catatan*\n";
        $message .= "──────────────────\n";
        $message .= htmlspecialchars($invoice['notes']) . "\n";
        $message .= "\n";
    }
    
    $message .= "📌 *Cara Konfirmasi*\n";
    $message .= "──────────────────\n";
    $message .= "1. Lakukan pembayaran sesuai total\n";
    $message .= "2. Kirim bukti transfer ke WhatsApp ini\n";
    $message .= "3. Invoice akan diupdate statusnya\n";
    $message .= "\n";
    
    $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $message .= "Terima kasih telah berbelanja di *DKV ROOM* 🙏\n";
    $message .= "DKV ROOM - Desain Komunikasi Visual";
    
    return $message;
}

// Include header
include __DIR__ . '/includes/header_public.php';
?>

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Inter', sans-serif;
        background: #f5f7fa;
        padding: 2rem;
        min-height: 100vh;
    }
    .container {
        max-width: 1100px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        animation: fadeIn 0.4s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    }
    .card-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1f2462;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .card-title i {
        color: #e8b830;
    }
    
    .order-item {
        display: flex;
        justify-content: space-between;
        padding: 0.8rem 0;
        border-bottom: 1px solid #eef2f0;
    }
    .order-item:last-child {
        border-bottom: none;
    }
    .order-item-name {
        font-weight: 500;
        color: #1a2a3a;
    }
    .order-item-price {
        font-weight: 600;
        color: #1f2462;
    }
    .order-total {
        display: flex;
        justify-content: space-between;
        padding-top: 1rem;
        margin-top: 1rem;
        border-top: 2px solid #e8b830;
        font-size: 1.2rem;
        font-weight: 700;
        color: #1f2462;
    }
    .order-total span:last-child {
        color: #e8b830;
        font-size: 1.4rem;
    }
    
    .form-group {
        margin-bottom: 1.2rem;
    }
    .form-group label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: #1a2a3a;
        margin-bottom: 0.3rem;
    }
    .form-group label .required {
        color: #ef4444;
    }
    .form-control {
        width: 100%;
        padding: 0.7rem 1rem;
        border: 1.5px solid #eef2f0;
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        font-family: 'Inter', sans-serif;
    }
    .form-control:focus {
        outline: none;
        border-color: #e8b830;
        box-shadow: 0 0 0 3px rgba(232, 184, 48, 0.15);
    }
    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }
    
    .payment-methods {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        margin-top: 0.5rem;
    }
    .payment-method {
        padding: 0.8rem;
        border: 2px solid #eef2f0;
        border-radius: 10px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }
    .payment-method:hover {
        border-color: #d1d5db;
    }
    .payment-method input[type="radio"] {
        display: none;
    }
    .payment-method.active {
        border-color: #e8b830;
        background: rgba(232, 184, 48, 0.05);
    }
    .payment-method i {
        font-size: 1.5rem;
        display: block;
        margin-bottom: 0.3rem;
    }
    .payment-method span {
        font-size: 0.8rem;
        font-weight: 500;
        color: #1a2a3a;
    }
    
    .btn-checkout {
        width: 100%;
        padding: 0.9rem;
        background: linear-gradient(135deg, #e8b830, #d4a020);
        color: #1f2462;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
    }
    .btn-checkout:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 24px rgba(232, 184, 48, 0.35);
    }
    .btn-checkout:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }
    
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #6c7a8a;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s;
        margin-bottom: 1rem;
    }
    .btn-back:hover {
        color: #1f2462;
    }
    
    .alert {
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .alert-danger {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #dc2626;
    }
    
    /* Debug info */
    .debug-info {
        background: #1e293b;
        color: #a5f3fc;
        padding: 0.8rem 1rem;
        border-radius: 8px;
        font-family: monospace;
        font-size: 0.8rem;
        margin-bottom: 1rem;
        overflow: auto;
    }
    
    @media (max-width: 768px) {
        body {
            padding: 1rem;
        }
        .container {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        .card {
            padding: 1.5rem;
        }
    }
    @media (max-width: 480px) {
        .payment-methods {
            grid-template-columns: 1fr;
        }
        .card-title {
            font-size: 1rem;
        }
    }
</style>

<div class="container">
    <!-- Left: Checkout Form -->
    <div>
        <a href="katalog/cart/index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Keranjang</a>
        
        <div class="card">
            <div class="card-title">
                <i class="fas fa-user"></i> Data Pemesan
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <!-- Debug Info -->
            <div class="debug-info">
                <strong>📊 Info:</strong> <?= count($cart_items) ?> item | Total: Rp <?= number_format($total_price, 0, ',', '.') ?>
            </div>
            
            <form method="POST" action="checkout.php" id="checkoutForm">
                <div class="form-group">
                    <label>Nama Lengkap <span class="required">*</span></label>
                    <input type="text" name="client_name" class="form-control" 
                           placeholder="Masukkan nama lengkap" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="client_email" class="form-control" 
                           placeholder="email@domain.com">
                </div>
                
                <div class="form-group">
                    <label>Nomor Telepon <span class="required">*</span></label>
                    <input type="tel" name="client_phone" class="form-control" 
                           placeholder="081234567890" required>
                </div>
                
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="client_address" class="form-control" 
                              placeholder="Masukkan alamat lengkap"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Catatan (Opsional)</label>
                    <textarea name="notes" class="form-control" 
                              placeholder="Catatan tambahan untuk pesanan"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <div class="payment-methods">
                        <label class="payment-method active">
                            <input type="radio" name="payment_method" value="bank_transfer" checked>
                            <i class="fas fa-university"></i>
                            <span>Transfer Bank</span>
                        </label>
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="qris">
                            <i class="fas fa-qrcode"></i>
                            <span>QRIS</span>
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn-checkout" id="checkoutBtn" <?= empty($cart_items) ? 'disabled' : '' ?>>
                    <i class="fas fa-shopping-cart"></i> 
                    <?= empty($cart_items) ? 'Keranjang Kosong' : 'Checkout Sekarang' ?>
                </button>
            </form>
        </div>
    </div>
    
    <!-- Right: Order Summary -->
    <div>
        <div class="card">
            <div class="card-title">
                <i class="fas fa-receipt"></i> Ringkasan Pesanan
            </div>
            
            <div class="order-items">
                <?php if (empty($cart_items)): ?>
                    <p style="color: #6c7a8a; text-align: center; padding: 2rem 0;">
                        <i class="fas fa-shopping-cart" style="font-size: 2rem; display: block; margin-bottom: 0.5rem; opacity: 0.3;"></i>
                        Keranjang belanja kosong
                    </p>
                <?php else: ?>
                    <?php foreach ($cart_items as $id => $item): ?>
                        <div class="order-item">
                            <span class="order-item-name"><?= htmlspecialchars($item['name']) ?></span>
                            <span class="order-item-price"><?= htmlspecialchars($item['price']) ?></span>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="order-total">
                        <span>Total</span>
                        <span>Rp <?= number_format($total_price, 0, ',', '.') ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Payment method selection
    document.querySelectorAll('.payment-method').forEach(method => {
        method.addEventListener('click', function() {
            document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('active'));
            this.classList.add('active');
            this.querySelector('input[type="radio"]').checked = true;
        });
    });
    
    // Form validation
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        const name = this.querySelector('input[name="client_name"]');
        const phone = this.querySelector('input[name="client_phone"]');
        const btn = document.getElementById('checkoutBtn');
        
        if (!name.value.trim()) {
            e.preventDefault();
            alert('⚠️ Nama lengkap wajib diisi!');
            name.focus();
            return false;
        }
        
        if (!phone.value.trim()) {
            e.preventDefault();
            alert('⚠️ Nomor telepon wajib diisi!');
            phone.focus();
            return false;
        }
        
        // Validasi nomor telepon minimal 10 digit
        const phoneClean = phone.value.replace(/[^0-9]/g, '');
        if (phoneClean.length < 10) {
            e.preventDefault();
            alert('⚠️ Nomor telepon minimal 10 digit!');
            phone.focus();
            return false;
        }
        
        // Ubah tombol menjadi loading
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        btn.disabled = true;
        
        return true;
    });
</script>

<?php
include __DIR__ . '/includes/footer_public.php';
?>
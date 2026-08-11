<?php
$page_title = 'Buat Invoice Baru';
require_once '../../config.php';

/** @var mysqli $conn */
global $conn;

require_login();

// ============================================================
// FUNGSI HELPER
// ============================================================

function generate_invoice_number() {
    return 'DKV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
}

function generate_invoice_link($length = 12) {
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
}

// ============================================================
// AMBIL PRODUK UNTUK DROPDOWN
// ============================================================
$products = [];
$stmt_prod = mysqli_prepare($conn, "SELECT id, name, price, description FROM products ORDER BY name");
if ($stmt_prod) {
    mysqli_stmt_execute($stmt_prod);
    $prod_result = mysqli_stmt_get_result($stmt_prod);
    while ($row = mysqli_fetch_assoc($prod_result)) {
        $products[] = $row;
    }
    mysqli_stmt_close($stmt_prod);
}

// ============================================================
// PROSES SIMPAN
// ============================================================
$error = '';
$success = '';
$form_data = [];
$invoice_id = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $client_name = trim($_POST['client_name'] ?? '');
    $client_email = trim($_POST['client_email'] ?? '');
    $client_phone = trim($_POST['client_phone'] ?? '');
    $client_address = trim($_POST['client_address'] ?? '');
    $service_name = trim($_POST['service_name'] ?? '');
    $service_description = trim($_POST['service_description'] ?? '');
    $guide_content = trim($_POST['guide_content'] ?? '');
    $schedule = trim($_POST['schedule'] ?? '');
    
    $price_raw = $_POST['price'] ?? '0';
    $price_clean = preg_replace('/[^0-9]/', '', $price_raw);
    $amount = floatval($price_clean);
    
    $tax = isset($_POST['tax']) ? floatval($_POST['tax']) : 0;
    $discount = isset($_POST['discount']) ? floatval($_POST['discount']) : 0;
    $issue_date = $_POST['issue_date'] ?? date('Y-m-d');
    $due_date = $_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days'));
    $notes = trim($_POST['notes'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    
    // Action: save_draft atau checkout
    $action = $_POST['action'] ?? 'save_draft';
    
    $form_data = [
        'product_id' => $product_id,
        'client_name' => $client_name,
        'client_email' => $client_email,
        'client_phone' => $client_phone,
        'client_address' => $client_address,
        'service_name' => $service_name,
        'service_description' => $service_description,
        'guide_content' => $guide_content,
        'schedule' => $schedule,
        'amount' => $amount,
        'tax' => $tax,
        'discount' => $discount,
        'issue_date' => $issue_date,
        'due_date' => $due_date,
        'notes' => $notes,
        'status' => $status
    ];
    
    // Validasi
    $errors = [];
    if (empty($client_name)) $errors[] = 'Nama klien wajib diisi!';
    if (empty($service_name)) $errors[] = 'Nama layanan wajib diisi!';
    if ($amount <= 0) $errors[] = 'Jumlah wajib diisi dan harus lebih dari 0!';
    
    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    } else {
        $total = $amount + $tax - $discount;
        if ($total < 0) $total = 0;
        
        $invoice_number = generate_invoice_number();
        $unique_link = generate_invoice_link(12);
        
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
                    status, issue_date, due_date, notes, unique_link, product_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt_insert) {
            mysqli_stmt_bind_param($stmt_insert, 
                "sssssssssdddssssssi", 
                $invoice_number, $client_name, $client_email, $client_phone, $client_address,
                $service_name, $service_description, $guide_content, $schedule,
                $amount, $tax, $discount, $total,
                $status, $issue_date, $due_date, $notes, $unique_link, $product_id
            );
            
            if (mysqli_stmt_execute($stmt_insert)) {
                $invoice_id = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt_insert);
                
                // Jika checkout, redirect ke checkout.php
                if ($action === 'checkout') {
                    header("Location: checkout.php?id=" . $invoice_id);
                    exit;
                }
                
                // Jika save draft
                $success = 'Invoice berhasil dibuat!';
                $form_data = [];
                header("refresh:2;url=index.php");
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

include '../includes/header.php';
?>

<!-- ============================================================ -->
<!-- CSS INLINE -->
<!-- ============================================================ -->
<style>
.admin-content {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

/* Header Actions */
.header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}

.header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.page-title-admin {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2462;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.btn-secondary-dark {
    background: rgba(31, 36, 98, 0.08);
    color: #1f2462;
    padding: 0.65rem 1.5rem;
    border: 1px solid rgba(31, 36, 98, 0.15);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
}

.btn-secondary-dark:hover {
    background: rgba(31, 36, 98, 0.15);
}

/* Form Card */
.form-card {
    background: #fff;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    border-top: 4px solid #e8b830;
}

.form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f0f0f0;
}

.form-header h3 {
    color: #1f2462;
    margin: 0;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-badge {
    background: #e8b830;
    color: #1f2462;
    padding: 0.2rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* Form Grids */
.form-grid-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.form-grid-2col-small {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-grid-4col {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr;
    gap: 1rem;
}

.form-section {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid #e9ecef;
}

.form-section.full-width {
    grid-column: 1 / -1;
}

.section-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2462;
    margin-bottom: 1.25rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid rgba(232, 184, 48, 0.3);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Form Groups */
.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    font-size: 0.85rem;
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.3rem;
}

.form-group label .required {
    color: #dc2626;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.6rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: #fff;
    color: #1f2937;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #e8b830;
    box-shadow: 0 0 0 3px rgba(232, 184, 48, 0.2);
}

/* Action Buttons */
.form-actions {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn-gold {
    background: linear-gradient(135deg, #e8b830, #d4a020);
    color: #1f2462;
    padding: 0.65rem 2rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}

.btn-gold:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(232, 184, 48, 0.4);
}

.btn-checkout {
    background: linear-gradient(135deg, #25D366, #128C7E);
    color: #fff;
    padding: 0.65rem 2rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-checkout:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4);
}

/* Alert */
.alert-success {
    background: rgba(34, 197, 94, 0.1);
    color: #16a34a;
    padding: 1rem 1.25rem;
    border-radius: 8px;
    border-left: 4px solid #22c55e;
    margin-bottom: 1.5rem;
}

.alert-error {
    background: rgba(220, 38, 38, 0.1);
    color: #dc2626;
    padding: 1rem 1.25rem;
    border-radius: 8px;
    border-left: 4px solid #dc2626;
    margin-bottom: 1.5rem;
}

/* Responsive */
@media (max-width: 992px) {
    .form-grid-2col {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    .form-grid-4col {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 576px) {
    .form-grid-4col {
        grid-template-columns: 1fr;
    }
    .form-grid-2col-small {
        grid-template-columns: 1fr;
    }
    .form-actions {
        flex-direction: column;
    }
    .form-actions .btn-gold,
    .form-actions .btn-checkout,
    .form-actions .btn-secondary-dark {
        width: 100%;
        justify-content: center;
    }
}
</style>

<!-- ============================================================ -->
<!-- CONTENT -->
<!-- ============================================================ -->

<div class="admin-content">

    <div class="header-actions">
        <div class="header-left">
            <h2 class="page-title-admin">
                <i class="fas fa-file-invoice" style="color: #e8b830;"></i> 
                Buat Invoice Baru
            </h2>
        </div>
        <div class="header-right">
            <a href="index.php" class="btn-secondary-dark">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
    <?php endif; ?>

    <div class="form-card">
        <div class="form-header">
            <h3><i class="fas fa-plus-circle" style="color: #e8b830;"></i> Data Invoice</h3>
            <span class="form-badge">📄 Baru</span>
        </div>
        
        <form method="POST">
            <!-- Grid 2 Kolom -->
            <div class="form-grid-2col">
                
                <!-- Kolom Kiri: Data Klien -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-user" style="color: #e8b830;"></i> Data Klien
                    </div>
                    <div class="form-group">
                        <label>Nama Klien <span class="required">*</span></label>
                        <input type="text" name="client_name" required 
                               value="<?= htmlspecialchars($form_data['client_name'] ?? '') ?>" 
                               placeholder="Masukkan nama klien">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="client_email" 
                               value="<?= htmlspecialchars($form_data['client_email'] ?? '') ?>" 
                               placeholder="email@domain.com">
                    </div>
                    <div class="form-group">
                        <label>Telepon <span class="required">*</span></label>
                        <input type="text" name="client_phone" required
                               value="<?= htmlspecialchars($form_data['client_phone'] ?? '') ?>" 
                               placeholder="081234567890">
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="client_address" rows="3" 
                                  placeholder="Masukkan alamat lengkap"><?= htmlspecialchars($form_data['client_address'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Kolom Kanan: Data Layanan -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-briefcase" style="color: #e8b830;"></i> Data Layanan & Jadwal
                    </div>
                    <div class="form-group">
                        <label>Pilih Produk</label>
                        <select name="product_id" id="productSelect" onchange="fillProductData(this)">
                            <option value="0">-- Pilih Produk (Opsional) --</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nama Layanan <span class="required">*</span></label>
                        <input type="text" name="service_name" id="serviceName" required 
                               value="<?= htmlspecialchars($form_data['service_name'] ?? '') ?>" 
                               placeholder="Masukkan nama layanan">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi Layanan</label>
                        <textarea name="service_description" id="serviceDesc" rows="3" 
                                  placeholder="Deskripsikan layanan"><?= htmlspecialchars($form_data['service_description'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Jadwal Program</label>
                        <textarea name="schedule" id="scheduleField" rows="2" 
                                  placeholder="Masukkan jadwal program"><?= htmlspecialchars($form_data['schedule'] ?? '') ?></textarea>
                    </div>
                    
                    <!-- Harga, Pajak, Diskon -->
                    <div class="form-grid-2col-small">
                        <div class="form-group">
                            <label>Jumlah <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="input-icon">Rp</span>
                                <input type="text" name="price" id="servicePrice" required 
                                       value="<?= htmlspecialchars($form_data['amount'] ?? '') ?>" 
                                       placeholder="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Pajak</label>
                            <div class="input-with-icon">
                                <span class="input-icon">Rp</span>
                                <input type="number" name="tax" step="1000" 
                                       value="<?= $form_data['tax'] ?? 0 ?>" placeholder="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Diskon</label>
                            <div class="input-with-icon">
                                <span class="input-icon">Rp</span>
                                <input type="number" name="discount" step="1000" 
                                       value="<?= $form_data['discount'] ?? 0 ?>" placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panduan Penggunaan -->
            <div class="form-section full-width" style="margin-top: 1.5rem;">
                <div class="section-title">
                    <i class="fas fa-book" style="color: #e8b830;"></i> Panduan Penggunaan
                </div>
                <div class="form-group">
                    <textarea name="guide_content" id="guideContent" rows="8" 
                              placeholder="Masukkan panduan penggunaan untuk klien..."><?= htmlspecialchars($form_data['guide_content'] ?? '') ?></textarea>
                </div>
                <div class="template-buttons" style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:0.5rem;">
                    <button type="button" class="btn-template" onclick="insertTemplate1()" style="padding:0.4rem 1rem;background:rgba(232,184,48,0.1);color:#b8941f;border:1px solid rgba(232,184,48,0.25);border-radius:20px;cursor:pointer;font-size:0.8rem;">
                        <i class="fas fa-file-alt"></i> Template 1
                    </button>
                    <button type="button" class="btn-template" onclick="insertTemplate2()" style="padding:0.4rem 1rem;background:rgba(232,184,48,0.1);color:#b8941f;border:1px solid rgba(232,184,48,0.25);border-radius:20px;cursor:pointer;font-size:0.8rem;">
                        <i class="fas fa-file-alt"></i> Template 2
                    </button>
                    <button type="button" class="btn-template" onclick="insertTemplate3()" style="padding:0.4rem 1rem;background:rgba(232,184,48,0.1);color:#b8941f;border:1px solid rgba(232,184,48,0.25);border-radius:20px;cursor:pointer;font-size:0.8rem;">
                        <i class="fas fa-file-alt"></i> Template 3
                    </button>
                    <button type="button" class="btn-template-danger" onclick="clearGuide()" style="padding:0.4rem 1rem;background:rgba(220,38,38,0.1);color:#dc2626;border:1px solid rgba(220,38,38,0.25);border-radius:20px;cursor:pointer;font-size:0.8rem;">
                        <i class="fas fa-eraser"></i> Kosongkan
                    </button>
                </div>
            </div>

            <!-- Tanggal & Status -->
            <div class="form-grid-4col" style="margin-top: 1.5rem;">
                <div class="form-group">
                    <label>Tanggal Terbit</label>
                    <input type="date" name="issue_date" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Jatuh Tempo</label>
                    <input type="date" name="due_date" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="draft">📝 Draft</option>
                        <option value="sent">📤 Sent</option>
                        <option value="paid">✅ Paid</option>
                        <option value="overdue">⚠️ Overdue</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Catatan</label>
                    <input type="text" name="notes" 
                           value="<?= htmlspecialchars($form_data['notes'] ?? '') ?>" 
                           placeholder="Catatan internal">
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" name="action" value="save_draft" class="btn-gold">
                    <i class="fas fa-save"></i> Simpan Draft
                </button>
                <button type="submit" name="action" value="checkout" class="btn-checkout">
                    <i class="fab fa-whatsapp"></i> Checkout & Kirim WA
                </button>
                <a href="index.php" class="btn-secondary-dark">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function fillProductData(select) {
    const products = <?= json_encode($products) ?>;
    const id = parseInt(select.value);
    
    if (id === 0) {
        document.getElementById('serviceName').value = '';
        document.getElementById('serviceDesc').value = '';
        document.getElementById('servicePrice').value = '';
        document.getElementById('scheduleField').value = '';
        return;
    }
    
    const product = products.find(p => p.id === id);
    if (product) {
        document.getElementById('serviceName').value = product.name;
        document.getElementById('servicePrice').value = product.price.replace(/[^0-9]/g, '');
        document.getElementById('serviceDesc').value = product.description ?? '';
        
        const now = new Date();
        const start = new Date(now); 
        start.setDate(now.getDate() + 14);
        const end = new Date(start); 
        end.setDate(start.getDate() + 3);
        const opt = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        
        document.getElementById('scheduleField').value = 
            'Pelaksanaan Program: ' + start.toLocaleDateString('id-ID', opt) + ' - ' + end.toLocaleDateString('id-ID', opt) + '\n' +
            'Waktu: 08.00 - 16.00 WIB\n' +
            'Lokasi: LSP COACHPRO INDONESIA (Online / Offline)';
    }
}

function insertTemplate1() {
    document.getElementById('guideContent').value = 
'=== PANDUAN PENGGUNAAN ===\n\n' +
'1. Persiapan Awal\n' +
'   - Pastikan semua dokumen sudah lengkap\n' +
'   - Siapkan alat dan bahan yang diperlukan\n' +
'   - Cek koneksi internet\n\n' +
'2. Langkah-langkah\n' +
'   - Login ke sistem\n' +
'   - Pilih menu yang tersedia\n' +
'   - Ikuti instruksi yang muncul\n\n' +
'3. Troubleshooting\n' +
'   - Jika error, refresh halaman\n' +
'   - Hubungi admin jika masalah berlanjut';
}

function insertTemplate2() {
    document.getElementById('guideContent').value = 
'=== PETUNJUK TEKNIS ===\n\n' +
'A. Instalasi\n' +
'1. Download file installer\n' +
'2. Jalankan installer\n' +
'3. Ikuti wizard instalasi\n\n' +
'B. Konfigurasi\n' +
'1. Setting database\n' +
'2. Setting koneksi\n' +
'3. Setting parameter\n\n' +
'C. Penggunaan\n' +
'1. Buka aplikasi\n' +
'2. Login dengan kredensial\n' +
'3. Mulai gunakan fitur';
}

function insertTemplate3() {
    document.getElementById('guideContent').value = 
'=== MANUAL PENGGUNAAN ===\n\n' +
'Pendahuluan\n' +
'-----------\n' +
'Sistem ini dirancang untuk memudahkan proses bisnis Anda.\n\n' +
'Fitur Utama\n' +
'-----------\n' +
'1. Manajemen Data\n' +
'2. Laporan dan Statistik\n' +
'3. Notifikasi\n\n' +
'Cara Penggunaan\n' +
'--------------\n' +
'1. Buka dashboard\n' +
'2. Pilih menu yang diinginkan\n' +
'3. Input data dengan benar\n' +
'4. Simpan perubahan\n\n' +
'Tips & Trik\n' +
'-----------\n' +
'- Gunakan shortcut keyboard untuk akses cepat\n' +
'- Backup data secara berkala\n' +
'- Update sistem secara rutin';
}

function clearGuide() {
    if (confirm('Kosongkan konten panduan?')) {
        document.getElementById('guideContent').value = '';
    }
}
</script>

<?php include '../includes/footer.php'; ?>
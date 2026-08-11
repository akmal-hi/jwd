<?php
$page_title = 'Edit Invoice';
require_once '../../config.php';

/** @var mysqli $conn */
global $conn;

require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    $_SESSION['error'] = 'Invoice tidak ditemukan!';
    header('Location: index.php');
    exit;
}

// Ambil data invoice
$stmt = mysqli_prepare($conn, "SELECT * FROM invoices WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$invoice = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$invoice) {
    $_SESSION['error'] = 'Invoice tidak ditemukan!';
    header('Location: index.php');
    exit;
}

// Ambil produk
$products = [];
$stmt_prod = mysqli_prepare($conn, "SELECT id, name, price, description FROM products ORDER BY name");
mysqli_stmt_execute($stmt_prod);
$prod_result = mysqli_stmt_get_result($stmt_prod);
while ($row = mysqli_fetch_assoc($prod_result)) {
    $products[] = $row;
}
mysqli_stmt_close($stmt_prod);

// Proses update
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $invoice_number = trim($_POST['invoice_number'] ?? '');
    $client_name = trim($_POST['client_name'] ?? '');
    $client_email = trim($_POST['client_email'] ?? '');
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $service_name = trim($_POST['service_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $price = isset($_POST['price']) ? (float)str_replace(',', '.', $_POST['price']) : 0;
    $total = $quantity * $price;
    $status = $_POST['status'] ?? 'draft';
    $due_date = $_POST['due_date'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    // Validasi
    if (empty($invoice_number)) {
        $error = 'Nomor invoice wajib diisi!';
    } elseif (empty($client_name)) {
        $error = 'Nama klien wajib diisi!';
    } elseif (empty($client_email)) {
        $error = 'Email klien wajib diisi!';
    } elseif (!filter_var($client_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } elseif ($product_id <= 0 && empty($service_name)) {
        $error = 'Pilih produk atau isi nama layanan!';
    } elseif ($quantity <= 0) {
        $error = 'Quantity harus lebih dari 0!';
    } elseif ($price <= 0) {
        $error = 'Harga harus lebih dari 0!';
    } elseif (empty($due_date)) {
        $error = 'Tanggal jatuh tempo wajib diisi!';
    } else {
        // Update data
        $stmt_update = mysqli_prepare($conn, 
            "UPDATE invoices SET 
                invoice_number = ?,
                client_name = ?,
                client_email = ?,
                product_id = ?,
                service_name = ?,
                description = ?,
                quantity = ?,
                price = ?,
                total = ?,
                status = ?,
                due_date = ?,
                notes = ?,
                updated_at = NOW()
            WHERE id = ?"
        );
        
        mysqli_stmt_bind_param(
            $stmt_update,
            "sssisiddsssi",
            $invoice_number,
            $client_name,
            $client_email,
            $product_id,
            $service_name,
            $description,
            $quantity,
            $price,
            $total,
            $status,
            $due_date,
            $notes,
            $id
        );
        
        if (mysqli_stmt_execute($stmt_update)) {
            $success = 'Invoice berhasil diperbarui!';
            
            // Refresh data invoice
            $stmt_refresh = mysqli_prepare($conn, "SELECT * FROM invoices WHERE id = ?");
            mysqli_stmt_bind_param($stmt_refresh, "i", $id);
            mysqli_stmt_execute($stmt_refresh);
            $result_refresh = mysqli_stmt_get_result($stmt_refresh);
            $invoice = mysqli_fetch_assoc($result_refresh);
            mysqli_stmt_close($stmt_refresh);
        } else {
            $error = 'Gagal memperbarui invoice: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt_update);
    }
}

include '../includes/header.php';
?>

<style>
/* ============================================
   EDIT INVOICE PAGE STYLES
   ============================================ */

.admin-content.edit-invoice {
    padding: 20px 24px 40px;
    max-width: 900px;
    margin: 0 auto;
    width: 100%;
    box-sizing: border-box;
}

.header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 28px;
}

.header-actions .page-subtitle {
    font-size: 24px;
    font-weight: 600;
    margin: 0;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 10px;
}

.header-actions .page-subtitle i {
    font-size: 26px;
}

.btn-back {
    background: rgba(255,255,255,0.06);
    color: #8ba0ae;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 500;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: 1px solid rgba(255,255,255,0.06);
}

.btn-back:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
    border-color: rgba(255,255,255,0.12);
}

/* --- Form Card --- */
.form-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 32px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #e8edf0;
    margin-bottom: 6px;
}

.form-group label .required {
    color: #ef4444;
    margin-left: 2px;
}

.form-group .help-text {
    display: block;
    font-size: 12px;
    color: #8ba0ae;
    margin-top: 4px;
}

.form-control {
    width: 100%;
    padding: 10px 14px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 6px;
    color: #fff;
    font-size: 14px;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: #d4af37;
    background: rgba(255,255,255,0.07);
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
}

.form-control::placeholder {
    color: #6a7f8e;
}

.form-control[readonly] {
    opacity: 0.7;
    cursor: not-allowed;
}

select.form-control {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%238ba0ae' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    padding-right: 40px;
}

select.form-control option {
    background: #1a1f2e;
    color: #fff;
}

textarea.form-control {
    resize: vertical;
    min-height: 80px;
    font-family: inherit;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-row-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 16px;
}

/* --- Alert --- */
.alert {
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.alert-danger {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.2);
    color: #ef4444;
}

.alert i {
    font-size: 18px;
}

/* --- Buttons --- */
.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.06);
    flex-wrap: wrap;
}

.btn-save {
    background: #d4af37;
    color: #0a0e17;
    padding: 12px 32px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 14px;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-save:hover {
    background: #e8c84a;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(212, 175, 55, 0.3);
}

.btn-cancel {
    background: rgba(255,255,255,0.06);
    color: #8ba0ae;
    padding: 12px 24px;
    border-radius: 6px;
    font-weight: 500;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid rgba(255,255,255,0.06);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-cancel:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
    border-color: rgba(255,255,255,0.12);
}

/* --- Responsive --- */
@media (max-width: 768px) {
    .admin-content.edit-invoice {
        padding: 12px 12px 24px;
    }

    .header-actions {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }

    .header-actions .page-subtitle {
        font-size: 20px;
        justify-content: center;
    }

    .header-actions .page-subtitle i {
        font-size: 22px;
    }

    .btn-back {
        justify-content: center;
    }

    .form-card {
        padding: 20px;
    }

    .form-row {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .form-row-3 {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .form-control {
        font-size: 13px;
        padding: 8px 12px;
    }

    .form-group label {
        font-size: 13px;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn-save, .btn-cancel {
        justify-content: center;
        width: 100%;
    }
}

@media (max-width: 480px) {
    .admin-content.edit-invoice {
        padding: 8px 8px 16px;
    }

    .header-actions .page-subtitle {
        font-size: 17px;
    }

    .form-card {
        padding: 16px;
    }

    .form-row-3 {
        grid-template-columns: 1fr;
    }

    .form-control {
        font-size: 12px;
        padding: 8px 10px;
    }

    .btn-save, .btn-cancel {
        font-size: 13px;
        padding: 10px 16px;
    }
}
</style>

<div class="admin-content edit-invoice">

    <!-- Header -->
    <div class="header-actions">
        <h2 class="page-subtitle"><i class="fas fa-edit" style="color: #d4af37;"></i> Edit Invoice</h2>
        <a href="invoice.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>

    <!-- Alert -->
    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="form-card">
        <form method="POST" action="">
            <!-- Nomor Invoice (Readonly) -->
            <div class="form-group">
                <label for="invoice_number">Nomor Invoice <span class="required">*</span></label>
                <input type="text" id="invoice_number" name="invoice_number" class="form-control" 
                       value="<?= htmlspecialchars($invoice['invoice_number']) ?>" readonly>
                <span class="help-text">Nomor invoice tidak dapat diubah.</span>
            </div>

            <!-- Klien -->
            <div class="form-row">
                <div class="form-group">
                    <label for="client_name">Nama Klien <span class="required">*</span></label>
                    <input type="text" id="client_name" name="client_name" class="form-control" 
                           value="<?= htmlspecialchars($invoice['client_name']) ?>" 
                           placeholder="Masukkan nama klien" required>
                </div>
                <div class="form-group">
                    <label for="client_email">Email Klien <span class="required">*</span></label>
                    <input type="email" id="client_email" name="client_email" class="form-control" 
                           value="<?= htmlspecialchars($invoice['client_email']) ?>" 
                           placeholder="Masukkan email klien" required>
                </div>
            </div>

            <!-- Produk / Layanan -->
            <div class="form-row">
                <div class="form-group">
                    <label for="product_id">Produk</label>
                    <select id="product_id" name="product_id" class="form-control">
                        <option value="">-- Pilih Produk (Opsional) --</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>" 
                                <?= ($invoice['product_id'] == $product['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($product['name']) ?> 
                                (<?= format_rupiah($product['price']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="help-text">Pilih produk yang sudah ada atau isi layanan kustom di bawah.</span>
                </div>
                <div class="form-group">
                    <label for="service_name">Nama Layanan (Kustom)</label>
                    <input type="text" id="service_name" name="service_name" class="form-control" 
                           value="<?= htmlspecialchars($invoice['service_name']) ?>" 
                           placeholder="Misal: Desain Logo Premium">
                    <span class="help-text">Isi jika tidak memilih produk dari daftar.</span>
                </div>
            </div>

            <!-- Deskripsi -->
            <div class="form-group">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description" class="form-control" 
                          placeholder="Deskripsi detail tentang invoice ini"><?= htmlspecialchars($invoice['description']) ?></textarea>
            </div>

            <!-- Quantity, Price, Total -->
            <div class="form-row-3">
                <div class="form-group">
                    <label for="quantity">Quantity <span class="required">*</span></label>
                    <input type="number" id="quantity" name="quantity" class="form-control" 
                           value="<?= $invoice['quantity'] ?>" min="1" required>
                </div>
                <div class="form-group">
                    <label for="price">Harga (Rp) <span class="required">*</span></label>
                    <input type="text" id="price" name="price" class="form-control" 
                           value="<?= number_format($invoice['price'], 0, ',', '.') ?>" 
                           placeholder="0" required>
                </div>
                <div class="form-group">
                    <label for="total_display">Total (Otomatis)</label>
                    <input type="text" id="total_display" class="form-control" 
                           value="<?= format_rupiah($invoice['total']) ?>" readonly>
                    <span class="help-text">Total = Quantity × Harga</span>
                </div>
            </div>

            <!-- Status & Due Date -->
            <div class="form-row">
                <div class="form-group">
                    <label for="status">Status <span class="required">*</span></label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="draft" <?= ($invoice['status'] == 'draft') ? 'selected' : '' ?>>Draft</option>
                        <option value="sent" <?= ($invoice['status'] == 'sent') ? 'selected' : '' ?>>Sent</option>
                        <option value="paid" <?= ($invoice['status'] == 'paid') ? 'selected' : '' ?>>Paid</option>
                        <option value="overdue" <?= ($invoice['status'] == 'overdue') ? 'selected' : '' ?>>Overdue</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="due_date">Tanggal Jatuh Tempo <span class="required">*</span></label>
                    <input type="date" id="due_date" name="due_date" class="form-control" 
                           value="<?= $invoice['due_date'] ?>" required>
                </div>
            </div>

            <!-- Notes -->
            <div class="form-group">
                <label for="notes">Catatan</label>
                <textarea id="notes" name="notes" class="form-control" 
                          placeholder="Catatan tambahan untuk klien"><?= htmlspecialchars($invoice['notes']) ?></textarea>
            </div>

            <!-- Tombol -->
            <div class="form-actions">
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Perbarui Invoice
                </button>
                <a href="index.php" class="btn-cancel">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Auto calculate total
document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.getElementById('quantity');
    const priceInput = document.getElementById('price');
    const totalDisplay = document.getElementById('total_display');

    function calculateTotal() {
        const quantity = parseInt(quantityInput.value) || 0;
        // Clean price from formatting
        const priceStr = priceInput.value.replace(/\./g, '').replace(',', '.');
        const price = parseFloat(priceStr) || 0;
        const total = quantity * price;
        
        // Format total as Rupiah
        totalDisplay.value = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(total);
    }

    quantityInput.addEventListener('input', calculateTotal);
    priceInput.addEventListener('input', function() {
        // Auto format price with dots
        let value = this.value.replace(/[^0-9]/g, '');
        if (value) {
            this.value = new Intl.NumberFormat('id-ID').format(parseInt(value));
        }
        calculateTotal();
    });

    // Initial calculation
    calculateTotal();
});
</script>

<?php include '../includes/footer.php'; ?>
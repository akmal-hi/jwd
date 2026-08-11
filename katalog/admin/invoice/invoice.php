<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Daftar Invoice - DKV ROOM';
require_once '../../config.php';

/** @var mysqli $conn */
global $conn;

require_login();

function invoice_status_badge($status) {
    $colors = [
        'draft' => ['bg' => 'rgba(255,255,255,0.05)', 'color' => '#8ba0ae'],
        'sent' => ['bg' => 'rgba(212,175,55,0.08)', 'color' => '#d4af37'],
        'paid' => ['bg' => 'rgba(16,185,129,0.1)', 'color' => '#10b981'],
        'overdue' => ['bg' => 'rgba(239,68,68,0.1)', 'color' => '#ef4444']
    ];
    $color = $colors[$status] ?? ['bg' => 'rgba(255,255,255,0.05)', 'color' => '#8ba0ae'];
    
    return '<span class="status-badge" style="background:' . $color['bg'] . ';color:' . $color['color'] . ';">' . ucfirst($status) . '</span>';
}

$invoices = [];

$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'invoices'");
if (mysqli_num_rows($check_table) == 0) {
    die("Tabel 'invoices' belum ada di database. Silakan buat tabel terlebih dahulu.");
}

$stmt = mysqli_prepare($conn, "SELECT i.*, p.name as product_name 
                                FROM invoices i 
                                LEFT JOIN products p ON i.product_id = p.id 
                                ORDER BY i.id DESC");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $invoices[] = $row;
}
mysqli_stmt_close($stmt);

$total_invoice = count($invoices);
$total_amount = 0;
$paid_count = 0;
$pending_count = 0;
foreach ($invoices as $inv) {
    $total_amount += $inv['total'];
    if ($inv['status'] == 'paid') $paid_count++;
    if (in_array($inv['status'], ['draft', 'sent'])) $pending_count++;
}

include '../includes/header.php';
?>

<style>
/* ============================================
   RESPONSIVE INVOICE PAGE STYLES
   ============================================ */

/* --- Container --- */
.admin-content.invoice-page {
    padding: 20px 24px 40px;
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
    box-sizing: border-box;
}

/* --- Header Actions --- */
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

.btn-gold {
    background: #d4af37;
    color: #0a0e17;
    padding: 10px 24px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: none;
    cursor: pointer;
    white-space: nowrap;
}

.btn-gold:hover {
    background: #e8c84a;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(212, 175, 55, 0.3);
}

.btn-gold i {
    font-size: 14px;
}

/* --- Stats Row --- */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 30px;
}

.stat-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.3s ease;
}

.stat-card:hover {
    background: rgba(255,255,255,0.06);
    border-color: rgba(255,255,255,0.1);
}

.stat-card.gold-border {
    border-color: rgba(212, 175, 55, 0.25);
}

.stat-card.green-border {
    border-color: rgba(16, 185, 129, 0.25);
}

.stat-card.orange-border {
    border-color: rgba(245, 158, 11, 0.25);
}

.stat-icon {
    font-size: 28px;
    color: #8ba0ae;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.04);
    border-radius: 10px;
    flex-shrink: 0;
}

.stat-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.stat-label {
    font-size: 12px;
    color: #8ba0ae;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-number {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    line-height: 1.3;
}

/* --- Table Container --- */
.table-container {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    overflow: hidden;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table-container table {
    width: 100%;
    border-collapse: collapse;
    min-width: 720px;
    font-size: 14px;
}

.table-container thead {
    background: rgba(255,255,255,0.04);
    border-bottom: 1px solid rgba(255,255,255,0.06);
}

.table-container thead th {
    text-align: left;
    padding: 14px 16px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #8ba0ae;
    white-space: nowrap;
}

.table-container tbody tr {
    border-bottom: 1px solid rgba(255,255,255,0.04);
    transition: background 0.2s ease;
}

.table-container tbody tr:hover {
    background: rgba(255,255,255,0.03);
}

.table-container tbody tr:last-child {
    border-bottom: none;
}

.table-container tbody td {
    padding: 14px 16px;
    vertical-align: middle;
    color: #e8edf0;
}

/* --- Table Elements --- */
.id-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 28px;
    background: rgba(255,255,255,0.06);
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #8ba0ae;
}

.invoice-number {
    font-size: 13px;
    font-weight: 600;
    color: #fff;
    letter-spacing: 0.3px;
}

.category-badge {
    display: inline-block;
    padding: 4px 12px;
    background: rgba(212, 175, 55, 0.1);
    border: 1px solid rgba(212, 175, 55, 0.15);
    border-radius: 20px;
    font-size: 12px;
    color: #d4af37;
    white-space: nowrap;
}

.price-tag {
    font-weight: 600;
    color: #fff;
    font-size: 14px;
    white-space: nowrap;
}

/* --- Status Badge --- */
.status-badge {
    display: inline-block;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    text-transform: capitalize;
    white-space: nowrap;
}

/* --- Action Buttons --- */
.action-buttons {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.action-buttons a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.25s ease;
    font-size: 14px;
}

.btn-edit {
    background: rgba(212, 175, 55, 0.1);
    color: #d4af37;
}
.btn-edit:hover {
    background: rgba(212, 175, 55, 0.25);
    transform: scale(1.05);
}

.btn-view {
    background: rgba(139, 160, 174, 0.1);
    color: #8ba0ae;
}
.btn-view:hover {
    background: rgba(139, 160, 174, 0.25);
    transform: scale(1.05);
}

.btn-pdf {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}
.btn-pdf:hover {
    background: rgba(239, 68, 68, 0.25);
    transform: scale(1.05);
}

.btn-delete {
    background: rgba(239, 68, 68, 0.08);
    color: #ef4444;
}
.btn-delete:hover {
    background: rgba(239, 68, 68, 0.2);
    transform: scale(1.05);
}

/* --- Empty State --- */
.empty-state {
    text-align: center !important;
    padding: 60px 20px !important;
}

.empty-state i {
    font-size: 48px;
    color: #8ba0ae;
    opacity: 0.3;
    display: block;
    margin-bottom: 12px;
}

.empty-state p {
    font-size: 16px;
    color: #8ba0ae;
    margin: 0;
}

/* ============================================
   RESPONSIVE BREAKPOINTS
   ============================================ */

/* Tablets & small laptops */
@media (max-width: 1024px) {
    .admin-content.invoice-page {
        padding: 16px 18px 32px;
    }

    .stats-row {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .stat-card {
        padding: 14px 16px;
    }

    .stat-number {
        font-size: 18px;
    }

    .stat-icon {
        font-size: 22px;
        width: 40px;
        height: 40px;
    }

    .header-actions .page-subtitle {
        font-size: 20px;
    }

    .btn-gold {
        padding: 8px 18px;
        font-size: 13px;
    }
}

/* Mobile phones */
@media (max-width: 768px) {
    .admin-content.invoice-page {
        padding: 12px 12px 24px;
    }

    .header-actions {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
        margin-bottom: 20px;
    }

    .header-actions .page-subtitle {
        font-size: 18px;
        justify-content: center;
    }

    .header-actions .page-subtitle i {
        font-size: 20px;
    }

    .btn-gold {
        justify-content: center;
        padding: 10px 16px;
        font-size: 13px;
        width: 100%;
    }

    .stats-row {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-bottom: 20px;
    }

    .stat-card {
        padding: 12px 14px;
        gap: 12px;
        border-radius: 8px;
    }

    .stat-icon {
        font-size: 18px;
        width: 34px;
        height: 34px;
        border-radius: 8px;
    }

    .stat-label {
        font-size: 10px;
    }

    .stat-number {
        font-size: 16px;
    }

    .table-container {
        border-radius: 8px;
    }

    .table-container table {
        font-size: 12px;
        min-width: 640px;
    }

    .table-container thead th {
        padding: 10px 12px;
        font-size: 10px;
    }

    .table-container tbody td {
        padding: 10px 12px;
    }

    .invoice-number {
        font-size: 12px;
    }

    .price-tag {
        font-size: 12px;
    }

    .category-badge {
        font-size: 10px;
        padding: 3px 10px;
    }

    .status-badge {
        font-size: 10px;
        padding: 3px 10px;
    }

    .action-buttons a {
        width: 30px;
        height: 30px;
        font-size: 12px;
    }

    .id-badge {
        min-width: 24px;
        height: 24px;
        font-size: 10px;
    }

    .empty-state {
        padding: 40px 16px !important;
    }

    .empty-state i {
        font-size: 36px;
    }

    .empty-state p {
        font-size: 14px;
    }
}

/* Small phones */
@media (max-width: 480px) {
    .admin-content.invoice-page {
        padding: 8px 8px 16px;
    }

    .stats-row {
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .stat-card {
        padding: 10px 12px;
        gap: 10px;
    }

    .stat-icon {
        font-size: 16px;
        width: 30px;
        height: 30px;
    }

    .stat-number {
        font-size: 14px;
    }

    .stat-label {
        font-size: 9px;
    }

    .header-actions .page-subtitle {
        font-size: 16px;
    }

    .table-container table {
        font-size: 11px;
        min-width: 580px;
    }

    .table-container thead th {
        padding: 8px 10px;
        font-size: 9px;
    }

    .table-container tbody td {
        padding: 8px 10px;
    }

    .invoice-number {
        font-size: 11px;
    }

    .price-tag {
        font-size: 11px;
    }

    .category-badge {
        font-size: 9px;
        padding: 2px 8px;
    }

    .status-badge {
        font-size: 9px;
        padding: 2px 8px;
    }

    .action-buttons {
        gap: 4px;
    }

    .action-buttons a {
        width: 26px;
        height: 26px;
        font-size: 10px;
        border-radius: 4px;
    }

    .id-badge {
        min-width: 20px;
        height: 20px;
        font-size: 9px;
    }

    .btn-gold {
        font-size: 12px;
        padding: 8px 14px;
    }

    .btn-gold i {
        font-size: 12px;
    }
}
</style>

<div class="admin-content invoice-page">

<!-- Header Actions -->
    <div class="header-actions">
        <h2 class="page-subtitle"><i class="fas fa-file-invoice" style="color: #d4af37;"></i> Daftar Invoice</h2>
        <a href="create.php" class="btn-gold"><i class="fas fa-plus"></i> Invoice Baru</a>
    </div>

    <!-- Statistik Premium -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-file-invoice"></i></div>
            <div class="stat-info">
                <span class="stat-label">Total Invoice</span>
                <span class="stat-number"><?= $total_invoice ?></span>
            </div>
        </div>
        <div class="stat-card gold-border">
            <div class="stat-icon" style="color: #d4af37;"><i class="fas fa-money-bill-wave"></i></div>
            <div class="stat-info">
                <span class="stat-label">Total Pendapatan</span>
                <span class="stat-number" style="color: #d4af37;"><?= format_rupiah($total_amount) ?></span>
            </div>
        </div>
        <div class="stat-card green-border">
            <div class="stat-icon" style="color: #10b981;"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <span class="stat-label">Lunas</span>
                <span class="stat-number" style="color: #10b981;"><?= $paid_count ?></span>
            </div>
        </div>
        <div class="stat-card orange-border">
            <div class="stat-icon" style="color: #f59e0b;"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <span class="stat-label">Pending</span>
                <span class="stat-number" style="color: #f59e0b;"><?= $pending_count ?></span>
            </div>
        </div>
    </div>

    <!-- Tabel -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>No. Invoice</th>
                    <th>Klien</th>
                    <th>Produk/Layanan</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Jatuh Tempo</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="fas fa-file-invoice"></i>
                            <p>Belum ada invoice.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $i => $inv): ?>
                    <tr>
                        <td><span class="id-badge"><?= $i+1 ?></span></td>
                        <td><strong class="invoice-number"><?= htmlspecialchars($inv['invoice_number']) ?></strong></td>
                        <td><?= htmlspecialchars($inv['client_name']) ?></td>
                        <td>
                            <?php if ($inv['product_id'] > 0 && !empty($inv['product_name'])): ?>
                                <span class="category-badge"><?= htmlspecialchars($inv['product_name']) ?></span>
                            <?php else: ?>
                                <span style="color: #8ba0ae; font-size: 13px;"><?= htmlspecialchars($inv['service_name']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="price-tag"><?= format_rupiah($inv['total']) ?></td>
                        <td><?= invoice_status_badge($inv['status']) ?></td>
                        <td style="font-size: 13px; color: #b0c4d0;"><?= date('d/m/Y', strtotime($inv['due_date'])) ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit.php?id=<?= $inv['id'] ?>" class="btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                                <a href="delete.php?id=<?= $inv['id'] ?>" class="btn-delete" onclick="return confirm('Yakin hapus?')" title="Hapus"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
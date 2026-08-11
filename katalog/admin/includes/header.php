<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $page_title ?? 'Admin Panel' ?> — LSP COACHPRO INDONESIA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* ==========================================
           RESET & BASE — DARK GOLD THEME
           ========================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: #0b0b0b;
            color: #f0f0f0;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        button {
            font-family: inherit;
            cursor: pointer;
            border: none;
            outline: none;
        }

        /* ==========================================
           ADMIN WRAPPER
           ========================================== */
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* ==========================================
           SIDEBAR — DARK LUXURY
           ========================================== */
        .admin-sidebar {
            width: 270px;
            background: linear-gradient(160deg, #0f0f0f 0%, #1a1a1a 100%);
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            overflow-y: auto;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            transform: translateX(-100%);
            border-right: 1px solid rgba(212, 175, 55, 0.08);
        }
        .admin-sidebar.open {
            transform: translateX(0);
        }
        @media (min-width: 769px) {
            .admin-sidebar { transform: translateX(0) !important; }
        }

        .admin-sidebar::-webkit-scrollbar { width: 3px; }
        .admin-sidebar::-webkit-scrollbar-track { background: #1a1a1a; }
        .admin-sidebar::-webkit-scrollbar-thumb { background: #d4af37; border-radius: 3px; }

        /* Sidebar Header */
        .sidebar-header {
            padding: 2rem 1.5rem 1.2rem;
            border-bottom: 1px solid rgba(212, 175, 55, 0.08);
            text-align: center;
        }
        .sidebar-header .logo-icon {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.6rem;
            font-size: 1.4rem;
            font-weight: 800;
            color: white;
        }
        .sidebar-header .logo-icon i {
            color: #d4af37;
            font-size: 1.8rem;
        }
        .sidebar-header .logo-icon span {
            background: linear-gradient(135deg, #fff, #f5d77b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .sidebar-header p {
            font-size: 0.65rem;
            color: rgba(255,255,255,0.3);
            margin-top: 0.2rem;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* Profile */
        .sidebar-profile {
            padding: 1rem 1.5rem 1.2rem;
            border-bottom: 1px solid rgba(212, 175, 55, 0.08);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .sidebar-profile .avatar {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #d4af37, #f5d77b);
            color: #0b0b0b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
        }
        .sidebar-profile .info .name {
            font-weight: 600;
            font-size: 0.9rem;
            color: #f5f5f5;
        }
        .sidebar-profile .info .role {
            font-size: 0.65rem;
            color: rgba(255,255,255,0.35);
        }

        /* Menu */
        .sidebar-menu {
            list-style: none;
            padding: 0.8rem 0.8rem;
        }
        .sidebar-menu li {
            margin-bottom: 0.15rem;
        }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.65rem 1rem;
            color: rgba(255,255,255,0.5);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.25s ease;
            font-weight: 500;
            font-size: 0.85rem;
        }
        .sidebar-menu a i {
            width: 22px;
            font-size: 1rem;
            color: rgba(255,255,255,0.3);
            transition: color 0.25s;
        }
        .sidebar-menu a:hover {
            background: rgba(212, 175, 55, 0.06);
            color: white;
        }
        .sidebar-menu a:hover i {
            color: #d4af37;
        }
        .sidebar-menu li.active a {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.10), rgba(212, 175, 55, 0.03));
            color: #f5f5f5;
        }
        .sidebar-menu li.active a i {
            color: #d4af37;
        }

        .sidebar-divider {
            height: 1px;
            background: rgba(212, 175, 55, 0.08);
            margin: 0.5rem 1.5rem;
        }

        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(212, 175, 55, 0.08);
            font-size: 0.6rem;
            color: rgba(255,255,255,0.2);
            text-align: center;
        }
        .sidebar-footer strong { color: rgba(255,255,255,0.4); }

        /* Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(4px);
            z-index: 999;
        }
        .sidebar-overlay.active { display: block; }

        /* ==========================================
           MAIN CONTENT
           ========================================== */
        .admin-main {
            flex: 1;
            margin-left: 0;
            padding: 20px 30px;
            transition: margin-left 0.35s ease;
            width: 100%;
            min-height: 100vh;
            background: #0b0b0b;
        }
        @media (min-width: 769px) {
            .admin-main { margin-left: 270px; }
        }

        /* ==========================================
           TOP BAR — Glass
           ========================================== */
        .top-bar {
            background: rgba(255,255,255,0.03);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 16px;
            padding: 0.7rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border: 1px solid rgba(212, 175, 55, 0.06);
            position: sticky;
            top: 0;
            z-index: 99;
        }
        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 1.2rem;
        }
        .page-title {
            font-size: 1rem;
            font-weight: 700;
            color: #f5f5f5;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .page-title i { color: #d4af37; }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .user-info .user-badge {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            background: rgba(255,255,255,0.04);
            padding: 0.25rem 1rem 0.25rem 0.5rem;
            border-radius: 40px;
            border: 1px solid rgba(212, 175, 55, 0.08);
        }
        .user-info .user-badge i {
            color: #d4af37;
            font-size: 1.1rem;
        }
        .user-info .user-badge span {
            font-weight: 500;
            font-size: 0.8rem;
            color: #d0d0d0;
        }
        .btn-logout {
            background: linear-gradient(135deg, #d4af37, #b8962e);
            color: #0b0b0b;
            padding: 0.25rem 1.2rem;
            border-radius: 40px;
            text-decoration: none;
            font-size: 0.7rem;
            font-weight: 700;
            transition: all 0.3s;
        }
        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.2);
        }

        .mobile-toggle {
            display: block;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(212, 175, 55, 0.08);
            font-size: 1.1rem;
            cursor: pointer;
            color: #d0d0d0;
            padding: 0.4rem 0.7rem;
            border-radius: 10px;
            transition: 0.2s;
        }
        .mobile-toggle:hover { background: rgba(212, 175, 55, 0.06); }
        @media (min-width: 769px) {
            .mobile-toggle { display: none; }
        }

        /* ==========================================
           DASHBOARD STYLES
           ========================================== */
        .dashboard-container {
            max-width: 1300px;
            margin: 0 auto;
        }

        /* Stats Row */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.2rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(212, 175, 55, 0.06);
            border-radius: 16px;
            padding: 1.2rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.2rem;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            background: rgba(212, 175, 55, 0.03);
            border-color: rgba(212, 175, 55, 0.12);
            transform: translateY(-2px);
        }
        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(212, 175, 55, 0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #d4af37;
            flex-shrink: 0;
        }
        .stat-card .stat-info {
            display: flex;
            flex-direction: column;
        }
        .stat-card .stat-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: rgba(255,255,255,0.3);
            font-weight: 600;
        }
        .stat-card .stat-number {
            font-size: 1.8rem;
            font-weight: 800;
            color: #f5f5f5;
            line-height: 1.2;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .dashboard-card {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(212, 175, 55, 0.06);
            border-radius: 16px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }
        .dashboard-card:hover {
            border-color: rgba(212, 175, 55, 0.12);
        }
        .dashboard-card .card-header {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1.2rem;
        }
        .dashboard-card .card-header i {
            color: #d4af37;
            font-size: 1.1rem;
        }
        .dashboard-card .card-header h3 {
            font-size: 0.9rem;
            font-weight: 600;
            color: #d0d0d0;
        }

        .chart-container {
            position: relative;
            height: 220px;
        }

        /* Table */
        .table-wrapper {
            overflow-x: auto;
        }
        .recent-table {
            width: 100%;
            border-collapse: collapse;
        }
        .recent-table th {
            padding: 0.5rem 0.8rem;
            text-align: left;
            color: rgba(255,255,255,0.3);
            font-weight: 600;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.06);
        }
        .recent-table td {
            padding: 0.6rem 0.8rem;
            border-bottom: 1px solid rgba(255,255,255,0.03);
            font-size: 0.8rem;
            color: #c0c0c0;
        }
        .recent-table tr:hover td {
            background: rgba(212, 175, 55, 0.02);
        }
        .id-badge {
            color: rgba(255,255,255,0.2);
            font-weight: 500;
            font-size: 0.7rem;
        }
        .category-badge {
            background: rgba(212, 175, 55, 0.08);
            padding: 0.15rem 0.7rem;
            border-radius: 20px;
            font-size: 0.7rem;
            color: #d4af37;
            font-weight: 500;
        }
        .price-tag {
            color: #f5d77b;
            font-weight: 600;
            font-size: 0.8rem;
        }
        .empty-state {
            text-align: center;
            color: rgba(255,255,255,0.2);
            padding: 1.5rem 0;
            font-size: 0.8rem;
        }

        /* Menu Grid */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }
        .menu-item {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(212, 175, 55, 0.06);
            border-radius: 14px;
            padding: 1.2rem 1rem;
            text-align: center;
            text-decoration: none;
            color: #c0c0c0;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.3rem;
        }
        .menu-item .menu-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(212, 175, 55, 0.04);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: #d4af37;
        }
        .menu-item span {
            font-weight: 600;
            font-size: 0.8rem;
            color: #e8e8e8;
        }
        .menu-item small {
            font-size: 0.6rem;
            color: rgba(255,255,255,0.2);
        }
        .menu-item:hover {
            background: rgba(212, 175, 55, 0.04);
            border-color: rgba(212, 175, 55, 0.15);
            transform: translateY(-3px);
        }

        /* ==========================================
           ADMIN CONTENT - DARK GOLD THEME
           ========================================== */
        .admin-content {
            max-width: 1300px;
            margin: 0 auto;
        }

        /* Page Title Admin */
        .page-title-admin {
            font-size: 1.3rem;
            font-weight: 700;
            color: #f5f5f5;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        /* Header Actions */
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .header-actions .header-left {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        .header-actions .header-right {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            flex-wrap: wrap;
        }

        /* Buttons */
        .btn-gold {
            background: linear-gradient(135deg, #d4af37, #b8962e);
            color: #0b0b0b;
            padding: 0.5rem 1.5rem;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        .btn-gold:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.25);
        }

        .btn-secondary-dark {
            background: rgba(255,255,255,0.04);
            color: #a0a0a0;
            padding: 0.5rem 1.5rem;
            border-radius: 30px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            font-size: 0.85rem;
            border: 1px solid rgba(255,255,255,0.06);
            transition: all 0.3s ease;
        }
        .btn-secondary-dark:hover {
            background: rgba(255,255,255,0.08);
            color: #f5f5f5;
        }

        .btn-edit {
            background: rgba(212, 175, 55, 0.08);
            color: #d4af37;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn-edit:hover {
            background: rgba(212, 175, 55, 0.15);
            transform: scale(1.05);
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.08);
            color: #ef4444;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn-delete:hover {
            background: rgba(239, 68, 68, 0.15);
            transform: scale(1.05);
        }

        .btn-disabled {
            color: rgba(255,255,255,0.1);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: not-allowed;
        }

        .btn-full {
            width: 100%;
            justify-content: center;
            padding: 0.8rem;
            font-size: 1rem;
        }

        .btn-reset {
            background: rgba(255,255,255,0.04);
            color: #8ba0ae;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: 0.3s;
        }
        .btn-reset:hover {
            background: rgba(255,255,255,0.08);
        }

        /* Search Box */
        .search-box {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 30px;
            padding: 0.2rem 0.2rem 0.2rem 1rem;
        }
        .search-box input {
            background: transparent;
            border: none;
            color: #f5f5f5;
            font-size: 0.85rem;
            padding: 0.4rem 0;
            min-width: 160px;
            outline: none;
        }
        .search-box input::placeholder {
            color: rgba(255,255,255,0.2);
        }
        .search-box button {
            background: rgba(212, 175, 55, 0.08);
            color: #d4af37;
            border: none;
            border-radius: 30px;
            padding: 0.4rem 1rem;
            cursor: pointer;
            transition: 0.3s;
        }
        .search-box button:hover {
            background: rgba(212, 175, 55, 0.15);
        }

        /* Form Card */
        .form-card {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(212, 175, 55, 0.06);
            border-radius: 16px;
            padding: 1.8rem;
            margin-bottom: 2rem;
        }
        .form-card-small {
            max-width: 600px;
        }
        .form-card .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .form-card .form-header h3 {
            color: #f5f5f5;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .form-badge {
            background: rgba(212, 175, 55, 0.08);
            color: #d4af37;
            padding: 0.2rem 1rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .form-grid .full-width {
            grid-column: span 2;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }
        .form-group label {
            font-weight: 600;
            font-size: 0.8rem;
            color: #d0d0d0;
        }
        .form-group .required {
            color: #ef4444;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 0.6rem 0.9rem;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.06);
            background: rgba(255,255,255,0.02);
            color: #f5f5f5;
            font-size: 0.9rem;
            transition: 0.3s;
            outline: none;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: rgba(212, 175, 55, 0.25);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.04);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .form-group select option {
            background: #1a1a1a;
            color: #f5f5f5;
        }
        .form-group small {
            color: rgba(255,255,255,0.25);
            font-size: 0.7rem;
        }

        .form-actions {
            display: flex;
            gap: 0.8rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }
        .form-actions button,
        .form-actions a {
            padding: 0.5rem 1.8rem;
        }

        /* File Upload */
        .file-upload-wrapper {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            flex-wrap: wrap;
        }
        .file-upload-wrapper input[type="file"] {
            display: none;
        }
        .file-label {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
            padding: 0.4rem 1.2rem;
            border-radius: 30px;
            cursor: pointer;
            font-size: 0.8rem;
            color: #d0d0d0;
            transition: 0.3s;
        }
        .file-label:hover {
            background: rgba(255,255,255,0.08);
        }
        .file-name {
            color: rgba(255,255,255,0.2);
            font-size: 0.8rem;
        }

        /* Preview Image */
        .preview-image {
            margin-top: 0.5rem;
            max-width: 150px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.06);
            padding: 4px;
        }
        .current-image {
            margin-top: 0.5rem;
            padding: 0.5rem;
            background: rgba(255,255,255,0.02);
            border-radius: 8px;
        }
        .current-image strong {
            display: block;
            margin-bottom: 0.25rem;
            font-size: 0.75rem;
            color: rgba(255,255,255,0.3);
        }
        .image-preview-wrapper {
            display: inline-block;
        }
        .image-preview-wrapper img {
            max-width: 150px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.06);
        }

        /* Logo Upload */
        .logo-upload-wrapper {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .logo-preview {
            height: 60px;
            width: auto;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.06);
            padding: 4px;
            background: rgba(255,255,255,0.02);
        }
        .logo-placeholder {
            height: 60px;
            width: 60px;
            border: 2px dashed rgba(255,255,255,0.06);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,0.15);
            font-size: 0.65rem;
            flex-direction: column;
            gap: 4px;
        }
        .logo-placeholder i {
            font-size: 1.2rem;
        }

        /* Alert Messages */
        .alert-success {
            background: rgba(16, 185, 129, 0.06);
            color: #34d399;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            border-left: 3px solid #10b981;
        }
        .alert-success i {
            margin-right: 0.5rem;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.06);
            color: #f87171;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            border-left: 3px solid #ef4444;
        }
        .alert-error i {
            margin-right: 0.5rem;
        }
        .alert-error ul {
            margin: 0.3rem 0 0 1.2rem;
        }

        /* Table Container */
        .table-container {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(212, 175, 55, 0.06);
            border-radius: 16px;
            padding: 1rem;
            overflow-x: auto;
        }
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .table-title {
            color: #d0d0d0;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .table-title i {
            color: #d4af37;
            margin-right: 0.5rem;
        }
        .table-count {
            color: rgba(255,255,255,0.2);
            font-size: 0.8rem;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-container thead {
            border-bottom: 1px solid rgba(212, 175, 55, 0.06);
        }
        .table-container th {
            padding: 0.7rem 0.8rem;
            text-align: left;
            color: rgba(255,255,255,0.3);
            font-weight: 600;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table-container td {
            padding: 0.7rem 0.8rem;
            border-bottom: 1px solid rgba(255,255,255,0.02);
            font-size: 0.85rem;
            color: #c0c0c0;
            vertical-align: middle;
        }
        .table-container tr:hover td {
            background: rgba(212, 175, 55, 0.02);
        }

        /* Badges */
        .count-badge {
            background: rgba(255,255,255,0.03);
            padding: 0.15rem 0.7rem;
            border-radius: 20px;
            font-size: 0.7rem;
            color: rgba(255,255,255,0.3);
        }

        /* Product Image */
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.06);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.3rem;
            align-items: center;
        }
        .action-buttons a,
        .action-buttons span {
            font-size: 0.75rem;
            text-decoration: none;
        }

        /* Settings Page */
        .settings-page .settings-section {
            border-bottom: 1px solid rgba(212, 175, 55, 0.04);
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .settings-page .settings-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .settings-page .settings-section h4 {
            color: #d0d0d0;
            font-size: 0.95rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .section-desc {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.25);
            margin-bottom: 1rem;
        }

        /* ==========================================
           RESPONSIVE
           ========================================== */
        @media (max-width: 992px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .admin-main {
                padding: 15px;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            .form-grid .full-width {
                grid-column: span 1;
            }
            .header-actions {
                flex-direction: column;
                align-items: stretch;
            }
            .header-actions .header-right {
                flex-wrap: wrap;
            }
            .search-box {
                flex: 1;
            }
            .search-box input {
                min-width: 100px;
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .stats-row {
                grid-template-columns: 1fr;
                gap: 0.8rem;
            }
            .stat-card {
                padding: 1rem;
            }
            .stat-card .stat-number {
                font-size: 1.4rem;
            }
            .menu-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.8rem;
            }
            .top-bar {
                flex-direction: column;
                align-items: stretch;
                gap: 0.8rem;
                padding: 0.7rem 1rem;
            }
            .user-info {
                justify-content: space-between;
            }
            .page-title {
                font-size: 0.85rem;
            }
            .form-card {
                padding: 1.2rem;
            }
            .table-container {
                padding: 0.5rem;
            }
            .table-container th,
            .table-container td {
                padding: 0.4rem 0.5rem;
                font-size: 0.7rem;
            }
            .product-image {
                width: 36px;
                height: 36px;
            }
            .action-buttons a,
            .action-buttons span {
                width: 26px;
                height: 26px;
                font-size: 0.65rem;
            }
            .form-actions button,
            .form-actions a {
                padding: 0.4rem 1.2rem;
                font-size: 0.8rem;
            }
            .form-card .form-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .header-actions .header-right {
                flex-direction: column;
                align-items: stretch;
            }
            .search-box {
                padding: 0.2rem 0.2rem 0.2rem 0.8rem;
            }
            .search-box input {
                min-width: 80px;
            }
            .logo-upload-wrapper {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 480px) {
            .admin-main {
                padding: 10px;
            }
            .dashboard-card {
                padding: 1rem;
            }
            .menu-grid {
                grid-template-columns: 1fr;
            }
            .recent-table th,
            .recent-table td {
                padding: 0.4rem 0.5rem;
                font-size: 0.7rem;
            }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'sidebar.php'; ?>

    
    
    <!-- Overlay untuk mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    
    <div class="admin-main">
        <div class="top-bar">
            <div class="top-bar-left">
                <button class="mobile-toggle" id="mobileToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="page-title">
                    <i class="fas fa-certificate"></i> <?= $page_title ?? 'Dashboard' ?>
                </div>
            </div>
            <div class="user-info">
                <div class="user-badge">
                    <i class="fas fa-user-circle"></i>
                    <span><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></span>
                </div>
                <a href="../../katalog/logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Data to CSV</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .content {
            padding: 40px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: #f8f9ff;
            padding: 25px;
            border-radius: 12px;
            border-left: 4px solid #4facfe;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #4facfe;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .export-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #4facfe;
            padding-bottom: 10px;
        }

        .export-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
        }

        .export-card {
            background: #fff;
            border: 1px solid #e1e5e9;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .export-card h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.3rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4facfe;
            box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .btn {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            width: 100%;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 172, 254, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn.btn-secondary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
            margin-left: 10px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .export-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 2rem;
            }

            .content {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📊 Export Data to CSV</h1>
            <p>Xuất dữ liệu therapists và users thành file CSV</p>
        </div>

        <div class="content">
            <!-- Statistics -->
            <div class="stats-grid" id="stats-grid">
                <div class="stat-card">
                    <div class="stat-number" id="therapists-count">-</div>
                    <div class="stat-label">Therapists</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="users-count">-</div>
                    <div class="stat-label">Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="verified-users-count">-</div>
                    <div class="stat-label">Verified Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="unverified-users-count">-</div>
                    <div class="stat-label">Unverified Users</div>
                </div>
            </div>

            <!-- Export Sections -->
            <div class="export-section">
                <h2 class="section-title">🧠 Export Therapists</h2>
                <div class="export-grid">
                    <!-- Export All Therapists -->
                    <div class="export-card">
                        <h3>Xuất tất cả Therapists</h3>
                        <form id="export-all-therapists">
                            <div class="form-group">
                                <label>Tên file (không bắt buộc)</label>
                                <input type="text" name="filename" placeholder="therapists_export.csv">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Quốc gia</label>
                                    <input type="text" name="country" placeholder="Lọc theo quốc gia">
                                </div>
                                <div class="form-group">
                                    <label>Bang/Tỉnh</label>
                                    <input type="text" name="state" placeholder="Lọc theo bang/tỉnh">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Thành phố</label>
                                    <input type="text" name="city" placeholder="Lọc theo thành phố">
                                </div>
                                <div class="form-group">
                                    <label>Giới tính</label>
                                    <select name="gender">
                                        <option value="">Tất cả</option>
                                        <option value="Male">Nam</option>
                                        <option value="Female">Nữ</option>
                                        <option value="Other">Khác</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Chuyên khoa</label>
                                <input type="text" name="specialty" placeholder="Lọc theo chuyên khoa">
                            </div>

                            <div class="form-group">
                                <label>Có dịch vụ online</label>
                                <select name="online_offered">
                                    <option value="">Tất cả</option>
                                    <option value="1">Có</option>
                                    <option value="0">Không</option>
                                </select>
                            </div>

                            <button type="submit" class="btn">📥 Xuất tất cả Therapists</button>
                        </form>
                    </div>

                    <!-- Export Selected Therapists -->
                    <div class="export-card">
                        <h3>Xuất Therapists được chọn</h3>
                        <form id="export-selected-therapists">
                            <div class="form-group">
                                <label>Tên file (không bắt buộc)</label>
                                <input type="text" name="filename" placeholder="selected_therapists.csv">
                            </div>

                            <div class="form-group">
                                <label>IDs của Therapists (cách nhau bằng dấu phẩy)</label>
                                <input type="text" name="ids" placeholder="1,2,3,4,5" required>
                                <small style="color: #666; font-size: 0.85rem;">Ví dụ: 1,2,3,4,5</small>
                            </div>

                            <button type="submit" class="btn btn-secondary">📥 Xuất Therapists được chọn</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Export Users Section -->
            <div class="export-section">
                <h2 class="section-title">👥 Export Users</h2>
                <div class="export-grid">
                    <!-- Export All Users -->
                    <div class="export-card">
                        <h3>Xuất tất cả Users</h3>
                        <form id="export-all-users">
                            <div class="form-group">
                                <label>Tên file (không bắt buộc)</label>
                                <input type="text" name="filename" placeholder="users_export.csv">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Email (tìm kiếm)</label>
                                    <input type="text" name="email" placeholder="Lọc theo email">
                                </div>
                                <div class="form-group">
                                    <label>Tên (tìm kiếm)</label>
                                    <input type="text" name="name" placeholder="Lọc theo tên">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Trạng thái xác thực</label>
                                <select name="verified">
                                    <option value="">Tất cả</option>
                                    <option value="1">Đã xác thực</option>
                                    <option value="0">Chưa xác thực</option>
                                </select>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Từ ngày</label>
                                    <input type="date" name="created_from">
                                </div>
                                <div class="form-group">
                                    <label>Đến ngày</label>
                                    <input type="date" name="created_to">
                                </div>
                            </div>

                            <button type="submit" class="btn">📥 Xuất tất cả Users</button>
                        </form>
                    </div>

                    <!-- Export Selected Users -->
                    <div class="export-card">
                        <h3>Xuất Users được chọn</h3>
                        <form id="export-selected-users">
                            <div class="form-group">
                                <label>Tên file (không bắt buộc)</label>
                                <input type="text" name="filename" placeholder="selected_users.csv">
                            </div>

                            <div class="form-group">
                                <label>IDs của Users (cách nhau bằng dấu phẩy)</label>
                                <input type="text" name="ids" placeholder="1,2,3,4,5" required>
                                <small style="color: #666; font-size: 0.85rem;">Ví dụ: 1,2,3,4,5</small>
                            </div>

                            <button type="submit" class="btn btn-secondary">📥 Xuất Users được chọn</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Setup CSRF token for AJAX requests
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Load statistics on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
        });

        // Load statistics
        function loadStats() {
            fetch('/export/stats')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('therapists-count').textContent = data.therapists_count.toLocaleString();
                    document.getElementById('users-count').textContent = data.users_count.toLocaleString();
                    document.getElementById('verified-users-count').textContent = data.users_verified_count.toLocaleString();
                    document.getElementById('unverified-users-count').textContent = data.users_unverified_count.toLocaleString();
                })
                .catch(error => {
                    console.error('Error loading stats:', error);
                });
        }

        // Export all therapists
        document.getElementById('export-all-therapists').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);

            const btn = this.querySelector('button[type="submit"]');
            btn.classList.add('loading');
            btn.textContent = 'Đang xuất...';

            window.location.href = `/export/therapists?${params.toString()}`;

            setTimeout(() => {
                btn.classList.remove('loading');
                btn.textContent = '📥 Xuất tất cả Therapists';
            }, 2000);
        });

        // Export selected therapists
        document.getElementById('export-selected-therapists').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            // Convert comma-separated IDs to array
            const idsString = formData.get('ids');
            const ids = idsString.split(',').map(id => id.trim()).filter(id => id);

            const data = {
                ids: ids,
                filename: formData.get('filename')
            };

            const btn = this.querySelector('button[type="submit"]');
            btn.classList.add('loading');
            btn.textContent = 'Đang xuất...';

            fetch('/export/therapists/selected', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (response.ok) {
                        return response.blob();
                    }
                    throw new Error('Export failed');
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = data.filename || 'selected_therapists.csv';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                })
                .catch(error => {
                    console.error('Export error:', error);
                    alert('Có lỗi xảy ra khi xuất file!');
                })
                .finally(() => {
                    btn.classList.remove('loading');
                    btn.textContent = '📥 Xuất Therapists được chọn';
                });
        });

        // Export all users
        document.getElementById('export-all-users').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);

            const btn = this.querySelector('button[type="submit"]');
            btn.classList.add('loading');
            btn.textContent = 'Đang xuất...';

            window.location.href = `/export/users?${params.toString()}`;

            setTimeout(() => {
                btn.classList.remove('loading');
                btn.textContent = '📥 Xuất tất cả Users';
            }, 2000);
        });

        // Export selected users
        document.getElementById('export-selected-users').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            // Convert comma-separated IDs to array
            const idsString = formData.get('ids');
            const ids = idsString.split(',').map(id => id.trim()).filter(id => id);

            const data = {
                ids: ids,
                filename: formData.get('filename')
            };

            const btn = this.querySelector('button[type="submit"]');
            btn.classList.add('loading');
            btn.textContent = 'Đang xuất...';

            fetch('/export/users/selected', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (response.ok) {
                        return response.blob();
                    }
                    throw new Error('Export failed');
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = data.filename || 'selected_users.csv';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                })
                .catch(error => {
                    console.error('Export error:', error);
                    alert('Có lỗi xảy ra khi xuất file!');
                })
                .finally(() => {
                    btn.classList.remove('loading');
                    btn.textContent = '📥 Xuất Users được chọn';
                });
        });
    </script>
</body>

</html>
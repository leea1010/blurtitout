# Hướng dẫn sử dụng Therapists Scraping System

Hệ thống đã được tách thành 2 command riêng biệt như yêu cầu:

## 1. Command Scraping (Chạy liên tục)

**File:** `app/Console/Commands/ScrapeTherapistsData.php`  
**Command:** `php artisan app:scrape-therapists`

### Chức năng:
- Chạy Python script để scrape dữ liệu từ external sources
- Chạy liên tục (continuous loop)
- Có delay 30 giây giữa các cycle để tránh overwhelming target
- Hỗ trợ proxy list qua option

### Cách sử dụng:
```bash
# Chạy basic (không proxy)
php artisan app:scrape-therapists

# Chạy với proxy list
php artisan app:scrape-therapists --proxy-list='["proxy1:port", "proxy2:port"]'
```

### Chạy như background service:
```bash
# Windows (PowerShell)
Start-Job -ScriptBlock { php artisan app:scrape-therapists }

# Linux/Unix
nohup php artisan app:scrape-therapists > scraping.log 2>&1 &
```

## 2. Command Import (Chạy mỗi phút)

**File:** `app/Console/Commands/ImportTherapistsData.php`  
**Command:** `php artisan app:import-therapists`

### Chức năng:
- Import dữ liệu từ JSON file vào database
- Đã được config để chạy mỗi phút qua Laravel Scheduler
- Process theo batch để tránh memory issues
- Duplicate checking dựa trên tên therapist
- Comprehensive logging

### Cách sử dụng:
```bash
# Import từ file mặc định
php artisan app:import-therapists

# Import từ file tùy chỉnh
php artisan app:import-therapists --file=/path/to/custom/file.json

# Thay đổi batch size
php artisan app:import-therapists --batch-size=50
```

## 3. Scheduler Setup

Laravel Scheduler đã được config trong `app/Console/Kernel.php`:

```php
// Import command chạy mỗi phút
$schedule->command('app:import-therapists')
    ->everyMinute()
    ->withoutOverlapping();
```

### Để kích hoạt scheduler:

**Windows:**
```batch
# Tạo scheduled task chạy mỗi phút
schtasks /create /tn "Laravel Scheduler" /tr "php c:\path\to\your\project\artisan schedule:run" /sc minute

# Hoặc sử dụng file batch có sẵn
.\schedule.bat
```

**Linux/Unix:**
```bash
# Thêm vào crontab
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## 4. Workflow hoàn chỉnh

1. **Bước 1:** Chạy scraping command (background):
   ```bash
   php artisan app:scrape-therapists
   ```

2. **Bước 2:** Đảm bảo scheduler đang chạy:
   ```bash
   # Test scheduler
   php artisan schedule:run
   
   # Hoặc chạy manual import
   php artisan app:import-therapists
   ```

## 5. Monitoring & Logging

Tất cả activities được log vào Laravel log system:

- **Scraping logs:** Chi tiết về Python script execution
- **Import logs:** Thống kê import (total, imported, skipped)
- **Error logs:** Chi tiết lỗi khi process fails

Xem logs:
```bash
tail -f storage/logs/laravel.log
```

## 6. Troubleshooting

### Python Script Issues:
- Kiểm tra Python environment và dependencies
- Verify script path: `scripts/main.py`
- Check Python executable path trong production

### Import Issues:
- Verify JSON file tồn tại: `scripts/therapists_result.json`
- Check database connection
- Review logs cho chi tiết lỗi

### Scheduler Issues:
```bash
# Test scheduler manually
php artisan schedule:list
php artisan schedule:run
```

## 7. Advanced Options

### Custom JSON Processing:
- Modify `prepareTherapistData()` method để customize field mapping
- Adjust `cleanValue()` và `cleanArray()` cho specific data cleaning rules

### Performance Tuning:
- Adjust batch size: `--batch-size` option
- Modify delay between scraping cycles
- Configure memory limits cho large datasets

### Multiple Sources:
- Extend scraping command để support multiple sources
- Modify data preparation để handle different data formats

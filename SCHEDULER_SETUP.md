# Hướng dẫn thiết lập Laravel Task Scheduler

## 1. Code đã được cài đặt
- Command `app:scrape-reins-data` đã được lên lịch chạy hàng ngày lúc 1:00 AM
- File `app/Console/Kernel.php` đã được cập nhật với lịch trình

## 2. Cách thức hoạt động
- Laravel Task Scheduler sẽ chạy command vào lúc 1:00 AM mỗi ngày
- `withoutOverlapping()` đảm bảo không có 2 instance chạy cùng lúc
- Log sẽ được ghi khi command thành công hoặc thất bại

## 3. Để thiết lập trên Windows (sử dụng Task Scheduler)

### Bước 1: Mở Windows Task Scheduler
- Nhấn Windows + R, gõ `taskschd.msc`

### Bước 2: Tạo Basic Task
- Click "Create Basic Task"
- Name: "Laravel Schedule Runner"
- Description: "Run Laravel task scheduler every minute"

### Bước 3: Trigger
- Chọn "Daily"
- Start time: 00:00:00 (hoặc bất kỳ thời gian nào)
- Repeat task every: 1 minute
- Duration: Indefinitely

### Bước 4: Action
- Start a program
- Program/script: `powershell.exe`
- Arguments: `-File "c:\laragon\www\blurtitout\schedule.ps1"`
- Start in: `c:\laragon\www\blurtitout`

## 4. Kiểm tra hoạt động
```bash
# Chạy thử scheduler để xem command có được nhận diện không
php artisan schedule:list

# Chạy thử scheduler
php artisan schedule:run

# Chạy trực tiếp command
php artisan app:scrape-reins-data
```

## 5. Xem logs
- Laravel logs: `storage/logs/laravel.log`
- Cron logs sẽ ghi thông tin về việc thực thi

## 6. Lưu ý quan trọng
- Task Scheduler của Windows phải chạy mỗi phút để Laravel scheduler hoạt động
- Laravel scheduler sẽ tự động kiểm tra và chỉ chạy các task đúng thời gian
- Đảm bảo PHP có thể chạy từ command line

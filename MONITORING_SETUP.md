# Website Monitoring Setup - Laravel 12

This document explains how the automatic website monitoring is configured using Laravel 12's native scheduling system.

## 🎯 Overview

The monitoring system automatically checks:
- **Website Status**: Every minute (HTTP 200 checks)
- **SSL Certificates**: Daily at 2 AM
- **Domain Validation**: Daily at 2 AM

## 📋 Laravel 12 Implementation

### Scheduled Tasks Configuration

Tasks are defined in `routes/console.php` (Laravel 12 standard):

```php
use Illuminate\Support\Facades\Schedule;

// Website Status Monitoring - Every minute
Schedule::command('websites:monitor-status')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// SSL/Domain Monitoring - Daily at 2 AM
Schedule::command('websites:monitor-domain-ssl')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground();
```

### Cron Configuration

Only **one** cron entry is needed (Laravel 12 best practice):

```bash
* * * * * cd /home/redalerts/html/redalertsv2 && php artisan schedule:run >> /dev/null 2>&1
```

## 🛠️ Management Commands

### Check Scheduled Tasks
```bash
php artisan schedule:list
```

### Run Scheduler Manually
```bash
php artisan schedule:run
```

### Monitor Specific Website
```bash
php artisan websites:monitor-status --website-id=7
```

### Check Monitoring Status
```bash
php artisan monitoring:status
```

### Run SSL Monitoring
```bash
php artisan websites:monitor-domain-ssl
```

## 🔍 Verification

### 1. Check if tasks are scheduled:
```bash
php artisan schedule:list
```

### 2. Verify cron job:
```bash
crontab -l
```

### 3. Test monitoring:
```bash
php artisan monitoring:status --website-id=7
```

## 📊 Features

- ✅ **Automatic monitoring** every minute
- ✅ **Background processing** (non-blocking)
- ✅ **Overlap prevention** (withoutOverlapping)
- ✅ **SSL certificate tracking**
- ✅ **Email notifications** (when configured)
- ✅ **Real-time status updates**
- ✅ **Professional logging**

## 🚀 Benefits of Laravel 12 Approach

1. **Native Laravel**: Uses built-in scheduling system
2. **Single cron entry**: Easier to manage
3. **Source control**: Schedule is in code, not server
4. **Background tasks**: Non-blocking execution
5. **Overlap prevention**: Prevents duplicate runs
6. **Professional**: Industry standard approach

## 🔧 Troubleshooting

### If monitoring stops working:

1. **Check cron job**:
   ```bash
   crontab -l
   ```

2. **Test scheduler**:
   ```bash
   php artisan schedule:run
   ```

3. **Check logs**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **Verify tasks**:
   ```bash
   php artisan schedule:list
   ```

This implementation follows Laravel 12 best practices and provides reliable, automatic website monitoring without shell scripts or complex configurations.

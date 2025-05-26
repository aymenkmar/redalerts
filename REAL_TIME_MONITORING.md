# Real-Time Website Monitoring

This document explains the real-time monitoring features implemented in the website monitoring system.

## 🔄 Auto-Refresh Features

### Automatic Updates
- **Polling Interval**: Every 30 seconds
- **Technology**: Livewire polling (`wire:poll.30s`)
- **No Page Refresh**: Updates happen seamlessly in the background
- **Real-time Status**: Website status changes automatically

### Visual Indicators
- **Green Pulse Dot**: Shows auto-refresh is active
- **Last Updated Time**: Displays when data was last refreshed
- **Manual Refresh Button**: Allows immediate updates

## 🎯 How It Works

### 1. Automatic Polling
```php
// Livewire component polls every 30 seconds
wire:poll.30s="refreshData"
```

### 2. Background Monitoring
```bash
# Cron job runs every minute
* * * * * cd /path && php artisan schedule:run
```

### 3. Status Updates
```php
// Laravel scheduler runs monitoring commands
Schedule::command('websites:monitor-status')->everyMinute()
```

## 📊 Real-Time Features

### Status Changes
- ✅ **Up to Down**: Automatically shows red status
- ❌ **Down to Up**: Automatically shows green status
- ⚠️ **Warning States**: Shows yellow for SSL warnings
- 🔄 **Instant Updates**: No manual refresh needed

### Email Notifications
- 📧 **Down Alerts**: Sent immediately when site goes offline
- ✅ **Recovery Alerts**: Sent when site comes back online
- ⏱️ **Real-time**: Notifications sent within 1-2 minutes

### Visual Updates
- 🟢 **Status Indicators**: Color-coded dots update automatically
- 📈 **Uptime Percentages**: Recalculated in real-time
- ⏰ **Last Checked**: Shows when each site was last monitored
- 🔄 **Response Times**: Updated with latest measurements

## 🛠️ Technical Implementation

### Livewire Polling
```php
public function refreshData()
{
    $this->lastRefresh = now()->format('H:i:s');
    // Render method automatically fetches fresh data
}
```

### Database Updates
```php
// Status updates trigger overall website status calculation
$websiteUrl->updateStatus($status, $errorMessage);
$this->website->updateOverallStatus();
```

### Automatic Monitoring
```php
// Scheduled tasks in routes/console.php
Schedule::command('websites:monitor-status')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
```

## 🎨 User Experience

### No Manual Refresh Needed
- **Automatic Updates**: Page updates every 30 seconds
- **Seamless Experience**: No page reloads or interruptions
- **Real-time Data**: Always shows current status
- **Instant Feedback**: Changes appear immediately

### Visual Feedback
- **Auto-refresh Indicator**: Blue banner shows system is active
- **Last Update Time**: Shows when data was last refreshed
- **Manual Refresh**: Button for immediate updates
- **Status Colors**: Green (up), Red (down), Yellow (warning)

### Performance Optimized
- **Background Updates**: Non-blocking operations
- **Efficient Polling**: Only updates changed data
- **Minimal Bandwidth**: Livewire sends only necessary updates
- **Fast Response**: 30-second refresh interval

## 🚀 Benefits

### For Users
- ✅ **Real-time Monitoring**: See status changes immediately
- ✅ **No Manual Work**: Automatic updates without intervention
- ✅ **Instant Alerts**: Email notifications within minutes
- ✅ **Professional UI**: Clean, modern interface

### For System
- ✅ **Reliable Monitoring**: Continuous background checks
- ✅ **Accurate Data**: Always up-to-date information
- ✅ **Efficient Processing**: Optimized for performance
- ✅ **Scalable Design**: Handles multiple websites easily

## 🔧 Configuration

### Polling Frequency
To change the auto-refresh interval, modify the Blade template:
```php
// Current: 30 seconds
wire:poll.30s="refreshData"

// Options:
wire:poll.10s="refreshData"  // 10 seconds
wire:poll.60s="refreshData"  // 60 seconds
wire:poll.5m="refreshData"   // 5 minutes
```

### Monitoring Frequency
To change monitoring frequency, modify `routes/console.php`:
```php
// Current: Every minute
Schedule::command('websites:monitor-status')->everyMinute()

// Options:
->everyTwoMinutes()
->everyFiveMinutes()
->everyTenMinutes()
```

## 📈 Performance

### Optimizations
- **Background Processing**: Monitoring runs in background
- **Efficient Queries**: Optimized database queries
- **Minimal Updates**: Only changed data is transmitted
- **Caching**: Intelligent caching for better performance

### Monitoring
- **Response Times**: Tracked for each check
- **Success Rates**: Calculated automatically
- **Uptime Percentages**: Updated in real-time
- **Historical Data**: Stored for analysis

This real-time monitoring system provides a professional, UptimeRobot-like experience with automatic updates and instant notifications!

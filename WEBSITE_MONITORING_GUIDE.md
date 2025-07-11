# 🌐 Website Monitoring System - Complete Guide

## 🎯 Overview

This comprehensive website monitoring system provides real-time monitoring of website status, domain validation, and SSL certificate checks with email notifications and detailed historical tracking.

### ✨ Key Features

- **HTTP Status Monitoring** - Every minute checks (200 = up, others = down)
- **Domain Expiration Monitoring** - Every 24 hours domain expiration date checking using whois
- **SSL Certificate Monitoring** - Every 24 hours certificate validity and expiration
- **Email Notifications** - Down/up alerts with beautiful HTML templates
- **Historical Tracking** - Complete logs and downtime incident tracking
- **Beautiful UI** - Inspired by UptimeRobot with your existing red theme
- **Multiple URLs per Website** - Monitor multiple endpoints per site
- **Flexible Email Lists** - Multiple notification emails per website

## 🚀 Getting Started

### 1. Access the System
- **Main Dashboard** → Click "Website Monitoring" card
- **Direct URL**: `/website-monitoring`

### 2. Add Your First Website
1. Click "Add Website" button
2. Fill in website information:
   - **Name**: Display name for your website
   - **Description**: Optional description
3. Add notification emails (can add multiple)
4. Add URLs with monitoring options:
   - ✅ **Status Monitoring**: HTTP 200 checks (every minute)
   - ✅ **Domain Validation**: DNS verification (every 24h)
   - ✅ **SSL Validation**: Certificate checks (every 24h)
5. Click "Save Website"

### 3. View Monitoring Results
- **Website List**: See all websites with status overview
- **History Page**: Click chart icon → View detailed logs and incidents
- **Real-time Updates**: Status changes appear automatically

## 📊 Monitoring Details

### Status Monitoring (Every Minute)
- **✅ Up**: HTTP 200 response
- **❌ Down**: Non-200 response or connection failure
- **Response Time**: Measured in milliseconds
- **Automatic Notifications**: Email sent when status changes

### Domain Expiration Monitoring (Every 24 Hours)
- **Whois Check**: Retrieves domain expiration date using whois command
- **✅ Up**: Domain expires in more than 30 days
- **⚠️ Warning**: Domain expires within 30 days
- **❌ Down**: Domain has expired
- **Email Notifications**: Sent when domain expires within 30 days

### SSL Certificate Monitoring (Every 24 Hours)
- **Certificate Validity**: Checks if certificate is valid
- **Expiration Tracking**: Days until expiration
- **⚠️ Warning**: Certificate expires within 7 days
- **❌ Down**: Certificate expired or invalid

## 📧 Email Notifications

### SMTP Configuration (Already Set Up)
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=7351bb8c12dabe
MAIL_PASSWORD=748cd566c2e166
MAIL_ENCRYPTION=tls
```

### Notification Types
1. **🔴 Website Down Alert**: Sent when website goes offline
2. **✅ Website Recovered**: Sent when website comes back online

### Email Features
- **Beautiful HTML Templates**: Professional design with your branding
- **Downtime Duration**: Shows how long the site was down
- **Error Details**: Specific error messages and status codes
- **Dashboard Links**: Direct links to view more details

## 🔧 Automation Setup

### Option 1: Supervisor (Recommended)
Copy the supervisor configuration:
```bash
sudo cp supervisor-website-monitoring.conf /etc/supervisor/conf.d/
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start website-monitoring:*
```

### Option 2: Cron Jobs
Add to crontab (`crontab -e`):
```bash
# Status monitoring every minute
* * * * * cd /home/redalerts/html/redalertsv2 && php artisan websites:monitor-status

# Domain/SSL monitoring every 24 hours at 2 AM
0 2 * * * cd /home/redalerts/html/redalertsv2 && php artisan websites:monitor-domain-ssl
```

## 🎨 User Interface

### Website List Page
- **Status Overview**: See all websites at a glance
- **Uptime Percentage**: Last 30 days uptime
- **Quick Actions**: Check now, view history, activate/deactivate
- **Search & Filter**: Find websites by name or status

### History Page
- **Two Tabs**: Monitoring Logs & Downtime Incidents
- **Advanced Filtering**:
  - URL selection
  - Check type (status/domain/ssl)
  - Status filter
  - Date range picker
  - Quick date buttons (1D, 7D, 30D, 90D)
- **Statistics Cards**: Total checks, uptime %, online/offline counts
- **Detailed Tables**: Complete monitoring history with response times

### Add Website Page
- **Multiple URLs**: Add as many URLs as needed per website
- **Multiple Emails**: Add multiple notification recipients
- **Flexible Monitoring**: Choose which checks to enable per URL
- **Clear Instructions**: Helpful text explaining each monitoring type

## 🛠️ Manual Commands

### Monitor Specific Website
```bash
php artisan websites:monitor-status --website-id=1
php artisan websites:monitor-domain-ssl --website-id=1
```

### Monitor All Websites
```bash
php artisan websites:monitor-status
php artisan websites:monitor-domain-ssl
```

### View Command Help
```bash
php artisan websites:monitor-status --help
php artisan websites:monitor-domain-ssl --help
```

## 📈 Database Structure

### Tables Created
- **websites**: Website information and notification emails
- **website_urls**: Individual URLs with monitoring settings
- **website_monitoring_logs**: Complete monitoring history
- **website_downtime_incidents**: Downtime tracking with duration

### Data Retention
- **Monitoring Logs**: Kept for 30 days (configurable)
- **Downtime Incidents**: Kept permanently for historical analysis
- **Automatic Cleanup**: Cron job removes old logs daily

## 🔍 Troubleshooting

### Common Issues

1. **Emails Not Sending**
   - Check SMTP settings in `.env`
   - Verify Mailtrap credentials
   - Check Laravel logs: `tail -f storage/logs/laravel.log`

2. **Monitoring Not Running**
   - Check supervisor status: `sudo supervisorctl status`
   - Verify cron jobs: `crontab -l`
   - Check command manually: `php artisan websites:monitor-status`

3. **SSL Checks Failing**
   - Ensure OpenSSL extension is enabled
   - Check if server can connect to HTTPS sites
   - Verify firewall settings

### Debug Commands
```bash
# Check website count
php artisan tinker --execute="echo App\Models\Website::count() . ' websites found';"

# Test single website
php artisan websites:monitor-status --website-id=1

# View recent logs
tail -f storage/logs/laravel.log
```

## 🎯 Best Practices

### Website Setup
- **Use descriptive names**: Make it easy to identify websites
- **Add multiple emails**: Ensure notifications reach the right people
- **Monitor critical paths**: Add important pages, not just homepages
- **Enable all checks**: Status, domain, and SSL for comprehensive monitoring

### Monitoring Strategy
- **Status checks**: Every minute for critical sites
- **Domain/SSL checks**: Daily is sufficient for most sites
- **Email management**: Use distribution lists for team notifications
- **Regular reviews**: Check history pages weekly for patterns

### Performance Tips
- **Batch monitoring**: System handles multiple sites efficiently
- **Reasonable timeouts**: 30-second timeout prevents hanging
- **Log rotation**: Automatic cleanup keeps database size manageable
- **Selective monitoring**: Disable unnecessary checks to reduce load

## 🔮 Future Enhancements

### Planned Features
- **Response Time Alerts**: Notify when sites are slow
- **Maintenance Windows**: Disable alerts during planned downtime
- **Status Page**: Public status page for your services
- **API Integration**: REST API for external integrations
- **Mobile App**: Native mobile notifications
- **Advanced Analytics**: Detailed performance metrics

This monitoring system provides enterprise-grade website monitoring with a beautiful, user-friendly interface. It's designed to be reliable, scalable, and easy to use while maintaining the aesthetic consistency of your existing platform.

# 🛡️ Trivy Security Integration Guide

## Overview

This guide covers the complete Trivy security scanning integration for the RedAlerts Kubernetes dashboard. The integration provides automated daily security scans for all clusters with a comprehensive web interface for viewing and downloading reports.

## ✨ Features

- **🔍 Automated Daily Scans**: All clusters are scanned daily at 3:00 AM
- **🖥️ Web Dashboard**: Beautiful security dashboard with vulnerability summaries
- **📊 Multiple Report Formats**: JSON and text summary reports
- **📥 Download Functionality**: Download reports directly from the web interface
- **⚡ Manual Scans**: Trigger scans manually when needed
- **📈 Historical Data**: View scan history for each cluster
- **🎯 Severity Indicators**: Visual indicators for vulnerability severity levels
- **🔄 Real-time Updates**: Dashboard updates automatically during scans

## 🚀 Getting Started

### 1. Access the Security Dashboard

1. Navigate to your Kubernetes dashboard
2. Look for the **🛡️ Security** button in the sidebar
3. Click to access the security dashboard

### 2. View Security Reports

- **Latest Report**: Shows the most recent scan results for the selected cluster
- **Vulnerability Counts**: Displays critical, high, medium, low, and unknown vulnerabilities
- **Security Level**: Overall security assessment based on highest severity found
- **Scan History**: View previous scan results

### 3. Download Reports

- **JSON Report**: Complete Trivy output in JSON format
- **Summary Report**: Human-readable text summary

## 🔧 Manual Operations

### Command Line Interface

```bash
# Scan a specific cluster
php artisan security:scan <cluster-name>

# Scan all clusters
php artisan security:scan --all

# Force scan (even if one is running)
php artisan security:scan --all --force

# Cleanup old reports
php artisan security:scan --cleanup

# View available options
php artisan security:scan --help
```

### Examples

```bash
# Scan the Convergence cluster
php artisan security:scan Convergence

# Scan all clusters and cleanup old reports
php artisan security:scan --all --cleanup

# Force scan all clusters (override running scans)
php artisan security:scan --all --force
```

## 📅 Automated Scheduling

### Daily Scans

- **Schedule**: Every day at 3:00 AM
- **Command**: `php artisan security:scan --all --cleanup`
- **Features**:
  - Scans all available clusters
  - Cleans up reports older than 30 days
  - Runs in background without overlapping
  - Maintains latest report for each cluster

### Cron Job Setup

The Laravel scheduler handles all timing. Ensure this cron job exists:

```bash
* * * * * cd /path/to/redalertsv2 && php artisan schedule:run >> /dev/null 2>&1
```

## 📁 File Structure

```
storage/app/security-reports/
├── {cluster_name}/
│   ├── {timestamp}_scan.json          # Full JSON report
│   ├── {timestamp}_summary.txt        # Text summary
│   ├── {timestamp}_formatted_summary.txt  # Formatted summary
│   └── latest/                        # Symlinks to latest reports
│       ├── scan.json
│       ├── summary.txt
│       └── formatted_summary.txt
└── README.md
```

## 🔍 Understanding Reports

### Vulnerability Severity Levels

- **🔴 Critical**: Immediate attention required
- **🟠 High**: Should be addressed soon
- **🟡 Medium**: Moderate risk
- **🔵 Low**: Minor risk
- **⚪ Unknown**: Severity not determined

### Security Level Assessment

- **Critical**: One or more critical vulnerabilities found
- **High**: High-severity vulnerabilities (no critical)
- **Medium**: Medium-severity vulnerabilities (no high/critical)
- **Low**: Only low-severity vulnerabilities
- **None**: No vulnerabilities found ✅

## 🛠️ Troubleshooting

### Common Issues

1. **Scan Timeout**
   - Large clusters may take 30+ minutes
   - Check logs: `tail -f storage/logs/laravel.log`
   - Increase timeout if needed

2. **Missing Reports**
   - Verify Trivy is installed: `trivy --version`
   - Check kubeconfig files exist in `storage/app/kubeconfigs/`
   - Verify permissions on security-reports directory

3. **Cron Not Running**
   - Check cron job: `crontab -l`
   - Verify Laravel scheduler: `php artisan schedule:list`
   - Check system cron service: `systemctl status cron`

### Log Files

- **Laravel Logs**: `storage/logs/laravel.log`
- **Trivy Scan Logs**: `storage/logs/trivy-scan.log`
- **Error Logs**: `storage/app/security-reports/{cluster}/error.log`

## 🔧 Configuration

### Environment Variables

```bash
# Trivy binary path (default: trivy)
TRIVY_BINARY_PATH=/usr/local/bin/trivy

# Kubeconfig directory (default: storage/app/kubeconfigs)
KUBECONFIG_PATH=/path/to/kubeconfigs
```

### Customization

- **Scan Schedule**: Modify `routes/console.php`
- **Report Retention**: Change cleanup days in `TrivySecurityService`
- **Timeout Settings**: Adjust in `TrivySecurityService` and shell script

## 📊 API Endpoints

For programmatic access:

```bash
# Get latest report for cluster
GET /api/security/{clusterName}/latest

# Get scan history
GET /api/security/{clusterName}/history

# Start manual scan
POST /api/security/scan

# Download report
GET /api/security/report/{reportId}/download?format=json

# Security overview (all clusters)
GET /api/security/overview
```

## 🎯 Best Practices

1. **Regular Monitoring**: Check the dashboard weekly
2. **Address Critical Issues**: Prioritize critical and high-severity vulnerabilities
3. **Keep Trivy Updated**: Update Trivy regularly for latest vulnerability data
4. **Monitor Scan Performance**: Large clusters may need timeout adjustments
5. **Backup Reports**: Important reports should be backed up externally

## 🔒 Security Considerations

- Reports contain sensitive vulnerability information
- Access is restricted through Laravel authentication
- Files are stored outside the web root
- API endpoints require authentication
- Consider encrypting stored reports for highly sensitive environments

## 📞 Support

For issues or questions:

1. Check the troubleshooting section above
2. Review Laravel logs for error details
3. Verify Trivy installation and cluster connectivity
4. Ensure proper file permissions

## 🎉 Success!

Your Trivy security integration is now fully operational! The system will:

- ✅ Automatically scan all clusters daily
- ✅ Provide a beautiful web interface for viewing results
- ✅ Allow manual scans when needed
- ✅ Maintain historical data
- ✅ Enable easy report downloads
- ✅ Clean up old data automatically

Navigate to the **🛡️ Security** section in your Kubernetes dashboard to start using the security features!

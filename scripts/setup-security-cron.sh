#!/bin/bash

# Setup Security Scanning Cron Job for RedAlerts
# This script sets up the Laravel scheduler to run security scans

set -euo pipefail

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
CRON_USER="${CRON_USER:-www-data}"
PHP_BINARY="${PHP_BINARY:-/usr/bin/php}"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $*"
}

success() {
    echo -e "${GREEN}✅ $*${NC}"
}

warning() {
    echo -e "${YELLOW}⚠️  $*${NC}"
}

error() {
    echo -e "${RED}❌ $*${NC}"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    error "This script must be run as root (use sudo)"
    exit 1
fi

log "Setting up Security Scanning Cron Job for RedAlerts"
log "=================================================="

# Validate project structure
if [ ! -f "$PROJECT_ROOT/artisan" ]; then
    error "Laravel artisan file not found. Please run this script from the project directory."
    exit 1
fi

# Check if PHP is available
if ! command -v "$PHP_BINARY" &> /dev/null; then
    error "PHP binary not found at $PHP_BINARY"
    exit 1
fi

# Check if Trivy is installed
if ! command -v trivy &> /dev/null; then
    warning "Trivy not found in PATH. Please ensure Trivy is installed and accessible."
    warning "You can install Trivy from: https://trivy.dev/v0.28.1/docs/getting-started/installation/"
fi

# Create cron job entry
CRON_COMMAND="* * * * * cd $PROJECT_ROOT && $PHP_BINARY artisan schedule:run >> /dev/null 2>&1"

log "Creating cron job for user: $CRON_USER"
log "Command: $CRON_COMMAND"

# Check if cron job already exists
if crontab -u "$CRON_USER" -l 2>/dev/null | grep -q "artisan schedule:run"; then
    warning "Laravel scheduler cron job already exists for user $CRON_USER"
    log "Current crontab for $CRON_USER:"
    crontab -u "$CRON_USER" -l 2>/dev/null | grep "artisan schedule:run" || true
else
    # Add cron job
    (crontab -u "$CRON_USER" -l 2>/dev/null; echo "$CRON_COMMAND") | crontab -u "$CRON_USER" -
    success "Laravel scheduler cron job added for user $CRON_USER"
fi

# Create log directory
LOG_DIR="$PROJECT_ROOT/storage/logs"
mkdir -p "$LOG_DIR"
chown -R "$CRON_USER:$CRON_USER" "$LOG_DIR"
success "Log directory created and permissions set"

# Create security reports directory
SECURITY_DIR="$PROJECT_ROOT/storage/app/security-reports"
mkdir -p "$SECURITY_DIR"
chown -R "$CRON_USER:$CRON_USER" "$SECURITY_DIR"
chmod 755 "$SECURITY_DIR"
success "Security reports directory created and permissions set"

# Set permissions for scripts
chmod +x "$PROJECT_ROOT/scripts/trivy-scan.sh"
success "Script permissions set"

# Test the artisan command
log "Testing artisan command..."
if sudo -u "$CRON_USER" "$PHP_BINARY" "$PROJECT_ROOT/artisan" list | grep -q "security:scan"; then
    success "Security scan command is available"
else
    error "Security scan command not found. Please check the Laravel installation."
    exit 1
fi

# Create systemd service for better process management (optional)
SYSTEMD_SERVICE="/etc/systemd/system/redalerts-security-scan.service"
if [ ! -f "$SYSTEMD_SERVICE" ]; then
    log "Creating systemd service for security scanning..."
    
    cat > "$SYSTEMD_SERVICE" << EOF
[Unit]
Description=RedAlerts Security Scan Service
After=network.target

[Service]
Type=oneshot
User=$CRON_USER
WorkingDirectory=$PROJECT_ROOT
ExecStart=$PHP_BINARY $PROJECT_ROOT/artisan security:scan --all --cleanup
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
EOF

    systemctl daemon-reload
    success "Systemd service created: $SYSTEMD_SERVICE"
    log "You can manually run scans with: systemctl start redalerts-security-scan"
fi

# Create systemd timer for daily execution (alternative to cron)
SYSTEMD_TIMER="/etc/systemd/system/redalerts-security-scan.timer"
if [ ! -f "$SYSTEMD_TIMER" ]; then
    log "Creating systemd timer for daily security scanning..."
    
    cat > "$SYSTEMD_TIMER" << EOF
[Unit]
Description=Run RedAlerts Security Scan Daily
Requires=redalerts-security-scan.service

[Timer]
OnCalendar=daily
Persistent=true
RandomizedDelaySec=300

[Install]
WantedBy=timers.target
EOF

    systemctl daemon-reload
    success "Systemd timer created: $SYSTEMD_TIMER"
    log "To enable the timer: systemctl enable --now redalerts-security-scan.timer"
fi

# Display setup summary
log ""
log "🎉 Security Scanning Setup Complete!"
log "===================================="
log ""
log "📋 What was configured:"
log "   ✅ Laravel scheduler cron job for user: $CRON_USER"
log "   ✅ Security reports directory: $SECURITY_DIR"
log "   ✅ Log directory: $LOG_DIR"
log "   ✅ Script permissions set"
log "   ✅ Systemd service: $SYSTEMD_SERVICE"
log "   ✅ Systemd timer: $SYSTEMD_TIMER"
log ""
log "📅 Scheduled Tasks:"
log "   🕒 Daily at 03:00 AM: Full security scan of all clusters"
log "   🧹 Automatic cleanup of old reports (30+ days)"
log ""
log "🔧 Manual Commands:"
log "   Scan all clusters:     php artisan security:scan --all"
log "   Scan specific cluster: php artisan security:scan <cluster-name>"
log "   Force scan:           php artisan security:scan --all --force"
log "   Cleanup old reports:  php artisan security:scan --cleanup"
log ""
log "🔍 Monitoring:"
log "   Check cron jobs:      crontab -u $CRON_USER -l"
log "   View logs:           tail -f $LOG_DIR/laravel.log"
log "   Check systemd timer:  systemctl status redalerts-security-scan.timer"
log ""
log "⚠️  Important Notes:"
log "   - Ensure Trivy is installed and accessible in PATH"
log "   - Verify kubeconfig files are present in storage/app/kubeconfigs/"
log "   - Monitor the first few runs to ensure everything works correctly"
log "   - Large clusters may take 30+ minutes to scan"
log ""

success "Setup completed successfully! 🚀"

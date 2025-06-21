#!/bin/bash

# Trivy Security Scanner Script for RedAlerts
# This script runs Trivy scans for Kubernetes clusters
# Usage: ./trivy-scan.sh <cluster_name> <kubeconfig_path> <output_dir>

set -euo pipefail

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
TRIVY_BINARY="${TRIVY_BINARY_PATH:-trivy}"
LOG_FILE="$PROJECT_ROOT/storage/logs/trivy-scan.log"

# Ensure log directory exists
mkdir -p "$(dirname "$LOG_FILE")"

# Logging function
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*" | tee -a "$LOG_FILE"
}

# Error handling
error_exit() {
    log "ERROR: $1"
    exit 1
}

# Validate arguments
if [ $# -ne 3 ]; then
    error_exit "Usage: $0 <cluster_name> <kubeconfig_path> <output_dir>"
fi

CLUSTER_NAME="$1"
KUBECONFIG_PATH="$2"
OUTPUT_DIR="$3"
TIMESTAMP=$(date '+%Y-%m-%d_%H-%M-%S')

# Validate inputs
[ -f "$KUBECONFIG_PATH" ] || error_exit "Kubeconfig file not found: $KUBECONFIG_PATH"
[ -d "$OUTPUT_DIR" ] || mkdir -p "$OUTPUT_DIR"

# Define output files
JSON_OUTPUT="$OUTPUT_DIR/${TIMESTAMP}_scan.json"
SUMMARY_OUTPUT="$OUTPUT_DIR/${TIMESTAMP}_summary.txt"
ERROR_OUTPUT="$OUTPUT_DIR/${TIMESTAMP}_error.log"

log "Starting Trivy scan for cluster: $CLUSTER_NAME"
log "Kubeconfig: $KUBECONFIG_PATH"
log "Output directory: $OUTPUT_DIR"

# Check Trivy availability
if ! command -v "$TRIVY_BINARY" &> /dev/null; then
    error_exit "Trivy binary not found: $TRIVY_BINARY"
fi

# Get Trivy version
TRIVY_VERSION=$($TRIVY_BINARY --version 2>/dev/null | head -n1 || echo "Unknown")
log "Trivy version: $TRIVY_VERSION"

# Set timeout (1 hour)
TIMEOUT=3600

# Run Trivy scan
log "Executing Trivy scan..."
START_TIME=$(date +%s)

# Run namespace-based scanning to avoid timeouts
log "Starting namespace-based scanning to avoid timeouts..."

# Get all namespaces
NAMESPACES=$(kubectl --kubeconfig "$KUBECONFIG_PATH" get ns -o jsonpath='{.items[*].metadata.name}' 2>/dev/null)
if [ $? -ne 0 ] || [ -z "$NAMESPACES" ]; then
    log "Failed to get namespaces, falling back to full cluster scan"
    NAMESPACES="__FULL_CLUSTER__"
fi

# Initialize combined results
echo '{"Findings": []}' > "$JSON_OUTPUT"
echo "" > "$SUMMARY_OUTPUT"

TOTAL_CRITICAL=0
TOTAL_HIGH=0
TOTAL_MEDIUM=0
TOTAL_LOW=0
TOTAL_UNKNOWN=0

# Scan each namespace separately
for ns in $NAMESPACES; do
    if [ "$ns" = "__FULL_CLUSTER__" ]; then
        log "Scanning full cluster (fallback mode)..."
        NS_JSON_OUTPUT="${JSON_OUTPUT}.full"
        NS_SUMMARY_OUTPUT="${SUMMARY_OUTPUT}.full"

        timeout $TIMEOUT "$TRIVY_BINARY" k8s \
            --kubeconfig "$KUBECONFIG_PATH" \
            --format json \
            --output "$NS_JSON_OUTPUT" \
            --report summary \
            --timeout 30m \
            2>> "$ERROR_OUTPUT"
    else
        log "Scanning namespace: $ns"
        NS_JSON_OUTPUT="${JSON_OUTPUT}.${ns}"
        NS_SUMMARY_OUTPUT="${SUMMARY_OUTPUT}.${ns}"

        timeout $TIMEOUT "$TRIVY_BINARY" k8s \
            --kubeconfig "$KUBECONFIG_PATH" \
            --include-namespaces "$ns" \
            --format json \
            --output "$NS_JSON_OUTPUT" \
            --report summary \
            --timeout 30m \
            --disable-node-collector \
            2>> "$ERROR_OUTPUT"

        # Also generate summary for this namespace
        timeout $TIMEOUT "$TRIVY_BINARY" k8s \
            --kubeconfig "$KUBECONFIG_PATH" \
            --include-namespaces "$ns" \
            --report summary \
            --timeout 30m \
            --disable-node-collector \
            >> "$NS_SUMMARY_OUTPUT" 2>> "$ERROR_OUTPUT"
    fi

    # Append namespace summary to main summary
    if [ -f "$NS_SUMMARY_OUTPUT" ]; then
        echo "🔍 Scanning namespace: $ns" >> "$SUMMARY_OUTPUT"
        cat "$NS_SUMMARY_OUTPUT" >> "$SUMMARY_OUTPUT"
        echo "✅ Done scanning namespace: $ns" >> "$SUMMARY_OUTPUT"
        echo "-------------------------------------" >> "$SUMMARY_OUTPUT"
    fi
done

# Combine all JSON reports
log "Combining namespace scan results..."
python3 -c "
import json
import glob
import sys

combined = {'Findings': []}
pattern = '${JSON_OUTPUT}.*'

for file in glob.glob(pattern):
    if file.endswith('.json'):
        continue
    try:
        with open(file, 'r') as f:
            data = json.load(f)
            if 'Findings' in data:
                combined['Findings'].extend(data['Findings'])
    except Exception as e:
        print(f'Error processing {file}: {e}', file=sys.stderr)

with open('${JSON_OUTPUT}', 'w') as f:
    json.dump(combined, f, indent=2)
" 2>> "$ERROR_OUTPUT"

if [ $? -eq 0 ]; then
    
    END_TIME=$(date +%s)
    DURATION=$((END_TIME - START_TIME))
    
    log "Scan completed successfully in ${DURATION} seconds"
    
    # Parse vulnerability counts from combined JSON (Kubernetes scan structure)
    if [ -f "$JSON_OUTPUT" ] && command -v jq &> /dev/null; then
        log "Parsing vulnerability counts from combined results..."

        # For Kubernetes scans, vulnerabilities are in .Findings[].Results[].Vulnerabilities[]
        CRITICAL=$(jq '[.Findings[]?.Results[]?.Vulnerabilities[]? | select(.Severity == "CRITICAL")] | length' "$JSON_OUTPUT" 2>/dev/null || echo 0)
        HIGH=$(jq '[.Findings[]?.Results[]?.Vulnerabilities[]? | select(.Severity == "HIGH")] | length' "$JSON_OUTPUT" 2>/dev/null || echo 0)
        MEDIUM=$(jq '[.Findings[]?.Results[]?.Vulnerabilities[]? | select(.Severity == "MEDIUM")] | length' "$JSON_OUTPUT" 2>/dev/null || echo 0)
        LOW=$(jq '[.Findings[]?.Results[]?.Vulnerabilities[]? | select(.Severity == "LOW")] | length' "$JSON_OUTPUT" 2>/dev/null || echo 0)
        UNKNOWN=$(jq '[.Findings[]?.Results[]?.Vulnerabilities[]? | select(.Severity == "UNKNOWN")] | length' "$JSON_OUTPUT" 2>/dev/null || echo 0)

        # Also count misconfigurations as they are security issues too
        CRITICAL_MISCONF=$(jq '[.Findings[]?.Results[]?.Misconfigurations[]? | select(.Severity == "CRITICAL")] | length' "$JSON_OUTPUT" 2>/dev/null || echo 0)
        HIGH_MISCONF=$(jq '[.Findings[]?.Results[]?.Misconfigurations[]? | select(.Severity == "HIGH")] | length' "$JSON_OUTPUT" 2>/dev/null || echo 0)
        MEDIUM_MISCONF=$(jq '[.Findings[]?.Results[]?.Misconfigurations[]? | select(.Severity == "MEDIUM")] | length' "$JSON_OUTPUT" 2>/dev/null || echo 0)
        LOW_MISCONF=$(jq '[.Findings[]?.Results[]?.Misconfigurations[]? | select(.Severity == "LOW")] | length' "$JSON_OUTPUT" 2>/dev/null || echo 0)
        UNKNOWN_MISCONF=$(jq '[.Findings[]?.Results[]?.Misconfigurations[]? | select(.Severity == "UNKNOWN")] | length' "$JSON_OUTPUT" 2>/dev/null || echo 0)

        # Combine vulnerabilities and misconfigurations
        CRITICAL=$((CRITICAL + CRITICAL_MISCONF))
        HIGH=$((HIGH + HIGH_MISCONF))
        MEDIUM=$((MEDIUM + MEDIUM_MISCONF))
        LOW=$((LOW + LOW_MISCONF))
        UNKNOWN=$((UNKNOWN + UNKNOWN_MISCONF))
        TOTAL=$((CRITICAL + HIGH + MEDIUM + LOW + UNKNOWN))

        log "Vulnerability summary: Critical=$CRITICAL, High=$HIGH, Medium=$MEDIUM, Low=$LOW, Unknown=$UNKNOWN, Total=$TOTAL"
        log "  - Pure vulnerabilities: C=$((CRITICAL-CRITICAL_MISCONF)), H=$((HIGH-HIGH_MISCONF)), M=$((MEDIUM-MEDIUM_MISCONF)), L=$((LOW-LOW_MISCONF)), U=$((UNKNOWN-UNKNOWN_MISCONF))"
        log "  - Misconfigurations: C=$CRITICAL_MISCONF, H=$HIGH_MISCONF, M=$MEDIUM_MISCONF, L=$LOW_MISCONF, U=$UNKNOWN_MISCONF"

        # Clean up temporary namespace files
        log "Cleaning up temporary namespace files..."
        rm -f "${JSON_OUTPUT}".* "${SUMMARY_OUTPUT}".* 2>/dev/null
        
        # Create summary file with counts
        {
            echo "=== Trivy Security Scan Summary ==="
            echo "Cluster: $CLUSTER_NAME"
            echo "Scan Date: $(date)"
            echo "Duration: ${DURATION} seconds"
            echo "Trivy Version: $TRIVY_VERSION"
            echo ""
            echo "Vulnerability Counts:"
            echo "  Critical: $CRITICAL"
            echo "  High: $HIGH"
            echo "  Medium: $MEDIUM"
            echo "  Low: $LOW"
            echo "  Unknown: $UNKNOWN"
            echo "  Total: $TOTAL"
            echo ""
            echo "=== Detailed Report ==="
            cat "$SUMMARY_OUTPUT"
        } > "${OUTPUT_DIR}/${TIMESTAMP}_formatted_summary.txt"
    fi
    
    # Create latest symlinks
    LATEST_DIR="$OUTPUT_DIR/latest"
    mkdir -p "$LATEST_DIR"
    
    # Remove old symlinks
    rm -f "$LATEST_DIR"/{scan.json,summary.txt,formatted_summary.txt}
    
    # Create new symlinks
    ln -s "../${TIMESTAMP}_scan.json" "$LATEST_DIR/scan.json"
    ln -s "../${TIMESTAMP}_summary.txt" "$LATEST_DIR/summary.txt"
    ln -s "../${TIMESTAMP}_formatted_summary.txt" "$LATEST_DIR/formatted_summary.txt"
    
    log "Latest symlinks created in $LATEST_DIR"
    
    # Output success status for PHP to parse
    echo "SUCCESS:$DURATION:$CRITICAL:$HIGH:$MEDIUM:$LOW:$UNKNOWN:$TOTAL"
    
else
    END_TIME=$(date +%s)
    DURATION=$((END_TIME - START_TIME))
    
    log "Scan failed after ${DURATION} seconds"
    
    if [ -f "$ERROR_OUTPUT" ]; then
        log "Error output:"
        cat "$ERROR_OUTPUT" | tee -a "$LOG_FILE"
    fi
    
    # Output failure status for PHP to parse
    echo "FAILURE:$DURATION:$(cat "$ERROR_OUTPUT" 2>/dev/null | head -n1 || echo 'Unknown error')"
    exit 1
fi

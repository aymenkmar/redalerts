#!/bin/bash

# Update kubectl-ai binary script
# This script downloads the latest kubectl-ai binary and updates the project copy

set -e

echo "🔄 Updating kubectl-ai binary..."

# Get the directory where this script is located
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BINARY_PATH="$SCRIPT_DIR/kubectl-ai"

# Backup current binary
if [ -f "$BINARY_PATH" ]; then
    echo "📦 Backing up current binary..."
    cp "$BINARY_PATH" "$BINARY_PATH.backup.$(date +%Y%m%d_%H%M%S)"
fi

# Download latest kubectl-ai
echo "⬇️  Downloading latest kubectl-ai..."
cd /tmp
curl -sSL https://raw.githubusercontent.com/GoogleCloudPlatform/kubectl-ai/main/install.sh | bash

# Copy to project location
echo "📁 Copying to project location..."
cp /usr/local/bin/kubectl-ai "$BINARY_PATH"
chmod +x "$BINARY_PATH"

# Verify installation
echo "✅ Verifying installation..."
"$BINARY_PATH" version

# Update README with new version info
VERSION_INFO=$("$BINARY_PATH" version)
echo "📝 Updating README..."
cat > "$SCRIPT_DIR/README.md" << EOF
# kubectl-ai Google Binary

This folder contains the kubectl-ai binary from Google Cloud Platform for the redalertsv2 project.

## Contents

- \`kubectl-ai\` - The main binary executable
- \`update-kubectl-ai.sh\` - Script to update the binary

## Source

Downloaded from: https://github.com/GoogleCloudPlatform/kubectl-ai
Updated: $(date)

## Usage

This binary is used by the redalertsv2 application to provide AI-powered Kubernetes assistance through the chatbot widget.

## Installation Details

- Original location: \`/usr/local/bin/kubectl-ai\`
- Project location: \`storage/kubectl-ai-google/kubectl-ai\`
- Permissions: Executable (755)

## Version Info

\`\`\`
$VERSION_INFO
\`\`\`

## License

This binary is subject to the Apache-2.0 license from the original kubectl-ai project.

## Updating

To update the binary, run:
\`\`\`bash
./storage/kubectl-ai-google/update-kubectl-ai.sh
\`\`\`
EOF

echo "🎉 kubectl-ai binary updated successfully!"
echo "📍 Location: $BINARY_PATH"
echo "📋 Version: $VERSION_INFO"

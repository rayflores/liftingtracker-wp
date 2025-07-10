#!/bin/bash

# LiftingTracker Pro WordPress Theme Setup Script

echo "🏋️‍♂️ LiftingTracker Pro WordPress Theme Setup"
echo "=============================================="
echo ""

# Check if we're in WordPress themes directory
if [ ! -d "../../plugins" ]; then
    echo "❌ Error: This script should be run from the WordPress themes directory"
    echo "   Expected location: /wp-content/themes/"
    exit 1
fi

echo "📁 Setting up theme directory structure..."

# Create missing directories
mkdir -p assets/js
mkdir -p assets/css
mkdir -p assets/images
mkdir -p includes
mkdir -p templates

echo "✅ Directory structure created"

# Set permissions (if on Linux/Mac)
if [[ "$OSTYPE" == "linux-gnu"* ]] || [[ "$OSTYPE" == "darwin"* ]]; then
    echo "🔒 Setting file permissions..."
    chmod -R 755 .
    echo "✅ File permissions set"
fi

echo ""
echo "🎯 Setup Complete!"
echo ""
echo "Next steps:"
echo "1. Install Node.js dependencies:"
echo "   npm install"
echo ""
echo "2. Build the assets:"
echo "   npm run build"
echo ""
echo "3. For development with watch mode:"
echo "   npm run start"
echo ""
echo "4. Go to WordPress Admin → Appearance → Themes"
echo "5. Activate 'LiftingTracker Pro' theme"
echo "6. Go to Settings → Stripe Settings to configure payments"
echo "7. Create required pages (Dashboard, Sign Up, etc.)"
echo ""
echo "📖 See README.md for detailed installation instructions"
echo ""
echo "🚀 Your fitness tracking platform is ready!"

#!/bin/bash

# Video AI Frame Extraction Service - Startup Script
# Usage: ./start.sh

echo "════════════════════════════════════════════════════════════════"
echo "  Starting Video AI Frame Extraction Service"
echo "════════════════════════════════════════════════════════════════"

# Get script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

# Activate virtual environment
echo "📦 Activating virtual environment..."
source venv/bin/activate

# Check if activation worked
if [ -z "$VIRTUAL_ENV" ]; then
    echo "❌ ERROR: Failed to activate virtual environment"
    echo "   Run: python3.10 -m venv venv"
    exit 1
fi

echo "✅ Virtual environment activated: $VIRTUAL_ENV"

# Check Python version
PYTHON_VERSION=$(python --version 2>&1 | awk '{print $2}')
echo "🐍 Python version: $PYTHON_VERSION"

# Install/upgrade dependencies if needed
if [ "$1" == "--install" ] || [ "$1" == "-i" ]; then
    echo "📥 Installing dependencies..."
    pip install --upgrade pip
    pip install -r requirements.txt
fi

# Set environment variables
export PYTHONPATH="$SCRIPT_DIR:$PYTHONPATH"
export CUDA_VISIBLE_DEVICES=""  # Force CPU mode (no GPU)

# Create necessary directories
mkdir -p uploads outputs logs models

# Check if port 8001 is already in use
PORT_CHECK=$(netstat -tuln 2>/dev/null | grep ':8001 ' || lsof -ti:8001 2>/dev/null)
if [ ! -z "$PORT_CHECK" ]; then
    echo "⚠️  WARNING: Port 8001 is already in use"
    echo "   Kill existing process? (y/n)"
    read -r response
    if [ "$response" == "y" ]; then
        echo "🔪 Killing process on port 8001..."
        sudo kill -9 $(sudo lsof -t -i:8001) 2>/dev/null || true
        sleep 2
    else
        echo "❌ Cannot start service - port 8001 is occupied"
        exit 1
    fi
fi

# Start the service
echo "════════════════════════════════════════════════════════════════"
echo "🚀 Starting FastAPI service on http://0.0.0.0:8001"
echo "════════════════════════════════════════════════════════════════"
echo ""
echo "Press CTRL+C to stop the service"
echo ""

# Start with uvicorn (2 workers for better performance)
uvicorn app.main:app \
    --host 0.0.0.0 \
    --port 8001 \
    --workers 2 \
    --log-level info \
    --access-log

# Note: For production, use:
# uvicorn app.main:app --host 0.0.0.0 --port 8001 --workers 4 &

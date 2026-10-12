#!/bin/bash

# Configuration
BASE_URL="http://127.0.0.1:8088"
TIMEOUT=5
MAX_RETRIES=10

echo "================================================"
echo "   Stack Health Verification System (TDD)"
echo "================================================"

verify_endpoint() {
    local name=$1
    local path=$2
    local expected_content=$3
    local url="${BASE_URL}${path}"

    echo -n "Checking $name ($url)... "
    
    local response
    response=$(curl -s -L --max-time $TIMEOUT "$url")
    local status=$?

    if [ $status -ne 0 ]; then
        echo "FAILED (Connection Refused)"
        return 1
    fi

    if echo "$response" | grep -q "$expected_content"; then
        echo "OK"
        return 0
    else
        echo "FAILED (Unexpected Content)"
        return 1
    fi
}

echo "Starting tests..."
echo "Wait for services to initialize..."
sleep 5

FAILED=0

# Test Frontend Dev
verify_endpoint "FE Dev" "/dev/" "<title>" || FAILED=1

# Test Frontend Prod
verify_endpoint "FE Prod" "/prod/" "<title>" || FAILED=1

# Test API Dev
verify_endpoint "API Dev Health" "/dev/api/health" "ok" || FAILED=1

# Test API Prod
verify_endpoint "API Prod Health" "/prod/api/health" "ok" || FAILED=1

# Test Nginx Gateway
verify_endpoint "Nginx Gateway" "/dev/" "dev" || FAILED=1

echo "================================================"
if [ $FAILED -eq 0 ]; then
    echo "SUCCESS: All critical services are UP and HEALTHY."
    exit 0
else
    echo "ERROR: One or more services are DOWN or UNSTABLE."
    exit 1
fi

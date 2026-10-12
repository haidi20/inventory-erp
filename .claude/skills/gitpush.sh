#!/bin/bash

# Skill: gitpush
# Deskripsi: Check type, jalankan test yg sudah dibuat, kemudian git push dengan message standar
# Penggunaan: ./gitpush.sh

set -e

echo "🔍 Step 1: TypeScript Type Checking..."
cd src
npm run typecheck
echo "✅ Type checking passed"

echo ""
echo "🧪 Step 2: Running Modified Tests Only..."
echo "Running: DispensingRepositoryTest, ShipRepositoryTest"
php artisan test --filter="DispensingRepositoryTest|ShipRepositoryTest" -q
echo "✅ All modified tests passed"

echo ""
echo "📝 Step 3: Preparing Git Push..."
echo ""
echo "Changed files (excluding .txt):"
git diff --name-only HEAD | grep -v '\.txt$' || echo "No changes (excluding .txt)"

echo ""
echo "✅ All checks passed! Ready for git push."
echo ""
echo "📌 Next: Execute git push with standard message"
echo ""
echo "Standard commit message format (Indonesian):"
echo "[Feat/Fix/Refactor] Multi-company rollout - module isolation"
echo ""
echo "Changes included in this commit:"
echo "- Dispensing repository company_uuid filtering (TDD with 3 tests)"
echo "- Ship/Shift repository company isolation verification (2 tests)"
echo "- Entity updates with company scoping fields"
echo "- Comprehensive test coverage for company data isolation"

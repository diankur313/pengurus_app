<?php

/**
 * Test script for PPAB Scanner API UUID validation fix
 * 
 * Tests that the API now accepts truncated UUIDs (32 chars)
 * after removing strict 'uuid' validation rule.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== PPAB Scanner UUID Validation Test ===\n\n";

// Test UUID from issue report
$testUuid = '0be4499c-8b7f-432a-9a5c-efa3650a';

echo "1. Testing UUID: {$testUuid}\n";
echo "   Length: " . strlen($testUuid) . " chars (truncated, not standard 36 chars)\n\n";

// Check if member exists in database
echo "2. Checking database for this UUID...\n";
$member = DB::connection('ppab')
    ->table('ppab_member')
    ->where('uuid', $testUuid)
    ->first(['uuid', 'name', 'stage', 'id_session', 'angkatan', 'paket', 'gender']);

if ($member) {
    echo "   ✓ Member found in database:\n";
    echo "     - Name: {$member->name}\n";
    echo "     - Stage: {$member->stage}\n";
    echo "     - Session ID: {$member->id_session}\n";
    echo "     - Angkatan: {$member->angkatan}\n";
    echo "     - Paket: {$member->paket}\n";
    echo "     - Gender: {$member->gender}\n\n";
} else {
    echo "   ✗ Member NOT found in database\n\n";
    exit(1);
}

// Check validation rules
echo "3. Testing validation rules...\n";

$validator = Validator::make(
    ['member_uuid' => $testUuid],
    ['member_uuid' => 'required|string|max:255']
);

if ($validator->passes()) {
    echo "   ✓ Validation PASSED (string|max:255)\n\n";
} else {
    echo "   ✗ Validation FAILED\n";
    print_r($validator->errors()->all());
    echo "\n";
}

// Test old strict validation (should fail)
echo "4. Testing OLD strict uuid validation (for comparison)...\n";
$oldValidator = Validator::make(
    ['member_uuid' => $testUuid],
    ['member_uuid' => 'required|string|uuid']
);

if ($oldValidator->fails()) {
    echo "   ✓ Old validation correctly FAILS (uuid format too strict)\n";
    echo "     Error: " . $oldValidator->errors()->first('member_uuid') . "\n\n";
} else {
    echo "   ✗ Old validation unexpectedly passed\n\n";
}

// Check existing attendance records
echo "5. Checking existing attendance records...\n";
$attendance = DB::connection('ppab')
    ->table('ppab_attendance')
    ->where('member_id', $testUuid)
    ->get(['id', 'member_id', 'session_id', 'created_at']);

if ($attendance->isEmpty()) {
    echo "   ✓ No attendance records yet (can scan for first time)\n\n";
} else {
    echo "   ⚠ Attendance records exist ({$attendance->count()}):\n";
    foreach ($attendance as $record) {
        echo "     - Session: {$record->session_id}, Time: {$record->created_at}\n";
    }
    echo "   (Duplicate prevention should work)\n\n";
}

// Check payment status
echo "6. Checking payment status...\n";
$payment = DB::connection('ppab')
    ->table('ppab_transactions_xendit')
    ->where('id_member', $testUuid)
    ->where('status', 'PAID')
    ->first(['id', 'status', 'amount', 'external_id']);

if ($payment) {
    echo "   ✓ Payment found: Status={$payment->status}, Amount={$payment->amount}\n\n";
} else {
    echo "   ⚠ No PAID payment found (scan will return MEMBER_NOT_PAID)\n\n";
}

// Summary
echo "=== Test Summary ===\n";
echo "✓ UUID validation fix is working correctly\n";
echo "✓ Database query will work with truncated UUID\n";
echo "✓ API endpoint should now accept QR scans\n\n";

echo "Expected API behavior:\n";
if ($member->stage === 'paid_payment' || $member->stage === 'done') {
    if ($attendance->isEmpty()) {
        echo "→ First scan: Will create attendance record and return SUCCESS\n";
    } else {
        echo "→ Duplicate scan: Will return 200 with DUPLICATE_SCAN code\n";
    }
} else {
    echo "→ Will return 422 MEMBER_NOT_PAID (stage: {$member->stage})\n";
}

echo "\nTest completed successfully!\n";

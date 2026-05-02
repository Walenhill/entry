<?php
require_once __DIR__ . '/../includes/auth.php';

/**
 * Simple Test Runner for Auth Functions
 */

function testHashPasswordFormat() {
    echo "Testing hashPassword format... ";
    $password = "test_password_123";
    $hash = hashPassword($password);

    if (empty($hash)) {
        echo "FAIL: Hash is empty\n";
        return false;
    }

    // Argon2id hashes start with $argon2id$
    if (strpos($hash, '$argon2id$') !== 0) {
        echo "FAIL: Hash does not start with \$argon2id$\n";
        echo "Hash: $hash\n";
        return false;
    }

    echo "PASS\n";
    return true;
}

function testHashPasswordSalting() {
    echo "Testing hashPassword salting... ";
    $password = "same_password";
    $hash1 = hashPassword($password);
    $hash2 = hashPassword($password);

    if ($hash1 === $hash2) {
        echo "FAIL: Two hashes of the same password are identical (no salting?)\n";
        return false;
    }

    echo "PASS\n";
    return true;
}

function testVerifyPasswordSuccess() {
    echo "Testing verifyPassword success... ";
    $password = "secure_password";
    $hash = hashPassword($password);

    if (!verifyPassword($password, $hash)) {
        echo "FAIL: verifyPassword failed for correct password\n";
        return false;
    }

    echo "PASS\n";
    return true;
}

function testVerifyPasswordFailure() {
    echo "Testing verifyPassword failure... ";
    $password = "correct_password";
    $wrongPassword = "wrong_password";
    $hash = hashPassword($password);

    if (verifyPassword($wrongPassword, $hash)) {
        echo "FAIL: verifyPassword succeeded for incorrect password\n";
        return false;
    }

    echo "PASS\n";
    return true;
}

// Run tests
$tests = [
    'testHashPasswordFormat',
    'testHashPasswordSalting',
    'testVerifyPasswordSuccess',
    'testVerifyPasswordFailure'
];

$passedCount = 0;
foreach ($tests as $test) {
    if ($test()) {
        $passedCount++;
    }
}

echo "\nSummary: $passedCount/" . count($tests) . " tests passed.\n";

if ($passedCount === count($tests)) {
    exit(0);
} else {
    exit(1);
}

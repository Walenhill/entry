<?php
require_once __DIR__ . '/../includes/helpers.php';

/**
 * Simple Test Runner for Helper Functions
 */

function testValidateDateTime() {
    echo "Testing validateDateTime... ";

    $cases = [
        ['2023-10-27 10:00:00', true, 'Valid datetime'],
        ['2023/10/27 10:00:00', false, 'Invalid format (slashes)'],
        ['2023-02-30 10:00:00', false, 'Invalid date (Feb 30)'],
        ['2023-10-27 25:00:00', false, 'Invalid time (25 hours)'],
        ['', false, 'Empty string'],
        [null, false, 'Null value'],
        ['not-a-date', false, 'Non-date string'],
        ['2023-10-27', false, 'Partial date'],
        ['2023-10-27 10:00:00 extra', false, 'Extra characters']
    ];

    foreach ($cases as [$input, $expected, $label]) {
        if (validateDateTime($input) !== $expected) {
            echo "\nFAIL: $label failed for input '" . var_export($input, true) . "'. Expected " . var_export($expected, true) . ", got " . var_export(! $expected, true) . "\n";
            return false;
        }
    }

    echo "PASS\n";
    return true;
}

function testSanitizeInput() {
    echo "Testing sanitizeInput... ";

    $cases = [
        ['  hello  ', 'hello', 'Basic trimming'],
        ['O\'Connor', 'O\'Connor', 'Preserve single quotes'],
        ['"Hello"', '"Hello"', 'Preserve double quotes'],
        ['', '', 'Empty string'],
        ['  ', '', 'Whitespace only string']
    ];

    foreach ($cases as [$input, $expected, $label]) {
        $result = sanitizeInput($input);
        if ($result !== $expected) {
            echo "\nFAIL: $label failed for input '" . var_export($input, true) . "'. Expected " . var_export($expected, true) . ", got " . var_export($result, true) . "\n";
            return false;
        }
    }

    echo "PASS\n";
    return true;
}

// Run tests
$tests = [
    'testValidateDateTime',
    'testSanitizeInput'
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

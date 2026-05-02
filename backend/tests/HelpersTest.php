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

function testHasTimeOverlap() {
    echo "Testing hasTimeOverlap... ";

    $cases = [
        // Overlapping
        ['10:00', '12:00', '11:00', '13:00', true, 'Simple overlap'],
        ['10:00', '12:00', '09:00', '11:00', true, 'Overlap from start'],
        ['10:00', '12:00', '10:30', '11:30', true, 'Inner overlap'],
        ['10:00', '12:00', '09:00', '13:00', true, 'Outer overlap'],

        // Not overlapping
        ['10:00', '11:00', '12:00', '13:00', false, 'Separated'],
        ['12:00', '13:00', '10:00', '11:00', false, 'Separated reverse'],

        // Touching
        ['10:00', '11:00', '11:00', '12:00', false, 'Touching end-to-start'],
        ['11:00', '12:00', '10:00', '11:00', false, 'Touching start-to-end']
    ];

    foreach ($cases as [$s1, $e1, $s2, $e2, $expected, $label]) {
        if (hasTimeOverlap($s1, $e1, $s2, $e2) !== $expected) {
            echo "\nFAIL: $label failed for range1[$s1, $e1] and range2[$s2, $e2]. Expected " . var_export($expected, true) . "\n";
            return false;
        }
    }

    echo "PASS\n";
    return true;
}

// Run tests
$tests = [
    'testValidateDateTime',
    'testHasTimeOverlap'
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

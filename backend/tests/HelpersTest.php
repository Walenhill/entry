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
        ['  ', '', 'Whitespace only string'],
        ['<p>hello</p>', 'hello', 'Strip basic HTML tags'],
        ['<script>alert(1)</script>', 'alert(1)', 'Strip script tags'],
        [12345, '12345', 'Integer value'],
        [12.34, '12.34', 'Float value'],
        [true, '1', 'Boolean true value'],
        [false, '', 'Boolean false value'],
        [[], '', 'Array value']
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

function testGetRequestPath() {
    echo "Testing getRequestPath... ";

    $cases = [
        ['/api/slots', 'slots', 'Basic endpoint with api prefix'],
        ['/api/slots/123/book', 'slots/123/book', 'Nested endpoint with api prefix'],
        ['/slots', '/slots', 'Endpoint without api prefix'],
        ['/api', '/api', 'Exact /api path'],
        ['/api/', '', 'Exact /api/ path'],
        ['/', '/', 'Root path'],
        ['/api/slots?date=2023-10-27', 'slots', 'Path with query parameters'],
        ['', '/', 'Empty URI']
    ];

    foreach ($cases as [$uri, $expected, $label]) {
        if ($uri === '') {
            unset($_SERVER['REQUEST_URI']);
        } else {
            $_SERVER['REQUEST_URI'] = $uri;
        }

        $result = getRequestPath();
        if ($result !== $expected) {
            echo "\nFAIL: $label failed for input '$uri'. Expected " . var_export($expected, true) . ", got " . var_export($result, true) . "\n";
            return false;
        }
    }

    echo "PASS\n";
    return true;
}

function testGetQueryParams() {
    echo "Testing getQueryParams... ";

    $cases = [
        ['/api/slots?date=2023-10-27&role=admin', ['date' => '2023-10-27', 'role' => 'admin'], 'Multiple query params'],
        ['/api/slots?date=2023-10-27', ['date' => '2023-10-27'], 'Single query param'],
        ['/api/slots', [], 'No query params'],
        ['/api/slots?empty=&param2=val', ['empty' => '', 'param2' => 'val'], 'Empty query param'],
        ['', [], 'Empty URI']
    ];

    foreach ($cases as [$uri, $expected, $label]) {
        if ($uri === '') {
            unset($_SERVER['REQUEST_URI']);
        } else {
            $_SERVER['REQUEST_URI'] = $uri;
        }

        $result = getQueryParams();

        // Use == for array comparison to ignore key order, though keys should match here
        if ($result != $expected) {
            echo "\nFAIL: $label failed for input '$uri'. Expected " . var_export($expected, true) . ", got " . var_export($result, true) . "\n";
            return false;
        }
    }

    echo "PASS\n";
    return true;
}

// Run tests
$tests = [
    'testValidateDateTime',
    'testSanitizeInput',
    'testGetRequestPath',
    'testGetQueryParams'
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

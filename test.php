<?php

error_reporting(-1);
ini_set('display_errors', 1);

$t0 = 0;

function cmpEqual($op1, $op2, $expectedResult) {
    global $t0;

    $start = microtime(true);
    for ($i = 0; $i < 1000000; ++$i) {
        $rs = ($op1 == $op2);
    }
    $end = microtime(true);

    $t   = ($end - $start);
    $t0 += $t;

    printRs('==', $op1, $op2, $expectedResult, $rs, $t);
}

function cmpGreaterThen($op1, $op2, $expectedResult) {
    global $t0;

    $start = microtime(true);
    for ($i = 0; $i < 1000000; ++$i) {
        $rs = ($op1 > $op2);
    }
    $end = microtime(true);

    $t   = ($end - $start);
    $t0 += $t;

    printRs('>', $op1, $op2, $expectedResult, $rs, $t);
}

function printRs($op, $op1, $op2, $expectedResult, $result, $time) {
    echo ($result === $expectedResult) ? 'OK   ' : 'FAIL ';
    echo str_pad(sprintf(
        "(%s %s %s)",
        fmtVar($op1, true),
        $op,
        fmtVar($op2, true)
    ), 44, ' ', STR_PAD_RIGHT);

    printf("\t= %s\ttime = %f\n", fmtVar($result, true), $time);
}

function fmtVar($var) {
    switch (gettype($var)) {
        case 'string':
            return str_replace(array(
                "\\",   "\0", "\t", "\n", "\r"
            ), array(
                '\\\\', '\0', '\t', '\n', '\r'
            ), var_export($var, true));
        default:
            return var_export($var, true);
    }
}

echo "String (non-numeric) to string (non-numeric):\n";
cmpEqual('test', 'test', true);
cmpEqual('simple', 'test', false);

cmpGreaterThen('test', 'test', false);
cmpGreaterThen('teste', 'test', true);
cmpGreaterThen('u', 'test', true);
cmpGreaterThen('tesu', 'test', true);
cmpGreaterThen('tess', 'test', false);
cmpGreaterThen('a', 'Z', true);

echo "\nString (non-numeric) to numeric:\n";
cmpEqual('string', '0', false);
cmpEqual('string', 0, false);
cmpEqual('string', '0.1', false);
cmpEqual('string', 0.1, false);

cmpGreaterThen('str', '123', true);
cmpGreaterThen('STR', '123', true);

echo "\nString (eventual numeric) to numeric:\n";
cmpEqual('1string', '1', false);
cmpEqual('1string', 1, false);
cmpEqual('string1', '1', false);
cmpEqual('string1', 1, false);
cmpEqual(' 1', '1', false);
cmpEqual(' 1', 1, false);
cmpEqual("\t1", '1', false);
cmpEqual("\t1", 1, false);
cmpEqual("\01", '1', false);
cmpEqual("\01", 1, false);

echo "\nString (numeric) to numeric:\n";
cmpEqual('1.1', 1.1, true);
cmpEqual('1.1000000000000001', 1.1, true);
cmpEqual('1e1', '10', false);
cmpEqual('1E1', '10', false);
cmpEqual('1e-1', '0.1', false);
cmpEqual('1E-1', '0.1', false);
cmpEqual('+1', '1', false);
cmpEqual('+0', '-0', false);
cmpEqual('0.99999999999999994', '1', false);
cmpEqual('0.99999999999999995', '1', false);

cmpGreaterThen('123', '123', false);
cmpGreaterThen('124', '123', true);
cmpGreaterThen('2', '123', true);

echo "\nInteger to numeric:\n";
cmpEqual(1, 1.0, true);
cmpEqual(1, '1', true);
cmpEqual(1, '1.0', true);
cmpEqual(1, 2, false);
cmpEqual(1, 1.1, false);
cmpEqual(-0, +0.0, true);

echo "\nFloat to numeric:\n";
cmpEqual(1.1, 1.1, true);
cmpEqual(1.1, '1.1', true);
cmpEqual(1.1, '1.1000000000000001', true);
cmpEqual(1.1, 2, false);


echo "\nNULL to string:\n";
cmpEqual(NULL, '', true);
cmpEqual(NULL, ' ', false);
cmpEqual(NULL, '0', false);

echo "\nNULL to integer:\n";
cmpEqual(NULL, 0, true);
cmpEqual(NULL, -1, false);
cmpEQUAL(NULL, 1, false);

cmpGreaterThen(NULL, 0, false);
cmpGreaterThen(NULL, -1, true);
cmpGreaterThen(NULL, 1, false);

echo "\nNULL to float:\n";
cmpEqual(NULL, 0.0, true);
cmpEqual(NULL, -1.0, false);
cmpEQUAL(NULL, 1.0, false);

cmpGreaterThen(NULL, 0.0, false);
cmpGreaterThen(NULL, -1.0, true);
cmpGreaterThen(NULL, 1.0, false);


printf("\nTotal \t\t\t\t\t\t\ttime = %f\n", $t0);

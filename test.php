<?php

error_reporting(-1);
ini_set('display_errors', 1);

$iterations = 10000;
$t0 = 0;

function cmp($v1, $v2, $expect) {
    global $iterations;

    // equal
    $t1 = microtime(true);
    for ($i=0; $i<$iterations; ++$i) $rs = ($v1 == $v2);
    $t2 = microtime(true);
    $ok = ($rs === ($expect === 0));
    result('==', $v1, $v2, ($expect === 0), $rs, $t2-$t1);
    
    // lower
    $t1 = microtime(true);
    for ($i=0; $i<$iterations; ++$i) $rs = ($v1 < $v2);
    $t2 = microtime(true);
    $ok = ($rs === ($expect === -1));
    result('<', $v1, $v2, ($expect === -1), $rs, $t2-$t1);

    // lower or equal
    $t1 = microtime(true);
    for ($i=0; $i<$iterations; ++$i) $rs = ($v1 <= $v2);
    $t2 = microtime(true);
    $ok = ($rs === ($expect === -1 || $expect === 0));
    result('<=', $v1, $v2, ($expect === -1 || $expect === 0), $rs, $t2-$t1);

    // greater
    $t1 = microtime(true);
    for ($i=0; $i<$iterations; ++$i) $rs = ($v1 > $v2);
    $t2 = microtime(true);
    $ok = ($rs === ($expect === 1));
    result('>', $v1, $v2, ($expect === 1), $rs, $t2-$t1);

    // greater or equal
    $t1 = microtime(true);
    for ($i=0; $i<$iterations; ++$i) $rs = ($v1 >= $v2);
    $t2 = microtime(true);
    $ok = ($rs === ($expect === 1 || $expect === 0));
    result('>=', $v1, $v2, ($expect === 1 || $expect === 0), $rs, $t2-$t1);
}

function result($op, $v1, $v2, $expectedRs, $rs, $time) {
    global $t0;

    $t0 += $time;

    echo ($expectedRs === $rs) ? 'OK   ' : 'FAIL ';
    echo str_pad(sprintf(
        "%s %s %s",
        fmt($v1),
        $op,
        fmt($v2)
    ), 44, ' ', STR_PAD_RIGHT);

    printf("\t= %s\ttime = %f\n", fmt($rs), $time);
}

function fmt($var) {
    switch (gettype($var)) {
        case 'string':
            return str_replace(array(
                "\\",   "\0", "\t", "\n", "\r"
            ), array(
                '\\\\', '\0', '\t', '\n', '\r'
            ), var_export($var, true));
        case 'double':
            $ret = var_export($var, true);
            return (strpos($ret, '.') === false) ? $ret . '.0' : $ret;
        case 'int':
            return var_export($var, true);
        default:
            return var_export($var, true);
    }
}

echo "String (non-numeric) to string (non-numeric):\n";
cmp('str', 'str', 0);
cmp('str ', 'str', 1);
cmp('php', 'str', -1);
cmp('u', 'str', 1);
cmp('s', 'str', -1);
cmp('str', 'std', 1);
cmp('str', 'sts', -1);
cmp('a', 'Z', 1);
cmp('Z', 'a', -1);

echo "\nString (non-numeric) to numeric:\n";
cmp('str', '0', 1);
cmp('str', 0, false);
cmp('str', '0.1', 1);
cmp('str', 0.1, false);

cmp('str', '123', 1);
cmp('STR', '123', 1);
cmp(':', '9', 1);
cmp('/', '0', -1);

echo "\nString (eventual numeric) to numeric:\n";
cmp('1str', '1', 1);
cmp('1str', 1, false);
cmp('str1', '1', 1);
cmp('str1', 1, false);
cmp(' 1', '1', -1);
cmp(' 1', 1, false);
cmp("\t1", '1', -1);
cmp("\t1", 1, false);
cmp("\01", '1', -1);
cmp("\01", 1, false);

echo "\nString (numeric) to numeric:\n";
cmp('1.1', 1.1, 0);
cmp('1.1000000000000001', 1.1, 0);
cmp('1e1', '10', 1);
cmp('1E1', '10', 1);
cmp('1e-1', '0.1', 1);
cmp('1E-1', '0.1', 1);
cmp('+1', '1', -1);
cmp('+0', '-0', -1);
cmp('0.99999999999999994', '1', -1);
cmp('0.99999999999999995', '1', -1);

cmp('123', '123', 0);
cmp('124', '123', 1);
cmp('2', '123', 1);

echo "\nInteger to numeric:\n";
cmp(1, 1.0, 0);
cmp(1, '1', 0);
cmp(1, '1.0', 0);
cmp(1, 2, -1);
cmp(1, 1.1, -1);
cmp(-0, +0.0, 0);

echo "\nFloat to numeric:\n";
cmp(1.1, 1.1, 0);
cmp(1.0, '1', 0);
cmp(1.1, '1.1', 0);
cmp(1.1, '1.1000000000000001', 0);
cmp(1.1, 2, -1);


echo "\nNULL to string:\n";
cmp(NULL, '', 0);
cmp(NULL, ' ', -1);
cmp(NULL, '0', -1);

echo "\nNULL to integer:\n";
cmp(NULL, 0, 0);
cmp(NULL, -1, 1);
cmp(NULL, 1, -1);

echo "\nNULL to float:\n";
cmp(NULL, 0.0, 0);
cmp(NULL, -1.0, 1);
cmp(NULL, 1.0, -1);

printf("\nTotal \t\t\t\t\t\t\ttime = %f\n", $t0);

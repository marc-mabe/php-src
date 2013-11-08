<?php

$t0 = 0;

function printCmp($op1, $op2) {
    global $t0;

    $start = microtime(true);
    for ($i; $i < 1000000; ++$i) {
        $ret = ($op1 == $op2);
    }
    $end = microtime(true);

    $t   = ($end - $start);
    $t0 += $t;

    echo str_pad(sprintf(
        "(%s == %s)",
        var_export($op1, true),
        var_export($op2, true)
    ), 40, ' ', STR_PAD_RIGHT);

    printf("\t= %s\ttime = %f\n", var_export($ret, true), $t);
}

echo "String (non-numeric):\n";
printCmp('test', 'test');
printCmp('simple', 'test');

echo "\nString (non-numeric) to numeric:\n";
printCmp('string', '0');
printCmp('string', 0);
printCmp('string', '0.1');
printCmp('string', 0.1);

echo "\nString (eventual numeric) to numeric:\n";
printCmp('1string', '1');
printCmp('1string', 1);
printCmp('string1', '1');
printCmp('string1', 1);
printCmp(' 1', '1');
printCmp(' 1', 1);
printCmp("\t1", '1');
printCmp("\t1", 1);
printCmp("\01", '1');
printCmp("\01", 1);

echo "\nString (numeric) to numeric:\n";
printCmp('1.1', 1.1);
printCmp('1.1000000000000001', 1.1);
printCmp('1e1', '10');
printCmp('1E1', '10');
printCmp('1e-1', '0.1');
printCmp('1E-1', '0.1');
printCmp('+1', '1');
printCmp('+0', '-0');
printCmp('0.99999999999999994', '1');
printCmp('0.99999999999999995', '1');

echo "\nInteger to numeric:\n";
printCmp(1, 1.0);
printCmp(1, '1');
printCmp(1, '1.0');
printCmp(1, 2);
printCmp(1, 1.1);
printCmp(-0, +0.0);

echo "\nFloat to numeric:\n";
printCmp(1.1, 1.1);
printCmp(1.1, '1.1');
printCmp(1.1, '1.1000000000000001');
printCmp(1.1, 2);


printf("\nTotal \t\t\t\t\t\t\ttime = %f\n", $t0);

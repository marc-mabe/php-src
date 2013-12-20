<?php

echo <<<RULES
Comparison operators:
	identical        : ===, !==
	equal            : ==, != or <>
	smaller or equal : <, <=
	greater or equal : >, >=

Comparison rules:
	- If a===b
		- true: b===a, a==b, b==a, a<=b, b<=a, a>=b, b>=a
		- false: a!==b, b!==a, a!=b, b!=a, a<b, b<a, a>b, b>a

	- If a!==b
		- true: b!==a
		- false: a===b, b===a

	- If a==b
		- true: b==a, a<=b, b<=a, a>=b, b>=a
		- false: a!=b, b!=a, a<b, b<a, a>b, b>a

	- If a!=b
		- true: b!=a, a!==b, b!==a
		- false: a==b, b==a, a===b, b===a

	- If a<b
		- true: a<=b, b>a, b>=a, a!==b, b!==a, a!=b, b!=a
		- false: a>b, a>=b, b<a, b<=a, a===b, b===a, a==b, b==a

Comparing different types:
	- null to bool          : handle null as the same as false
	- null to int/float     : handle null as the same as 0
	- null to string        : handle null as the same as an empty string
	- null to array         : handle null as the same as an empty array
	- null to resource      : not comparable
	- null to object        : not comparable
	- bool to int/float     : handle numbers of 0 as false and all other as true
	- bool to string        : handle an empty string as false and all other as true
	- bool to array         : on equality: handle an empty array as false and all other as true
	                          on ordering: not comparable
	- bool to resource      : on equality: handle resources as true
	                          on ordering: not comparable
	- bool to object        : on equality: handle objects as true
	                          on ordering: not comparable
	- int/float to string   : convert string into int/float - not comparable on an invalid number or error
	                          Format for a valid number in base 10: ^[+-]?[0-9]+$
	                          Format for a valid number in base 16: ^0x[0-9a-fA-F]+$
	                          Format for a valid float (TODO):      ^[+-]? ... $
	- int/float to array	: count array entries to compare two numbers
	- int/float to resource	: not comparable
	- int/float to object   : not comparable
	- string to array       : not comparable
	- string to resource    : not comparable
	- string to object      : not comparable
	- array to resource     : not comparable
	- array to object       : not comparable
	- resource to object    : not comparable

Comparing booleans:
	- TRUE === TRUE, FALSE === FALSE, TRUE !== FALSE, TRUE > FALSE

Comparing floats:
	- +0 and -0 will be identical
	- +Inf will be the highest possible value
	- -Inf will be the lowest possible value
	- NaN is not comparable expect for NaN

Comparing strings:
	- string are identical if they are binary identical
	- ordering will be done on byte sequence

Comparing arrays:
	- an array will be identical if all elements (keys and values) are identical
	- ordering will be done using the number of elements of the arrays

RULES;

error_reporting(-1);
ini_set('display_errors', 1);

$iterations = 100;
$t0         = 0;
$types      = array_map('strtolower', array_slice($_SERVER['argv'], 1));

function cmp($v1, $v2, $expect) {
    global $iterations, $types;

    if ($types && !in_array(strtolower(gettype($v1)), $types) && !in_array(strtolower(gettype($v2)), $types)) {
        return;
    }

    // equal
    $t1 = microtime(true);
    for ($i=0; $i<$iterations; ++$i) $rs = ($v1 == $v2);
    $t2 = microtime(true);
    result('==', $v1, $v2, ($expect === 0), $rs, $t2-$t1);

    // not equal
    $t1 = microtime(true);
    for ($i=0; $i<$iterations; ++$i) $rs = ($v1 != $v2);
    $t2 = microtime(true);
    result('!=', $v1, $v2, ($expect !== 0), $rs, $t2-$t1);

    // lower
    $t1 = microtime(true);
    for ($i=0; $i<$iterations; ++$i) $rs = ($v1 < $v2);
    $t2 = microtime(true);
    result('<', $v1, $v2, ($expect === -1), $rs, $t2-$t1);

    // lower or equal
    $t1 = microtime(true);
    for ($i=0; $i<$iterations; ++$i) $rs = ($v1 <= $v2);
    $t2 = microtime(true);
    result('<=', $v1, $v2, ($expect === -1 || $expect === 0), $rs, $t2-$t1);

    // greater
    $t1 = microtime(true);
    for ($i=0; $i<$iterations; ++$i) $rs = ($v1 > $v2);
    $t2 = microtime(true);
    result('>', $v1, $v2, ($expect === 1), $rs, $t2-$t1);

    // greater or equal
    $t1 = microtime(true);
    for ($i=0; $i<$iterations; ++$i) $rs = ($v1 >= $v2);
    $t2 = microtime(true);
    result('>=', $v1, $v2, ($expect === 1 || $expect === 0), $rs, $t2-$t1);
}

function result($op, $v1, $v2, $expectedRs, $rs, $time) {
    global $t0;

    $t0 += $time;

    printf(
        "%s\t%s \t= %s\ttime = %f\n",
        ($expectedRs === $rs) ? 'OK' : 'FAIL',
        str_pad(sprintf(
            "%s %s %s",
            fmt($v1),
            $op,
            fmt($v2)
        ), 44, ' ', STR_PAD_RIGHT),
        fmt($rs),
        $time
    );
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
        case 'resource':
            return print_r($var, true);
        case 'array':
            $list = array();
            foreach ($var as $v) {
                $list[] = fmt($v);
            }
            return '[' . implode(',', $list) . ']';

        default:
            return var_export($var, true);
    }
}

echo "\nString (non-numeric) to string (non-numeric):\n";
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

echo "\nArray comparison:\n";
cmp(array(), array(), 0);
cmp(array(1), array(1), 0);
cmp(array(1), array(2), -1);
cmp(array(1), array(), 1);
cmp(array(1), array(2), -1);
cmp(array('1'), array(1), 0);
cmp(array(), null, false);
cmp(array(1), null, false);
cmp(array(), false, false);
cmp(array(1), false, false);
cmp(array(), true, false);
cmp(array(1), true, false);
cmp(array(1,2), true, false);
cmp(array(), 0, false);
cmp(array(1), 0, false);
cmp(array(), 1, false);
cmp(array(1), 1, false);
cmp(array(1), 2, false);


echo "\nResource comparison:\n";
$rs1 = fopen(__FILE__, 'rb');
$rs2 = fopen(__DIR__ . '/php.gif', 'rb');
cmp($rs1, $rs1, 0);
cmp($rs1, $rs2, false);
cmp($rs1, (int)$rs1, false);
cmp($rs1, (float)(int)$rs1, false);


printf("\nTotal \t\t\t\t\t\t\ttime = %f\n", $t0);

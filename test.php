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

Loose type comparison:
	- null to bool          : handle null as the same as false
	- null to int/float     : handle null as the same as 0
	- null to string        : handle null as the same as an empty string
	- null to array         : handle an empty array as the same as null else the array is greater
	- bool to bool		: false is smaller then true
	- bool to int/float     : handle 0 as false and all other as true
	- bool to string        : handle an empty string as false and all other as true
	- bool to array         : handle an empty array as false and all other as true
	- int/float to string   : convert string to int/float
		- handle an invalid number as the same as NaN
		- an empty string will be invalid
		- any invalid character will be invalid including beginning whitespaces
		- allow hex values beginning with "0x" followed by at least one of 0-9, a-f, A-F
		- allow octal values beginning with "0" fllowed by at least one of 0-7
		- handle all others as decimal:
			- allow one optional sign "-" and "+" (default)
			- allow one "." as decimal separator
			- allow the decimal separator as the first character after the optional sign
			- allow "e" and "E" as exponent separator followed by at least one of 0-9
	- int/float to array	: count the number of array elements to compare to the number
	- string to array       : handle an empty string and an empty array as equal else false
	- array to array	:
		- compare the length of the arrays
		- compare all keys and values using loose type comparison
		- compare all elements in the same order as they are
		
	- object to x           :
		- if possible use comparison table of the left object
		- else if possible use comparison table of the right object
		- else if possible compare the object to type x
	- All other will be the same as strict comparison

Specials on floating point numbers:
	- +0 and -0 will be identical
	- NaN and NaN will be identical
	- +Inf and +Inf will be identical
	- +Inf will be the highest possible value
	- -Inf and -Inf will be identical
	- -Inf will be the lowest possible value

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
    if ($v1 !== $v2) {
        result('==', $v2, $v1, ($expect === 0), $rs, $t2-$t1); // reverse
    }

    // not equal
    $t1 = microtime(true);
    for ($i=0; $i<$iterations; ++$i) $rs = ($v1 != $v2);
    $t2 = microtime(true);
    result('!=', $v1, $v2, ($expect !== 0), $rs, $t2-$t1);
    if ($v1 !== $v2) {
        result('!=', $v2, $v1, ($expect !== 0), $rs, $t2-$t1); // reverse
    }

    // lower / greater
    $t1 = microtime(true);
    for ($i=0; $i<$iterations; ++$i) $rs = ($v1 < $v2);
    $t2 = microtime(true);
    result('<', $v1, $v2, ($expect === -1), $rs, $t2-$t1);
    result('>', $v2, $v1, ($expect === -1), $rs, $t2-$t1); // reverse

    // lower or equal / grreater or equaö
    $t1 = microtime(true);
    for ($i=0; $i<$iterations; ++$i) $rs = ($v1 <= $v2);
    $t2 = microtime(true);
    result('<=', $v1, $v2, ($expect === -1 || $expect === 0), $rs, $t2-$t1);
    result('>=', $v2, $v1, ($expect === -1 || $expect === 0), $rs, $t2-$t1); // reverse
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
            if (is_nan($var)) {
                return 'NaN';
            } else if ($var === +INF) {
                return '+Inf';
            } else if ($var === -INF) {
                return '-Inf';
            }
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

echo "\nString (evtl. numeric) to numeric:\n";
cmp('1str', '1', 1);
cmp('1str', 1, false);
cmp('str1', '1', 1);
cmp('str1', 1, false);
cmp(' 1', '1', -1);
cmp(' 1', 1, false);
cmp("\t1", '1', -1);
cmp("\t1", 1, false);

echo "\nString (numeric) to numeric:\n";
cmp('1.1', 1.1, 0);
cmp('1.1000000000000001', 1.1, 0);

cmp('2e2', '200', 1);
cmp('2E2', 200, 0);
cmp('2E2', 200.0, 0);
cmp('2e3', 200.0, 1);

cmp('2e-2', '0.02', 1);
cmp('2E-2', 0.02, 0);
cmp('2e-3', 0.02, -1);

cmp('+1', '1', -1);
cmp('+0', '-0', -1);
cmp('0.99999999999999994', '1', -1);
cmp('0.99999999999999995', '1', -1);
cmp('123', '123', 0);
cmp('124', '123', 1);
cmp('2', '123', 1);

echo "\nFloat:\n";
cmp(+0, -0, 0);
cmp(NAN, NAN, 0);
cmp(+INF, +INF, 0);
cmp(-INF, -INF, 0);
cmp(1.1, 1.1000000000000001, 0);
cmp(1.1, 1.1000000000000002, -1);
cmp(+INF, -INF, 1);
cmp(+INF, 1e22, 1);
cmp(-INF, -1E22, -1);
cmp('a', NAN, 0);
cmp('1', NAN, false);
cmp(1, NAN, false);
cmp(1.1, NAN, false);
cmp(INF, NAN, false);

echo "\nInteger to numeric:\n";
cmp(1, 1.0, 0);
cmp(1, '1', 0);
cmp(1, '1.0', 0);
cmp((PHP_INT_MAX+1), (string)(PHP_INT_MAX+1), 0);
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
cmp(array(), null, 0);
cmp(array(1), null, 1);
cmp(array(), false, 0);
cmp(array(1), false, false);
cmp(array(), true, false);
cmp(array(1), true, 0);
cmp(array(1,2), true, 0);
cmp(array(), 0, 0);
cmp(array(1), 0, 1);
cmp(array(), 1, -1);
cmp(array(1), 1, 0);
cmp(array(1), 2, -1);


echo "\nResource comparison:\n";
$rs1 = fopen(__FILE__, 'rb');
$rs2 = fopen(__DIR__ . '/php.gif', 'rb');
cmp($rs1, $rs1, 0);
cmp($rs1, $rs2, false);
cmp($rs1, (int)$rs1, false);
cmp($rs1, (float)(int)$rs1, false);


echo "\nBoolean:\n";
cmp(true, true, 0);
cmp(true, false, 1);
cmp(2, true, 0);
cmp(0, true, false);
cmp(false, 0, 0);
cmp(false, 1, false);

printf("\nTotal \t\t\t\t\t\t\ttime = %f\n", $t0);

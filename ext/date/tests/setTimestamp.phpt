--TEST--
Test DateTime[Immutable]->setTimestamp()
--FILE--
<?php

$utc = new DateTimeZone('UTC');
$bln = new DateTimeZone('Europe/Berlin');

$dt_utc = new DateTime('2023-01-01T01:02:03.56789', $utc);
$dt_bln = new DateTime('2023-01-01T01:02:03.56789', $bln);

$dti_utc = new DateTimeImmutable('2023-01-01T01:02:03.56789', $utc);
$dti_bln = new DateTimeImmutable('2023-01-01T01:02:03.56789', $bln);

$timestamps = array(
    1697372806,
    -1697372806,
    1697372806.1234567,
    -1697372806.1234567,
    0,
    0.0,
    -0.0,
    2147483647,
    -2147483648,
    -2147483648.5,
    NAN,
    +INF,
    -INF,
    2**64
);

foreach ($timestamps as $timestamp) {
    printf("\$dt_utc->setTimestamp(%s):\n", var_export($timestamp, true));
    try {
        var_dump($dt_utc->setTimestamp($timestamp));
        var_dump($dt_utc);
    } catch (Throwable $e) {
        echo get_class($e) . ': ' . $e->getMessage() . "\n";
    }

    printf("\$dt_bln->setTimestamp(%s):\n", var_export($timestamp, true));
    try {
        var_dump($dt_bln->setTimestamp($timestamp));
        var_dump($dt_bln);
    } catch (Throwable $e) {
        echo get_class($e) . ': ' . $e->getMessage() . "\n";
    }

    printf("\$dti_utc->setTimestamp(%s):\n", var_export($timestamp, true));
    try {
        var_dump($dti_utc->setTimestamp($timestamp));
        var_dump($dti_utc);
    } catch (Throwable $e) {
        echo get_class($e) . ': ' . $e->getMessage() . "\n";
    }

    printf("\$dti_bln->setTimestamp(%s):\n", var_export($timestamp, true));
    try {
        var_dump($dti_bln->setTimestamp($timestamp));
        var_dump($dti_bln);
    } catch (Throwable $e) {
        echo get_class($e) . ': ' . $e->getMessage() . "\n";
    }

    echo "###################################################\n";
}

?>
--EXPECTF--
$dt_utc->setTimestamp(1697372806):
object(DateTime)#3 (3) {
  ["date"]=>
  string(26) "2023-10-15 12:26:46.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
object(DateTime)#3 (3) {
  ["date"]=>
  string(26) "2023-10-15 12:26:46.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
$dt_bln->setTimestamp(1697372806):
object(DateTime)#4 (3) {
  ["date"]=>
  string(26) "2023-10-15 14:26:46.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
object(DateTime)#4 (3) {
  ["date"]=>
  string(26) "2023-10-15 14:26:46.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
$dti_utc->setTimestamp(1697372806):
object(DateTimeImmutable)#7 (3) {
  ["date"]=>
  string(26) "2023-10-15 12:26:46.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
object(DateTimeImmutable)#5 (3) {
  ["date"]=>
  string(26) "2023-01-01 01:02:03.567890"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
$dti_bln->setTimestamp(1697372806):
object(DateTimeImmutable)#7 (3) {
  ["date"]=>
  string(26) "2023-10-15 14:26:46.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
object(DateTimeImmutable)#6 (3) {
  ["date"]=>
  string(26) "2023-01-01 01:02:03.567890"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
###################################################
$dt_utc->setTimestamp(-1697372806):
object(DateTime)#3 (3) {
  ["date"]=>
  string(26) "1916-03-19 11:33:14.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
object(DateTime)#3 (3) {
  ["date"]=>
  string(26) "1916-03-19 11:33:14.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
$dt_bln->setTimestamp(-1697372806):
object(DateTime)#4 (3) {
  ["date"]=>
  string(26) "1916-03-19 12:33:14.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
object(DateTime)#4 (3) {
  ["date"]=>
  string(26) "1916-03-19 12:33:14.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
$dti_utc->setTimestamp(-1697372806):
object(DateTimeImmutable)#7 (3) {
  ["date"]=>
  string(26) "1916-03-19 11:33:14.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
object(DateTimeImmutable)#5 (3) {
  ["date"]=>
  string(26) "2023-01-01 01:02:03.567890"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
$dti_bln->setTimestamp(-1697372806):
object(DateTimeImmutable)#7 (3) {
  ["date"]=>
  string(26) "1916-03-19 12:33:14.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
object(DateTimeImmutable)#6 (3) {
  ["date"]=>
  string(26) "2023-01-01 01:02:03.567890"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
###################################################
$dt_utc->setTimestamp(1697372806.1234567):
object(DateTime)#3 (3) {
  ["date"]=>
  string(26) "2023-10-15 12:26:46.123456"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
object(DateTime)#3 (3) {
  ["date"]=>
  string(26) "2023-10-15 12:26:46.123456"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
$dt_bln->setTimestamp(1697372806.1234567):
object(DateTime)#4 (3) {
  ["date"]=>
  string(26) "2023-10-15 14:26:46.123456"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
object(DateTime)#4 (3) {
  ["date"]=>
  string(26) "2023-10-15 14:26:46.123456"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
$dti_utc->setTimestamp(1697372806.1234567):
object(DateTimeImmutable)#7 (3) {
  ["date"]=>
  string(26) "2023-10-15 12:26:46.123456"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
object(DateTimeImmutable)#5 (3) {
  ["date"]=>
  string(26) "2023-01-01 01:02:03.567890"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
$dti_bln->setTimestamp(1697372806.1234567):
object(DateTimeImmutable)#7 (3) {
  ["date"]=>
  string(26) "2023-10-15 14:26:46.123456"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
object(DateTimeImmutable)#6 (3) {
  ["date"]=>
  string(26) "2023-01-01 01:02:03.567890"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
###################################################
$dt_utc->setTimestamp(-1697372806.1234567):
object(DateTime)#3 (3) {
  ["date"]=>
  string(26) "1916-03-19 11:33:13.876544"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
object(DateTime)#3 (3) {
  ["date"]=>
  string(26) "1916-03-19 11:33:13.876544"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
$dt_bln->setTimestamp(-1697372806.1234567):
object(DateTime)#4 (3) {
  ["date"]=>
  string(26) "1916-03-19 12:33:13.876544"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
object(DateTime)#4 (3) {
  ["date"]=>
  string(26) "1916-03-19 12:33:13.876544"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
$dti_utc->setTimestamp(-1697372806.1234567):
object(DateTimeImmutable)#7 (3) {
  ["date"]=>
  string(26) "1916-03-19 11:33:13.876544"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
object(DateTimeImmutable)#5 (3) {
  ["date"]=>
  string(26) "2023-01-01 01:02:03.567890"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
$dti_bln->setTimestamp(-1697372806.1234567):
object(DateTimeImmutable)#7 (3) {
  ["date"]=>
  string(26) "1916-03-19 12:33:13.876544"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
object(DateTimeImmutable)#6 (3) {
  ["date"]=>
  string(26) "2023-01-01 01:02:03.567890"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
###################################################
$dt_utc->setTimestamp(0):
object(DateTime)#3 (3) {
  ["date"]=>
  string(26) "1970-01-01 00:00:00.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
object(DateTime)#3 (3) {
  ["date"]=>
  string(26) "1970-01-01 00:00:00.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
$dt_bln->setTimestamp(0):
object(DateTime)#4 (3) {
  ["date"]=>
  string(26) "1970-01-01 01:00:00.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
object(DateTime)#4 (3) {
  ["date"]=>
  string(26) "1970-01-01 01:00:00.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
$dti_utc->setTimestamp(0):
object(DateTimeImmutable)#7 (3) {
  ["date"]=>
  string(26) "1970-01-01 00:00:00.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
object(DateTimeImmutable)#5 (3) {
  ["date"]=>
  string(26) "2023-01-01 01:02:03.567890"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
$dti_bln->setTimestamp(0):
object(DateTimeImmutable)#7 (3) {
  ["date"]=>
  string(26) "1970-01-01 01:00:00.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
object(DateTimeImmutable)#6 (3) {
  ["date"]=>
  string(26) "2023-01-01 01:02:03.567890"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
###################################################
$dt_utc->setTimestamp(0.0):
object(DateTime)#3 (3) {
  ["date"]=>
  string(26) "1970-01-01 00:00:00.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
object(DateTime)#3 (3) {
  ["date"]=>
  string(26) "1970-01-01 00:00:00.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
$dt_bln->setTimestamp(0.0):
object(DateTime)#4 (3) {
  ["date"]=>
  string(26) "1970-01-01 01:00:00.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
object(DateTime)#4 (3) {
  ["date"]=>
  string(26) "1970-01-01 01:00:00.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
$dti_utc->setTimestamp(0.0):
object(DateTimeImmutable)#7 (3) {
  ["date"]=>
  string(26) "1970-01-01 00:00:00.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
object(DateTimeImmutable)#5 (3) {
  ["date"]=>
  string(26) "2023-01-01 01:02:03.567890"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
$dti_bln->setTimestamp(0.0):
object(DateTimeImmutable)#7 (3) {
  ["date"]=>
  string(26) "1970-01-01 01:00:00.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
object(DateTimeImmutable)#6 (3) {
  ["date"]=>
  string(26) "2023-01-01 01:02:03.567890"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
###################################################
$dt_utc->setTimestamp(-0.0):
object(DateTime)#3 (3) {
  ["date"]=>
  string(26) "1970-01-01 00:00:00.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
object(DateTime)#3 (3) {
  ["date"]=>
  string(26) "1970-01-01 00:00:00.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
$dt_bln->setTimestamp(-0.0):
object(DateTime)#4 (3) {
  ["date"]=>
  string(26) "1970-01-01 01:00:00.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
object(DateTime)#4 (3) {
  ["date"]=>
  string(26) "1970-01-01 01:00:00.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
$dti_utc->setTimestamp(-0.0):
object(DateTimeImmutable)#7 (3) {
  ["date"]=>
  string(26) "1970-01-01 00:00:00.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
object(DateTimeImmutable)#5 (3) {
  ["date"]=>
  string(26) "2023-01-01 01:02:03.567890"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
$dti_bln->setTimestamp(-0.0):
object(DateTimeImmutable)#7 (3) {
  ["date"]=>
  string(26) "1970-01-01 01:00:00.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
object(DateTimeImmutable)#6 (3) {
  ["date"]=>
  string(26) "2023-01-01 01:02:03.567890"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
###################################################
$dt_utc->setTimestamp(2147483647):
object(DateTime)#3 (3) {
  ["date"]=>
  string(26) "2038-01-19 03:14:07.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
object(DateTime)#3 (3) {
  ["date"]=>
  string(26) "2038-01-19 03:14:07.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
$dt_bln->setTimestamp(2147483647):
object(DateTime)#4 (3) {
  ["date"]=>
  string(26) "2038-01-19 04:14:07.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
object(DateTime)#4 (3) {
  ["date"]=>
  string(26) "2038-01-19 04:14:07.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
$dti_utc->setTimestamp(2147483647):
object(DateTimeImmutable)#7 (3) {
  ["date"]=>
  string(26) "2038-01-19 03:14:07.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
object(DateTimeImmutable)#5 (3) {
  ["date"]=>
  string(26) "2023-01-01 01:02:03.567890"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
$dti_bln->setTimestamp(2147483647):
object(DateTimeImmutable)#7 (3) {
  ["date"]=>
  string(26) "2038-01-19 04:14:07.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
object(DateTimeImmutable)#6 (3) {
  ["date"]=>
  string(26) "2023-01-01 01:02:03.567890"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
###################################################
$dt_utc->setTimestamp(-2147483648):
object(DateTime)#3 (3) {
  ["date"]=>
  string(26) "1901-12-13 20:45:52.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
object(DateTime)#3 (3) {
  ["date"]=>
  string(26) "1901-12-13 20:45:52.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
$dt_bln->setTimestamp(-2147483648):
object(DateTime)#4 (3) {
  ["date"]=>
  string(26) "1901-12-13 21:45:52.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
object(DateTime)#4 (3) {
  ["date"]=>
  string(26) "1901-12-13 21:45:52.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
$dti_utc->setTimestamp(-2147483648):
object(DateTimeImmutable)#7 (3) {
  ["date"]=>
  string(26) "1901-12-13 20:45:52.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
object(DateTimeImmutable)#5 (3) {
  ["date"]=>
  string(26) "2023-01-01 01:02:03.567890"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
$dti_bln->setTimestamp(-2147483648):
object(DateTimeImmutable)#7 (3) {
  ["date"]=>
  string(26) "1901-12-13 21:45:52.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
object(DateTimeImmutable)#6 (3) {
  ["date"]=>
  string(26) "2023-01-01 01:02:03.567890"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
###################################################
$dt_utc->setTimestamp(-2147483648.5):
object(DateTime)#3 (3) {
  ["date"]=>
  string(26) "1901-12-13 20:45:51.500000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
object(DateTime)#3 (3) {
  ["date"]=>
  string(26) "1901-12-13 20:45:51.500000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
$dt_bln->setTimestamp(-2147483648.5):
object(DateTime)#4 (3) {
  ["date"]=>
  string(26) "1901-12-13 21:45:51.500000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
object(DateTime)#4 (3) {
  ["date"]=>
  string(26) "1901-12-13 21:45:51.500000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
$dti_utc->setTimestamp(-2147483648.5):
object(DateTimeImmutable)#7 (3) {
  ["date"]=>
  string(26) "1901-12-13 20:45:51.500000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
object(DateTimeImmutable)#5 (3) {
  ["date"]=>
  string(26) "2023-01-01 01:02:03.567890"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
$dti_bln->setTimestamp(-2147483648.5):
object(DateTimeImmutable)#7 (3) {
  ["date"]=>
  string(26) "1901-12-13 21:45:51.500000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
object(DateTimeImmutable)#6 (3) {
  ["date"]=>
  string(26) "2023-01-01 01:02:03.567890"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Berlin"
}
###################################################
$dt_utc->setTimestamp(NAN):
DateRangeError: Seconds must be a finite number between -9223372036854775808 and 9223372036854775807, NAN given
$dt_bln->setTimestamp(NAN):
DateRangeError: Seconds must be a finite number between -9223372036854775808 and 9223372036854775807, NAN given
$dti_utc->setTimestamp(NAN):
DateRangeError: Seconds must be a finite number between -9223372036854775808 and 9223372036854775807, NAN given
$dti_bln->setTimestamp(NAN):
DateRangeError: Seconds must be a finite number between -9223372036854775808 and 9223372036854775807, NAN given
###################################################
$dt_utc->setTimestamp(INF):
DateRangeError: Seconds must be a finite number between -9223372036854775808 and 9223372036854775807, INF given
$dt_bln->setTimestamp(INF):
DateRangeError: Seconds must be a finite number between -9223372036854775808 and 9223372036854775807, INF given
$dti_utc->setTimestamp(INF):
DateRangeError: Seconds must be a finite number between -9223372036854775808 and 9223372036854775807, INF given
$dti_bln->setTimestamp(INF):
DateRangeError: Seconds must be a finite number between -9223372036854775808 and 9223372036854775807, INF given
###################################################
$dt_utc->setTimestamp(-INF):
DateRangeError: Seconds must be a finite number between -9223372036854775808 and 9223372036854775807, -INF given
$dt_bln->setTimestamp(-INF):
DateRangeError: Seconds must be a finite number between -9223372036854775808 and 9223372036854775807, -INF given
$dti_utc->setTimestamp(-INF):
DateRangeError: Seconds must be a finite number between -9223372036854775808 and 9223372036854775807, -INF given
$dti_bln->setTimestamp(-INF):
DateRangeError: Seconds must be a finite number between -9223372036854775808 and 9223372036854775807, -INF given
###################################################
$dt_utc->setTimestamp(1.8446744073709552E+19):
DateRangeError: Seconds must be a finite number between -9223372036854775808 and 9223372036854775807, 1.84467e+19 given
$dt_bln->setTimestamp(1.8446744073709552E+19):
DateRangeError: Seconds must be a finite number between -9223372036854775808 and 9223372036854775807, 1.84467e+19 given
$dti_utc->setTimestamp(1.8446744073709552E+19):
DateRangeError: Seconds must be a finite number between -9223372036854775808 and 9223372036854775807, 1.84467e+19 given
$dti_bln->setTimestamp(1.8446744073709552E+19):
DateRangeError: Seconds must be a finite number between -9223372036854775808 and 9223372036854775807, 1.84467e+19 given
###################################################

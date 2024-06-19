--TEST--
Bug #51393 (DateTime::createFromFormat() fails if format string contains timezone)
--INI--
date.timezone=UTC
--FILE--
<?php
$dt = DateTime::createFromFormat('O', '+0800');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('P', '+08:00');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('p', '+08:00');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('O', '-0800');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('P', '-08:00');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('p', '-08:00');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('O', 'Z');
var_dump($dt);

$dt = DateTime::createFromFormat('P', 'Z');
var_dump($dt);

$dt = DateTime::createFromFormat('p', 'Z');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('[O]', '[+0800]');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('[P]', '[+08:00]');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('[p]', '[+08:00]');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('[O]', '[-0800]');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('[P]', '[-08:00]');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('[p]', '[-08:00]');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('O', 'GMT+0800');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('P', 'GMT+08:00');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('p', 'GMT+08:00');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('O', 'GMT-0800');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('P', 'GMT-08:00');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('p', 'GMT-08:00');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('[O]', '[GMT+0800]');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('[P]', '[GMT+08:00]');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('[p]', '[GMT+08:00]');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('[O]', '[GMT-0800]');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('[P]', '[GMT-08:00]');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('[p]', '[GMT-08:00]');
var_dump($dt->getOffset(), $dt->getTimezone()->getName());

$dt = DateTime::createFromFormat('O', 'invalid');
var_dump($dt);
?>
--EXPECT--
int(28800)
string(6) "+08:00"
int(28800)
string(6) "+08:00"
int(28800)
string(6) "+08:00"
int(-28800)
string(6) "-08:00"
int(-28800)
string(6) "-08:00"
int(-28800)
string(6) "-08:00"
bool(false)
bool(false)
int(0)
string(6) "+00:00"
int(28800)
string(6) "+08:00"
int(28800)
string(6) "+08:00"
int(28800)
string(6) "+08:00"
int(-28800)
string(6) "-08:00"
int(-28800)
string(6) "-08:00"
int(-28800)
string(6) "-08:00"
int(28800)
string(6) "+08:00"
int(28800)
string(6) "+08:00"
int(28800)
string(6) "+08:00"
int(-28800)
string(6) "-08:00"
int(-28800)
string(6) "-08:00"
int(-28800)
string(6) "-08:00"
int(28800)
string(6) "+08:00"
int(28800)
string(6) "+08:00"
int(28800)
string(6) "+08:00"
int(-28800)
string(6) "-08:00"
int(-28800)
string(6) "-08:00"
int(-28800)
string(6) "-08:00"
bool(false)

<?php


$enc = file_get_contents('encrypted.txt');
var_dump(explode(' ',$enc));

$codes = file_get_contents('codes.txt');
var_dump(explode(' ', $codes));

var_dump(explode("\n", $codes));
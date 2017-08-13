<?php

include_once 'hts.php';

// Compute md5 sums from wordlist - gather information what range of sum is normal.
//
// plain:
// Max: 371
// Min: 104
// Peak: 240
//
// md5:
// Max: 373
// Min: 109
// Peak: 241
//
// double md5:
// Max: 384
// Min: 104
// Peak: 240

$sums = array();

$passwords = file(__DIR__.DIRECTORY_SEPARATOR.'rockyou.txt');
foreach ($passwords as $x => $password) {
    $intMD5Total = encryptSum(md5($password.$x));

    if (!isset($sums[$intMD5Total])) {
        $sums[$intMD5Total] = 0;
    }

    $sums[$intMD5Total]++;
}

ksort($sums);

$keys = array_keys($sums);

$maxValue = max($sums);
echo 'Max: '.max($keys)."\n";
echo 'Min: '.min($keys)."\n";
echo 'Peak: '.array_search($maxValue, $sums)."\n";
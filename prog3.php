<?php


function encryptSum($md5_string)
{
    $total = 0;
    $chars = str_split($md5_string, 1);

    foreach ($chars as $char) {
        $total += hexdec($char);
    }

    return $total;
}


function encryptString($plainText, $password)
{
    // $strString is the content of the entire file with serials
    $passwordMD5 = md5($password);
    $MD5Total = encryptSum($passwordMD5);

    $encrypted = array();
    $length = strlen($plainText);

    for ($i = 0; $i < $length; $i++) {
        $encrypted[] =  ord(substr($plainText, $i, 1))
                                +  hexdec(substr($passwordMD5, $i%32, 1))
                                -  $MD5Total;

        $MD5Total = encryptSum(substr(md5(substr($plainText,0,$i+1)), 0, 16).substr(md5($MD5Total), 0, 16));
    }

    return implode(' ' , $encrypted);
}
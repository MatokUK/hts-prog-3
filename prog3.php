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


function encryptString($strString, $strPassword)
{
    // $strString is the content of the entire file with serials
    $strPasswordMD5 = md5($strPassword);
    $intMD5Total = encryptSum($strPasswordMD5);
    //var_dump($intMD5Total,$strPasswordMD5);
    $arrEncryptedValues = array();
    $intStrlen = strlen($strString);
    for ($i = 0; $i < $intStrlen; $i++) {
      //  echo 'ORD '.substr($strString, $i, 1).' - '.ord(substr($strString, $i, 1))."\n";
        //echo 'HEX '.substr($strPasswordMD5, $i%32, 1)."\n";

        $arrEncryptedValues[] =  ord(substr($strString, $i, 1))
                                +  hexdec(substr($strPasswordMD5, $i%32, 1))
                                -  $intMD5Total;

       // echo 'NEXT TOTAL '.substr(md5(substr($strString,0,$i+1)), 0, 16)
         //   .  substr(md5($intMD5Total), 0, 16)."\n";
        $intMD5Total = encryptSum(substr(md5(substr($strString,0,$i+1)), 0, 16)
            .  substr(md5($intMD5Total), 0, 16));
    }

    return implode(' ' , $arrEncryptedValues);
}
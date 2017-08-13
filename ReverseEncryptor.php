<?php

class ReverseEncryptor
{

    private $codes;

    public function __construct($encryptedString)
    {
        $this->codes = explode(' ', $encryptedString);
    }

    public function solve()
    {
//        var_dump($this->codes[31]);
//        var_dump($this->codes[30]);
//        var_dump($this->codes[29]);
//        var_dump($this->codes[28]);
//        var_dump($this->codes[27]);


        var_dump($this->codes[11]);
        var_dump($this->codes[10]);
        var_dump($this->codes[9]);
        var_dump($this->codes[8]);
        var_dump($this->codes[7]);

        $this->decryptCode($this->codes[7], '-');
    }


    private function decryptCode($code, $char = null)
    {
        $ord = ord($char);
        var_dump($ord);
       // $arrEncryptedValues[] =  ord(substr($strString, $i, 1))
       //     +  hexdec(substr($strPasswordMD5, $i%32, 1))
       //     -  $intMD5Total;
    }
}
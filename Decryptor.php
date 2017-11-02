<?php

class Decryptor
{
    private $hash;
    private $nextSum;
    private $initSum;
    private $codes;

    public function __construct($hash, $initSum, $codes)
    {
        $this->hash = $hash;
        $this->codes = $codes;
        $this->initSum = $initSum;
    }

    public function decrypt()
    {
        $plaintext = '';
        $this->nextSum = $this->initSum;

        $idx = 0;
        foreach ($this->codes as $code) {
            $plaintext .= chr((int)$code + $this->nextSum - (int) hexdec(substr($this->hash, $idx % 32,1)));

            $this->nextSum = encryptSum((substr(md5($plaintext), 0, 16)).substr(md5($this->nextSum), 0, 16));
            $idx++;

        }

        return preg_match('/.+1\.1\s$/', $plaintext) ? $plaintext : false;
    }
}
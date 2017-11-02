<?php

use Symfony\Component\Console\Helper\ProgressBar;

class ReverseEncryptorSecond
{
    private $output;
    private $codes;
    private $guessPassword;

    private $initPlainText;
    private $plainText;
    private $nextSum;
    private $h;

    private $indexes;


    private $possibilities = array();
    private $knownPlaintext = array();

    public function __construct($output, $encryptedString, $combination)
    {
        $this->output = $output;
        $this->codes = explode(' ', $encryptedString);
        $this->combination = $combination;
    }

    public function setDecryptIndexes(array $indexes)
    {
        $this->indexes = $indexes;
    }

    public function setKnownPlaintextPositions(array $knownPlaintext)
    {
        $this->knownPlaintext = $knownPlaintext;
    }

    public function solve()
    {
        $progress = new ProgressBar($this->output, $this->combination->getPossibilitiesCount());
        $progress->setRedrawFrequency($this->combination->getPossibilitiesCount() / 10);

        do {
            list($solutionA, $passA, $passB, $passC) = $this->combination->current();
            $this->guessPassword = $solutionA['password_hash'].$passA.$passB.$passC;
            $this->initPlainText = $solutionA['plain_text'];
            $this->nextSum = $solutionA['next_total'];
            $this->plainText = $this->initPlainText;

            $wtf = true;
            foreach ($this->indexes as $idx) {
                $decrypted = $this->decryptCode($this->codes[$idx], $idx);

                if (false === $decrypted) {
                    $wtf = false;
                    break;
                }
            }

            if ($wtf) {
                $this->h = '';
                if ($this->decryptPasswordHash($this->knownPlaintext)) {
                    $this->possibilities[] = array('init_total' => $solutionA['init_total'],
                                                    'plain_text' => $this->plainText,
                                                    'password_hash' => $solutionA['password_hash'].$passA.$passB.$passC.$this->h,
                                                    'next_total' => $this->nextSum);
                }
            }

            $this->combination->next();
            $progress->advance();
        } while ($this->combination->valid());

        return $this->possibilities;
    }


    private function decryptCode($code, $position)
    {
        $passwordHexValueOnPosition = hexdec(substr($this->guessPassword, $position % 32, 1));

        $plaintext = $code + $this->nextSum - $passwordHexValueOnPosition;

        if ($plaintext > 64 && $plaintext < 91 || $plaintext > 47 && $plaintext < 58) { // [0-9A-Z]
            $this->plainText .= chr($plaintext);
            $this->nextSum = encryptSum(substr(md5($this->plainText), 0, 16) . substr(md5( $this->nextSum), 0, 16));

            return $passwordHexValueOnPosition;
        }

        return false;
    }

    private function decryptPasswordHash(array $codes)
    {
        foreach ($codes as $code => $char) {
            $passwordHexValueOnPosition = $code + $this->nextSum - ord($char);
            if ($passwordHexValueOnPosition > 0 && $passwordHexValueOnPosition < 16) { // [0..16]
                $this->plainText .= $char;
                $plaintextMd5 = md5($this->plainText);
                $this->nextSum = encryptSum(substr($plaintextMd5, 0, 16) .  substr(md5($this->nextSum), 0, 16));

                $this->h .= dechex($passwordHexValueOnPosition);
            } else {
                return false;
            }
        }

        return true;
    }
}
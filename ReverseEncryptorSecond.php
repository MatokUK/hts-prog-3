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

    public function solve()
    {
        $progress = new ProgressBar($this->output, $this->combination->getPossibilitiesCount());
        $progress->setRedrawFrequency($this->combination->getPossibilitiesCount() / 100);

        do {
            list($solutionA, $passA, $passB, $passC) = $this->combination->current();
            $this->guessPassword = $solutionA['password_hash'].$passA.$passB.$passC;
            $this->initPlainText = $solutionA['plain_text'];


            foreach ($this->indexes as $idx) {

            }
            $decrypt0 = $this->decryptCode($this->codes[4], 4, $solutionA['next_total'], $solutionA['plain_text']);
            if (false !== $decrypt0) {

                $decrypt1 = $this->decryptCode($this->codes[5], 5, $decrypt0[2], $solutionA['plain_text'] . $decrypt0[0]);
                if (false !== $decrypt1) {
                    $decrypt2 = $this->decryptCode($this->codes[6], 6, $decrypt1[2], $solutionA['plain_text'] . $decrypt0[0] . $decrypt1[0]);


                    if (false !== $decrypt2) {
                        $this->plainText = $solutionA['plain_text'] . $decrypt0[0] . $decrypt1[0] . $decrypt2[0];
                        $this->nextSum = $decrypt2[2];
                        $this->h = '';

                        if ($this->decryptPasswordHash(array($this->codes[7] => '-', $this->codes[8] => 'O', $this->codes[9] => 'E', $this->codes[10] => 'M', $this->codes[11] => '-'))) {
                            $this->possibilities[] = array('init_total' => $solutionA['init_total'],
                                'plain_text' => $this->plainText,
                                'password_hash' => $solutionA['password_hash'] . dechex($decrypt0[1]) . dechex($decrypt1[1]) . dechex($decrypt2[1]) .
                                    $this->h,
                                'next_total' => $this->nextSum);
                        }
                    }
                }
            }


            $this->combination->next();
            $progress->advance();
        } while ($this->combination->valid());

        return $this->possibilities;
    }


    private function decryptCode($code, $position, $totalGuess, $previousDecryptedText = '')
    {
        $passwordHexValueOnPosition = hexdec(substr($this->guessPassword, $position % 32, 1));

        $plaintext = $code + $totalGuess - $passwordHexValueOnPosition;

        if ($plaintext > 64 && $plaintext < 91 || $plaintext > 47 && $plaintext < 58) {
            $plaintextMd5 = $previousDecryptedText.chr($plaintext);
            $nextTotal = encryptSum(substr(md5($plaintextMd5), 0, 16)
                .  substr(md5($totalGuess), 0, 16));


            return array(chr($plaintext), $passwordHexValueOnPosition, $nextTotal);
        }

        return false;
    }

    private function decryptPasswordHash(array $codes)
    {
        foreach ($codes as $code => $char) {
            $passwordHexValueOnPosition = $code + $this->nextSum - ord($char);
            if ($passwordHexValueOnPosition > 0 && $passwordHexValueOnPosition < 16) {
                $this->plainText .= $char;
                $plaintextMd5 = md5($this->plainText);
                $this->nextSum = encryptSum(substr($plaintextMd5, 0, 16)
                    .  substr(md5($this->nextSum), 0, 16));

                $this->h .= dechex($passwordHexValueOnPosition);
            } else {
                return false;
            }
        }

        return true;
    }
}
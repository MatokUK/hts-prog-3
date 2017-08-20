<?php

use Symfony\Component\Console\Helper\ProgressBar;

class ReverseEncryptor
{
    private $output;
    private $codes;
    private $guessPassword;

    private $possibilities = array();

    public function __construct($output, $encryptedString, $combination)
    {
        $this->output = $output;
        $this->codes = explode(' ', $encryptedString);
        $this->combination = $combination;
    }

    public function solve()
    {
        $progress = new ProgressBar($this->output, $this->combination->getPossibilitiesCount());
        $progress->setRedrawFrequency(90000);

        $T = 0;
        $V = 0;

        echo implode('',$this->combination->current());
        do {
            $T++;
            list($total, $passA, $passB, $passC) = $this->combination->current();
            $this->guessPassword = $passA.$passB.$passC;

         //   echo 'pass hash: '.$passA.$passB.$passC."\n";

            $decrypt = $this->decryptCode($this->codes[0], 0, $total);
           // var_dump($decrypt);
            if (false !== $decrypt) {
                $decrypted = $decrypt[0];
                $passwordHash = $decrypt[1];
                $total_0 = $decrypt[2];

                $decrypt1 = $this->decryptCode($this->codes[1], 1, $total_0, $decrypt[0]);
               // var_dump($decrypt1);
                if (false !== $decrypt1) {
                    $decrypt2 = $this->decryptCode($this->codes[2], 2, $decrypt1[2], $decrypt[0].$decrypt1[0]);

                    if (false !== $decrypt2) {
                        $nextPasswordHash = $this->decryptPasswordHash($this->codes[3], '-', $decrypt2[2], $decrypted.$decrypt1[0].$decrypt2[0]);
                        if ($nextPasswordHash) {
                            $this->possibilities[] = array('init_total' => $total,
                                                           'plain_text' => $decrypted.$decrypt1[0].$decrypt2[0].'-',
                                                           'password_hash' => dechex($passwordHash).dechex($decrypt1[1]).dechex($decrypt2[1]).dechex($nextPasswordHash[0]),
                                                           'next_total' => $nextPasswordHash[1]);
                           // if ($total == 253) {
                              /*  printf("\n\nInit total: %d", $total);
                                printf("\nDecrypted code[0]: %s | Password: %s | Next total: %d", $decrypted, $passwordHash, $total_0);
                                printf("\nDecrypted code[1]: %s | Password: %s | Next total: %d", $decrypt1[0], $decrypt1[1], $decrypt1[2]);
                                printf("\nDecrypted code[2]: %s | Password: %s | Next total: %d", $decrypt2[0], $decrypt2[1], $decrypt2[2]);
                                var_dump($nextPasswordHash);*/
                                $V++;
                        //    }
                        }
                     //   exit;

                    }

                    //var_dump($this->guessPassword, $this->codes[1]);
                    //var_dump($decrypt1);


                }

            }


            $this->combination->next();
            $progress->advance();
        } while ($this->combination->valid());

var_dump($T, $V);

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

    private function decryptPasswordHash($code, $char, $totalGuess, $decryptedPlaintext = '')
    {
        //$passwordHexValueOnPosition = hexdec(substr($this->guessPassword, $position % 32, 1));
        $passwordHexValueOnPosition = $code + $totalGuess - ord($char);
        if ($passwordHexValueOnPosition > 0 && $passwordHexValueOnPosition < 16) {
            $plaintextMd5 = md5($decryptedPlaintext.'-');
            $nextTotal = encryptSum(substr($plaintextMd5, 0, 16)
                .  substr(md5($totalGuess), 0, 16));

            return array($passwordHexValueOnPosition, $nextTotal);
        }

        return false;
    }
}
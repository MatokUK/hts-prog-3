<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;

/*
 *
 VXT-H6C-OEM-TC1-1.1
10N-I82-OEM-V0Z-1.1
07I-31P-OEM-PCT-1.1
7X1-HSY-OEM-AA4-1.1
O4G-Q6H-OEM-DPP-1.1

array(1) {
  [0]=>
  array(2) {
    [0]=>
    int(213)
    [1]=>
    string(32) "4a26baf9bf233163549e1047933f7c81"
  }
}
Finished in 12 seconds


my codes:
-123 -99 -189 -159 -173 -221 -190 -195 -135 -105 -146 -164 -131 -231 -205 -178 -218 -179 -176 -201 -167 -189 -190 -203 -159 -212 -192 -197 -173 -167 -163 -194 -127 -154 -145 -217 -180 -200 -217 -241 -139 -170 -178 -175 -173 -139 -158 -162 -173 -201 -151 -144 -127 -167 -138 -226 -182 -182 -151 -225 -169 -100 -201 -153 -208 -144 -151 -173 -155 -130 -124 -230 -188 -168 -215 -164 -209 -199 -193 -227 -107 -187 -144 -200 -190 -147 -193 -172 -127 -131 -164 -153 -187 -176 -131 -178 -205 -135 -167 -237
 */

class ReverseEncryptionCommand extends \Symfony\Component\Console\Command\Command
{
    const COLOR_DASH = 'yellow';
    const COLOR_MIDDLE = 'red';
    const COLOR_END = 'green';

    private $output;

    protected function configure()
    {
        $this
            ->setName('hts:prog-3')
            ->setDescription('Reverse encryption.')
            ->addArgument('cmd', InputArgument::REQUIRED)
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $command = $input->getArgument('cmd');
        $password = $input->getOption('password');

        switch ($command) {
            case 'dump':
                $this->printInfo($password);
                $output->writeln("\nTotal: ".encryptSum(md5($password)));
                $output->writeln("\nMD5: ".md5($password));
                break;

            case 'encrypt':
                $codes = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'codes.txt');
                $encoded = encryptString($codes, $password);
                var_dump(array_chunk( explode(' ', $encoded), 32));
                break;

            case 'decrypt':
                $start = time();
                $sumVector = $this->getPobabilitySumVector();

               // $codes = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'codes.txt');
               // $encoded = encryptString($codes, $password);
                $encoded = '-123 -99 -189 -159 -173 -221 -190 -195 -135 -105 -146 -164 -131 -231 -205 -178 -218 -179 -176 -201 -167 -189 -190 -203 -159 -212 -192 -197 -173 -167 -163 -194 -127 -154 -145 -217 -180 -200 -217 -241 -139 -170 -178 -175 -173 -139 -158 -162 -173 -201 -151 -144 -127 -167 -138 -226 -182 -182 -151 -225 -169 -100 -201 -153 -208 -144 -151 -173 -155 -130 -124 -230 -188 -168 -215 -164 -209 -199 -193 -227 -107 -187 -144 -200 -190 -147 -193 -172 -127 -131 -164 -153 -187 -176 -131 -178 -205 -135 -167 -237';
                $encodedArr = explode(' ', $encoded);


                // SOLUTION FOR XXX-... => [0..3]
                $output->writeln('DECRYPTING FIRST:');
                $combination = new Combination(array($sumVector, '0123456789abcdef', '0123456789abcdef', '0123456789abcdef'));
                $r = new ReverseEncryptor($output, $encoded, $combination);


                $possibilities = $r->solve();

                // SOLUTION FOR ....XXX-OEM-... => [0..11]
                $output->writeln('DECRYPTING SECOND:');
                $combination2 = new Combination(array($possibilities, '0123456789abcdef', '0123456789abcdef', '0123456789abcdef'));

                $r2 = new ReverseEncryptorSecond($output, $encoded, $combination2);
                $r2->setDecryptIndexes(array(4,5,6));
                $r2->setKnownPlaintextPositions(array($encodedArr[7] => '-', $encodedArr[8] => 'O', $encodedArr[9] => 'E', $encodedArr[10] => 'M', $encodedArr[11] => '-'));

                $possibilities = $r2->solve();

                // SOLUTION FOR .......-...-XXX- => [0..16]
                $output->writeln('DECRYPTING THIRD:');
                $combinationForEndFirstLine = new Combination(array($possibilities, '0123456789abcdef', '0123456789abcdef', '0123456789abcdef'));
                $solutionFirstLineEnding = new ReverseEncryptorSecond($output, $encoded, $combinationForEndFirstLine);
                $solutionFirstLineEnding->setDecryptIndexes(array(12,13,14));
                $solutionFirstLineEnding->setKnownPlaintextPositions(array($encodedArr[15] => '-', $encodedArr[16] => '1', $encodedArr[17] => '.', $encodedArr[18] => '1', $encodedArr[19] => "\n"));

                $possibilities = $solutionFirstLineEnding->solve();
               /* foreach ($possibilities as $item) {
                    printf("\nInit Total: %d Password: %s | next total: %d Text:\n%s \n\n", $item['init_total'], $item['password_hash'], $item['next_total'], $item['plain_text']);
                }*/

                // SOLUTION FOR BEGINNING OF SECOND LINE
                $combinationForSecondLine = new Combination(array($possibilities, '0123456789abcdef', '0123456789abcdef', '0123456789abcdef'));
                $solutionSecondLineBeginning = new ReverseEncryptorSecond($output, $encoded, $combinationForSecondLine);
                $solutionSecondLineBeginning->setDecryptIndexes(array(20,21,22));
                $solutionSecondLineBeginning->setKnownPlaintextPositions(array($encodedArr[23] => '-'));
                $possibilities = $solutionSecondLineBeginning->solve();

                // SOLUTION FOR MID OF SECOND LINE
                $combinationForSecondLine = new Combination(array($possibilities, '0123456789abcdef', '0123456789abcdef', '0123456789abcdef'));
                $solutionSecondLineMid = new ReverseEncryptorSecond($output, $encoded, $combinationForSecondLine);
                $solutionSecondLineMid->setDecryptIndexes(array(24,25,26));
                $solutionSecondLineMid->setKnownPlaintextPositions(array($encodedArr[27] => '-', $encodedArr[28] => 'O', $encodedArr[29] => 'E', $encodedArr[30] => 'M', $encodedArr[31] => '-'));
                $possibilities = $solutionSecondLineMid->solve();

                $hashes = [];
                foreach ($possibilities as $item) {
                //    printf("\nInit Total: %d Password: %s | next total: %d Text:\n%s \n\n", $item['init_total'], $item['password_hash'], $item['next_total'], $item['plain_text']);
                    $hashes[] = array($item['init_total'], $item['password_hash']);
                }


               // $hashes = array(array('6f7eb7ddc2bcd2021d8350a778117ccc', 253), array('6f7eb7ddc2bcd2021d8324d9cb1c1c3f', 253), array('6f7eb7ddc2bcd2021d83d8d9795c2422', 253), array('6f7eb7ddc2bcd2021d8341f7967a838a', 253));
                foreach ($hashes as $hash) {
                    $decryptor = new Decryptor($hash[1], $hash[0], $encodedArr);
                    $plainText = $decryptor->decrypt();
                    if ($plainText) {
                        $output->writeln($plainText);
                    }
                }
                var_dump($hashes);

                $output->writeln(sprintf("Finished in %d seconds\n\n", (time() - $start)));
                break;
        }
    }

    private function getPobabilitySumVector()
    {
        $seed = 240;
        $max = 390;
        $min = 100;

        $idx = 0;
        $vector = array($seed);
        do {
            $idx++;
            if ($seed + $idx < $max) {
                $vector[] = $seed + $idx;
            }
            if ($seed - $idx > $min) {
                $vector[] = $seed - $idx;
            }
        } while ($seed + $idx < $max && $seed - $idx > $min);

        return $vector;
    }


    private function printInfo($password)
    {
        $codes = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'codes.txt');
        $encoded = encryptString($codes, $password);

        $encoded = explode(' ', $encoded);

        foreach ($encoded as $idx => $value) {
            if (!empty($codes) && $idx % 20 == 0) {
                for ($i = $idx; $i < $idx + 20; $i ++) {
                    if ($codes[$i] != "\n") {
                        $this->printInfoChar($codes[$i], $i % 20);
                    } else {
                        $this->printInfoChar('\n', $i % 20);
                        $this->output->writeln('');
                    }
                }
            }

            $this->printInfoChar($value, $idx % 20);

            if ($idx > 8 && ($idx +1)  % 20 == 0) {
                $this->output->writeln('');
            }
        }
    }

    private function printInfoChar($char, $idx)
    {
        $str = sprintf("% 5s", $char);
        if ($idx < 19 && ($idx+1) % 4 == 0) {
            $str = '<fg='.self::COLOR_DASH.'>'.$str.'</>';
        } elseif ($idx > 7 && $idx < 11) {
            $str = '<fg='.self::COLOR_MIDDLE.'>'.$str.'</>';
        } elseif ($idx > 15) {
            $str = '<fg='.self::COLOR_END.'>'.$str.'</>';
        }

        $this->output->write($str);
    }
}
<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;

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
                $sumVector = $this->getPobabilitySumVector();
               // $sumVector = [253];

                $combination = new Combination(array($sumVector, '0123456789abcdef', '0123456789abcdef', '0123456789abcdef'));



                $codes = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'codes.txt');
                $encoded = encryptString($codes, $password);
                $r = new ReverseEncryptor($output, $encoded, $combination);

                /**/

               // exit;

                $possibilities = $r->solve();

               /* foreach ($posiblitities as $item) {
                    if ('99Z-' ==  $item['plain_text'])
                        printf("Init Total: %d Text: %s Password: %s | newxt to: %d\n", $item['init_total'], $item['plain_text'], $item['password_hash'], $item['next_total']);
                }*/


                $combination2 = new Combination(array($possibilities, '0123456789abcdef', '0123456789abcdef', '0123456789abcdef'));

                $r2 = new ReverseEncryptorSecond($output, $encoded, $combination2);
           //     var_dump($r2);
                $possibilities = $r2->solve();
                foreach ($possibilities as $item) {
                    //var_dump($item);exit;
                   // if (strpos($item['password_hash'], '6f7') !== false && strpos($item['plain_text'], '99Z-KH') !== false)
                        printf("Init Total: %d Text: %s Password: %s | newxt to: %d\n", $item['init_total'], $item['plain_text'], $item['password_hash'], $item['next_total']);
                }
                var_dump(count($possibilities));


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
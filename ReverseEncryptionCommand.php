<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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
                break;

            case 'encrypt':
                $codes = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'codes.txt');
                $encoded = encryptString($codes, $password);
                var_dump(array_chunk( explode(' ', $encoded), 32));
                break;

            case 'decrypt':
                $str = 'A';
                for ($i = 0; $i < 100; $i ++) {
                    echo $str.' ';
                    $str ++;

                }
                exit;
                $codes = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'codes.txt');
                $encoded = encryptString($codes, $password);
                $r = new ReverseEncryptor($encoded);
                $r->solve();
                break;
        }
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
<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new ReverseEncryptionCommand());

$application->run();


//$enc = file_get_contents('encrypted.txt');
//var_dump(explode(' ',$enc));

//

//var_dump(explode("\n", $codes));


//
//echo "\n\n";


//$md5 = md5('foobar');
//var_dump(evalCrossTotal($md5));
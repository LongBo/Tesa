<?php
/**
 *@author longbo
 */

namespace Tesa;
use Tesa\Libs\Cmd;

require __DIR__ . '/vendor/autoload.php';

try {
    $app = new Cmd();
    $app->run();
} catch (\Exception $e) {
    echo $e->getMessage()."\n";
}

exit;

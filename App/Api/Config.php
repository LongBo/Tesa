<?php
/**
 *@author longbo
 */
namespace Tesa\App\Api;

use Tesa\Libs\Container;
use Tesa\Libs\ConfigBase;

class Config extends ConfigBase
{
    public $hostList = [
        'online' => 'https://api.onlinedomain.com',
        'test' => 'http://%s.api.testdomain.com',
    ];

    public $secret = '';

    const APP_DIR = 'actions';

    public $apiHeader = [
        "CHANNEL: App Store",
        "Version: 1",
        "OS: iOS",
    ];

    //public $apiHeader = [];

    public function __construct($env) {
        $this->env = $env;
        $dir =  __DIR__ . '/'. self::APP_DIR;
        $this->apiDir = str_replace('\\', '/', $dir);
        Container::register('request', function(){
                return new Play($this);
            }
        );
    }

    public function getHeader() {
        return array_merge($this->header, $this->apiHeader);
    }

}


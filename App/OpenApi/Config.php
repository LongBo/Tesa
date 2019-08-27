<?php
/**
 *@author longbo
 */
namespace Tesa\App\Oapi;

use Tesa\Libs\Container;
use Tesa\Libs\ConfigBase;

class Config extends ConfigBase
{
    public $hostList = [
        'online' => 'https://openapi.onlinedomain.com',
        'test' => 'http://openapi.test.com',
        ];

    const CLIENT_FILE = '_common/client.json';
    const OPEN_DIR = 'actions';

    public static $oauthAction = 'oauth2.authorize';

    public $secret = '';
    public $clientId = '';

    public $clientArr = [];
    public $clientInfo = [];

    public function __construct($env = 'test') {
        $dir =  __DIR__ . '/'. self::OPEN_DIR;
        $this->apiDir = str_replace('\\', '/', $dir);
        $this->env = $env;
        $this->setClient();
        Container::register('request', function(){
                return new Play($this);
            }
        );
    }

    public function setClient($clientId = '') {
        if ($clientId) {
            $this->clientArr = ClientConfig::getClient($this->env);
            if (!empty($this->clientArr[$clientId])) {
                $this->clientId = $this->clientArr[$clientId]['client_id'];
                $this->secret = $this->clientArr[$clientId]['client_secret'];
                $this->clientInfo = $this->clientArr[$clientId];
                $this->saveClientInfo($this->clientArr[$clientId]);
            } else {
                throw new \Exception('clientId is not exist');
            }
        }
        else {
            $client = json_decode(parent::getContentFromFile(self::CLIENT_FILE), true);
            $this->clientInfo = $client;
            $this->clientId = $client['client_id'];
            $this->secret = $client['client_secret'];
        }

        if (!$this->clientId) {
            throw new \Exception('no clientId');
        }
    }

    public function getApi($key) {
        $confArr = parent::getApi($key);
        $this->fillClientParams(
            ['client_id', 'client_secret', 'redirect_uri', 'grant_type'],
            $confArr
        );
        $confArr['get']['client_id'] = $this->clientId;
        return $confArr;
    }

    public function saveClientInfo($clientInfo) {
        if (is_array($clientInfo)) {
            $file = $this->getApiDir() . '/' . self::CLIENT_FILE;
            file_put_contents(
                $file,
                json_encode($clientInfo, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)
            );
        }
    }


}


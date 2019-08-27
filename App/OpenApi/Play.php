<?php
/**
 * @author:longbo
 */
namespace Tesa\App\Oapi;

use Tesa\Libs\PlayBase;

class Play extends PlayBase
{
    public function __construct(Config $config) {
        $this->appConfig = $config;
    }

    protected function before() {
        self::$config['signCase'] = 'upper';
    }

    protected function after() {
        if ($res = json_decode(self::$response, true)) {
            if (isset($res['access_token'])) {
                $token = array(
                    'key' => 'access_token',
                    'value' => $res['access_token']
                );
                $this->setToken(json_encode($token));
            }
        }
        if (!empty(self::$rData['autoLogin'])) {
            $this->getToken();
        }
        parent::after();
    }

    public function getToken(){
        $headers = explode("\r\n", parent::$response);
        if (empty($headers)) {
            throw new \Exception('Header is empty');
        }

        foreach ($headers as $headValue) {
            if (false !== stripos($headValue, 'Location')) {
                preg_match('/code=([a-zA-Z\d]+)/i', $headValue, $matches);
                $code = $matches[1] ?: null;
                break;
            }
        }
        if (!empty($code)) {
            $client_id = self::$rData['getParams']['client_id'];
            self::$rData['postParams'] = [];
            unset(self::$rData['getParams']);
            self::$rData['postParams']['code'] = $code;
            self::$rData['postParams']['grant_type'] = 'authorization_code';
            self::$rData['postParams']['client_secret'] = self::$rData['secret'];
            self::$rData['postParams']['scope'] = '';
            self::$rData['postParams']['client_id'] = $client_id;
            self::$rData['backHeader'] = 0;
            self::$rData['autoLogin'] = 0;
            self::$rData['action'] = 'oauth2/token';
            self::$rData['fileName'] = 'oauth2_token.json';
            $this->execute();
        } else {
            var_export($headers);
            throw new \Exception('oauth code is null');
        }
    }


}

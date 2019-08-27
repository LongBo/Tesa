<?php
/**
 * @author longbo
 */
namespace Tesa\App\Api;

use Tesa\Libs\PlayBase;

class Play extends PlayBase
{

    public function __construct(Config $config) {
        $this->appConfig = $config;
        self::$config['signKey'] = ['sign' => 'signature'];
        //self::$config['output'] = 'raw';
    }

    protected function setSign() {
        self::$rData['postParams']['timestamp'] = time();
        $dataParams = self::$rData['postParams'];
        ksort($dataParams);
        $sign_str = urldecode(http_build_query($dataParams));
        $sign_str .= '&key=' . self::$rData['secret'];
        $this->sign = strtolower(md5($sign_str));
        self::$rData['postParams'][self::$config['signKey']['sign']] = $this->sign;
        return $this;
    }

    protected function after() {
        if ($res = json_decode(self::$response, true)) {
            if (isset($res['data']['token'])) {
                $token = array('key' => 'token', 'value' => $res['data']['token']);
                $this->setToken(json_encode($token));
            }
        }
        parent::after();
    }
}

<?php
/**
 *@author longbo
 */
namespace Tesa\Libs;

use Tesa\Libs\Curl;

class PlayBase
{
    const TIMEOUT = 20;

    public static $config = [
            'unsetKey' => ['sign'],
            'signKey' => ['time' => 'timestamp', 'sign' => 'sign'],
            'signCase' => 'lower',
            'timeType' => 0,
            'output' => 'pretty',
            'cachedOutput' => 1,
        ];
    public static $rData = [
            'method' => 'get',
            'secret' => '',
            'host' => '',
            'action' => '',
            'getParams' => [],
            'postParams' => [],
            'header' => [],
            'cookie' => '',
            'fileName' => '',
            'backHeader' => 0,
            'autoLogin' => 0,
        ];

    public static $response = null;
    public static $execTime = '';
    public static $execUrl = '';

    protected $allParams = [];
    protected $queryParams = [];
    protected $sign = '';
    protected $url = '';
    protected $appConfig;

    public function __construct() {
    }

    public function setRequestData($request = []) {
        self::$rData = array_merge(self::$rData, $request);
        return $this;
    }
    public function execute() {
        return $this->call('before')
            ->call('setAllParams')
            ->call('setSign')
            ->call('setQueryParams')
            ->call('setCompleteUrl')
            ->call('execRequest')
            ->call('after');
    }

    protected function call($method) {
        if (is_callable(array($this, $method))) {
            $this->$method();
        }
        //var_export($this);
        return $this;
    }

    protected function before() {
        return $this;
    }

    protected function setAllParams() {
        if (isset(self::$config['signKey']['time'])) {
            $time = self::$config['timeType'] == 1 ? date("Y-m-d H:i:s", time()) : time();
            self::$rData['getParams'][self::$config['signKey']['time']] = $time;
        }

        $this->allParams = array_merge(
            self::$rData['getParams'],
            self::$rData['postParams']
        );
        return $this;
    }

    protected function setSign() {
        $dataParams = $this->allParams;
        if (isset(self::$config['unsetKey'])) {
            foreach (self::$config['unsetKey'] as $val) {
                unset($dataParams[$val]);
            }
        }
        $sign_str = self::$rData['secret'];
        ksort($dataParams);
        reset($dataParams);
        foreach($dataParams as $key => $value) {
            $sign_str .= $key.$value;
        }
        $sign_str .= self::$rData['secret'];
        $this->sign = self::$config['signCase'] == 'lower' ?
                    strtolower(md5($sign_str)) :
                    strtoupper(md5($sign_str));
        return $this;
    }

    protected function setQueryParams() {
        $getParams = self::$rData['getParams'];
        $getParams[self::$config['signKey']['sign']] = $this->sign;
        $this->queryParams = $getParams;
        return $this;
    }

    protected function setCompleteUrl() {
        $query = empty($this->queryParams) ?
                '' : '?' . http_build_query($this->queryParams);

        $this->url = trim(self::$rData['host'], '/').'/'
            .trim(self::$rData['action'], '/')
            .$query;

        return $this;
    }

    protected function execRequest() {
        $response = new Curl();

        $response->setUrl($this->url)
                 ->setHeader(self::$rData['header'])
                 ->setCookie(self::$rData['cookie']);

        if (self::$rData['backHeader']) {
            $response->setBackHeader(true);
        }
        (!empty(self::$rData['postParams']) || (self::$rData['method'] == 'post')) ?
            $response->setPostData(self::$rData['postParams'])->post():
            $response->get();

        if ($response::$errno) {
           throw new \Exception($response::$error);
        }
        if ($response::$httpCode != 200 && !self::$rData['backHeader']) {
           throw new \Exception('http code:'.$response::$httpCode."\n".$response::$result);
        }
        self::$response = $response::$result;
        self::$execTime = $response::$execTime;
        return $this;
    }

    protected function after() {
        if (self::$config['output'] == 'pretty') {
            if ($data = json_decode(self::$response)) {
                self::$response = json_encode(
                    $data,
                    JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT
                );
            }
        }
        if (self::$config['cachedOutput'] == 1) {
            $this->cachedOutput();
        }
        return $this;
    }

    protected function cachedOutput(){
        $config = $this->appConfig;
        $file = $config->getApiDir().'/_output/'.self::$rData['fileName'];
        file_put_contents($file, self::$response);
    }

    protected function setToken($token){
        $config = $this->appConfig;
        $file = $config->getApiDir().'/_common/token';
        file_put_contents($file, $token);
    }

    function getUrl() {
        return $this->url;
    }
 
}

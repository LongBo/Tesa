<?php
/**
 *@author longbo
 */
namespace Tesa\Libs;

class ConfigBase 
{
    protected $hostList = [];
    protected $secret = ''; 
    protected $apiDir = ''; 
    protected $fileName = '';
    protected $env = '';
    protected $clientInfo = [];

    protected $header = [
        "User-Agent: Mozilla/5.0 (iPhone; U; CPU iPhone OS 7_0_6 like Mac OS X; zh-CN) AppleWebKit/537.51.1 (KHTML, like Gecko) Version/7.0 Mobile/11A501 Safari/9537.53",
        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
        "Accept-Language: zh-cn",
    ];
    public static $oauthAction;



    public function getHost() {
        if (!$this->env) {
            throw new \Exception('host is null!');
        }
        if ($this->env == 'online') {
            return $this->hostList[$this->env];
        } else {
            return sprintf($this->hostList['test'], $this->env);
        }
    }

    public function getHeader() {
        return $this->header;
    }

    public function getSecret() {
        return $this->secret;
    }

    public function getFileName() {
        return $this->fileName;
    }

    public function getApi($key) {
        if (empty($key)) {
            throw new \Exception("api name is null!");
        }
        $fileName = str_replace(array('.', '-'), '_', $key).'.json';
        $jsonApi = $this->getContentFromFile($fileName); 
        if ($confArr = json_decode($jsonApi, true)) {
            $this->arrFlip($confArr['get']);
            $this->arrFlip($confArr['post']);
            if ($confArr['needToken']) {
                $tokenArr = json_decode($this->getToken(), true);
                $confArr['method'] == 'post' ?
                    $confArr['post'][$tokenArr['key']] = $tokenArr['value']:
                    $confArr['get'][$tokenArr['key']] = $tokenArr['value'];
            }
            if ($commonConf = $this->getCommon()) {
                $confArr['get'] = array_merge($confArr['get'], $commonConf['get']);
                $confArr['post'] = array_merge($confArr['post'], $commonConf['post']);
            }
        } else {
            throw new \Exception('Action File is not json:'.$jsonApi);
        }
        $this->fileName = $fileName;
        return $confArr;
    }

    protected function getContentFromFile($fileName) {
        $file = $this->getApiDir() . '/' . $fileName;
        if (!$str = file_get_contents($file)) {
            throw new \Exception('Read File Failed:'.$file);
        }
        return $str;
    }

    protected function arrFlip(&$arr) {
        if ($arr) {
            foreach ($arr as $k => &$v) {
                if (is_null($v['default'])) {
                    $v = null;
                } else {
                    $v = $v['default'];
                }
            }
        }
    }

    protected function getToken() {
        return $this->getContentFromFile('_common/token');
    }

    protected function getCommon() {
        return json_decode($this->getContentFromFile('_common/common.json'), true);
    }

    public function getApiDir() {
        return $this->apiDir;
    }

    protected function fillClientParams($params, &$confArr){
        foreach ($params as $param) {
            if (isset($confArr['get'][$param])) {
                $confArr['get'][$param] = $this->clientInfo[$param];
            } elseif (isset($confArr['post'][$param])) {
                $confArr['post'][$param] = $this->clientInfo[$param];
            }
        }
    }


}


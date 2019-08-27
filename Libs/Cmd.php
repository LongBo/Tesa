<?php
/**
 *@author longbo
 */

namespace Tesa\Libs;

use Tesa\Libs\Container;
use Exception;

class Cmd
{
    static $projectConfigMap = [];

    static $shortOpt = "p:h:a:";
    static $longOpt = ["client:"];

    private $commands = [];
    private $request = [];

    public function __construct(){
        $this->commands = getopt(self::$shortOpt, self::$longOpt);
    }

    public function run(){
        $projectConf = $this->getConfigClass(
            trim($this->commands['p']),
            trim($this->commands['h'])
        );

        if (isset($this->commands['client'])) {
            $projectConf->setClient($this->commands['client']);
        }

        $isOauth = 0;
        $apiAction = trim(strtolower($this->commands['a']));
        if ($apiAction == 'oauth') {
            $apiAction = $projectConf::$oauthAction;
            $isOauth = 1;
        }
        $apiInfo = $projectConf->getApi($apiAction);

        $request = [];
        $request['host'] = $projectConf->getHost();
        $request['action'] = $apiInfo['action'];
        $request['secret'] = $projectConf->getSecret();
        $request['getParams'] = $this->fillData($apiInfo['get']);
        $request['postParams'] = $this->fillData($apiInfo['post']);
        $request['method'] = $apiInfo['method'];
        $request['header'] = $projectConf->getHeader();
        $request['fileName'] = $projectConf->getFileName();

        if ($isOauth) {
            $request['backHeader'] = 1;
            $request['autoLogin'] = 1;
        }

        $this->request = $request;

        $res = $this->requestData();
        $this->output($res);
    }

    public function output($res){
        echo "START:\n";
        echo $res::$response."\n";
        echo "ExecTime:\033[31m".$res::$execTime."ms\033[0m\n";
        echo "\033[36m".$res->getUrl()."\033[0m\n";
        echo "END\n";
    }

    public function requestData(){
        return Container::book('request')
                ->setRequestData($this->request)
                ->execute();
    }

    private function getConfigClass($project, $env){
        $projectConfClass = isset(self::$projectConfigMap[$project]) ?
            self::$projectConfigMap[$project] :
            'Tesa\\App\\'.ucfirst(strtolower($project)).'\\Config';

        if (class_exists($projectConfClass)) {
            return new $projectConfClass($env);
        } else {
            throw new Exception('config class is not found!');
        }
    }

    private function fillData($params){
        if (!empty($params)) {
            foreach ($params as $k => &$v) {
                fwrite(STDOUT, "Please input \033[31m{$k}\033[0m (default:\033[32m{$v}\033[0m): ");
                $input = trim(fgets(STDIN));
                if (is_null($input) || $input === '') {
                    if (is_null($v)) unset($params[$k]);
                } else {
                    $v = $input;
                }
            }
        }

        return $params;
    }

    public function setCommands($commands = [])
    {
        $this->commands = $commands;
    }
}


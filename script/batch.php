<?php
error_reporting(E_ALL);
$command = getopt("p:");

$apiDir = '';
$apiWriteDir = __DIR__.'';

$openapiDir = '';
$openapiWriteDir = __DIR__.'';

if ($command['p'] == 'openapi') {
    $apiDir = $openapiDir;
    $apiWriteDir = $openapiWriteDir;
}

$dataFormat = array();

$dirs = scandir($apiDir);

$explode_dir = array('.', '..', 'error');
foreach ($dirs as $dir) {

    if (!in_array($dir, $explode_dir) && is_dir($apiDir.$dir)) {

        $apiFiles = scandir($apiDir.$dir);
        foreach ($apiFiles as $file) {
            $apiFile = $apiDir.$dir.'/'.$file;
            if (!in_array($file, $explode_dir) && is_file($apiFile)) {

                $fileName = $apiWriteDir.$dir.'_'.strtolower(explode('.', $file)[0]).'.json';

                if (!file_exists($fileName)) {
                    var_dump($fileName);
                    $code = file_get_contents($apiFile);
                    preg_match('/this->form->rules\s+=\s+(array\(.*?);/is', $code, $matches);
                    $thisV = (object) array();
                    $params = array();
                    if (!empty($matches[1])) {
                        try {
                            $codes = str_replace('this', 'thisV', $matches[1]);
                            $arr_code = '$params=' . $codes . ';';
                            eval($arr_code);
                        } catch (\Exception $e) {
                            var_export($e->getMessage());
                        }
                    }
                    $dataWrite = array();
                    $dataWrite['action'] = $dir.'/'.(explode('.', $file)[0]);
                    $dataWrite['desc'] = '';
                    $newParams = array();
                    $needToken = 0;
                    foreach ($params as $k => $v) {
                        if (in_array($k, array('token'))) {
                            $needToken = 1;
                            continue;
                        }
                        $vs = array();
                        $vs['default'] = null;
                        if ($v['filter'] == 'required') {
                            $vs['required'] = 1;
                            $vs['type'] = 'string';
                        } else {
                            $vs['required'] = 0;
                            $vs['type'] = $v['filter'];
                        }
                        $vs['desc'] = '';
                        $newParams[$k] = $vs;
                    }
                    $dataWrite['get'] = (object) array();
                    $dataWrite['post'] = (object) $newParams;
                    $dataWrite['method'] = 'post';
                    $dataWrite['needToken'] = ($command['p'] == 'openapi') ? 1:$needToken;

                    $fs = file_put_contents($fileName, json_encode($dataWrite, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
                }
            } else {
                continue;
            }
        }

    }
}


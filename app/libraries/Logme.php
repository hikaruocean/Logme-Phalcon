<?php

namespace Hikaru\Libraries;

class Logme {

    private $apiKey;
    private $hostname;
    private $api = 'PHP';
    private $user_agent = '';
    private $JSIniPath;

    function __construct($apiKey, $hostname) {
            $this->apiKey = isset($apiKey) ? $apiKey : '';
            $this->hostname = isset($hostname) ? $hostname : '';
            $this->user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }

    public function setApiKey($apiKey) {
        $this->apiKey = $apiKey;
        return true;
    }

    public function getApiKey() {
        return $this->apiKey;
    }
    
    public function setHostname($hostname) {
        $this->hostname = $hostname;
        return true;
    }

    public function getHostname() {
        return $this->hostname;
    }
    
    public function setJsIniPath($iniPath) {
        $this->JSIniPath = $iniPath;
        return true;
    }

    public function getJsIniPath() {
        return $this->JSIniPath;
    }

    public function getAccessToken() {
        $ch = curl_init();    // initialize curl handle
        curl_setopt($ch, CURLOPT_URL, trim($this->hostname, '/') . '/accesstoken'); // set url to post to
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);              // Fail on errors
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    // allow redirects
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 15); // times out after 15s
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        $result_str = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result_str);
        return $result->accessToken;
    }

    public function getSecretKey($data) {
        return sha1($data);
    }

    public function sendLog(Array $ary) {
        $ak = $this->getAccessToken();
        $ary['API'] = isset($ary['API']) ? $ary['API'] : $this->api;
        $ary['accessToken'] = $ak;
        $body = json_encode($ary);
        $s = $this->getSecretKey($body . '@' . $this->apiKey);
        /**
         *  Send Curl
         */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, trim($this->hostname, '/') . '/?s=' . $s);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);              // Fail on errors
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    // allow redirects
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15); // times out after 15s
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        $result_str = curl_exec($ch);
        curl_close($ch);
        return json_decode($result_str);
    }

    public function sendLogJS(){
        $jsActionMap = parse_ini_file(rtrim($this->JSIniPath,'/').'/JSActionMap.ini');
        if(!isset($_POST['action']) || !isset($jsActionMap[trim($_POST['action'])]) || !$jsActionMap[trim($_POST['action'])]){
            echo json_encode(['result'=>'401','msg'=>'This Action not in JSActionMap']);
            die();
        }
        $_POST['API'] = 'JS';
        echo json_encode($this->sendLog($_POST));
    }
}

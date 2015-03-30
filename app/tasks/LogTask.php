<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LogTask
 *
 * @author hikaru
 */
class LogTask extends \Phalcon\CLI\Task{
    public function loginLogAction($params){
        try {
            $content = json_decode($params[0],true);
            $logme = new \Hikaru\Libraries\Logme($this->config->secret->apiKey, 'www.logservice.localhost');
            $result = $logme->sendLog($content);
            if ($result->result === '200') {
                echo 'success';
            } else {
                echo 'failed';
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
}

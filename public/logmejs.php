<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once($_SERVER['DOCUMENT_ROOT'].'/../app/libraries/Logme.php');
$Logme = new \Hikaru\Libraries\Logme('hikaru740126','www.logservice.localhost');
$Logme->setJsIniPath($_SERVER['DOCUMENT_ROOT'].'/');
$Logme->sendLogJS();
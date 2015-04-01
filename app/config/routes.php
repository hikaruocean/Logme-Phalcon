<?php
$router->removeExtraSlashes(true);
$router->setDefaults(array(
    'controller' => 'index',
    'action' => 'index'
));
$router->notFound('index::index');
$router->addGet('/','index::index');
$router->addGet('/accesstoken','index::getAccessToken');
$router->addGet('/testsendlog','index::testJobSendLog');
$router->addGet('/admin','report::index');
$router->addGet('/admin/logactionmap','actionmap::logActionMap');
$router->addGet('/admin/chart/{range:date|month|year}','chart::index');
$router->addGet('/admin/active/{range:date|month|year}','chart::active');
$router->addGet('/admin/register/{range:date|month|year}','chart::register');
$router->addGet('/admin/setting','setting::index');
$router->addGet('/logout','index::logout');

$router->addPost('/','index::logApi');
$router->addPost('/login','index::login');
$router->addPost('/admin/logactionmap/params/save','actionmap::actionMapParamsSave');
$router->addPost('/admin/logactionmap/action/save','actionmap::actionMapActionSave');
$router->addPost('/admin/chart/draw','chart::draw');
$router->addPost('/admin/active/draw','chart::activeDraw');
$router->addPost('/admin/register/draw','chart::registerDraw');
$router->addPost('/admin/setting/save','setting::save');
<?php

return new \Phalcon\Config(array(
    'database' => array(
        'adapter'     => 'Mysql',
        'host'        => 'localhost',
        'username'    => 'root',
        'password'    => '',
        'dbname'      => 'test',
        'charset'   => 'utf8'
    ),
    'collection' => array(
        'host'=>'127.0.0.1',
        'port'=>'27017',
        'username'=>'hikaru',
        'password'=>'k740605k',
        'dbname'=>'test'
    ),
    'application' => array(
        'controllersDir' => __DIR__ . '/../../app/controllers/',
        'modelsDir'      => __DIR__ . '/../../app/models/',
        'collectionsDir' => __DIR__ . '/../../app/collections/',
        'viewsDir'       => __DIR__ . '/../../app/views/',
        'librariesDir'     => __DIR__ . '/../../app/libraries/',
        'cacheDir'       => __DIR__ . '/../../app/cache/',
        'libExtendsDir' => __DIR__.'/../../app/libextends/',
        'baseUri'        => '',
    ),
    'job'=>array(
        'php' => '/usr/bin/php5',
        'index' => $_SERVER['DOCUMENT_ROOT'].'/../cli/index.php'
    ),
    'secret'=>array(
        'adminId' => 'hikaruocean',
        'adminPassword' => 'qeksnopre',
        'apiKey' => 'hikaru740126'
    )
));

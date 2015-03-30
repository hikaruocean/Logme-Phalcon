<?php

use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Session\Adapter\Libmemcached as SessionAdapter;
use Phalcon\Mvc\Router as Router;

/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new FactoryDefault();

/**
 * Set config to DI
 */
$di->set('config', $config, true);
/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->set('url', function () use ($config) {
    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
}, true);

/**
 * Set backgroundJob to DI
 */
$di->set('job', function() use($config) {
    $job = new \Hikaru\Libraries\Job();
    $job->php = $config->job->php;
    $job->index = $config->job->index;
    return $job;
}, true);

/**
 * Setting cache
 */
$di->set('cache',function() use($config){
    // Cache data for 2 days
    $frontCache = new Phalcon\Cache\Frontend\Data(array(
       "lifetime" => 172800
    ));

    //Create the Cache setting memcached connection options
    $cache = new Phalcon\Cache\Backend\Libmemcached($frontCache, array(
        'servers' => array(
            array('host' => 'localhost',
                  'port' => 11211,
                  'weight' => 1),
        ),
        'client' => array(
            Memcached::OPT_HASH => Memcached::HASH_MD5,
            Memcached::OPT_PREFIX_KEY => 'COMMON.',
        )
    ));
    return $cache;
},true);

/**
 * Setting router
 */
$di->set('router', function() {
    $router = new Router(false);//false表示關掉default的 Controller/Action URL配對
    require(__DIR__ . '/routes.php');
    return $router;
});

/**
 * Setting diapatcher
 */
$di->set('dispatcher', function () use ($di) {
    $dispatcher = new \Phalcon\Mvc\Dispatcher();
    require(__DIR__ . '/dispatch.php');
    return $dispatcher;
}, true
);


/**
 * Setting up the view component
 */
$di->set('view', function () use ($config) {

    $view = new View();

    $view->setViewsDir($config->application->viewsDir);

    $view->registerEngines(array(
        '.phtml' => function ($view, $di) use ($config) {

            $volt = new VoltEngine($view, $di);

            $volt->setOptions(array(
                'compiledPath' => $config->application->cacheDir,
                'compiledSeparator' => '_'
            ));

            $compiler = $volt->getCompiler();

            $compiler->addFunction('substr', function($resolvedArgs, $exprArgs) {
                if (function_exists('mb_substr')) {
                    return 'mb_substr(' . $resolvedArgs . ')';
                } else {
                    return 'substr(' . $resolvedArgs . ')';
                }
            })
            ->addFilter('u',function($resolvedArgs, $exprArgs){
                return 'urlencode('.$resolvedArgs.')';
            });

            return $volt;
        }
    ));
    return $view;
}, true);

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->set('db', function () use ($config) {
    return new DbAdapter(array(
        'host' => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname' => $config->database->dbname,
        'charset' => $config->database->charset
    ));
});

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->set('modelsMetadata', function () {
    return new MetaDataAdapter();
});

/**
 * mongodb connection
 */
$di->set('mongo', function() use($config) {
    $mongo = new MongoClient('mongodb://'.$config->collection->username.':'.$config->collection->password.'@'.$config->collection->host.':'.$config->collection->port.'/'.$config->collection->dbname);
    return $mongo->selectDB($config->collection->dbname);
}, true);

/**
 * mongodb Registering the collectionManager service
 */
$di->set('collectionManager', function() {
    $modelsManager = new Phalcon\Mvc\Collection\Manager();
    return $modelsManager;
}, true);

/**
 * Start the session the first time some component request the session service
 */
$di->set('session', function () {//session change to memk 
    try {
        //session_set_cookie_params(0, '/', '.euroasia.localhost');//set cookies domain
        $session = new SessionAdapter(array(
            'servers' => array(
                array('host' => '127.0.0.1', 'port' => 11211, 'weight' => 1),
            ),
            'client' => array(
                Memcached::OPT_HASH => Memcached::HASH_MD5,
                Memcached::OPT_PREFIX_KEY => 'SESSION.',
            ),
            'lifetime' => 3600,
            'prefix' => 'session_'
        ));
        $session->start();
    } catch (Exception $e) {
        die($e->getMessage());
    }
    return $session;
}, true);

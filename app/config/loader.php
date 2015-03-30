<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerDirs(
        array(
            $config->application->controllersDir,
            $config->application->collectionsDir,
            $config->application->modelsDir
        )
);
$loader->registerNamespaces(array(
    'Hikaru\Libraries' => $config->application->librariesDir,
    'Phalcon\Image\Adapter' => $config->application->libExtendsDir . 'image',
    'Phalcon\Paginator\Adapter' => $config->application->libExtendsDir . 'paginator',
    'Phalcon\Mvc\Collection' => $config->application->libExtendsDir . 'collection',
));
$loader->register();

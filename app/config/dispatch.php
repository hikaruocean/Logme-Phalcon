<?php

$evManager = $di->get('eventsManager');
$evManager->attach(
        'dispatch:beforeExecuteRoute', function($event, $dispatcher) use ($di) {
    //Check whether the "auth" variable exists in session to define the active role
    $id = $di->get('session')->get('id');
    if (!$id) {
        $role = 'Guest';
    } else {
        $role = 'Admin';
    }

    //Take the active controller/action from the dispatcher
    $controller = $dispatcher->getControllerName();
    $action = $dispatcher->getActionName();
    //perpare the ACL list
    $aclmts = filemtime(__DIR__ . '/acl.php');
    $aclcachepath = __DIR__ . '/../acl/aclcache_' . $aclmts;
    if (!file_exists($aclcachepath)) {
        require(__DIR__ . '/acl.php');
        file_put_contents($aclcachepath, serialize($acl));
    }
    $acl = unserialize(file_get_contents($aclcachepath));
    //Check if the Role have access to the controller (resource)
    $allowed = $acl->isAllowed($role, $controller, $action);
    if ($allowed != Phalcon\Acl::ALLOW) {
        //If he doesn't have access forward him to the index controller
        $dispatcher->forward(
                array(
                    'controller' => 'index',
                    'action' => 'index'
                )
        );
        //Returning "false" we tell to the dispatcher to stop the current operation
        return false;
    }
});
$evManager->attach(
        "dispatch:beforeException", function ($event, $dispatcher, $exception) {
    switch ($exception->getCode()) {
        case \Phalcon\Mvc\Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
        case \Phalcon\Mvc\Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
            $dispatcher->forward(
                    array(
                        'controller' => 'error',
                        'action' => 'show404'
                    )
            );
            return false;
    }
    $dispatcher->forward(array(
        'controller' => 'errors',
        'action' => 'show500'
    ));
    return false;
}
);
$dispatcher->setEventsManager($evManager);

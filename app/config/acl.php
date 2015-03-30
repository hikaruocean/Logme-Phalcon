<?php

//Create the ACL
$acl = new Phalcon\Acl\Adapter\Memory();

//The default action is DENY access
$acl->setDefaultAction(Phalcon\Acl::DENY);
//Register two roles, Users is registered users
//and guests are users without a defined identity
$roles = array(
    'admin' => new Phalcon\Acl\Role('Admin'),
    'guest' => new Phalcon\Acl\Role('Guest')
);
foreach ($roles as $r) {
    $acl->addRole($r);
}

//resources
$adminResources = array(
    'setting' => array('*'),
    'report' => array('*'),
    'actionmap' => array('*'),
    'chart'=> array('*'),
);

$publicResources = array(
    'index' => array('*'),
    'error' =>array('*')
);

$resourceSet = [
    $adminResources,
    $publicResources
];

foreach ($resourceSet as $v){
    foreach ($v as $resource => $actions) {
        $acl->addResource(new Phalcon\Acl\Resource($resource), $actions);
    }
}

//Grant access to su resource
foreach ($adminResources as $resource => $actions) {
    foreach ($actions as $action) {
        $acl->allow('Admin', $resource, $action);
    }
}

//Grant access to guest  resources
foreach ($publicResources as $resource => $actions) {
    foreach ($actions as $action) {
        $acl->allow('Admin', $resource, $action);
        $acl->allow('Guest', $resource, $action);
    }
}
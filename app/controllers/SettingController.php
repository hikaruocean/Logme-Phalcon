<?php

class SettingController extends ControllerBase
{

    public function initialize(){
        $this->view->setTemplateBefore('backend');
    }

    public function indexAction()
    {
        $am_ary = SysActionmap::find([
            'sort'=>['action'=>1]
            ]);
        $js_define = "var am_obj={".PHP_EOL;
        foreach($am_ary as $v){
            $actionmap[$v->action] = $v->params;
            if($v->RELATION){
                $this->view->loginAction = $v->action;
                $this->view->loginJoinKey = $v->RELATION['OWN_KEY'];
                $this->view->registerJoinKey = $v->RELATION['REL_KEY'];
            }
            if($v->SUBDOC){
                $this->view->registerAction = $v->action;
            }
            $js_define.=$v->action.":{".PHP_EOL;
            foreach($v->params as $pk => $pv){
                $js_define.=$pk.":".'"'.$pv.'",';
            }
            $js_define.='API:"string",CRT:"isodate"},'.PHP_EOL;
        }
        $js_define.="};".PHP_EOL;
        $this->view->am_obj = $js_define;
        $this->view->actionmap = $actionmap;
    }

    public function saveAction(){
        $this->view->disable();
        $post = $this->request->getPost();
        $valid_msg='';
        if(!$post['reg_action']){
            $valid_msg='register action is required'.PHP_EOL;
        }
        if(!$post['reg_key']){
            $valid_msg='register join key is required'.PHP_EOL;
        }
        if(!$post['log_action']){
            $valid_msg='login action is required'.PHP_EOL;
        }
        if(!$post['log_action']){
            $valid_msg='login join key is required'.PHP_EOL;
        }
        if($valid_msg){
            echo json_encode(['result'=>'100','msg'=>$valid_msg]);
            die();
        }
        /**
         *  clear old register action
         */
        $obj = SysActionmap::findFirst(array(
            'conditions'=>['SUBDOC'=>['$exists'=>true]]
        ));
        unset($obj->SUBDOC);
        if($obj && !$obj->save()){
            echo json_encode(['result'=>'500','msg'=>'clear old register failed']);
            die();
        }
        /**
         *  clear old login action
         */
        $obj = SysActionmap::findFirst(array(
            'conditions'=>['RELATION'=>['$exists'=>true]]
        ));
        unset($obj->RELATION);
        if($obj && !$obj->save()){
            echo json_encode(['result'=>'500','msg'=>'clear old login failed']);
            die();
        }
        /**
         *  set  register action
         */
        $obj = SysActionmap::findFirst(array(
            'conditions'=>['action'=>$post['reg_action']]
        ));
        $obj->SUBDOC = $post['log_action'];
        if(!$obj->save()){
            echo json_encode(['result'=>'500','msg'=>'set  register action failed']);
            die();
        }
        /**
         *  set  login action
         */
        $obj = SysActionmap::findFirst(array(
            'conditions'=>['action'=>$post['log_action']]
        ));
        $RELATION = new stdClass();
        $RELATION->OWN_KEY = $post['log_key'];
        $RELATION->REL_KEY = $post['reg_key'];
        $RELATION->PUT_KEY = 'CRT';
        $obj->RELATION = $RELATION;
        if(!$obj->save()){
            echo json_encode(['result'=>'500','msg'=>'set  register action failed']);
            die();
        }
        echo json_encode(['result'=>'200']);
    }
}

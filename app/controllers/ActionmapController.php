<?php

class ActionmapController extends ControllerBase{
    
    public function initialize(){
        $this->view->setTemplateBefore('backend');
    }

    public function indexAction()
    {
//        $this->view->disable();
        $conditions = ['CRT'=>['$gte'=>new MongoDate(strtotime('2015-03-26 00:00:00')),'$lte'=>new MongoDate(strtotime('2015-03-26 23:59:59'))]];
        $regCount= Register::count(array(
            'conditions'=>$conditions
        ));
        $conditions['SUBDOCVAL'] = ['$elemMatch'=>['$gte'=>new MongoDate(strtotime('2015-03-27 00:00:00')),'$lte'=>new MongoDate(strtotime('2015-03-27 23:59:59'))]];
        $retentionCount= Register::count(array(
            'conditions'=>$conditions
        ));
//        echo $regCount.'/'.$retentionCount;
    }

    public function logActionMapAction(){
        $this->view->searchaction = $this->request->getQuery('search');
        $this->view->am_ary = SysActionmap::find(['sort'=>['action'=>1]]);
    }
    
    public function actionMapParamsSaveAction(){
        $this->view->disable();
        $post = $this->request->getPost();
        $am_obj = SysActionmap::findById($post['oid']);
        if(!$am_obj){
            echo json_encode(['result'=>'100','msg'=>'has no Document']);
            die();
        }
        $params_obj = new stdClass();
        if(!is_array($post['key'])){
            echo json_encode(['result'=>'100','msg'=>'has no any Key']);
            die();
        }
        foreach($post['key'] as $k => $v){
            if($v){
                $params_obj->{$v} = $post['value'][$k];
            }
        }
        $am_obj->params = $params_obj;
        if(!$am_obj->save()){
            echo json_encode(['result'=>'500','msg'=>'save error']);
            die();
        }
        echo json_encode(['result'=>'200']);
    }
    
    public function actionMapActionSaveAction(){
        $this->view->disable();
        $post = $this->request->getPost();
        if(!preg_match("/^[a-zA-Z0-9_]+$/", $post['action'])){
            echo json_encode(['result'=>'100','msg'=>'Action must be alphanumeric or underline']);
            die();
        }
        $am_obj = SysActionmap::findFirst(['conditions'=>['action'=>$post['action']]]);
        if($am_obj){
            echo json_encode(['result'=>'100','msg'=>'This Document is exsit']);
            die();
        }
        $am_obj = new SysActionmap();
        $am_obj->action = $post['action'];
        $am_obj->params = new stdClass();
        if(!$am_obj->save()){
            echo json_encode(['result'=>'500','msg'=>'save error']);
            die();
        }
        echo json_encode(['result'=>'200']);
    }
}


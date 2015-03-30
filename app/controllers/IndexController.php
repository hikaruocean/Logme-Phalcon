<?php

class IndexController extends ControllerBase {

    private $atobj;
    private $content;
    private $actionMap = [];
    private $relationMap = [];

    public function indexAction() {
//        $this->view->disable();
        //echo 'Wellcom To Log Service '.date('Y-m-d H:i:s');
        try {
//        Header('Content-type: text/xml');
//        $xml = new SimpleXMLElement('<data/>');
//        $xml->addChild('books');
//        $xml->books->addChild('book');
//        $xml->books->book[0]->addChild('name','小王子[奇遇記');
//        $xml->books->book[0]->addChild('price','299');
//        echo $xml->asXML();
//        $this->view->q = $this->request->getQuery();
//            echo microtime().'<br />';
//            $t = new MongoDate();
//            var_dump($t);
//            echo '<br />';
//            $ro = Reward::find(['conditions'=>['CRT'=>['$gte'=>new MongoDate(strtotime('2015-03-20 11:42:17')),'$lt'=>new MongoDate()]]]);
//            foreach($ro as $v){
//                echo $v->_id->{'$id'}.' : '.date('Y-m-d H:i:s',(int)$v->CRT->sec).'.'.$v->CRT->usec.'<br />';
//            }
            
            $result = Register::mapReduce($this->mongo,'function(){emit(this.member,{"member":this.member});}',NULL,'tmp');
            $cursor = Register::mapReduceFind($this->mongo,$result);
            foreach($cursor as $v){
                var_dump($v);
                echo PHP_EOL;
            }
            //Register::mapReduceClose($this->mongo,$result);
            //die();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function loginAction() {
        $this->view->disable();
        $post = $this->request->getPost();
        if ($post['id'] != $this->config->secret->adminId || $post['password'] != $this->config->secret->adminPassword) {
            echo json_encode(['result' => '100', 'msg' => 'Log Failed']);
            die();
        }
        $this->session->set('id', $post['id']);
        echo json_encode(['result' => '200']);
    }

    public function logoutAction() {
        $this->view->disable();
        $this->session->remove('id');
        $this->session->destroy();
        $this->response->redirect('/');
    }

    public function getAccessTokenAction() {
        try {
            $this->view->disable();
            $obj = new Accesstoken();
            $obj->timestamp = new MongoDate(time() + 30);
            $obj->save();
            echo json_encode(['accessToken' => $obj->_id->{'$id'}]);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function logApiAction() {
        $this->view->disable();

        /**
         *  Get actionMap
         */
        $sysam_ary = SysActionmap::find();
        foreach ($sysam_ary as $v) {
            $this->actionMap[$v->action] = (array) $v->params;
            $this->relationMap[$v->action] = $v->RELATION ? $v->RELATION : '';
        }

        /**
         * Authentication Process
         */
        $this->AP();


        /**
         * Save Log Process
         */
        $this->SP();

        /**
         *  Delete AccessToken
         */
        $this->atobj->delete();
    }

    private function AP() {
        $signature = $this->request->getQuery('s');
        $key = $this->config->secret->apiKey;
        if (!$signature) {
            echo json_encode(['result' => '100', 'msg' => 'need signature']);
            die();
        }
//        $content_str = file_get_contents('php://input');
        $content_str = $this->request->getRawBody();
        $this->content = json_decode($content_str, true);
        if (!$this->content['accessToken']) {
            echo json_encode(['result' => '100', 'msg' => 'need accessToken']);
            die();
        }
        if (sha1($content_str . '@' . $key) != $signature) {
            echo json_encode(['result' => '401']);
            die();
        }
        $this->atobj = Accesstoken::findById($this->content['accessToken']);
        if (!$this->atobj) {
            echo json_encode(['result' => '401']);
            die();
        }
        if (time() > $this->atobj->timestamp->sec) {
            $this->atobj->delete();
            echo json_encode(['result' => '100', 'msg' => 'Expired']);
            die();
        }
    }

    private function SP() {
        if (!$this->actionMap[$this->content['action']]) {
            echo json_encode(['result' => '100', 'msg' => 'has no ' . $this->content['action'] . ' action']);
            die();
        }
        $dclass = ucfirst($this->content['action']);
        if (!class_exists($dclass, true)) {
            $fh = fopen(__DIR__ . '/../collections/' . $dclass . '.php', 'w');
            $sourceCode = '<?php' . PHP_EOL .
                    'class ' . $dclass . ' extends \Phalcon\Mvc\Collection\CollectionExt' . PHP_EOL .
                    '{' . PHP_EOL . PHP_EOL .
                    '    public function getSource()' . PHP_EOL .
                    '    {' . PHP_EOL .
                    '       return "' . $this->content['action'] . '";' . PHP_EOL .
                    '    }' . PHP_EOL . PHP_EOL .
                    '}';
            fwrite($fh, $sourceCode);
            fclose($fh);
        }

        $coll_obj = new $dclass();
        foreach ($this->actionMap[$this->content['action']] as $key => $type) {
            if ($this->content[$key]) {
                switch ($type) {
                    case 'string':
                        $this->content[$key] = (string) $this->content[$key];
                        break;
                    case 'bool':
                        $this->content[$key] = (bool) $this->content[$key];
                        break;
                    case 'int':
                        $this->content[$key] = (int) $this->content[$key];
                        break;
                    case 'float':
                        $this->content[$key] = (float) $this->content[$key];
                        break;
                    case 'isodate':
                        $this->content[$key] = new MongoDate((int) $this->content[$key]);
                        break;
                    default:
                        echo json_encode(['result' => '100', 'msg' => 'type not found (' . $type . ')' . $key]);
                        continue;
                }
                $coll_obj->{$key} = $this->content[$key];
            }
        }
        $coll_obj->CRT = new MongoDate();
        $coll_obj->API = $this->content['API'] ? $this->content['API'] : '';
        if (!$coll_obj->save()) {
            echo json_encode(['result' => '100', 'msg' => 'log save error']);
            die();
        }
        /**
         *  Process Relation For retention
         */
        $RELATION = $this->relationMap[$this->content['action']];

        if ($RELATION['PUT_KEY']) {
            $relaction_ary = SysActionmap::find(['conditions' => ['SUBDOC' => $this->content['action']]]);
            foreach ($relaction_ary as $k => $v) {
                $relaAction = ucfirst($v->action);
                $relaActionObj = $relaAction::findFirst(['conditions' => [$RELATION['REL_KEY'] => $coll_obj->$RELATION['OWN_KEY']]]);
                if ($relaActionObj) {
                    $tmpAry = $relaActionObj->SUBDOCVAL ? $relaActionObj->SUBDOCVAL : [];
                    array_push($tmpAry, $coll_obj->{$RELATION['PUT_KEY']});
                    $relaActionObj->SUBDOCVAL = $tmpAry;
                    if ($relaActionObj->save()) {
                        //do nothing
                    }
                }
            }
        }
        echo json_encode(['result' => '200']);
    }

    public function testJobSendLogAction() {
        $logo = new stdClass();
        $logo->action = 'reward';
        $logo->gold = 10000;
        $logo->member = 'Stomvi';
        $logo->timestamp = time();
        $this->job->run('Log loginLog "' . addslashes(json_encode($logo)) . '"');
    }

}

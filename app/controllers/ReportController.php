<?php

class ReportController extends ControllerBase {

    public function initialize() {
        $this->view->setTemplateBefore('backend');
    }

    public function indexAction() {
        try{
        /**
         *  variable initial
         */
        $date_ary = []; //[date][retention N day]
        $daytime = 86400;
        $report_timestamp = strtotime(date('Y-m-d 00:00:00'));
        $register = [];
        $login = [];
        /**
         *  find login collection and register collection
         */
        $loginCollection = ucfirst(SysActionmap::findFirst([
            'conditions'=>['RELATION'=>['$exists'=>true]]
        ])->action);
        $registerCollection = ucfirst(SysActionmap::findFirst([
            'conditions'=>['SUBDOC'=>['$exists'=>true]]
        ])->action);
        /**
         * total user count
         */
        $this->view->totalUser = $registerCollection::count();
        /**
         * day login count and day register count
         */
        for ($i = 0; $i < 3; $i++) {
            $report['start'] = $report_timestamp - $i * $daytime;
            $report['end'] = $report['start'] + $daytime;
            $register[$i] = $registerCollection::count(array(
                        'conditions' => ['CRT' => ['$gte' => new MongoDate($report['start']), '$lt' => new MongoDate($report['end'])]]
            ));
            $result = $loginCollection::aggregate([
                        ['$match' =>
                            [
                                'CRT' => ['$gte' => new MongoDate($report['start']),'$lt' => new MongoDate($report['end'])]
                            ]
                        ],
                        [
                            '$group' => [
                            '_id' => '$member'
                            ]
                        ]
            ]);
            $login[$i] = count($result['result']);
        }
        $this->view->register = $register;
        $this->view->login = $login;
        /**
         * retention rate
         */
        for ($i = 8; $i > 1; $i--) {
            $reg_time['start'] = $report_timestamp - $i * $daytime;
            $reg_time['end'] = $reg_time['start'] + $daytime;
            $retention_ary = [];
            $conditions = [];
            $conditions = ['CRT' => ['$gte' => new MongoDate($reg_time['start']), '$lt' => new MongoDate($reg_time['end'])]];
            $regCount = $registerCollection::count(array(
                        'conditions' => $conditions
            ));
            for ($j = 1; $j < 8; $j++) {
                if ($j < $i) {
                    $log_time['start'] = $reg_time['start'] + $j * $daytime;
                    $log_time['end'] = $log_time['start'] + $daytime;
                    $conditions['SUBDOCVAL'] = ['$elemMatch' => ['$gte' => new MongoDate($log_time['start']), '$lte' => new MongoDate($log_time['end'])]];
                    $retentionCount = $registerCollection::count(array(
                                'conditions' => $conditions
                    ));
                    $ret_rate = $regCount ? ( floor(($retentionCount / $regCount) * 10000) / 100) : 0;
                    array_push($retention_ary, $ret_rate);
                    if ($i == 2 && $j == 1) {
                        $this->view->lastRetention = $retentionCount;
                    }
                } else {
                    array_push($retention_ary, NULL);
                }
            }
            array_push($date_ary, $retention_ary);
        }

        $this->view->report_time = time() - 86400 * 7;
        $this->view->dateAry = $date_ary;
        }
        catch (Exception $e){
            die($e->getMessage());
        }
    }

}

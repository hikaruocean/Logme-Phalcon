<?php

class ChartController extends ControllerBase {

    public function initialize() {
        $this->view->setTemplateBefore('backend');
    }

    public function indexAction() {
        $am_ary = SysActionmap::find([
                    'sort' => ['action' => 1]
        ]);
        $js_define = "var am_obj={" . PHP_EOL;
        foreach ($am_ary as $v) {
            $actionmap[$v->action] = $v->params;
            $js_define.=$v->action . ":{" . PHP_EOL;
            foreach ($v->params as $pk => $pv) {
                $js_define.=$pk . ":" . '"' . $pv . '",';
            }
            $js_define.='API:"string",CRT:"isodate"},' . PHP_EOL;
        }
        $js_define.="};" . PHP_EOL;
        $this->view->range = ucfirst($this->dispatcher->getParam('range'));
        $this->view->am_obj = $js_define;
        $this->view->actionmap = $actionmap;
    }

    public function drawAction() {
        try {
            $this->view->disable();
            /**
             *  setting timezone for mongodb $group CRT query
             */
            $timezone = Date('Z') / 3600;
            $timezone_gap = 1000 * 60 * 60 * $timezone * -1;

            /**
             *  prepare HighChart Basic Options
             */
            $draw_setting_str = '{"chart":{"type":"spline","zoomType":"x"},"xAxis":{"type":"datetime","dateTimeLabelFormats":{"month":"%e. %b / %Y","year":"%b / %y"},"title":{"text":"Time Line"}},"yAxis":{"title":{"text":"Quantity"},"min":0},"tooltip":{"headerFormat":"<b>{series.name}</b><br>","pointFormat":"{point.x:%e. %b / %Y}: {point.y:.2f}"},"plotOptions":{"spline":{"dataLabels":{"enabled":true},"marker":{"enabled":true},"enableMouseTracking":true}}}';
            $draw_ary = json_decode($draw_setting_str, true);
            $action = ucfirst(strtolower($this->request->getPost('action')));
            $draw_ary['title'] = ['text' => $action . ' Action'];
            $draw_ary['subtitle'] = ['text' => 'Log Quantity'];

            /**
             *  process customize conditoins
             */
            $fields = $this->request->getPost('field', NULL, []);
            $operators = $this->request->getPost('operator');
            $value = $this->request->getPost('value');
            $valueBt = $this->request->getPost('valueBt');
            $match = [
                '$match' => [
                    'CRT' => ['$exists' => true]
                ]
            ];
            foreach ($fields as $k => $v) {
                if ($v) {
                    switch ($operators[$k]) {
                        case 'Seq':
                            $match['$match'][$v] = $value[$k];
                            break;
                        case 'Sne':
                            $match['$match'][$v] = ['$ne' => $value[$k]];
                            break;
                        case 'Ssubstr':
                            $match['$match'][$v] = ['$regex' => $value[$k], '$options' => 'i'];
                            break;
                        case 'Ieq':
                            $match['$match'][$v] = (INT) $value[$k];
                            break;
                        case 'Ine':
                            $match['$match'][$v] = ['$ne' => (INT) $value[$k]];
                            break;
                        case 'Ilt':
                            $match['$match'][$v] = ['$lt' => (INT) $value[$k]];
                            break;
                        case 'Ilte':
                            $match['$match'][$v] = ['$lte' => (INT) $value[$k]];
                            break;
                        case 'Igt':
                            $match['$match'][$v] = ['$gt' => (INT) $value[$k]];
                            break;
                        case 'Igte':
                            $match['$match'][$v] = ['$gte' => (INT) $value[$k]];
                            break;
                        case 'Ibtw':
                            $match['$match'][$v] = ['$gte' => (INT) $value[$k], '$lte' => (INT) $valueBt[$k]];
                            break;
                        case 'Dlt':
                            $match['$match'][$v] = ['$lt' => new MongoDate(strtotime($value[$k]))];
                            break;
                        case 'Dlte':
                            $match['$match'][$v] = ['$lte' => new MongoDate(strtotime($value[$k]))];
                            break;
                        case 'Dgt':
                            $match['$match'][$v] = ['$gt' => new MongoDate(strtotime($value[$k]))];
                            break;
                        case 'Dgte':
                            $match['$match'][$v] = ['$gte' => new MongoDate(strtotime($value[$k]))];
                            break;
                        case 'Dbtw':
                            $match['$match'][$v] = ['$gte' => new MongoDate(strtotime($value[$k])), '$lte' => new MongoDate(strtotime($valueBt[$k]))];
                            break;
                    }
                }
            }
            /**
             *  prepare mongodb $group CRT
             */
            switch ($this->request->getPost('range', NULL, 'Date')) {
                case 'Date':
                    $group_condition = ['year' => ['$year' => '$CRT_Z8'], 'month' => ['$month' => '$CRT_Z8'], 'day' => ['$dayOfMonth' => '$CRT_Z8']];
                    break;
                case 'Month':
                    $group_condition = ['year' => ['$year' => '$CRT_Z8'], 'month' => ['$month' => '$CRT_Z8']];
                    break;
                case 'Year':
                    $group_condition = ['year' => ['$year' => '$CRT_Z8']];
                    break;
            }
            $sum = $this->request->getPost('sum');
            if ($sum) {//process sum mongoscript
                $project = [
                    '$project' => [
                        'CRT_Z8' => ['$subtract' => ['$CRT', $timezone_gap]],
                        'SVAL' => '$' . $sum
                    ]
                ];
                $group = [
                    '$group' => [
                        '_id' => $group_condition,
                        'count' => ['$sum' => '$SVAL']
                    ]
                ];
            } else {//process count mongoscript
                $project = [
                    '$project' => [
                        'CRT_Z8' => ['$subtract' => ['$CRT', $timezone_gap]]
                    ]
                ];
                $group = [
                    '$group' => [
                        '_id' => $group_condition,
                        'count' => ['$sum' => 1]
                    ]
                ];
            }
            if (!class_exists($action)) {
                echo json_encode(['result' => '100', 'msg' => 'have no this action']);
                die();
            }
            $obj_ary = $action::aggregate([
                        $match,
                        $project,
                        $group,
                        [
                            '$sort' => [
                                '_id' => 1
                            ]
                        ]
            ]);
            $totalsum = 0;
            $totalcount = 0;
            foreach ($obj_ary['result'] as $v) {
                if (!$v['_id']['day']) {
                    $v['_id']['day'] = '01';
                } else {
                    $v['_id']['day'] = sprintf('%02d', $v['_id']['day']);
                }
                if (!$v['_id']['month']) {
                    $v['_id']['month'] = '01';
                } else {
                    $v['_id']['month'] = sprintf('%02d', $v['_id']['month']);
                }
                $h = sprintf('%02d', (0 + $timezone));
                $data_ary[] = [strtotime($v['_id']['year'] . '-' . $v['_id']['month'] . '-' . $v['_id']['day'] . ' ' . $h . ':00:00') * 1000, $v['count']];
                $totalsum+=$v['count'];
                $totalcount++;
            }

            $draw_ary['series'] = [['name' => $action, 'data' => $data_ary]];
            echo json_encode(['result' => '200', 'highchart' => $draw_ary, 'totalsum' => $totalsum, 'totalcount' => $totalcount]);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function activeAction() {
        $report_timestamp = strtotime(date('Y-m-d 00:00:00'));
        
        switch ($this->dispatcher->getParam('range')) {
            case 'date':
                $this->view->endDate = date('Y-m-d H:i:s', $report_timestamp + 86400 -1);
                $this->view->startDate = date('Y-m-d H:i:s', $report_timestamp - 30 * 86400);
                break;
            case 'month':
                $this->view->endDate = date('Y-m-d H:i:s', $report_timestamp + 86400);
                $this->view->startDate = date('Y-m-d H:i:s', $report_timestamp - 365 * 86400);
                break;
        }

        $this->view->range = ucfirst($this->dispatcher->getParam('range'));
    }

    public function activeDrawAction() {
        try {
            $this->view->disable();
            /**
             *  setting timezone for mongodb $group CRT query
             */
            $timezone = Date('Z') / 3600;
            $timezone_gap = 1000 * 60 * 60 * $timezone * -1;

            /**
             * find login action
             */
            $obj = SysActionmap::findFirst(array(
                        'conditions' => ['RELATION' => ['$exists' => true]]
            ));
            $action = ucfirst($obj->action);
            $loginMID = $obj->RELATION['OWN_KEY'];

            /**
             *  prepare HighChart Basic Options
             */
            $draw_setting_str = '{"chart":{"type":"spline","zoomType":"x"},"xAxis":{"type":"datetime","dateTimeLabelFormats":{"month":"%e. %b / %Y","year":"%b / %y"},"title":{"text":"Time Line"}},"yAxis":{"title":{"text":"Quantity"},"min":0},"tooltip":{"headerFormat":"<b>{series.name}</b><br>","pointFormat":"{point.x:%e. %b / %Y}: {point.y:.2f}"},"plotOptions":{"spline":{"dataLabels":{"enabled":true},"marker":{"enabled":true},"enableMouseTracking":true}}}';
            $draw_ary = json_decode($draw_setting_str, true);
            $draw_ary['title'] = ['text' => 'User Active'];
            $draw_ary['subtitle'] = ['text' => 'Quantity'];

            /**
             *  process customize conditoins
             */
            $operator = $this->request->getPost('operator');
            $value = $this->request->getPost('value');
            $valueBt = $this->request->getPost('valueBt');
            $match = [
                '$match' => [
                    'CRT' => ['$exists' => true]
                ]
            ];
            switch ($operator) {
                case 'Dlt':
                    $match['$match']['CRT'] = ['$lt' => new MongoDate(strtotime($value))];
                    break;
                case 'Dlte':
                    $match['$match']['CRT'] = ['$lte' => new MongoDate(strtotime($value))];
                    break;
                case 'Dgt':
                    $match['$match']['CRT'] = ['$gt' => new MongoDate(strtotime($value))];
                    break;
                case 'Dgte':
                    $match['$match']['CRT'] = ['$gte' => new MongoDate(strtotime($value))];
                    break;
                case 'Dbtw':
                    $match['$match']['CRT'] = ['$gte' => new MongoDate(strtotime($value)), '$lte' => new MongoDate(strtotime($valueBt))];
                    break;
            }
            /**
             *  prepare mongodb $group CRT
             */
            switch ($this->request->getPost('range', NULL, 'Date')) {
                case 'Date':
                    $group_condition = ['year' => ['$year' => '$CRT_Z8'], 'month' => ['$month' => '$CRT_Z8'], 'day' => ['$dayOfMonth' => '$CRT_Z8']];
                    break;
                case 'Month':
                    $group_condition = ['year' => ['$year' => '$CRT_Z8'], 'month' => ['$month' => '$CRT_Z8']];
                    break;
                case 'Year':
                    $group_condition = ['year' => ['$year' => '$CRT_Z8']];
                    break;
            }
            $project = [
                '$project' => [
                    'CRT_Z8' => ['$subtract' => ['$CRT', $timezone_gap]],
                    'MID' => '$' . $loginMID
                ]
            ];
            $group = [
                '$group' => [
                    '_id' => $group_condition,
                    'm_ary' => ['$addToSet' => '$MID']
                ]
            ];
            if (!class_exists($action)) {
                echo json_encode(['result' => '100', 'msg' => 'have no this action']);
                die();
            }
            $obj_ary = $action::aggregate([
                        $match,
                        $project,
                        $group,
                        [
                            '$sort' => [
                                '_id' => 1
                            ]
                        ]
            ]);
            $totalsum = 0;
            $totalcount = 0;
            foreach ($obj_ary['result'] as $v) {
                if (!$v['_id']['day']) {
                    $v['_id']['day'] = '01';
                } else {
                    $v['_id']['day'] = sprintf('%02d', $v['_id']['day']);
                }
                if (!$v['_id']['month']) {
                    $v['_id']['month'] = '01';
                } else {
                    $v['_id']['month'] = sprintf('%02d', $v['_id']['month']);
                }
                $h = sprintf('%02d', (0 + $timezone));
                $data_ary[] = [strtotime($v['_id']['year'] . '-' . $v['_id']['month'] . '-' . $v['_id']['day'] . ' ' . $h . ':00:00') * 1000, count($v['m_ary'])];
                $totalsum+=count($v['m_ary']);
                $totalcount++;
            }

            $draw_ary['series'] = [['name' => $action, 'data' => $data_ary]];
            echo json_encode(['result' => '200', 'highchart' => $draw_ary, 'totalsum' => $totalsum, 'totalcount' => $totalcount]);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function registerAction() {
        $report_timestamp = strtotime(date('Y-m-d 00:00:00'));
        
        switch ($this->dispatcher->getParam('range')) {
            case 'date':
                $this->view->endDate = date('Y-m-d H:i:s', $report_timestamp + 86400 -1);
                $this->view->startDate = date('Y-m-d H:i:s', $report_timestamp - 30 * 86400);
                break;
            case 'month':
                $this->view->endDate = date('Y-m-d H:i:s', $report_timestamp + 86400);
                $this->view->startDate = date('Y-m-d H:i:s', $report_timestamp - 365 * 86400);
                break;
        }

        $this->view->range = ucfirst($this->dispatcher->getParam('range'));
    }

    public function registerDrawAction() {
        try {
            $this->view->disable();
            /**
             *  setting timezone for mongodb $group CRT query
             */
            $timezone = Date('Z') / 3600;
            $timezone_gap = 1000 * 60 * 60 * $timezone * -1;

            /**
             * find login action
             */
            $obj = SysActionmap::findFirst(array(
                        'conditions' => ['SUBDOC' => ['$exists' => true]]
            ));
            $action = ucfirst($obj->action);

            /**
             *  prepare HighChart Basic Options
             */
            $draw_setting_str = '{"chart":{"type":"spline","zoomType":"x"},"xAxis":{"type":"datetime","dateTimeLabelFormats":{"month":"%e. %b / %Y","year":"%b / %y"},"title":{"text":"Time Line"}},"yAxis":{"title":{"text":"Quantity"},"min":0},"tooltip":{"headerFormat":"<b>{series.name}</b><br>","pointFormat":"{point.x:%e. %b / %Y}: {point.y:.2f}"},"plotOptions":{"spline":{"dataLabels":{"enabled":true},"marker":{"enabled":true},"enableMouseTracking":true}}}';
            $draw_ary = json_decode($draw_setting_str, true);
            $draw_ary['title'] = ['text' => 'User Register'];
            $draw_ary['subtitle'] = ['text' => 'Quantity'];

            /**
             *  process customize conditoins
             */
            $operator = $this->request->getPost('operator');
            $value = $this->request->getPost('value');
            $valueBt = $this->request->getPost('valueBt');
            $match = [
                '$match' => [
                    'CRT' => ['$exists' => true]
                ]
            ];
            switch ($operator) {
                case 'Dlt':
                    $match['$match']['CRT'] = ['$lt' => new MongoDate(strtotime($value))];
                    break;
                case 'Dlte':
                    $match['$match']['CRT'] = ['$lte' => new MongoDate(strtotime($value))];
                    break;
                case 'Dgt':
                    $match['$match']['CRT'] = ['$gt' => new MongoDate(strtotime($value))];
                    break;
                case 'Dgte':
                    $match['$match']['CRT'] = ['$gte' => new MongoDate(strtotime($value))];
                    break;
                case 'Dbtw':
                    $match['$match']['CRT'] = ['$gte' => new MongoDate(strtotime($value)), '$lte' => new MongoDate(strtotime($valueBt))];
                    break;
            }
            /**
             *  prepare mongodb $group CRT
             */
            switch ($this->request->getPost('range', NULL, 'Date')) {
                case 'Date':
                    $group_condition = ['year' => ['$year' => '$CRT_Z8'], 'month' => ['$month' => '$CRT_Z8'], 'day' => ['$dayOfMonth' => '$CRT_Z8']];
                    break;
                case 'Month':
                    $group_condition = ['year' => ['$year' => '$CRT_Z8'], 'month' => ['$month' => '$CRT_Z8']];
                    break;
                case 'Year':
                    $group_condition = ['year' => ['$year' => '$CRT_Z8']];
                    break;
            }
            $project = [
                '$project' => [
                    'CRT_Z8' => ['$subtract' => ['$CRT', $timezone_gap]]
                ]
            ];
            $group = [
                '$group' => [
                    '_id' => $group_condition,
                    'count' => ['$sum' => 1]
                ]
            ];
            if (!class_exists($action)) {
                echo json_encode(['result' => '100', 'msg' => 'have no this action']);
                die();
            }
            $obj_ary = $action::aggregate([
                        $match,
                        $project,
                        $group,
                        [
                            '$sort' => [
                                '_id' => 1
                            ]
                        ]
            ]);
            $totalsum = 0;
            $totalcount = 0;
            foreach ($obj_ary['result'] as $v) {
                if (!$v['_id']['day']) {
                    $v['_id']['day'] = '01';
                } else {
                    $v['_id']['day'] = sprintf('%02d', $v['_id']['day']);
                }
                if (!$v['_id']['month']) {
                    $v['_id']['month'] = '01';
                } else {
                    $v['_id']['month'] = sprintf('%02d', $v['_id']['month']);
                }
                $h = sprintf('%02d', (0 + $timezone));
                $data_ary[] = [strtotime($v['_id']['year'] . '-' . $v['_id']['month'] . '-' . $v['_id']['day'] . ' ' . $h . ':00:00') * 1000, ($v['count'])];
                $totalsum+=$v['count'];
                $totalcount++;
            }

            $draw_ary['series'] = [['name' => $action, 'data' => $data_ary]];
            echo json_encode(['result' => '200', 'highchart' => $draw_ary, 'totalsum' => $totalsum, 'totalcount' => $totalcount]);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
}

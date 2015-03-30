<?php

/**
 * Description of BillTask
 *
 * @author hikaru
 */
CLASS DataTask EXTENDS \Phalcon\CLI\Task {

    public function importContractAction($params) {
        set_time_limit(0);
        
        $filepath = $params[0];
        if (!is_file($filepath)) {
            die('filepath error');
        }
        $fhw = fopen(__DIR__ . '/../../upload/importContractError.csv', 'w');
        $fh = fopen($filepath, 'r');
        $count = 0;
        while (!feof($fh)) {
            $data = fgetcsv($fh);
            if ($count < 1) {
                $count++;
                continue;
            }
            $count++;
            /**
             *  先檢查是否無ContractType
             * 13
             */
            $data[13] = trim($data[13]);
            $ct = ContractType::findFirst(['conditions' => "name = :name:", 'bind' => ['name' => $data[13]]]);
            if (!$ct) {
                $data[] = 'ct error';
                fputcsv($fhw, $data, ',', '"');
                continue;
            }
            $ct_sn = $ct->sn;
             /**
             *  先檢查月費是否為0
             * 14
             */
            $data[14] = trim($data[14]);
            if((int)$data[14]==0){
                $data[] = 'monthfee error';
                fputcsv($fhw, $data, ',', '"');
                continue;
            }
            /**
             *  先檢查分公司是否無該業務
             * 20 21
             */
            $data[20] = trim($data[20]);
            $me = Member::findFirst(['conditions' => "company = :company: AND name = :name:", 'bind' => ['company' => $data[20], 'name' => trim($data[21])]]);
            if (!$me) {
                $data[] = 'member error';
                fputcsv($fhw, $data, ',', '"');
                continue;
            }
            $op_m_sn = $me->sn;
            $pr_m_sn = $me->sn;
            /**
             *  先檢查zipcode是否正確
             * 6
             */
            $data[6] = trim($data[6]);
            $zp = Zipcode::findFirst(['conditions' => 'ZipCode = :zipcode:', 'bind' => ['zipcode' => $data[6]]]);
            if (!$zp) {
                $data[] = 'zp error';
                fputcsv($fhw, $data, ',', '"');
                continue;
            }
            $cus_city = $zp->City;
            $cus_district = $zp->Area;
            for ($i = 26; $data[$i]; $i+=3) {
                if (!$data[$i])
                    break;
                $dt = DeviceType::findFirst(['conditions' => "name = :name: ", 'bind' => ['name' => trim($data[$i])]]);
                if (!$dt) {
                    $data[] = 'dt error';
                    fputcsv($fhw, $data, ',', '"');
                    continue 2;
                }
                $data[$i] = $dt->sn;
            }
            $cim = CustomIdManagement::findFirst(['conditions'=>"id = :id: AND used = 'N'",'bind'=>['id'=>trim($data[0])]]);
            if(!$cim){
                $data[] = 'cim find error';
                fputcsv($fhw, $data, ',', '"');
                continue;
            }
            $this->db->begin();
            $custom = new Custom();
            $custom->id = $cim->id;
            if ($custom->save() == false) {
                $data[] = 'custom error';
                fputcsv($fhw, $data, ',', '"');
                continue;
            }
            $contract = new Contract();
            $contract->cus_sn = $custom->sn;
            $contract->cus_name = trim($data[2]);
            $contract->cus_taxid = trim($data[1]);
            //$contract->gt_sn = $req->getPost('gt_sn');
            $contract->cus_tel = $data[3];
//            $contract->cus_fax = $req->getPost('cus_fax');
            $contract->cus_city = $cus_city;
            $contract->cus_district = $cus_district;
            $contract->cus_zip = $data[6];
            $contract->cus_addr = $data[7];
            $contract->con_name = $data[8];
            $contract->con_tel = $data[9];
            $contract->con_email = $data[10];
            $contract->con_remark = $data[11];
            $contract->cus_remark = $data[12];
            $contract->ct_sn = $ct_sn;
            $contract->deposit = trim(str_replace(',', '', $data[17]));
            $contract->monthfee = trim(str_replace(',', '', $data[14]));
            if (trim($data[16]) === 'Y') {//月費是否含稅，含稅的話 業績 = 含稅月費 /1.05
                $contract->sales_value = floor((int) $data[14] / 1.05);
                $contract->include_tax = 'Y';
            } else {
                $contract->sales_value = $contract->monthfee;
                $contract->include_tax = 'N';
            }
            $contract->bill_period = trim($data[15]);
            $date_ary = explode('/', trim($data[18]));
            $contract->start_date = $date_ary[0] . '-' . $date_ary[1] . '-' . $date_ary[2] . ' 00:00:00';
            $contract->op_company = $data[20];
            $contract->op_m_sn = $op_m_sn;
            $contract->pr_company = $data[20];
            $contract->pr_m_sn = $pr_m_sn;
            $date_ary = explode('/', trim($data[22]));
            $contract->pr_start_date = $date_ary[0] . '-' . $date_ary[1] .'-01 00:00:00';
            $date_ary = explode('/', trim($data[19]));
            $contract->tip_end_date = $date_ary[0] . '-' . $date_ary[1] . '-01 00:00:00';
            //$contract->pr_end_date = $req->getPost('pr_end_date');
            $contract->pr_type = trim($data[23]);
            $contract->pr_type_data = trim($data[24]);
            $contract->descript = $data[25];
            $contract->zone = $this->config->addr_zone_ary[$contract->cus_city];
            if ($contract->save() == false) {
                $data[] = 'co save error';
                fputcsv($fhw, $data, ',', '"');
                continue;
            }
            for ($i = 26; $data[$i]; $i+=3) {
                if (!$data[$i])
                    break;
                $d = new Device();
                $data[$i + 1] = trim($data[$i + 1]);
                $d->device_id = $data[$i + 1] ? $data[$i + 1] : 'No_defined';
                $d->co_sn = $contract->sn;
                $d->dt_sn = $data[$i];
                $d->is_new = $data[$i + 2] === 'N' ? 'Y' : 'N';
                if (!$d->save()) {
                    $this->db->rollback();
                    $data[] = 'device save error';
                    fputcsv($fhw, $data, ',', '"');
                    continue 2;
                }
            }
            
            /*
             *  update cim to used
             */
            $cim->used = 'Y';
            if(!$cim->save()){
                $this->db->rollback();
                $data[] = 'cim update error';
                fputcsv($fhw, $data, ',', '"');
                continue 2;
            }
            $this->db->commit();
        }
    }

    public function importMemberAction($params) {
        set_time_limit(0);
        $this->db->begin();
        $filepath = $params[0];
        if (!is_file($filepath)) {
            die('filepath error');
        }
        $fhw = fopen(__DIR__ . '/../../upload/importMemberError.csv', 'w');
        $fh = fopen($filepath, 'r');
        $count = 0;
        while (!feof($fh)) {
            $data = fgetcsv($fh);
            if ($count < 1 || !$data[0]) {
                $count++;
                continue;
            }
            $count++;
            /*
             * 檢查帳號是否重複
             */
            $m = Member::findFirst(['conditions' => "account = :account:", 'bind' => ['account' => trim($data[3])]]);
            if ($m) {
                $data[] = 'account dupicate error';
                fputcsv($fhw, $data, ',', '"');
                continue;
            }
            /*
             * 檢查職稱是否正確
             */
            $tt = TitleType::findFirst(['conditions'=> "name = :name:",'bind'=>['name'=>trim($data[4])]]);
            if(!$tt){
                $data[] = 'tt error';
                fputcsv($fhw, $data, ',', '"');
                continue;
            }
            $tt_sn = $tt->sn;

            $m = new Member();
            $m->is_su = 'N';
            $m->account = trim($data[3]);
            $m->save();
            $m->id = sprintf('%04d', $m->sn);
            $m->is_manager = trim($data[1]);
            $m->company = trim($data[0]);
            $m->name = trim($data[2]);
            $m->status = 'A';
            $m->password = md5('0000');
            $m->email = trim($data[5]);
            $m->tt_sn = $tt_sn;
            if(!$m->save()){
                $data[] = 'member save error';
                fputcsv($fhw, $data, ',', '"');
                continue;
            }
        }
    }

    public function createCustomIdAction(){
        echo 'start: '.microtime().PHP_EOL;
        for($i = 0 ; $i<= 9999 ; $i++){
            $cim = new CustomIdManagement();
            $cim->id = sprintf('%04d',$i);
            $cim->used = 'N';
            $cim->save();
        }
        echo 'end: '.microtime().PHP_EOL;
    }
}

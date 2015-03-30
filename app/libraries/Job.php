<?php
namespace Hikaru\Libraries;
class Job{
    public $php='php5';
    public $index='';
    public function run($cmd){
        exec($this->php.' '.$this->index.' '.$cmd.' > /dev/null 2>/dev/null &');
    }
}
<?php

namespace Phalcon\Paginator\Adapter;

class ModelExt extends \Phalcon\Paginator\Adapter\Model {

    protected $url = '';
    protected $limit;

    function __construct($options) { // add option 'key' 
        if (!$options['page'])
            $options['page'] = 1;
        parent::__construct($options);
        $url_ary = explode('?', $_SERVER['REQUEST_URI']);
        $ue_get = $_GET;
        unset($ue_get['_url']);
        foreach ($ue_get as $k => $v) {
            $ue_get[$k] = $k . '=' . urlencode($v);
        }
        $this->url = str_ireplace(array('?' . $options['key'] . '=' . $options['page'], '&' . $options['key'] . '=' . $options['page']), '', $url_ary[0] . '?' . htmlspecialchars(implode('&', $ue_get), ENT_QUOTES));
        if (strpos($this->url, '?') !== FALSE) {
            $this->url.='&' . $options['key'] . '=';
        } else {
            $this->url.='?' . $options['key'] . '=';
        }
        $this->limit = $options['limit'];
    }

    public function getPaginate() {
        /**
         * provide following new attribute:
         * 	url
         * 	limit
         * 	page_start
         * 	page_end
         * 	before10
         * 	next10
         */
        $stdClass = parent::getPaginate();
        $stdClass->url = $this->url;
        $stdClass->limit = $this->limit;
        $stdClass->page_start = (int) (floor(($stdClass->current - 1) / 10) * 10 + 1);
        $stdClass->page_end = ($stdClass->page_start + 9) > $stdClass->total_pages ? $stdClass->total_pages : ($stdClass->page_start + 9);
        $stdClass->before10 = ($stdClass->page_start - 10) < 1 ? 1 : ($stdClass->page_start - 10);
        $stdClass->next10 = ($stdClass->page_start + 10) > $stdClass->total_pages ? $stdClass->page_start : ($stdClass->page_start + 10);
        return $stdClass;
    }

}

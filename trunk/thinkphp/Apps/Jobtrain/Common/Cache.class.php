<?php
/**
 * Cache.class.php
 * 培训缓存配置
 * @author: anything
 * @createTime: 2015/11/19 10:29
 * @version: $Id$ 
 * @copyright: 畅移信息
 */

namespace Jobtrain\Common;

class Cache extends \Com\Cache {

    // 实例化
    public static function &instance() {

        static $instance;
        if(empty($instance)) {
            $instance	= new self();
        }

        return $instance;
    }

    public function __construct() {

        parent::__construct();
    }

    /**
     * 获取 setting
     */
    public function setting() {

        $serv = D('Jobtrain/JobtrainSetting', 'Service');
        return $serv->list_kv();
    }
}
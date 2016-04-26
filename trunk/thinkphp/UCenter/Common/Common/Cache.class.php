<?php
/**
 * Cache.class.php
 * 缓存
 * $Author$
 */

namespace Common\Common;

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
     * 获取 setting 的缓存信息;
     * @return array
     */
    public static function setting() {

        // 获取全局配置表数据
        $serv = D('Common/Setting', 'Service');
        return $serv->list_kv();
    }

    // 默认处理
    public function __call($method, $args) {

        // 读取 UC 的数据
        if (preg_match('/^tj/i', $method)) {
            $serv_suite = D('Common/Suite', 'Service');
            $suite = $serv_suite->get_by_suiteid($method);
            return $suite;
        }

        E('_ERR_CACHE_UNDEFINED');
        return array();
    }

}

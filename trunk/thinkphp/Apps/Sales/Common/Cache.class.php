<?php
/**
 * Cache.class.php
 * 销售管理缓存
 * @create-time: 2015-07-01
 */
namespace Sales\Common;

class Cache extends \Com\Cache {

	// 实例化
	public static function &instance() {

		static $instance;
		if (empty($instance)) {
			$instance = new self();
		}
		return $instance;
	}

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 获取 sales_setting 的缓存信息;
	 * @return array
	 */
	public function setting() {

		// 获取销售管理配置表数据
		$serv = D('Sales/SalesSetting', 'Service');
		return $serv->list_kv();
	}

    /**
     * 获取文件类型 oa_sales_type 缓存信息
     * @return array
     */
    public function salestype() {

        // 获取文件类型表数据
        $serv = D('Sales/SalesType', 'Service');
        return $serv->list_all();
    }


}

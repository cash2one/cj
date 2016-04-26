<?php
/**
 * Cache.class.php
 * 留言本缓存
 * $Author$
 */
namespace Askfor\Common;

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
	 * 获取setting 的缓存信息;
	 * @return array
	 */
	public function setting() {

		// 获取留言本配置表数据
		$serv = D('Askfor/AskforSetting', 'Service');

		return $serv->list_kv();
	}

	/**
	 * 审批模板缓存
	 * @return mixed
	 */
	public function template() {

		// 获取审批模板列表数据
		$serv = D('Askfor/AskforTemplate', 'Service');
		$order_option = array('orderid' => 'ASC');
		$tmp = $serv->list_all_orderby_orderid($order_option);
		//将字符串拆为数组
		if (!empty($tmp)) {
			foreach ($tmp as &$val) {
				$val['bu_ids'] = explode(',', $val['bu_id']);
			}
		}

		return $tmp;
	}
}

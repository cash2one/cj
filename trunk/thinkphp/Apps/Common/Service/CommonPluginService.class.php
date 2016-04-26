<?php
/**
 * CommonPluginService.class.php
 * $author$
 */

namespace Common\Service;
use Common\Service\AbstractService;

class CommonPluginService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('Common/CommonPlugin');
	}

	/**
	 * 读取所有插件信息
	 * @see \Com\Service::list_all()
	 */
	public function list_all($page_option = null, $order_option = array()) {

		// 读取列表
		$list = parent::list_all($page_option, $order_option);

		// 转换键值
		return array_combine_by_key($list, 'cp_pluginid');
	}

}

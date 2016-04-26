<?php
/**
 * CommonPluginGroupService.class.php
 * $author$
 */

namespace Common\Service;
use Common\Service\AbstractService;

class CommonPluginGroupService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('Common/CommonPluginGroup');
	}

	/**
	 * 读取所有插件分组信息
	 * @see \Com\Service::list_all()
	 */
	public function list_all($page_option = null, $order_option = array()) {

		// 读取列表
		$list = parent::list_all($page_option, $order_option);

		// 转换键值
		return array_combine_by_key($list, 'cpg_id');
	}

	/**
	 * 更新套件信息
	 * @param $cpg_id 套件ID
	 * @param $data 更新数据
	 * @return mixed
	 */
	public function update_cpg($cpg_id, $data) {

		return $this->_d->update($cpg_id, $data);
	}

}

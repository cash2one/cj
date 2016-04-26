<?php
/**
 * voa_c_api_travel_get_goodsclass
 * 获取分类信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_get_goodsclass extends voa_c_api_travel_goodsabstract {
	// 默认 limit 值
	protected $_limit = 5000;

	protected function _before_action($action) {

		// 检查权限
		$this->_require_login = false;

		return parent::_before_action($action);
	}

	public function execute() {

		// 获取分页参数
		$page = (int)$this->_get('page');
		$limit = (int)$this->_get('limit');
		$limit = 0 >= $limit ? $this->_limit : $limit;
		list($start, $perpage, $page) = voa_h_func::get_limit($page, min($limit, $this->_limit));

		// 读取数据
		$total = 0;
		$list = array();
		$uda = &uda::factory('voa_uda_frontend_goods_class', $this->_ptname);
		if (!$uda->list_all($this->_params, $list, array($start, $perpage), $total)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		$this->_result = array(
			'total' => $total,
			'data' => empty($list) ? array() : array_values($list)
		);

		return true;
	}

	/**
	 * 设置插件/表格名称
	 * @return boolean
	 */
	protected function _init_ptname() {

		parent::_init_ptname();
		$this->_ptname['classes'] = voa_h_cache::get_instance()->get('plugin.travel.goodsclass', 'oa');
	}

}


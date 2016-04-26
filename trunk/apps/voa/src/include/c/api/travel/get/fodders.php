<?php
/**
 * voa_c_api_travel_get_fodders
 * 获取商品素材列表信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_get_fodders extends voa_c_api_travel_goodsabstract {
	// 数据处理 uda(货源)
	protected $_uda;
	// 最大 limit 值
	protected $_max_limit = 100;

	public function execute() {

		// 获取分页参数
		$page = (int)$this->_get('page');
		$limit = (int)$this->_get('limit');
		$limit = 0 >= $limit ? $this->_p_sets['perpage'] : $limit;
		list($start, $perpage, $page) = voa_h_func::get_limit($page, min($limit, $this->_max_limit));

		//默认admin，可查看所有素材
		$params = $this->_params;
		$params['is_admin'] = 1;
		$params['fodder'] = 'fodder';


		// 读取数据
		$total = 0;
		$list = array();
		$this->_uda = &uda::factory('voa_uda_frontend_goods_data', $this->_ptname);
		if (!$this->_uda->list_all($params, array($start, $perpage), $list, $total)) {
			$this->_errcode = $this->_uda->errno;
			$this->_errmsg = $this->_uda->error;
			return true;
		}

		$data = array();
		// 如果 $list 不为空
		if (!empty($list)) {
			// 数据整理
			foreach ($list as $_v) {
				$_v['_created'] = rgmdate($_v['created']);
				if (!empty($_v['fodder_img'])) {
					$_v['fodder_url'] = $_v['fodder_img'][0]['url'];
				} elseif ($_v['cover']) {
					$_v['fodder_url'] =$_v['cover'][0]['url'];
				}
				$data[] = $_v;
			}
		}

		$this->_result = array(
			'total' => $total,
			'data' => $data
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
		$this->_ptname['columns'] = voa_h_cache::get_instance()->get('plugin.travel.goodstablecol', 'oa');
		$this->_ptname['options'] = voa_h_cache::get_instance()->get('plugin.travel.goodstablecolopt', 'oa');
	}

}

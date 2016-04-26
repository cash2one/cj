<?php
/**
 * voa_c_api_travel_get_goods
 * 获取商品列表信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_get_goods extends voa_c_api_travel_goodsabstract {
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

		// 判断是否 admin
		$params = $this->_params;
		unset($params['is_admin']);
		if ($this->_is_admin) {
			$params['is_admin'] = 1;
		} else {
			$params['uid'] = array();
			// 如果用户已登录
			if (!empty($this->_member)) {
				// 如果是销售
				$tmp_uid = 0;
				if (empty($this->_member['mpuid'])) {
					$tmp_uid = $this->_member['m_uid'];
				} elseif (!empty($this->_member['saleid'])) { // 如果客户是来自销售
					$tmp_uid = $this->_member['saleid'];
				}

				$tmp_uid = (int)$tmp_uid;
				$params['uid'][$tmp_uid] = $tmp_uid;
			} else {
			    $params['uid'][] = 0;
			}
		}

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
				if (empty($_v['cover'])) {
					$_v['cover'] = '';
				} elseif ($_v['cover']) {
					$_v['cover'] = $_v['cover'][0]['url'];
				}

				$_v['_created'] = rgmdate($_v['created']);
				$data[] = $_v;
			}
		}

		// 购物车统计
		$cart_total = 0;
		if (!empty($this->_member['openid'])) {
			$cart_uda = new voa_uda_frontend_travel_cart();
			$cart_uda->get_cart_total($this->_member['openid'], $cart_total);
		}

		$this->_result = array(
			'total' => $total,
			'data' => $data,
			"cart_total" => $cart_total
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

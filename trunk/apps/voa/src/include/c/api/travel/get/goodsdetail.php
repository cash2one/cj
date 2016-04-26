<?php
/**
 * voa_c_api_travel_get_goodsdetail
 * 获取商品详情信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_get_goodsdetail extends voa_c_api_travel_goodsabstract {

	protected function _before_action($action) {

		// 检查权限
		$this->_chk_privilege();

		return parent::_before_action($action);
	}

	public function execute() {

		// 获取分页参数
		$dataid = (int)$this->_get('dataid');

		// 读取数据
		$data = array();
		$uda = &uda::factory('voa_uda_frontend_goods_data', $this->_ptname);
		if (!$uda->get_one($dataid, $data)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		// 剔除主键键值
		if (isset($data['slide']) && !empty($data['slide'])) {
			$data['slide'] = array_values($data['slide']);
		}

		// 推荐特殊处理
		if (!$this->_get('is_admin')) {
			$data['cover'] = empty($data['cover']) ? '' : $data['cover'][0]['url'];
		}

		// 如果非编辑操作
		if (!isset($this->_params['edit'])) {
			foreach ($data as &$_v) {
				if (!is_array($_v)) {
					$_v = nl2br($_v);
				}
			}

			unset($_v);
		}

		// 读取权限用户
		$mgusers = array();
		if ($this->_is_admin) {
			$serv_mg = new voa_s_oa_travel_mem2goods();
			$mglist = $serv_mg->list_by_dataid($data['dataid']);
			foreach ((array)$mglist as $_v) {
				if (empty($_v['uid'])) {
					continue;
				}

				$mgusers[] = array('id' => $_v['uid'], 'name' => $_v['username']);
			}
		}

		$data['uids'] = $mgusers;

		// 读取分享人信息
		$this->_get_sales($data);

		// 购物车统计
		$cart_total = 0;
		if (!empty($this->_member['openid'])) {
			$cart_uda = new voa_uda_frontend_travel_cart();
			$cart_uda->get_cart_total($this->_member['openid'], $cart_total);
		}

		$data['cart_total'] = $cart_total;

		$this->_result = $data;

		return true;
	}

	/**
	 * 读取sales信息
	 * @param unknown $data
	 * @return boolean
	 */
	protected function _get_sales(&$data) {

		// 如果非分享链接
		if ('qy' == startup_env::get('logintype')) {
			return true;
		}

		// 销售信息
		$sales = array(
			'uid' => 0,
			'mobile' => '',
			'username' => ''
		);

		// 读取用户信息
		$serv_mem = &service::factory('voa_s_oa_member');
		$m_uid = $data['uid'];
		if (0 < $m_uid) {
			$user = $serv_mem->fetch_by_uid($m_uid);
		}

		if (empty($user)) {
			// 取最后一个用户
			$tmp_users = $serv_mem->fetch_all_by_conditions(array(), array(), 0, 1);
			if (!empty($tmp_users)) {
				$user = array_pop($tmp_users);
				$m_uid = rand(0, $user['m_uid']);
				$tmp_users = $serv_mem->fetch_all_by_conditions(array('m_uid' => array($m_uid, '>')));
				$user = array_pop($tmp_users);
			}
		}

		if (!empty($user)) {
			$sales = array(
				'uid' => $user['m_uid'],
				'mobile' => $user['m_mobilephone'],
				'username' => $user['m_username']
			);
		}

		// sales 信息
		$data['sales'] = $sales;

		if (empty($this->_sig)) {
			return true;
		}

		// 统计
		$params = array(
			'uid' => $data['uid'],
			'goods_id' => $data['dataid'],
			//'sig' => $this->_sig
		);
		$uda_get = &uda::factory('voa_uda_frontend_travel_sharecount_get');
		$share = array();
		$uda_get->execute($params, $share);
		// 如果不记录存在
		if (empty($share)) {
			$uda_add = &uda::factory('voa_uda_frontend_travel_sharecount_add');
			$uda_add->execute($params, $share);
		} else { // 如果记录存在
			$uda_up = &uda::factory('voa_uda_frontend_travel_sharecount_update');
			$uda_up->execute(array('tsc_id' => $share['tsc_id']), $share);
		}

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

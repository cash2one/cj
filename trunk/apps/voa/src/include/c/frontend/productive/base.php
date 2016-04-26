<?php
/**
 * 活动反馈基类
 * $Author$
 * $Id$
 */

class voa_c_frontend_productive_base extends voa_c_frontend_base {
	/** 地区信息 */
	protected $_regions = array();
	/** 店铺信息 */
	protected $_shops = array();
	/** 评分项信息 */
	protected $_items = array();

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}

		/** 取评分项信息 */
		$this->_items = voa_h_cache::get_instance()->get('plugin.productive.item', 'oa');
		if (empty($this->_items)) {
			$this->_error_message('请先添加活动反馈评分项信息');
			return true;
		}

		/** 取地区配置 */
		$this->_regions = voa_h_cache::get_instance()->get('region', 'oa');
		/** 取店铺配置 */
		$this->_shops = voa_h_cache::get_instance()->get('shop', 'oa');

		$this->view->set('navtitle', '活动/产品反馈');
		$this->view->set('productive_set', $this->_p_sets);

		return true;
	}

	// 获取插件信息
	protected function _get_plugin() {

		/** 取应用配置 */
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.productive.setting', 'oa');
		/** 取应用插件信息 */
		$pluginid = $this->_p_sets['pluginid'];
		startup_env::set('pluginid', $pluginid);
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		// 如果应用信息不存在
		if (!array_key_exists($pluginid, $plugins)) {
			$this->_error_message('应用信息丢失，请重新开启');
			return true;
		}

		// 获取应用信息
		$this->_plugin = $plugins[$pluginid];

		// 判断应用是否关闭
		if ($this->_plugin['cp_available'] != voa_d_oa_common_plugin::AVAILABLE_OPEN) {
			$this->_error_message('本应用尚未开启 或 已关闭，请联系管理员启用后使用');
			return true;
		}

		startup_env::set('agentid', $this->_plugin['cp_agentid']);
		/** 加载提示语言 */
		language::load_lang($this->_plugin['cp_identifier']);

		return true;
	}

	/**
	 * 获取销售轨迹配置
	 */
	public static function fetch_cache_productive_setting() {
		$serv = &service::factory('voa_s_oa_productive_setting', array('pluginid' => startup_env::get('pluginid')));
		$data = $serv->fetch_all();
		$arr = array();
		foreach ($data as $v) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $v['pts_type']) {
				$arr[$v['pts_key']] = unserialize($v['pts_value']);
			} else {
				$arr[$v['pts_key']] = $v['pts_value'];
			}
		}

		return $arr;
	}

	/**
	 * 获取评分项配置
	 */
	public static function fetch_cache_productive_item() {

		$serv = &service::factory('voa_s_oa_productive_item', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->fetch_all();

		$data = array();
		$p2c = array();
		foreach ($list as $_v) {
			if (!array_key_exists($_v['pti_parent_id'], $p2c)) {
				$p2c[$_v['pti_parent_id']] = array();
			}

			$data[$_v['pti_id']] = $_v;
			$p2c[$_v['pti_parent_id']][$_v['pti_id']] = $_v['pti_id'];
		}

		$data['p2c'] = $p2c;

		return $data;
	}

	/**
	 * 检查当前用户是否有权限编辑
	 * @param array $productive 活动反馈信息
	 * @return boolean
	 */
	protected function _chk_edit_permit($productive) {

		/** 判断权限 */
		if (empty($productive) || $this->_user['m_uid'] != $productive['m_uid']) {
			$this->_error_message('no_privilege');
			return false;
		}

		/** 判断状态是否正确 */
		if (voa_d_oa_productive::STATUS_DONE == $productive['pt_status']) {
			$this->_error_message('productive_done');
			return false;
		}

		return true;
	}

	/**
	 * 获取查看详情的url
	 * @param string $url url地址
	 * @param int $ao_id 请假信息id
	 * @return boolean
	 */
	public function get_view_url(&$url, $pt_id) {

		/** 组织查看链接 */
		$scheme = config::get('voa.oa_http_scheme');
		$url = voa_wxqy_service::instance()->oauth_url($scheme.$this->_setting['domain'].'/frontend/productive/view/pt_id/'.$pt_id.'?pluginid='.startup_env::get('pluginid'));

		return true;
	}

	/** 组织地区 json 信息 */
	public function get_region_json(&$regions) {
		/** 从主地区开始遍历 */
		foreach ($this->_regions['level'][0] as $k => $_cr_id) {
			/** 判断是否有子地区 */
			if (!array_key_exists($_cr_id, $this->_regions['level']) || empty($this->_regions['level'][$_cr_id])) {
				continue;
			}

			$regions[$k] = array(
				'id' => $_cr_id,
				'title' => $this->_regions['data'][$_cr_id]['cr_name'],
				'districts' => array()
			);
			/** 遍历 */
			foreach ($this->_regions['level'][$_cr_id] as $_v) {
				$regions[$k]['districts'][] = array(
					'id' => $_v,
					'title' => $this->_regions['data'][$_v]['cr_name']
				);
			}
		}

		$regions = array_values($regions);
		return true;
	}

	/** 组织店铺 json 信息 */
	public function get_shop_json(&$region2shop) {
		/** 先遍历店铺 */
		$r2shp = array();
		foreach ($this->_shops as $_shp) {
			if (!array_key_exists($_shp['cr_id'], $r2shp)) {
				$r2shp[$_shp['cr_id']] = array();
			}

			$r2shp[$_shp['cr_id']][] = array(
				'id' => $_shp['csp_id'],
				'title' => $_shp['csp_name']
			);
		}

		/** 从主地区开始遍历 */
		foreach ($this->_regions['level'][0] as $k => $_cr_id) {
			/** 判断是否有子地区 */
			if (!array_key_exists($_cr_id, $this->_regions['level']) || empty($this->_regions['level'][$_cr_id])) {
				continue;
			}

			$region2shop[$k] = array(
				'id' => $_cr_id,
				'title' => $this->_regions['data'][$_cr_id]['cr_name'],
				'districts' => array()
			);
			/** 遍历 */
			foreach ($this->_regions['level'][$_cr_id] as $_v) {
				if (empty($r2shp[$_v])) {
					continue;
				}

				$region2shop[$k]['districts'][] = array(
					'id' => $_v,
					'title' => $this->_regions['data'][$_v]['cr_name'],
					'shops' => $r2shp[$_v]
				);
			}
		}

		$region2shop = array_values($region2shop);

		return true;
	}

	/**
	 * 更新草稿
	 * @param array $join_uids 接收人uids
	 * @param array $cc_uids 抄送人uids
	 */
	protected function _update_draft($join_uids = array(), $cc_uids = array()) {

		$serv = &service::factory('voa_s_oa_productive_draft', array('pluginid' => startup_env::get('pluginid')));
		$ptd_id = (int)$this->request->get('ptd_id');
		if (0 < $ptd_id) {
			$serv->update(array(
				'ptd_message' => '',
				'ptd_a_uid' => implode(',', $join_uids),
				'ptd_cc_uid' => implode(',', array_diff($cc_uids, array($this->_user['m_uid'])))
			), array('ptd_id' => $ptd_id, 'm_openid' => $this->_user['m_openid']));
		} else {
			$serv->insert(array(
				'm_openid' => $this->_user['m_openid'],
				'ptd_a_uid' => implode(',', $join_uids),
				'ptd_cc_uid' => implode(',', array_diff($cc_uids, array($this->_user['m_uid'])))
			));
		}

		return true;
	}

	/**
	 * 获取草稿信息
	 * @param array &$ret 草稿内容
	 */
	protected function _get_draft(&$ret) {

		$serv_dr = &service::factory('voa_s_oa_productive_draft', array('pluginid' => startup_env::get('pluginid')));
		$this->_draft = $serv_dr->get_by_openid($this->_user['m_openid']);
		if (empty($this->_draft)) {
			return true;
		}

		$this->view->set('ptd_id', $this->_draft['ptd_id']);

		/** 取最近一次操作相关人员 */
		$uids = array();
		if (!empty($this->_draft['ptd_cc_uid'])) {
			$uids = explode(',', $this->_draft['ptd_cc_uid']);
		}

		$a_uids = array();
		if (!empty($this->_draft['ptd_a_uid'])) {
			$a_uids = explode(',', $this->_draft['ptd_a_uid']);
			$uids = array_merge($uids, $a_uids);
		}

		/** 取用户信息 */
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => startup_env::get('pluginid')));
		$users = $serv_m->fetch_all_by_ids($uids);

		/** 输出接收人 */
		$ret = array();
		$accepters = array();
		if (!empty($this->_draft['ptd_a_uid'])) {
			foreach ($a_uids as $uid) {
				if (!empty($users[$uid])) {
					$accepters[$uid] = $users[$uid];
					unset($users[$uid]);
				}
			}
		}

		$ret['ccusers'] = $users;
		$ret['accepters'] = $accepters;
		$ret['message'] = $this->_draft['ptd_message'];
		return true;
	}
}

<?php
/**
 * voa_c_frontend_inspect_base
 * 巡店基类
 * $Author$
 * $Id$
 */

class voa_c_frontend_inspect_base extends voa_c_frontend_base {
	// 地区信息
	protected $_regions = array();
	// 店铺信息
	protected $_shops = array();
	// 评分项信息
	protected $_items = array();
	protected $_options = array();

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		// 取评分项信息
		$this->_options = voa_h_cache::get_instance()->get('plugin.inspect.option', 'oa');
		$this->_items = voa_h_cache::get_instance()->get('plugin.inspect.item', 'oa');
		if (empty($this->_items['p2c'])) {
			$this->_error_message('请先添加巡店评分项信息');
			return true;
		}

		// 取地区配置
		$this->_regions = voa_h_cache::get_instance()->get('region', 'oa');
		// 取店铺配置
		$this->_shops = voa_h_cache::get_instance()->get('shop', 'oa');

		$this->view->set('navtitle', '巡店');
		$this->view->set('inspect_set', $this->_p_sets);

		return true;
	}

	// 获取插件信息
	protected function _get_plugin() {

		// 取应用配置
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.inspect.setting', 'oa');
		// 取应用插件信息
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
		// 加载提示语言
		language::load_lang($this->_plugin['cp_identifier']);

		return true;
	}

	// 获取销售轨迹配置
	public static function fetch_cache_inspect_setting() {

		// 读取配置数据
		$serv_set = new voa_s_oa_inspect_setting();
		$data = $serv_set->list_all();

		$arr = array();
		foreach ($data as $v) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $v['is_type']) {
				$arr[$v['is_key']] = unserialize($v['is_value']);
			} else {
				$arr[$v['is_key']] = $v['is_value'];
			}
		}

		self::_check_agentid($arr, 'inspect');
		return $arr;
	}

	// 获取评分项配置
	public static function fetch_cache_inspect_item() {

		$serv_it = new voa_s_oa_inspect_item();
		$list = $serv_it->list_by_conds(array('insi_state=?' => voa_d_oa_inspect_item::STATE_USING), null, array('insi_ordernum' => 'DESC', 'insi_updated' => 'DESC'));

		$data = array(
			'p2c' => array()
		);
		foreach ($list as $_v) {
			if (!array_key_exists($_v['insi_parent_id'], $data['p2c'])) {
				$data['p2c'][$_v['insi_parent_id']] = array();
			}

			$data[$_v['insi_id']] = $_v;
			$data['p2c'][$_v['insi_parent_id']][$_v['insi_id']] = $_v['insi_id'];
		}

		return $data;
	}

	public static function fetch_cache_inspect_option() {

		$serv_it = new voa_s_oa_inspect_option();
		$list = $serv_it->list_by_conds(array('inso_state=?' => voa_d_oa_inspect_option::STATE_USING));

		$data = array(
			'i2o' => array()
		);
		foreach ($list as $_v) {
			if (!array_key_exists($_v['insi_id'], $data['i2o'])) {
				$data['i2o'][$_v['insi_id']] = array();
			}

			$data[$_v['inso_id']] = $_v;
			$data['i2o'][$_v['insi_id']][$_v['inso_id']] = $_v['inso_id'];
		}

		return $data;
	}

	/**
	 * 检查当前用户是否有权限编辑
	 * @param array $inspect 巡店信息
	 * @return boolean
	 */
	protected function _chk_edit_permit($inspect) {

		// 判断权限
		if (empty($inspect) || $this->_user['m_uid'] != $inspect['m_uid']) {
			$this->_error_message('no_privilege');
			return false;
		}

		// 判断状态是否正确
		if (voa_d_oa_inspect::TYPE_DONE == $inspect['ins_type']) {
			$this->_error_message('inspect_done');
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
	public function get_view_url(&$url, $ins_id) {

		// 组织查看链接
		$scheme = config::get('voa.oa_http_scheme');
		$url = voa_wxqy_service::instance()->oauth_url($scheme.$this->_setting['domain'].'/frontend/inspect/view/ins_id/'.$ins_id.'?pluginid='.startup_env::get('pluginid'));

		return true;
	}

	// 组织地区 json 信息
	public function get_region_json(&$regions) {

		// 从主地区开始遍历
		foreach ($this->_regions['level'][0] as $k => $_cr_id) {
			// 判断是否有子地区
			if (!array_key_exists($_cr_id, $this->_regions['level']) || empty($this->_regions['level'][$_cr_id])) {
				continue;
			}

			$regions[$k] = array(
				'id' => $_cr_id,
				'title' => $this->_regions['data'][$_cr_id]['cr_name'],
				'districts' => array()
			);
			// 遍历
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

	// 组织店铺 json 信息
	public function get_shop_json(&$region2shop) {

		// 先遍历店铺
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

		// 从主地区开始遍历
		foreach ($this->_regions['level'][0] as $k => $_cr_id) {
			// 判断是否有子地区
			if (!array_key_exists($_cr_id, $this->_regions['level']) || empty($this->_regions['level'][$_cr_id])) {
				continue;
			}

			$region2shop[$k] = array(
				'id' => $_cr_id,
				'title' => $this->_regions['data'][$_cr_id]['cr_name'],
				'districts' => array()
			);
			// 遍历
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

		// 取 insd_id
		$insd_id = (int)$this->request->get('insd_id');
		// 输入参数
		$in = array(
			'insd_message' => '',
			'insd_a_uid' => implode(',', $join_uids),
			'insd_cc_uid' => implode(',', array_diff($cc_uids, array($this->_user['m_uid']))),
			'm_openid' => $this->_user['m_openid']
		);

		$draft = array();
		// 如果 insd_id 有值
		if (!empty($insd_id)) {
			// uda 初始化
			$uda = &uda::factory('voa_uda_frontend_inspect_draft_update');
			// 更新草稿
			$in['insd_id'] = $insd_id;
			$uda->execute($in, $draft);
		} else {
			$uda = &uda::factory('voa_uda_frontend_inspect_draft_add');
			$uda->execute($in, $draft);
		}

		return true;
	}

	/**
	 * 获取草稿信息
	 * @param array &$ret 草稿内容
	 */
	protected function _get_draft(&$ret) {

		$in = array(
			'm_openid' => $this->_user['m_openid']
		);
		$uda_draft_get = &uda::factory('voa_uda_frontend_inspect_draft_get');
		if (!$uda_draft_get->execute($in, $this->_draft)) {
			return false;
		}

		$this->view->set('insd_id', $this->_draft['insd_id']);

		// 取最近一次操作相关人员
		$uids = array();
		if (!empty($this->_draft['insd_cc_uid'])) {
			$uids = explode(',', $this->_draft['insd_cc_uid']);
		}

		$a_uids = array();
		if (!empty($this->_draft['insd_a_uid'])) {
			$a_uids = explode(',', $this->_draft['insd_a_uid']);
			$uids = array_merge($uids, $a_uids);
		}

		// 取用户信息
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => startup_env::get('pluginid')));
		$users = $serv_m->fetch_all_by_ids($uids);

		// 输出接收人
		$ret = array();
		$accepters = array();
		if (!empty($this->_draft['insd_a_uid'])) {
			foreach ($a_uids as $uid) {
				if (!empty($users[$uid])) {
					$accepters[$uid] = $users[$uid];
					unset($users[$uid]);
				}
			}
		}

		$ret['ccusers'] = $users;
		$ret['accepters'] = $accepters;
		$ret['message'] = $this->_draft['insd_message'];
		return true;
	}

	protected function _get_ext_items(&$items, $exts) {

		$noids = array();
		foreach ($exts as $_scr) {
			if (isset($items[$_scr['insi_id']])) {
				continue;
			}

			$noids[] = $_scr['insi_id'];
		}

		if (empty($noids)) {
			return true;
		}

		$uda_ins_item = new voa_uda_frontend_inspect_item_list();
		$uda_ins_item->set_limit(false);
		$curitems = array();
		$p_ids = array();
		$uda_ins_item->execute(array('insi_id' => $noids), $curitems);
		foreach ($curitems as $_v) {
			if (!array_key_exists($_v['insi_parent_id'], $items['p2c'])) {
				$items['p2c'][$_v['insi_parent_id']] = array();
			}

			$items[$_v['insi_id']] = $_v;
			$items['p2c'][$_v['insi_parent_id']][$_v['insi_id']] = $_v['insi_id'];
			if (0 < $_v['insi_parent_id'] && !isset($curitems[$_v['insi_parent_id']])
					&& !isset($items[$_v['insi_parent_id']])) {
				$p_ids[] = array('insi_id' => $_v['insi_parent_id']);
			}
		}

		return $this->_get_ext_items($items, $p_ids);
	}
}

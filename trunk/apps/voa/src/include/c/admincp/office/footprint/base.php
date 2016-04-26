<?php
/**
 * voa_c_admincp_office_footprint_base
 * 企业后台/微办公/销售轨迹/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_footprint_base extends voa_c_admincp_office_base {

	/**
	 * 销售轨迹系统设置
	 * @var array
	 */
	protected $_sets = array();

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->_sets = voa_h_cache::get_instance()->get('plugin.footprint.setting', 'oa');
		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

	/**
	 * 搜索销售轨迹数据
	 * @param number $perpage
	 * @return array(total, multi, search_by, list)
	 */
	protected function _footprint_search($perpage) {

		//搜索条件
		$conditions = array();
		//搜索字段
		$search_by = array();

		$uda_footprint_search = &uda::factory('voa_uda_frontend_footprint_search');
		$uda_footprint_search->footprint_conditions($search_by, $conditions, array('shard_key' => $this->_module_plugin_id));

		$list = array();
		$total = $this->_service_single('footprint', $this->_module_plugin_id, 'count_by_conditions', $conditions);
		$multi = '';
		if ($total > 0) {
			$pagerOptions = array(
					'total_items' => $total,
					'per_page' => $perpage,
					'current_page' => $this->request->get('page'),
					'show_total_items' => true,
			);
			$multi = pager::make_links($pagerOptions);
			pager::resolve_options($pagerOptions);
			$list = $this->_service_single('footprint', $this->_module_plugin_id, 'fetch_by_conditions', $conditions, $pagerOptions['start'], $pagerOptions['per_page']);
			$m_uids = array();
			foreach ($list AS $_data) {
				if (!isset($m_uids[$_data['m_uid']])) {
					$m_uids[$_data['m_uid']] = $_data['m_uid'];
				}
			}
			$users = voa_h_user::get_multi($m_uids, array('_realname', '_department', '_job'));

			$uda_footprint_format = &uda::factory('voa_uda_frontend_footprint_format');
			$uda_member_format = &uda::factory('voa_uda_frontend_member_format');

			foreach ($list as &$_data) {
				$_member = array('_realname' => $_data['m_username'], '_department', '_job');
				if (isset($users[$_data['m_uid']])) {
					$_member = $users[$_data['m_uid']];
					$uda_member_format->format($_member);
				}

				$uda_footprint_format->format($_data);
				$_data = array_merge($_data, $_member);

				unset($_member);
			}
			unset($_data);
		}

		return array($total, $multi, $search_by, $list);
	}

}

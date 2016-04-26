<?php
/**
 * voa_c_admincp_office_meeting_reservation
 * 企业后台 - 会议通 - 预约列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_meeting_list extends voa_c_admincp_office_meeting_base {

	/** 会议进行状态 */
	protected $_expire_status_descriptions = array(
			'0' => '未开始',
			'1' => '正进行',
			'2' => '已结束'
	);

	/** 取消状态 */
	protected $_cancel_status_descriptions = array(
			voa_d_oa_meeting::STATUS_CANCEL => '已取消',
			voa_d_oa_meeting::STATUS_UPDATE => '未取消',
	);

	public function execute() {

		$searchDefault = array(
				'mr_id' => 0,
				'm_username' => '',
				'mt_username' => '',
				'mt_status' => '-1',
				'expire' => '-1',
				'mt_subject' => '',
		);
		$searchBy = array();
		foreach ( $searchDefault AS $_k=>$_v ) {
			if ( isset($_GET[$_k]) && $_v != $this->request->get($_k) ) {
				$searchBy[$_k] = $this->request->get($_k);
			}
		}

		$issearch = $this->request->get('issearch') ? 1 : 0;

		$urlParam = $issearch ? array() : $searchBy;

		$perpage = 10;
		list($total, $multi, $meetingList) = $this->_search_meeting($this->_module_plugin_id, $issearch, $searchDefault, $searchBy, $perpage);

		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
		$this->view->set('meetingRoomList', $this->_meeting_room_list($this->_module_plugin_id));
		$this->view->set('searchBy', array_merge($searchDefault, $searchBy));
		$this->view->set('issearch', $issearch);
		$this->view->set('expireStatus', $this->_expire_status_descriptions);
		$this->view->set('cancelStatus', $this->_cancel_status_descriptions);
		$this->view->set('cancelStatusValue', $this->_cancel_status_value);
		$this->view->set('multi', $multi);
		$this->view->set('meetingList', $meetingList);
		$this->view->set('total', $total);
		$this->view->set('timestamp', startup_env::get('timestamp'));
		$this->view->set('deleteUrlBase', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('mt_id'=>'')));
		$this->view->set('viewUrlBase', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array('mt_id'=>'')));
		$this->view->set('formDeleteUrl', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id));

		$this->output('office/meeting/list');

	}

	/**
	 * 搜索指定条件的会议信息
	 * @param number $issearch
	 * @param array $searchDefault
	 * @param array $searchBy
	 * @param number $perpage
	 * @return array(total, multi, list)
	 */
	protected function _search_meeting($cp_pluginid, $issearch, $searchDefault = array(), $searchBy = array(), $perpage = 10) {

		/** 搜索条件 */
		$conditions = array();
		/** 如果为搜索 */
		if ( $issearch ) {
			foreach ( $searchDefault AS $_k=>$_v ) {
				if ( isset($searchBy[$_k]) && $searchBy[$_k] != $_v ) {
					$v = $searchBy[$_k];
					/** 检查条件合法性 */
					if ( $_k == 'mr_id' ) {
						//会议室
						$meetingRoomList = $this->_meeting_room_list($this->_module_plugin_id);
						if ( isset($meetingRoomList[$v]) ) {
							$conditions[$_k] = $v;
						}
					} else {
						$conditions[$_k] = $v;
					}
				}
			}
		}

		$list = array();
		$total = $this->_service_single('meeting', $cp_pluginid, 'count_by_conditions', $conditions);
		$multi = '';
		if ( $total > 0 ) {
			$pagerOptions = array(
					'total_items' => $total,
					'per_page' => $perpage,
					'current_page' => $this->request->get('page'),
					'show_total_items' => true,
			);
			$multi = pager::make_links($pagerOptions);
			pager::resolve_options($pagerOptions);
			$tmp = $this->_service_single('meeting', $cp_pluginid, 'fetch_all_by_conditions', $conditions, $pagerOptions['start'], $pagerOptions['per_page']);
			foreach ( $tmp AS $_mt_id => $_mt ) {
				$list[$_mt_id] = $this->_format_meeting($_mt);
			}
			unset($tmp);
		}
		return array($total, $multi, $list);
	}

}

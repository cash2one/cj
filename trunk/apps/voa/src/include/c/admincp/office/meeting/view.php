<?php
/**
 * voa_c_admincp_office_meeting_view
 * 企业后台 - 会议通 - 会议详情查看
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_meeting_view extends voa_c_admincp_office_meeting_base {

	/** 用户参会状态 */
	protected $_meeting_mem_status_descriptions = array(
			voa_d_oa_meeting_mem::STATUS_NORMAL => '待确认',
			voa_d_oa_meeting_mem::STATUS_CONFIRM => '确认参加',
			voa_d_oa_meeting_mem::STATUS_ABSENCE => '不参加',
			voa_d_oa_meeting_mem::STATUS_REMOVE => '已删除',
	);

	public function execute() {

		$mt_id = $this->request->get('mt_id');
		if ( !$mt_id || !($meeting = $this->_get_meeting($this->_module_plugin_id, $mt_id)) ) {
			$this->message('error', '指定会议信息不存在');
		}
		/** 会议详情 */
		$meeting = $this->_format_meeting($meeting);

		$meetingRoomFields = array(
				'mr_name'=>'',
				'mr_address'=>'地点：',
				'mr_galleryful'=>'容纳人数：',
				'mr_device'=>'设备：'
		);
		$meetingRoom = array();
		$meetingRoomList = $this->_meeting_room_list($this->_module_plugin_id);
		if ( isset($meetingRoomList[$meeting['mr_id']]) ) {
			$meetingRoom = $meetingRoomList[$meeting['mr_id']];
		}
		unset($meetingRoomList);
		/** 参会人员列表 */
		$memList = $this->_service_single('meeting_mem', $this->_module_plugin_id, 'fetch_by_mt_id', $mt_id, 0, 0);
		$memList = $this->_format_meeting_mem($memList);


		$this->view->set('meetingRoomFields', $meetingRoomFields);
		$this->view->set('meetingRoom', $meetingRoom);
		$this->view->set('meeting', $meeting);
		$this->view->set('mt_id', $mt_id);
		$this->view->set('meetingMemStatusDescriptions', $this->_meeting_mem_status_descriptions);
		$this->view->set('memList', $memList);
		$this->view->set('meeting_cancel', $this->_cancel_status_value);

		$this->output('office/meeting/view');

	}

	/**
	 * 获取指定会议信息
	 * @param number $mt_id
	 * @return array
	 */
	protected function _get_meeting($cp_pluginid, $mt_id) {
		return $this->_service_single('meeting', $cp_pluginid, 'fetch_by_id', $mt_id);
	}

	/**
	 * 格式化参会人员列表数据
	 * @param array $memList
	 * @return array
	 */
	protected function _format_meeting_mem($memList) {
		$list	=	array();
		$orderNum = 1;
		foreach ( $memList AS $_mm_id => $_mm ) {
			$_mm['_status'] = isset($this->_meeting_mem_status_descriptions[$_mm['mm_status']]) ? $this->_meeting_mem_status_descriptions[$_mm['mm_status']] : '';
			$_mm['_status_tag'] = $_mm['mm_status'] == 2 ? 'success' : ( $_mm['mm_status'] == 1 ? 'warning' : 'default' );
			$_mm['_time'] = rgmdate(($_mm['mm_updated'] ? $_mm['mm_updated'] : $_mm['mm_created']), 'Y-m-d H:i');
			$_mm['_order_num'] = $orderNum;
			$orderNum++;
			$list[$_mm_id] = $_mm;
		}
		return $list;
	}

}

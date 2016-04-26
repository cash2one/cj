<?php
/**
 * voa_c_admincp_office_meeting_edit
 * 企业后台 - 会议通 - 编辑
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_meeting_mredit extends voa_c_admincp_office_meeting_base {

	public function execute() {

		/** 当前操作的会议室id */
		$mr_id = $this->request->get('mr_id');
		/** 会议室默认字段 */
		$meetingRoom = $this->_get_meeting_room($this->_module_plugin_id, $mr_id);
		if ( !$meetingRoom['mr_id'] ) {
			$this->message('error', '指定会议室不存在或已被删除');
		}
		if ( $this->_is_post() ) {
			$fields = array('mr_name', 'mr_address', 'mr_galleryful', 'mr_device', 'mr_volume', 'mr_timestart', 'mr_timeend','mr_floor');
			$param = array();
			foreach ( $fields AS $k ) {
				if ( isset($_POST[$k]) && is_scalar($_POST[$k]) ) {
					$param[$k]	=	$this->request->post($k);
				}
			}
			if(strlen($param['mr_timestart']) == 5) {
				$param['mr_timestart'] .= ':00'; 
			}
			if(strlen($param['mr_timeend']) == 5) {
				$param['mr_timeend'] .= ':00'; 
			}
			$this->_meeting_room_submit_edit($this->_module_plugin_id, $meetingRoom, $param, $mr_id);
			exit;
		}

		$meetingRoom['mr_timestart'] = substr($meetingRoom['mr_timestart'], 0, 5);
		$meetingRoom['mr_timeend'] = substr($meetingRoom['mr_timeend'], 0, 5);
		
		$this->view->set('meetingRoom', $meetingRoom);
		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('mr_id'=>$mr_id)));
		$this->view->set('mr_id', $mr_id);
		$this->view->set('meetingRoomVolume', $this->_meeting_room_volum_descriptions);
		$this->view->set('pluginid', $this->_module_plugin_id);

		$this->output('office/meeting/mredit_form');

	}

}

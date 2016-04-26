<?php
/**
 * voa_c_admincp_office_meeting_add
 * 会议通 - 添加会议室
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_meeting_mradd extends voa_c_admincp_office_meeting_base {

	public function execute() {

		/** 当前操作的会议室id，新增为：0 */
		$mr_id = 0;
		/** 会议室默认字段 */
		$meetingRoom = $this->_get_meeting_room($this->_module_plugin_id, $mr_id);
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
			//自动分配二维码code(从1-100)
			$param['mr_code'] = $this->_code();
			$this->_meeting_room_submit_edit($this->_module_plugin_id, $meetingRoom, $param, $mr_id);
			exit;
		}

		$meetingRoom['mr_timestart'] = substr($meetingRoom['mr_timestart'], 0, 5);
		$meetingRoom['mr_timeend'] = substr($meetingRoom['mr_timeend'], 0, 5);
		$this->view->set('meetingRoom', $meetingRoom);
		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
		$this->view->set('mr_id', $mr_id);
		$this->view->set('meetingRoomVolume', $this->_meeting_room_volum_descriptions);

		$this->output('office/meeting/mredit_form');

	}

	//自动分配二维码code,从1-100
	private function _code()
	{
		//获取已存在code
		$d = new voa_d_oa_meeting_room();
		$list = $d->fetch_all();
		foreach ($list as $l)
		{
			$code[] = intval($l['mr_code']);
		}
		
		for ($i = 1; $i <= 100; $i++)
		{
			if(!in_array($i, $code)) {
				$assign_code = $i;
				break;
			}
		}
		return $assign_code;
	}
}

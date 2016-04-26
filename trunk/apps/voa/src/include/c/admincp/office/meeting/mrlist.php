<?php
/**
 * voa_c_admincp_office_meeting_list
 * 会议通管理 - 列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_meeting_mrlist extends voa_c_admincp_office_meeting_base {

	public function execute() {

		$act = $this->request->get('act');
		//加载子动作
		if($act)  {
			$this->$act();
			exit;
		}
		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, 'mrdelete', $this->_module_plugin_id));
		$this->view->set('meetingRoomList', $this->_meeting_room_list($this->_module_plugin_id));
		$this->view->set('multi', '');
		$this->view->set('deleteUrlBase', $this->cpurl($this->_module, $this->_operation, 'mrdelete', $this->_module_plugin_id, array('mr_id'=>'')));
		$this->view->set('editUrlBase', $this->cpurl($this->_module, $this->_operation, 'mredit', $this->_module_plugin_id, array('mr_id'=>'')));

		$this->view->set('pluginid', $this->_module_plugin_id);
		$this->output('office/meeting/mrlist');

	}
	
	//获取二维码,并将文字写入图片中
	private function qrcode()
	{
		$id = $this->request->get('id');
		$uda = new voa_uda_frontend_meeting_base();
		$uda->qrcode($id, '', isset($_GET['download']));
	}
}

<?php
/**
 * 扫描结果
 * $Author$
 * $Id$
 */

class voa_c_frontend_meeting_result extends voa_c_frontend_meeting_base {

	public function execute() {
		$mt_id = rintval($this->request->get('mt_id'));
		$act = $this->request->get('act');
		$uid = $this->_user['m_uid'];
		
		if(!$mt_id) {
			$this->_error_message('会议ID错误');
		}
		
		$mem = new voa_d_oa_meeting_mem();
		$user = $mem->fetch_by_mt_id_uid($mt_id, $uid);
		if(!$user) {
			$this->_error_message('你不是参会者!');
		}
		
		//签到或退场
		$value = $act == 'sign' ? 1 : 2;
		$rs = $mem->update(array('mm_confirm' => $value), array('mm_id' => $user['mm_id']));
		
		
		//获取部门,职业
		$this->_set_dept_job();
		$this->view->set('user', $this->_user);
		$this->view->set('act', $act);
		$this->view->set('mt_id', $mt_id);
		$this->_output('meeting/result');
	}
}

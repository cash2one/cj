<?php
/**
 * 扫描二维码: 进入会议列表页,有确认,结束,取消三个按钮
 * $Author$
 * $Id$
 */

class voa_c_frontend_meeting_scan extends voa_c_frontend_meeting_base {

	public function execute() {
		$act = $this->request->get('act');

		//子操作
		if($act) {
			$this->$act();
			exit;
		}
		
		$mr_id = rintval($this->request->get('mr_id'));
		$uid = $this->_user['m_uid'];

		if(!$mr_id) $this->_error_message('会议室ID错误');
		if(!$uid) $this->_error_message('无法获取用户id');
		
		/**
		 * 列出当天有我参与的会议
		 * 1.当天
		 * 2.过滤会议室ID与参与人
		 */
		$meeting = new voa_d_oa_meeting();
		$start = rstrtotime('today');
		$end = $start + 86400;
		$where = "mr_id = $mr_id AND mt_status < 3 AND mt_begintime > $start AND mt_begintime < $end";
		$list = $meeting->fetch_all_by_conditions2($where);
		$d = new voa_d_oa_meeting_mem();
		foreach ($list as $k => & $r)
		{
			if(time() > $r['mt_endtime']) {
				//标记已结束
				$r['finish'] = 1;
			}elseif(time() < $r['mt_begintime']) {
				//未开始
				$r['no'] = 1;
			}else{
				//进行中
				$r['ing'] = 1;
			}
			//获取参与信息
			$mem = $d->fetch_by_mt_id_uid($r['mt_id'], $uid);
			if(!$mem) {
				unset($list[$k]);
				continue;
			}
			$r['mem'] = $mem;
			$r['bthi'] = rgmdate($r['mt_begintime'], 'H:i');
			$r['enhi'] = rgmdate($r['mt_endtime'], 'H:i');
		}
		
		if(!$list) {
			$this->_error_message("今天没有要参加的会议");
		}
		$this->view->set('list', $list);
		
		
		$this->_output('meeting/scanlist');
	}
	
	//签到
	private function sign()
	{
		$mt_id = rintval($this->request->get('mt_id'));	//会议id
		$d = new voa_d_oa_meeting_mem();
		$mem = $d->fetch_by_mt_id_uid($mt_id, $this->_user['m_uid']);
		if(!$mem['mm_confirm']) {
			//未签到过,执行签到并设置为确认参加操作
			$rs = $d->update(array('mm_confirm' => 1, 'mm_status' => 2), array('mm_id' => $mem['mm_id']));
			if(!$rs) $this->_error_message('签到操作失败');
		}
		
		//获取部门,职业
		$this->_set_dept_job();
		$this->view->set('mt_id', $mt_id);
		$this->view->set('user', $this->_user);
		$this->_output('meeting/sign');
		exit;
	}
	
	//提前结束
	private function finish()
	{
		$mt_id = rintval($this->request->get('mt_id'));
		$meeting = new voa_d_oa_meeting();
		$meet = $meeting->fetch_by_id($mt_id);
		if(!$meet) {
			$this->ajax(0, '无此会议');
		}
		if($meet['m_uid'] != $this->_user['m_uid']) {
			$this->ajax(0, '你不是会议发起人');
		}
		if(time() > $meet['mt_endtime']) {
			$this->ajax(0, '会议已结束');
		}
		if(time() < $meet['mt_begintime']) {
			$this->ajax(0, '会议未开始,可以取消,不可提前结束');
		}
		$rs = $meeting->update(array('mt_endtime' => time()), array('mt_id' => $mt_id));
		if(!$rs) {
			$this->ajax(0, '提前结束失败');
		}
		$this->ajax(1);
		exit;
	}
}

<?php
/**
 * 撤销审批
 * $Author$
 * $Id$
 */

class voa_c_frontend_askfor_verify_cancel extends voa_c_frontend_askfor_verify {

	protected $msg_title;
	protected $template;
	
	public function execute() {
		/** 权限检查 */
		$this->_chk_permit_for_myself(true);

		$serv = &service::factory('voa_s_oa_askfor', array('pluginid' => startup_env::get('pluginid')));
		$serv_p = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
		$sert = &service::factory('voa_s_oa_askfor_template', array('pluginid' => startup_env::get('pluginid')));
		
		try {
			$serv->begin();
			/** 催办信息入库 */
			$afp_id = $serv_p->insert(array(
				'af_id' => $this->_askfor['af_id'],
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'afp_status' => voa_d_oa_askfor_proc::STATUS_CANCEL
			), true);

			/** 更新审批以及审批进度状态 */
			$this->_update_status($this->_askfor['af_id'], array(
				'afp_id' => $afp_id,
				'af_status' => voa_d_oa_askfor::STATUS_CANCEL
			), $afp_id, array(
				'afp_status' => voa_d_oa_askfor_proc::STATUS_CANCEL,
				'afp_note' => $this->request->get('message')
			));

			$serv->commit();
		} catch (Exception $e) {
			$serv->rollback();
			/** 入库操作失败 */
			$this->_error_message('操作失败', get_referer());
		}

		/** 取用户信息 */
		$serv_m = &service::factory('voa_s_oa_member');
		$mem = $serv_m->fetch_by_uid($this->_proc['m_uid']);


		//获取模板数据
		$template = $sert->fetch_by_id($this->_askfor['aft_id']);
		/** 给审批人发送消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $this->_askfor['af_id']);
		//给审批人的审批已被撤销
		$this->msg_title = $this->_askfor['m_username'].$template['name']."审批已被撤销";
		
		$msg_desc = '主题：'.$this->_askfor['af_subject'];
		$msg_desc .= "\n申请人：".$this->_askfor['m_username'];
		$msg_url = $viewurl;
		$touser = $mem['m_openid'];
		voa_h_qymsg::push_news_send_queue($this->session, $this->msg_title, $msg_desc, $msg_url, $touser);
		/** 给抄送人发送消息 */
		if($this->_copy_users){
			
			//审批抄送的标题
			$this->msg_title = "抄送".$this->_askfor['m_username'].$template['name']."审批已被撤销";
			$msg_desc = '主题：'.$this->_askfor['af_subject'];
			$msg_desc .= "\n申请人：".$this->_askfor['m_username'];
			$msg_url = $viewurl;
			$touser = implode('|', array_column($this->_copy_users, 'm_openid'));
			voa_h_qymsg::push_news_send_queue($this->session, $this->msg_title, $msg_desc, $msg_url, $touser);

		}

		$this->_send_json('撤销成功');

	}
	
}

<?php
/**
 * 拒绝审批申请
 * $Author$
 * $Id$
 */

class voa_c_frontend_askfor_verify_refuse extends voa_c_frontend_askfor_verify {

	protected $msg_title;
	protected $template;
	
	public function execute() {
		/** 权限检查 */
		$this->_chk_permit();

		$serv = &service::factory('voa_s_oa_askfor', array('pluginid' => startup_env::get('pluginid')));
		$sert = &service::factory('voa_s_oa_askfor_template', array('pluginid' => startup_env::get('pluginid')));
		try {
			$serv->begin();
			$this->_update_status($this->_askfor['af_id'], array(
				'af_status' => voa_d_oa_askfor::STATUS_REFUSE
			), $this->_proc['afp_id'], array(
				'afp_status' => voa_d_oa_askfor_proc::STATUS_REFUSE,
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
		$mem = $serv_m->fetch_by_uid($this->_askfor['m_uid']);
		//获取模板数据
		$template = $sert->fetch_by_id($this->_askfor['aft_id']);
		/** 给申请人发送消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $this->_askfor['af_id']);
		//您的审批已被驳回
		$this->msg_title = $this->_askfor['af_subject']."审批已被驳回";
		$msg_desc = '主题：'.$this->_askfor['af_subject'];
		$msg_desc .= "\n审批人：".$this->_proc['m_username'];
		$msg_url = $viewurl;
		$touser = $mem['m_openid'];
		voa_h_qymsg::push_news_send_queue($this->session, $this->msg_title, $msg_desc, $msg_url, $touser);
		/** 给抄送人发送消息 */
		if($this->_copy_users){
			//审批抄送的标题
			$this->msg_title = "抄送".$this->_askfor['m_username'].$this->_askfor['af_subject']."审批已被驳回";
			$msg_desc = '主题：'.$this->_askfor['af_subject'];
			$msg_desc .= "\n申请人：".$this->_askfor['m_username'];
			$msg_url = $viewurl;
			$touser = implode('|', array_column($this->_copy_users, 'm_openid'));
			voa_h_qymsg::push_news_send_queue($this->session, $this->msg_title, $msg_desc, $msg_url, $touser);

		}
		echo rjson_encode(
				array(
					'errcode' => 0,
					'errmsg' => 'success',
					'timestamp' => startup_env::get('timestamp'),
					'result' => array(
						'url' =>  "/askfor/view/".$this->_askfor['af_id'],
						'message' => '操作成功'
					)
				)
		);
		exit();
	}
}

<?php
/**
 * 催办审批
 * $Author$
 * $Id$
 */

class voa_c_frontend_askfor_verify_reminder extends voa_c_frontend_askfor_verify {

	protected $msg_title;
	protected $template;
	protected $afat_status_array = array(
			'审批申请中',
			'审批通过',
			'转审批',
			'审批不通过',
			'草稿',
			'已催办',
			'已撤销',
			'已删除'
	);
	
	public function execute() {
		/** 权限检查 */
		$this->_chk_permit_for_myself(false);
        $serv_m = &service::factory('voa_s_oa_member');
		$serv = &service::factory('voa_s_oa_askfor', array('pluginid' => startup_env::get('pluginid')));
		$serv_p = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
		$sert = &service::factory('voa_s_oa_askfor_template', array('pluginid' => startup_env::get('pluginid')));
        // 获取模板数据
        $template = $sert->fetch_by_id($this->_askfor['aft_id']);
        try {
            $serv->begin();
            /** 催办信息入库 */
            $afp_id = $serv_p->insert(array(
                'af_id' => $this->_askfor['af_id'],
                'm_uid' => startup_env::get('wbs_uid'),
                'm_username' => startup_env::get('wbs_username'),
                'afp_status' => voa_d_oa_askfor_proc::STATUS_REMINDER
            ), true);

            /** 更新审批以及审批进度状态 */
            $this->_update_status($this->_askfor['af_id'], array(
                //	'af_status' => voa_d_oa_askfor::STATUS_REMINDER
            ), $afp_id, array(
                'afp_status' => voa_d_oa_askfor_proc::STATUS_REMINDER,
                'afp_note' => $this->request->get('message')
            ));

            $serv->commit();
        } catch (Exception $e) {
            $serv->rollback();
            /** 入库操作失败 */
            $this->_error_message('操作失败', get_referer());
        }

        /* 判断职务还是人物 */
        if (!empty($template['approvers'])) {
            /** 取用户信息 */
            $mem = $serv_m->fetch_by_uid($this->_proc['m_uid']);

            /** 发送消息 */
            $viewurl = '';
            $this->get_view_url($viewurl, $this->_askfor['af_id']);
            //您待处理的审批已被催办
            $this->msg_title = $this->_askfor['m_username'].$template['name'];
            //判断消息状态
            $this->_af_status($this->_askfor['af_status']);
            $msg_desc = '主题：'.$this->_askfor['af_subject'];
            $msg_desc .= "\n催办理由：".$this->request->get('message');
            $msg_desc .= "\n申请人：".$this->_askfor['m_username'];
            $msg_url = $viewurl;
            $touser = $mem['m_openid'];
            // 发送消息
            voa_h_qymsg::push_news_send_queue($this->session, $this->msg_title, $msg_desc, $msg_url, $touser);
        } elseif(!empty($template['positions']))  {
            $serv_d = &service::factory('voa_s_oa_member_department', array('pluginid' => 0));
            $temp_arr = array('mp_id' => $this->_proc['mp_id']);
            $mem_list = $serv_d->fetch_all_by_conditions($temp_arr);
            foreach ($mem_list as $v) {
                /** 发送消息 */
                $viewurl = '';
                $this->get_view_url($viewurl, $this->_askfor['af_id']);
                //您待处理的审批已被催办
                $this->msg_title = $this->_askfor['m_username'].$template['name'];
                //判断消息状态
                $this->_af_status($this->_askfor['af_status']);
                $msg_desc = '主题：'.$this->_askfor['af_subject'];
                $msg_desc .= "\n催办理由：".$this->request->get('message');
                $msg_desc .= "\n申请人：".$this->_askfor['m_username'];
                $msg_url = $viewurl;
                $mem1 = $serv_m->fetch_by_uid($v['m_uid']);
                $touser = $mem1['m_openid'];
                // 发送消息
                voa_h_qymsg::push_news_send_queue($this->session, $this->msg_title, $msg_desc, $msg_url, $touser);
            }
        } else {
            /** 取用户信息 */
            $mem = $serv_m->fetch_by_uid($this->_proc['m_uid']);

            /** 发送消息 */
            $viewurl = '';
            $this->get_view_url($viewurl, $this->_askfor['af_id']);
            //您待处理的审批已被催办
            $this->msg_title = $this->_askfor['m_username'];
            //判断消息状态
            $this->_af_status($this->_askfor['af_status']);
            $msg_desc = '主题：'.$this->_askfor['af_subject'];
            $msg_desc .= "\n催办理由：".$this->request->get('message');
            $msg_desc .= "\n申请人：".$this->_askfor['m_username'];
            $msg_url = $viewurl;
            $touser = $mem['m_openid'];
            // 发送消息
            voa_h_qymsg::push_news_send_queue($this->session, $this->msg_title, $msg_desc, $msg_url, $touser);
        }

		echo rjson_encode(
				array(
					'errcode' => 0,
					'errmsg' => 'success',
					'timestamp' => startup_env::get('timestamp'),
					'result' => array(
						'url' =>  "/askfor/view/".$this->_askfor['af_id'],
						'message' => '催办成功'
					)
				)
		);
		exit();

	}
	
	/** 判断消息状态 */
	protected function _af_status($af_status) {
		switch ($af_status) {
			case 1:
				$this->msg_title .= $this->afat_status_array[0];
				break;
			case 2:
				$this->msg_title .= $this->afat_status_array[1];
				break;
			case 3:
				$this->msg_title .= $this->afat_status_array[2];
				break;
			case 4:
				$this->msg_title .= $this->afat_status_array[3];
				break;
			case 5:
				$this->msg_title .= $this->afat_status_array[4];
				break;
			case 6:
				$this->msg_title .= $this->afat_status_array[5];
				break;
			case 7:
				$this->msg_title .= $this->afat_status_array[6];
				break;
			case 8:
				$this->msg_title .= $this->afat_status_array[7];
				break;
			default:
				break;
		}
			
	}
}

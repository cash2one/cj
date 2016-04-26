<?php
/**
 * voa_c_api_askfor_get_view
 * 查看审批
 * $Author$
 * $Id$
 */
class voa_c_api_askfor_get_view extends voa_c_api_askfor_base {

	public function execute() {

		// 请求参数
		$fields = array(
			'af_id' => array('type' => 'int', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}
		$af_id = $this->_params['af_id'];
		$uda_fmt = &uda::factory('voa_uda_frontend_askfor_format');
		/** 获取当前审批信息 */
		$servao = &service::factory('voa_s_oa_askfor', array('pluginid' => startup_env::get('pluginid')));
		$askfor = $servao->fetch_by_id($af_id);
		$uda_fmt->askfor($askfor);

		if (empty($af_id) || empty($askfor)) {
			//$this->_error_message('askfor_not_exist', get_referer());
			return $this->_set_errcode(voa_errcode_api_askfor::ASKFOR_NOT_EXIST, $af_id);
		}

		/** 获取审批进度信息列表 */
		$servp = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
		$procs = $servp->fetch_by_af_id($af_id);


		/** 如果不是自己发起的申请, 则判断是否有权限查看 */
		if ($askfor['m_uid'] != startup_env::get('wbs_uid')) {
			$view = false;
			foreach ($procs as $v) {
				/** 如果有权限, 即是审批人或抄送人 */
				if ($v['m_uid'] == startup_env::get('wbs_uid')) {
					$view = true;
					break;
				}
			}

			/** 如果不让查看, 则 */
			if (!$view) {
				return $this->_set_errcode(voa_errcode_api_askfor::ASKFOR_FORBIDDEN, $af_id);
			}
		}

		/** 取出审批人的用户信息, 取出抄送信息 */
		$uids = array();
		$carbon_un = array();
		/** 抄送人记录 */
		$carbon_copies = array();
		/** 当前进度记录 */
		$cur_proc = array();
		foreach ($procs as $k => &$v) {
			$uda_fmt->askfor_proc($v);
			$uids[$v['m_uid']] = $v['m_uid'];
			if (voa_d_oa_askfor_proc::STATUS_CARBON_COPY == $v['afp_status']) {
				$carbon_copies[] = $v;
				$v['m_uid'] != $askfor['m_uid'] && $carbon_un[] = $v['m_username'];
				unset($procs[$k]);
				continue;
			}

			/** 状态提示文字 */
			if (voa_d_oa_askfor_proc::STATUS_NORMAL == $v['afp_status']) {
				$v['_status_class'] = 'wait';
				$v['_status_tip'] = '待';
			} else if (voa_d_oa_askfor_proc::STATUS_APPROVE == $v['afp_status'] || voa_d_oa_askfor_proc::STATUS_APPROVE_APPLY == $v['afp_status']) {
				$v['_status_class'] = 'ok';
				$v['_status_tip'] = '√';
			} else if (voa_d_oa_askfor_proc::STATUS_REFUSE == $v['afp_status']) {
				$v['_status_class'] = 'wait';
				$v['_status_tip'] = '拒';
			}

			/** 获取当前进度记录 */
			if ($v['afp_id'] == $askfor['afp_id']) {
				$cur_proc = $v;
			}
		}
		unset($v);

		/** 读取 */
		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $servm->fetch_all_by_ids($uids);
		foreach ($users as $u) {
			voa_h_user::push($u);
		}

		/** 重组返回json数组 */
		$typearr = $this->_p_sets['types'];

		//格式调整
		foreach ($procs as & $r) {
			$r = array(
				'af_id'		=>	$r['af_id'],
				'afp_id'	=>	$r['afp_id'],
				'uid'		=>	$r['m_uid'],
				'username'	=>	$r['m_username'],
				'avatar'	=>	voa_h_user::avatar($r['m_uid']),
				'remark'	=>	$r['afp_note'],
				'status'	=>	$r['afp_status'],
				'created'	=>	$r['afp_updated'],
			);
		}
		foreach ($carbon_copies as & $v) {
			$v = array(
				'uid'		=>	$v['m_uid'],
				'username'	=>	$v['m_username'],
				'avatar'	=>	voa_h_user::avatar($v['m_uid']),
			);
		}

		$temp = array();
		foreach ($procs as $p)
		{
			$temp[] = $p;
		}
		$procs = $temp;


		$this->_result = array(
			'uid' 		=> $askfor['m_uid'],// 创建者uid
			'username' 	=> $askfor['m_username'],// 创建者名字
			'subject' 	=> $askfor['af_subject'] ?  $askfor['af_subject'] : '',// 审批内容
			'message' 	=> $askfor['af_message'] ?  $askfor['af_message'] : '',// 审批内容
			'created' 	=> $askfor['_created'],// 审批内容
			'avatar' 	=> voa_h_user::avatar($askfor['m_uid']),// 审批内容
			'ccusers'	=>	$carbon_copies,		//挑送人列表
			'procs'		=> $procs,		//审批流程(审批人列表)
			'comments'	=> $comments,	//评论列表
		);
		return true;
	}

}

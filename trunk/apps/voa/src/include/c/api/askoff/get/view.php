<?php
/**
 * voa_c_api_askoff_get_view
 * 查看请假
 * $Author$
 * $Id$
 */
class voa_c_api_askoff_get_view extends voa_c_api_askoff_base {

	public function execute() {

		// 请求参数
		$fields = array(
			// 日报ID
			'ao_id' => array('type' => 'int', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}
		$ao_id = $this->_params['ao_id'];
		$uda_fmt = &uda::factory('voa_uda_frontend_askoff_format');
		/** 获取当前请假信息 */
		$servao = &service::factory('voa_s_oa_askoff', array('pluginid' => startup_env::get('pluginid')));
		$askoff = $servao->fetch_by_id($ao_id);
		$uda_fmt->askoff($askoff);
		if (empty($ao_id) || empty($askoff)) {
			//$this->_error_message('askoff_not_exist', get_referer());
			return $this->_set_errcode(voa_errcode_api_askoff::VIEW_NOT_EXISTS, $ao_id);
		}

		/** 获取请假进度信息列表 */
		$servp = &service::factory('voa_s_oa_askoff_proc', array('pluginid' => startup_env::get('pluginid')));
		$procs = $servp->fetch_by_ao_id($ao_id);


		/** 如果不是自己发起的申请, 则判断是否有权限查看 */
		if ($askoff['m_uid'] != startup_env::get('wbs_uid')) {
			$view = false;
			foreach ($procs as $v) {
				/** 如果有权限, 即是请假人或抄送人 */
				if ($v['m_uid'] == startup_env::get('wbs_uid')) {
					$view = true;
					break;
				}
			}

			/** 如果不让查看, 则 */
			if (!$view) {
				//$this->_error_message('askoff_proc_error', get_referer());
				return $this->_set_errcode(voa_errcode_api_askoff::ASKOFF_PROC_ERROR, $ao_id);
			}
		}

		/** 取出请假人的用户信息, 取出抄送信息 */
		$uids = array();
		$carbon_un = array();
		/** 抄送人记录 */
		$carbon_copies = array();
		/** 当前进度记录 */
		$cur_proc = array();
		foreach ($procs as $k => &$v) {
			$uda_fmt->askoff_proc($v);
			$uids[$v['m_uid']] = $v['m_uid'];
			if (voa_d_oa_askoff_proc::STATUS_CARBON_COPY == $v['aopc_status']) {
				$carbon_copies[] = $v;
				$v['m_uid'] != $askoff['m_uid'] && $carbon_un[] = $v['m_username'];
				unset($procs[$k]);
				continue;
			}

			/** 状态提示文字 */
			if (voa_d_oa_askoff_proc::STATUS_NORMAL == $v['aopc_status']) {
				$v['_status_class'] = 'wait';
				$v['_status_tip'] = '待';
			} else if (voa_d_oa_askoff_proc::STATUS_APPROVE == $v['aopc_status'] || voa_d_oa_askoff_proc::STATUS_APPROVE_APPLY == $v['aopc_status']) {
				$v['_status_class'] = 'ok';
				$v['_status_tip'] = '√';
			} else if (voa_d_oa_askoff_proc::STATUS_REFUSE == $v['aopc_status']) {
				$v['_status_class'] = 'wait';
				$v['_status_tip'] = '拒';
			}

			/** 获取当前进度记录 */
			if ($v['aopc_id'] == $askoff['aopc_id']) {
				$cur_proc = $v;
			}
		}


		/** 根据评论 id 读取回复信息列表 */
		$servpt = &service::factory('voa_s_oa_askoff_post', array('pluginid' => startup_env::get('pluginid')));
		$posts = $servpt->fetch_by_ao_id($ao_id);

		/** 整理回复信息 */
		foreach ($posts as $k => &$v) {
			$uda_fmt->askoff_post($v);
			$uids[$v['m_uid']] = $v['m_uid'];
			/** 如果是请假主题信息 */
			if (voa_d_oa_askoff_post::FIRST_YES == $v['aopt_first']) {
				$askoff = array_merge($askoff, $v);
				unset($posts[$k]);
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
				'ao_id'		=>	$r['ao_id'],
				'aopc_id'	=>	$r['aopc_id'],
				'uid'		=>	$r['m_uid'],
				'username'	=>	$r['m_username'],
				'avatar'	=>	voa_h_user::avatar($r['m_uid']),
				'remark'	=>	$r['_remark'],
				'created'	=>	$r['aopc_updated'],
				'status'	=>	$r['aopc_status'],
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
			'uid' => $askoff['m_uid'],// 创建者uid
			'username' => $askoff['m_username'],// 创建者名字
			'message' => $askoff['_message'],// 请假内容
			'avatar' => voa_h_user::avatar($askoff['m_uid']),// 请假内容
			'type' => $askoff['ao_type'],// 请假类型
			'begintime' => $askoff['ao_begintime'],// 开始日期
			'endtime' => $askoff['ao_endtime'],// 结束日期
			'ccusers' => $carbon_copies,//挑送人列表
			'procs' => $procs,//审批流程(审批人列表)
			'days' => $askoff['_days'],// 请假天数
			'timespace' => $askoff['_timespace']// 请假时长
		);


		return true;
	}

}

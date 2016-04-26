<?php
/**
 * voa_c_api_project_get_view
 * 查看任务
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_project_get_view extends voa_c_api_project_base {

	public function execute() {

		// 请求参数
		$fields = array(
			// 任务ID
			'id' => array('type' => 'int', 'required' => true),
			// 附件图片显示宽度
			'thumbsize' => array('type' => 'int', 'required' => false),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}

		if ($this->_params['thumbsize'] <= 0) {
			$this->_params['thumbsize'] = 0;
		}
		$this->_params['thumbsize'] = (int)$this->_params['thumbsize'];

		// 读取任务信息
		$serv_p = &service::factory('voa_s_oa_project', array('pluginid' => $this->_pluginid));
		$project = $serv_p->fetch_by_id($this->_params['id']);
		if (empty($project)) {
			return $this->_set_errcode(voa_errcode_api_project::VIEW_NOT_EXISTS, $this->_params['id']);
		}

		// 读取任务所有相关文件
		$attachs = array();
		$serv_pat = &service::factory('voa_s_oa_project_attachment', array('pluginid' => $this->_pluginid));
		$attach_list = $serv_pat->fetch_all_by_p_id($this->_params['id']);
		if ($attach_list) {
			// 任务文件所关联的公共附件ID
			$at_ids = array();
			foreach ($attach_list as $v) {
				$at_ids[] = $v['at_id'];
			}

			$serv_at = &service::factory('voa_s_oa_common_attachment', array('pluginid' => 0));
			$common_attach_list = $serv_at->fetch_by_ids($at_ids);

			foreach ($attach_list as $v) {
				if (!isset($common_attach_list[$v['at_id']])) {
					continue;
				}
				$at = $common_attach_list[$v['at_id']];
				$attachs[$v['pp_id']][] = array(
					'id' => $v['pat_id'], // 任务文件ID
					'filename' => $at['at_filename'],// 附件名称
					'filesize' => $at['at_filesize'],// 附件容量
					'mediatype' => $at['at_mediatype'],// 媒体文件类型
					'description' => $at['at_description'],// 附件描述
					'isimage' => $at['at_isimage'] ? 1 : 0,// 是否是图片
					'url' => voa_h_attach::attachment_url($v['at_id'], 0),// 附件文件url
					'thumb' => $this->_params['thumbsize'] > 0 && $at['at_isimage'] ? voa_h_attach::attachment_url($v['at_id'], $this->_params['thumbsize']) : '',// 缩略图URL
				);
			}

		}

		// 读取任务人员/抄送人信息
		$serv_pm = &service::factory('voa_s_oa_project_mem', array('pluginid' => $this->_pluginid));
		$allmems = $serv_pm->fetch_by_p_id($this->_params['id']);

		// 总进度
		$progress = 0;
		// 所有任务相关人员uid
		$alluids = array();
		// 任务人员uid
		$puids = array();
		// 任务人员信息
		$project_mems = array();
		// 当前任务总人数
		$pm_num = 0;
		// 我的任务信息
		$my_pm = array();
		foreach ($allmems as $m) {
			$alluids[$m['m_uid']] = $m['m_uid'];
			if ($m['m_uid'] == $this->_member['m_uid']) {
				$my_pm = $m;
			}

			if (voa_d_oa_project_mem::STATUS_CC == $m['pm_status'] || voa_d_oa_project_mem::STATUS_OUTOF) {
				// 抄送人完全不可见
				//continue;
			}

			if (voa_d_oa_project_mem::STATUS_OUTOF == $m['pm_status'] || voa_d_oa_project_mem::STATUS_CC == $m['pm_status'] || voa_d_oa_project_mem::STATUS_OUTOF) {
				// 退出的人、抄送人、发起者但不参与的人           不参与任务总进程

			} else {
				$progress += $m['pm_progress'];
				$pm_num ++;
			}

			$project_mems[] = array(
				'pmid' => $m['pm_id'],
				'uid' => $m['m_uid'],
				'username' => $m['m_username'],
				'progress' => $m['pm_progress'],
				'status' => isset($this->_status_maps[$m['pm_status']]) ? $this->_status_maps[$m['pm_status']] : 'normal',
				'updated' => $m['pm_updated']
			);
			$puids[$m['m_uid']] = $m['m_uid'];
		}

		// 判断用户是否有权限
		if (empty($alluids[$this->_member['m_uid']]) || voa_d_oa_project_mem::STATUS_QUIT == $my_pm['pm_status']) {
			return $this->_set_errcode(voa_errcode_api_project::VIEW_NO);
		}

		if ($pm_num > 0) {
			$progress /= $pm_num;
		}
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $serv_m->fetch_all_by_ids($puids);
		foreach ($users as $u) {
			voa_h_user::push($u);
		}

		// 为参与项目的人员列表注入头像信息
		foreach ($project_mems as &$m) {
			$m['avatar'] = voa_h_user::avatar($m['uid'], isset($users[$m['uid']]) ? $users[$m['uid']] : array());
		}

		// 读取任务进度信息
		$serv_pp = &service::factory('voa_s_oa_project_proc', array('pluginid' => $this->_pluginid));
		$project_proc = $serv_pp->fetch_by_p_id($this->_params['id']);
		$uid2proc = array();
		foreach ($project_proc as $p) {

			$p['pp_message'] = rhtmlspecialchars($p['pp_message']);
			$p['pp_message'] = bbcode::instance()->bbcode2html($p['pp_message']);
			if (empty($uid2proc[$p['m_uid']])) {
				$uid2proc[$p['m_uid']] = array();
			}

			$uid2proc[$p['m_uid']][] = array(
				'ppid' => $p['pp_id'],// 进度ID
				'uid' => $p['m_uid'],// 进度添加人uid
				'username' => $p['m_username'],// 进度添加人名字
				'avatar' => voa_h_user::avatar($p['m_uid'], isset($users[$p['m_uid']]) ? $users[$p['m_uid']] : array()),
				'progress' => $p['pp_progress'],// 进度值
				'message' => $p['pp_message'],// 具体进度说明文字
				'createdtime' => $p['pp_created'],// 添加时间
				'file' => isset($attachs[$p['pp_id']]) ? $attachs[$p['pp_id']] : array(),// 相关附件列表
			);
		}

		// 调整$uid2proc输出
		/*
		// 为了便于程序处理调用，不再调整此处的输出
		$tmp = array();
		foreach ($uid2proc as $arr) {
			$tmp[] = $arr;
		}
		$uid2proc = $tmp;
		unset($tmp);
		*/

		// 判断任务是否关闭
		$closed = 1;
		if (in_array($project['p_status'], array(voa_d_oa_project::STATUS_NORMAL, voa_d_oa_project::STATUS_UPDATE))) {
			$closed = 0;
		}

		$this->_result = array(
			'id' => $this->_params['id'],
			'closed' => $closed,
			'usercount' => count($project_mems),
			'project' => array(
				'id' => $project['p_id'],// 任务ID
				'uid' => $project['m_uid'],// 创建者uid
				'username' => $project['m_username'],// 创建者名字
				'avatar' => voa_h_user::avatar($project['m_uid'], isset($users[$project['m_uid']]) ? $users[$project['m_uid']] : array()),
				'subject' => $project['p_subject'],// 任务名称
				'message' => $project['p_message'],// 任务说明
				'begintime' => $project['p_begintime'],// 任务开始时间
				'endtime' => $project['p_endtime'],// 任务结束时间
				'progress' => $project['p_progress'],//$progress,// 任务完成进度
				'myprogress' => isset($my_pm['pm_progress']) ? $my_pm['pm_progress'] : 0,// 当前登录者的进度
				'createdtime' => $project['p_created'],// 任务创建时间
				'updatedtime' => $project['p_updated'],// 任务更新时间
				'file' => isset($attachs[0]) ? $attachs[0] : array(),// 相关文件列表
			),
			'puids' => $puids,
			'uid2proc' => $uid2proc,
			'userlist' => $project_mems,
		);

		return true;
	}

}

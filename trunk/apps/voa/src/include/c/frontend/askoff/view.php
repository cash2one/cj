<?php
/**
 * 查看请假申请
 * $Author$
 * $Id$
 */

class voa_c_frontend_askoff_view extends voa_c_frontend_askoff_base {

	public function execute() {
		/** 请假ID */
		$ao_id = rintval($this->request->get('ao_id'));

		$uda_fmt = &uda::factory('voa_uda_frontend_askoff_format');
		/** 获取当前请假信息 */
		$servao = &service::factory('voa_s_oa_askoff', array('pluginid' => startup_env::get('pluginid')));
		$askoff = $servao->fetch_by_id($ao_id);
		$uda_fmt->askoff($askoff);
		if (empty($ao_id) || empty($askoff)) {
			$this->_error_message('askoff_not_exist', get_referer());
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
				$this->_error_message('askoff_proc_error', get_referer());
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

		unset($v);

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

		// 读取请假所有相关文件
		$attachs = array();
		$serv_aoat = &service::factory('voa_s_oa_askoff_attachment', array('pluginid' => startup_env::get('pluginid')));
		$attach_list = $serv_aoat->fetch_all_by_ao_id($ao_id);
		if ($attach_list) {
			// 请假文件所关联的公共附件ID
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
				$attachs[$v['aopt_id']][] = array(
					'at_id' => $v['at_id'],// 公共文件附件ID
					'id' => $v['ao_id'], // 任务文件ID
					'filename' => $at['at_filename'],// 附件名称
					'filesize' => $at['at_filesize'],// 附件容量
					'mediatype' => $at['at_mediatype'],// 媒体文件类型
					'description' => $at['at_description'],// 附件描述
					'isimage' => $at['at_isimage'] ? 1 : 0,// 是否是图片
					'url' => voa_h_attach::attachment_url($v['at_id'], 0),// 附件文件url
					'thumb' => $at['at_isimage'] ? voa_h_attach::attachment_url($v['at_id'], 45) : '',// 缩略图URL
				);
			}
		}

		$this->view->set('ao_id', $ao_id);
		$this->view->set('askoff', $askoff);
		$this->view->set('carbon_copies', $carbon_copies);
		$this->view->set('cc_users', $carbon_un);
		$this->view->set('procs', $procs);
		$this->view->set('posts', $posts);
		$this->view->set('cur_proc', $cur_proc);
		$this->view->set('weeknames', config::get('voa.misc.weeknames'));
		$this->view->set('types', $this->_p_sets['types']);
		// 请假图片
		$this->view->set('attachs', array_key_exists(0, $attachs) ? $attachs[0] : array());

		$this->_output('askoff/view');
	}
}


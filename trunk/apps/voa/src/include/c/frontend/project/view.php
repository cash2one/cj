<?php
/**
 * 任务列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_project_view extends voa_c_frontend_project_base {

	public function execute() {
		$p_id = intval($this->request->get('p_id'));
		/** 读取任务信息 */
		$serv_p = &service::factory('voa_s_oa_project', array('pluginid' => startup_env::get('pluginid')));
		$project = $serv_p->fetch_by_id($p_id);
		if (empty($project)) {
			$this->_error_message('该任务不存在或已删除');
		}

		$project['p_subject'] = rhtmlspecialchars($project['p_subject']);
		$project['p_message'] = rhtmlspecialchars($project['p_message']);

		/** 读取任务人员/抄送人信息 */
		$serv_pm = &service::factory('voa_s_oa_project_mem', array('pluginid' => startup_env::get('pluginid')));
		$allmems = $serv_pm->fetch_by_p_id($p_id);
		
		/** 总进度 */
		$progress = 0;
		/** 所有任务相关人员uid */
		$alluids = array();
		/** 任务人员uid */
		$puids = array();
		/** 任务人员信息 */
		$project_mems = array();
		/** 当前任务总人数 */
		$pm_num = 0;
		/** 我的任务信息 */
		$my_pm = array();
		foreach ($allmems as $m) {
			$alluids[$m['m_uid']] = $m['m_uid'];
			if ($m['m_uid'] == startup_env::get('wbs_uid')) {
				$my_pm = $m;
			}

			if (voa_d_oa_project_mem::STATUS_CC == $m['pm_status'] || voa_d_oa_project_mem::STATUS_OUTOF == $m['pm_status']) {
				continue;
			}

			$m['_updated'] = rgmdate($m['pm_updated'], 'u', 9999, 'Y-m-d');
			$project_mems[] = $m;
			$puids[$m['m_uid']] = $m['m_uid'];
			$progress += $m['pm_progress'];
			$pm_num ++;
		}
		$progress = $pm_num;
		/** 判断用户是否有权限 */
		if (empty($alluids[startup_env::get('wbs_uid')]) || voa_d_oa_project_mem::STATUS_QUIT == $my_pm['pm_status']) {
			$this->_error_message('您没有权限查看当前进度');
		}

		
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $serv_m->fetch_all_by_ids($puids);
		foreach ($users as $u) {
			voa_h_user::push($u);
		}

		/** 读取任务进度信息 */
		$serv_pp = &service::factory('voa_s_oa_project_proc', array('pluginid' => startup_env::get('pluginid')));
		$project_proc = $serv_pp->fetch_by_p_id($p_id);
		$uid2proc = array();
		foreach ($project_proc as $p) {
			$p['_created'] = rgmdate($p['pp_created'], 'u', 9999, 'Y-m-d');
			$p['pp_message'] = rhtmlspecialchars($p['pp_message']);
			
			$p['pp_message'] = bbcode::instance()->bbcode2html($p['pp_message']);
			
			if (empty($uid2proc[$p['m_uid']])) {
				$uid2proc[$p['m_uid']] = array();
			}

			$uid2proc[$p['m_uid']][] = $p;
		}
		
		/** 判断任务是否关闭 */
		$is_running = false;
		if (in_array($project['p_status'], array(voa_d_oa_project::STATUS_NORMAL, voa_d_oa_project::STATUS_UPDATE))) {
			$is_running = true;
		}

		// 读取任务所有相关文件
		$attachs = array();
		$serv_pat = &service::factory('voa_s_oa_project_attachment', array('pluginid' => startup_env::get('pluginid')));
		$attach_list = $serv_pat->fetch_all_by_p_id($p_id);
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
					'at_id' => $v['at_id'],// 公共文件附件ID
					'id' => $v['pat_id'], // 任务文件ID
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

		$this->view->set('project', $project);
		$this->view->set('project_mems', $project_mems);
		$this->view->set('ct_project_mems', count($project_mems));
		$this->view->set('puids', $puids);
		$this->view->set('progress', $progress);
		$this->view->set('uid2proc', $uid2proc);
		$this->view->set('p_id', $p_id);
		$this->view->set('is_running', $is_running);
		// 任务图片
		$this->view->set('attachs', array_key_exists(0, $attachs) ? $attachs[0] : array());

		$this->_output('project/view');
	}
}

<?php
/**
 * 更新任务进度
 * $Author$
 * $Id$
 */

class voa_c_frontend_project_progress extends voa_c_frontend_project_base {
	/** 任务id */
	protected $_p_id = 0;
	/** 任务所有人员 */
	protected $_p_uids = array();

	public function execute() {
		/** 可选进度 */
		$procvs = explode(',', $this->_p_sets['procvs']);

		$this->_p_id = intval($this->request->get('p_id'));
		/** 读取任务信息 */
		$serv_p = &service::factory('voa_s_oa_project', array('pluginid' => startup_env::get('pluginid')));
		$project = $serv_p->fetch_by_id($this->_p_id);
		if (empty($project)) {
			$this->_error_message('该任务不存在或已删除');
		}

		/** 读取当前进度信息 */
		$serv_pm = &service::factory('voa_s_oa_project_mem', array('pluginid' => startup_env::get('pluginid')));
		$proc = $serv_pm->fetch_by_p_id_uid($this->_p_id, startup_env::get('wbs_uid'));
		/** 判断权限 */
		if (voa_d_oa_project_mem::STATUS_NORMAL != $proc['pm_status'] && voa_d_oa_project_mem::STATUS_UPDATE != $proc['pm_status']) {
			$this->_error_message('您没有权限查看当前进度');
		}

		/** 检查进度值 */
		if (!in_array($proc['pm_progress'], $procvs) && 100 > $proc['pm_progress']) {
			$proc['pm_progress'] = 0;
		}

		if ($this->_is_post()) {
			$message = trim($this->request->get('message'));
			$progress = intval($this->request->get('progress'));
			if (empty($message)) {
				$this->_error_message('进展描述信息不能为空');
			}

			if ($progress < $proc['pm_progress']) {
				$progress = $proc['pm_progress'];
			}

			if ($progress < 0 || !in_array($progress, $procvs)) {
				$progress = 0;
			}

			if ($progress > 100) {
				$progress = 100;
			}

			list($p_num, $u_num) = $this->_get_progress_ct();
			/** 进度信息入库 */
			$serv_pp = &service::factory('voa_s_oa_project_proc', array('pluginid' => startup_env::get('pluginid')));
			try {
				$serv_p->begin();
				$serv_pp->insert(array(
					'p_id' => $this->_p_id,
					'm_uid' => startup_env::get('wbs_uid'),
					'm_username' => startup_env::get('wbs_username'),
					'pp_progress' => $progress,
					'pp_message' => $message
				));

				/** 更新用户进度 */
				$serv_pm->update(array(
					'pm_progress' => $progress
				), array('pm_id' => $proc['pm_id']));

				/** 更新总进度 */
				$p_progress = ($p_num + $progress) / ($u_num + 1);
				// 需要更新的数据
				$p_update = array(
					'p_progress' => $p_progress,
					'p_updated' => startup_env::get('timestamp')
				);
				// 标记已完成 by Deepseath@20141222
				if ($p_progress == 100) {
					$p_update['p_status'] = voa_d_oa_project::STATUS_COMPLETE;
				}
				$serv_p->update($p_update, array('p_id' => $this->_p_id));

				$serv_p->commit();
			} catch (Exception $e) {
				$serv_p->rollback();
				/** 如果 $id 值为空, 则说明入库操作失败 */
				$this->_error_message('任务进度更新失败');
			}

			/** 把微信消息推入队列 */
			$this->_to_queue($project, $p_progress);

			$this->_success_message('任务进度更新成功', '/project/view/'.$this->_p_id);
		}

		$this->view->set('ac', $this->action_name);
		$this->view->set('proc', $proc);
		$this->view->set('procvs', $procvs);
		$this->view->set('refer', get_referer());
		$this->view->set('p_id', $this->_p_id);

		$this->_output('project/progress');
	}

	/**
	 * 把微信消息推入队列
	 * @param array $project 任务详情信息
	 * @param int $progress 总进度
	 */
	protected function _to_queue($project, $progress) {
		/** 过滤任务详情信息 */
		$fmt = uda::factory('voa_uda_frontend_project_format');
		$fmt->project($project);

		/** 查询用户openid */
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => startup_env::get('wbs_uid')));
		$users = $serv_m->fetch_all_by_ids($this->_p_uids);

		$openids = array();
		foreach ($users as $u) {
			$openids[] = $u['m_openid'];
		}

		/** 发送微信消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $project['p_id']);
		if (100 <= $progress) {
			$content = "任务完成\n"
					 . "任务名：".$project['_subject']."\n"
					 . "完成时间：".$project['_updated']."\n"
					 . " <a href='".$viewurl."'>点击查看详情</a>";
		} else {
			$content = startup_env::get('wbs_username')." 进度更新\n"
					 . "任务名：".$project['_subject']."\n"
					 . "更新时间：".$project['_updated']."\n"
					 . " <a href='".$viewurl."'>点击查看详情</a>";
		}

		$data = array(
			'mq_touser' => implode('|', $openids),
			'mq_toparty' => '',
			'mq_msgtype' => voa_h_qymsg::MSGTYPE_TEXT,
			'mq_agentid' => (int)$this->_plugin['cp_agentid'],
			'mq_message' => $content
		);
		voa_h_qymsg::push_send_queue($data);

		/** 写入 cookie, 刷新页面时发送 */
		$this->set_queue_session(array($data['mq_id']));
	}

	/** 读取任务人员 */
	protected function _get_progress_ct() {
		$pnum = 0; /** 进度总值 */
		$unum = 0; /** 人数 */
		/** 读取任务人员/抄送人信息 */
		$serv = &service::factory('voa_s_oa_project_mem', array('pluginid' => startup_env::get('pluginid')));
		$allmems = $serv->fetch_by_p_id($this->_p_id);
		foreach ($allmems as $m) {
			/** 如果已退出 */
			if (voa_d_oa_project_mem::STATUS_QUIT != $m['pm_status']) {
				$this->_p_uids[$m['m_uid']] = $m['m_uid'];
			}

			// 发起者，但不参与，则不计入总的参与任务人数内
			if (voa_d_oa_project_mem::STATUS_OUTOF == $m['pm_status']) {
				continue;
			}

			if (voa_d_oa_project_mem::STATUS_CC == $m['pm_status']
					|| voa_d_oa_project_mem::STATUS_QUIT == $m['pm_status'] || $m['m_uid'] == startup_env::get('wbs_uid')) {
				continue;
			}

			$unum ++;
			$pnum += $m['pm_progress'];
		}

		return array($pnum, $unum);
	}
}

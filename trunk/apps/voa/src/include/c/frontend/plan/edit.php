<?php
/**
 * 编辑日程
 *
 * $Author$
 * $Id$
 */
class voa_c_frontend_plan_edit extends voa_c_frontend_plan_base {

	public function execute() {
		/** 日程ID */
		$pl_id = rintval($this->request->get('pl_id'));

		/** 读取日程信息 */
		$plan = $this->main->fetch_by_id($pl_id);

		if (empty($pl_id) || empty($plan)) {
			$this->_error_message('plan_is_not_exists');
		}

		/** 判断当前用户是否有权限查看 */
		if (startup_env::get('wbs_uid') != $plan['m_uid']) {
			$this->_error_message('no_privilege');
		}

		if (!$this->format->main($plan)) {
			$this->_error_message($this->format->error);
			return false;
		}

		if ($this->_is_post()) {
			$this->_edit();
		}

		/** 读取当前日程的抄送人（分享者） */
		$ccusers = array();

		$ccusers = $this->member->fetch_by_pl_id($pl_id);

		if (empty($ccusers)) {
			// $this->_error_message('member_not_exists');
		} else {
			$temp = array();

			// 准备临时数组，验证会员未被删除
			foreach ($ccusers as $pk => &$users) {
				$temp[] = (int)$users['m_uid'];
			}

			// 确保当前日程中所有的成员都是合法的会员
			$ccusers = $this->user->fetch_all_by_ids($temp);
		}

		$range_begin = $range_finish = rgmdate($plan['pl_begin_at'] - (30 * 86400), 'Y-m-d');

		$this->view->set('ac', $this->action_name);
		$this->view->set('plan', $plan);
		$this->view->set('types', $this->settings['types']);
		$this->view->set('ccusers', $ccusers);
		$this->view->set('range_begin', $range_begin);
		$this->view->set('range_finish', $range_finish);
		$this->view->set('selected_begin', $plan['_begin_at']);
		$this->view->set('selected_finish', $plan['_finish_at']);
		$this->view->set('form_action', "/plan/edit/{$pl_id}?handlekey=post");
		$this->view->set('pl_id', $pl_id);
		$this->view->set('navtitle', '查看日程');

		$this->_output('plan/post');
	}

	protected function _edit() {
		$ccuids = array();

		$pl_id = $this->request->get('pl_id');

		if (!$this->format->v_pl_id($pl_id)) {
			$this->_error_message('Missing argument\:pl_id');
		}

		$_temp = array(
			'pl_alarm_at'  => $this->format->v_alarm_at($this->request->get('alarm_at')),   #提醒
			'pl_begin_at'  => $this->format->v_begin_at($this->request->get('begin_at')),   #开始
			'pl_finish_at' => $this->format->v_finish_at($this->request->get('finish_at')), #结束
			'pl_subject'   => $this->format->v_subject($this->request->get('subject')),     #内容
			'pl_address'   => $this->format->v_address($this->request->get('address')),     #地址
			'pl_type'      => $this->format->v_type($this->request->get('type')),           #类型
			'pl_status'    => voa_d_oa_plan::STATUS_NORMAL
		);

		// 先更新主表数据
		$this->main->update($_temp, array('pl_id' => $pl_id), true);

		try {
			// 然后验证分享人列表，准备更新成员表数据
			$uidstr = (string) $this->request->get('carboncopyuids');
			if (!$this->format->v_shares($uidstr, $ccuids)) {
				return false;
			}
			// 根据传来的UID拿到成员详情
			$cculist = $this->user->fetch_all_by_ids($ccuids);

			// 验证通过，开始事物
			$this->main->begin();

			// 先删除所有成员
			$this->member->delete_by_pl_ids(array($pl_id));

			foreach ($cculist as $key => $value) {
				// 如果是自己
				if ($value['m_uid'] == startup_env::get('wbs_uid')) {
					continue;
				}
				// 逐个插入新成员
				$this->member->insert(array(
					'pl_id' => $pl_id,
					'm_uid' => $value['m_uid'],
					'm_username' => $value['m_username'],
					'plm_status' => voa_d_oa_plan_mem::STATUS_CARBON_COPY
				));
			}

			$this->main->commit();
		} catch (Exception $e) {
			$this->main->rollback();
			$this->_error_message('plan_update_failed', "/plan/edit/{$pl_id}");
			return false;
		}

		$this->_success_message('编辑日程成功', "/plan/edit/{$pl_id}");
		return true;
	}
}

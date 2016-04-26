<?php
/**
 * 查看日程
 *
 * @author s1m0n <18601673727@163.com>
 * $Author$
 * $Id$
 */

class voa_c_frontend_plan_view extends voa_c_frontend_plan_base {
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新 */
	const STATUS_UPDATE = 2;
	/** 抄送人 */
	const STATUS_CARBON_COPY = 3;
	/** 已删除 */
	const STATUS_REMOVE = 4;

	public function execute() {
		/** 日程ID */
		$pl_id = rintval($this->request->get('pl_id'));

		/** 读取日程信息 */
		$plan = $this->main->fetch_by_id($pl_id);
		if (empty($pl_id) || empty($plan)) {
			$this->_error_message('plan_is_not_exists');
		}

		if (!$this->format->my($plan)) {
			$this->_error_message($this->format->error);
			return false;
		}

		/** 判断当前用户是否有权限查看 */
		if (startup_env::get('wbs_uid') !== $plan['m_uid']) {
			$this->_error_message('no_privilege');
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

		$this->view->set('types', $this->settings['types']);
		$this->view->set('action', $this->action_name);
		$this->view->set('plan', $plan);
		$this->view->set('ccusers', $ccusers);
		$this->view->set('tousers', $tousers);
		$this->view->set('weeknames', config::get('voa.misc.weeknames'));
		$this->view->set('pl_id', $pl_id);
		$this->view->set('navtitle', '查看日程');

		$this->_output('plan/my');
	}
}

<?php
/**
 * 日程分享详情
 * $Author$
 * $Id$
 */
class voa_c_frontend_plan_share_detail extends voa_c_frontend_plan_base {

	public function execute() {
		$ac = $this->request->get('ac');

		$func = '_' . $ac;
		if (method_exists($this, $func)) {
			call_user_func(array(
				$this,
				$func
			));
			return true;
		}

		if (!$this->request->get('pl_id')) {
			$this->_error_message('missing_plan_id');
		}

		$plan = $this->member->fetch_shares_detail($this->request->get('pl_id'), startup_env::get('wbs_uid'));

		if (empty($plan) || !$this->format->my($plan)) {
			$this->_error_message('no_privilege');
		}

		$this->view->set('plan', $plan);
		$this->view->set('types', $this->settings['types']);
		$this->view->set('navtitle', '日程分享详情');

		$this->_output('plan/share_detail');
	}

	protected function _delete() {
		$rows = 0;

		$id = $this->request->get('id');

		$plan = $this->member->fetch_by_id($id);

		$plan_members = $this->member->fetch_by_pl_id($plan['pl_id']);

		foreach ($plan_members as $key => $value) {
			if ($value['m_uid'] === startup_env::get('wbs_uid')) {
				$rows += $this->member->delete_by_ids(array('plm_id' => $id));
			}
		}

		$ret = ($rows > 0) ? array('response' => 'success') : array('response' => 'fail');

		$this->_json_message($ret);
	}
}

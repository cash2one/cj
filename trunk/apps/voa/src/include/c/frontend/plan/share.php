<?php
/**
 * 日程分享列表
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_plan_share extends voa_c_frontend_plan_base {

	public function execute() {
		$plans = $this->member->fetch_shares_to_me(startup_env::get('wbs_uid'));

		foreach ($plans as $key => &$value) {
			$this->format->my($value);
		}

        unset($value);

		$this->view->set('list', $plans);
        $this->view->set('navtitle', '分享给我的日程');
		$this->_output('plan/share');
	}
}

<?php

/**
 * voa_c_frontend_sign_outsearch
 * $Id$
 */
class voa_c_frontend_sign_outsearch extends voa_c_frontend_sign_base {

	public function execute() {

		// 获取参数
		$cm_uid = startup_env::get('wbs_uid');
		//读取缓存中设置表
		$setting = voa_h_cache::get_instance()->get('plugin.sign.setting', 'oa');
		$permission = $setting['permission']['ss_value'];
		//构造选择的日期
		$current = startup_env::get('timestamp');
		$c_day = rgmdate($current, 'Y-m-d');
		$rday = rstrtotime($c_day);

		$dlist = array($c_day => $c_day);
		//前6个月
		for ($i = 1; $i <= 60; $i ++) {
			$dlist [rgmdate(strtotime("-" . $i . " day", $rday), 'Y-m-d')] = rgmdate(strtotime("-" . $i . " day", $rday), 'Y-m-d');
		}
		$this->view->set('current', $c_day);
		$this->view->set('day', $dlist);
		$this->view->set('permission', $permission);
		$this->view->set('udate', rgmdate(startup_env::get('timestamp'), 'Y-m-d'));
		$this->view->set('navtitle', '外勤记录');
		$this->view->set('cm_uid', $cm_uid);
		// 引入应用模板
		$this->_output('mobile/sign/outsearch');

		return true;
	}
}

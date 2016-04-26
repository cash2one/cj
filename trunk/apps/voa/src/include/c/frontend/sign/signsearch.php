<?php

/**
 * voa_c_frontend_sign_signsearch
 * $Id$
 */
class voa_c_frontend_sign_signsearch extends voa_c_frontend_sign_base {

	public function execute() {

		//header('Location: /h5/index.html?_ts=' . startup_env::get('timestamp') . '#/app/page/checking-in/query');
		//$this->response->stop(); exit;
		$url = '/h5/index.html?_ts=' . startup_env::get('timestamp') . '#/app/page/checking-in/query';
		$this->view->set('redirect_url', $url);
		$this->_output('mobile/redirect');
		exit;
		// 获取参数
		$cm_uid = startup_env::get('wbs_uid');

		$setting = voa_h_cache::get_instance()->get('plugin.sign.setting', 'oa');
		$permission = $setting['permission']['ss_value'];
		// 构造选择的日期
		$current = startup_env::get('timestamp');
		$c_month = rgmdate($current, 'Y-m');
		$rmonth = rstrtotime($c_month);
		$m_start = '';
		$mlist = array($c_month => $c_month);

		// 前6个月
		for ($i = 1; $i <= 6; $i ++) {
			$mlist [rgmdate(strtotime("-" . $i . " month", $rmonth), 'Y-m')] = rgmdate(strtotime("-" . $i . " month", $rmonth), 'Y-m');
		}
		$this->view->set('current', $c_month);
		$this->view->set('month', $mlist);
		$this->view->set('permission', $permission);
		$this->view->set('udate', rgmdate(startup_env::get('timestamp'), 'Y-m-d'));
		$this->view->set('navtitle', '公司考勤记录');
		$this->view->set('cm_uid', $cm_uid);

		// 引入应用模板
		$this->_output('mobile/sign/signsearch');

		return true;
	}
}

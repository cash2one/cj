<?php
/**
 * 活动->返回签到的二维码
 * $Author$
 * $Id$
 */
class voa_c_api_campaign_get_qrcode extends voa_c_api_campaign_base {

	protected function _before_action($action) {

		$this->_require_login = false;
		return parent::_before_action($action);
	}

	public function execute() {

		$regid = intval($_GET['regid']);
		if (! $regid) {
			$this->_set_errcode('报名id错误');
			return false;
		}

		// 生成二维码内嵌的url
		$scheme = config::get('voa.oa_http_scheme');
		$url = $scheme . "{$_SERVER['HTTP_HOST']}/frontend/campaign/scan?regid=$regid";

		// 生成二维码
		$uda = new voa_uda_frontend_campaign_campaign();
		$uda->qrcode($regid);
		exit;
	}
}

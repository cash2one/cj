<?php
/**
 * 活动详情
 * $Author$
 * $Id$
 * 正常情况下,详情页url带三个参数,例如
 * ?id=68&saleid=3&sharetime=1427265110
 * saleid表示这是哪个销售的,sharetime是分享时间戳,为了便于记录分享业绩的.
 * 但当后台发完活动,微信推送信息给销售时,只会带上id参数,这时候要进行页面跳转
 */
class voa_c_frontend_campaign_view extends voa_c_frontend_campaign_base {

	protected function _before_action($action) {

		$this->_require_login = false;
		return parent::_before_action($action);
	}

	public function execute() {

		$id = intval($_GET['id']);
		if ($this->_user['m_uid']) {
			if (! isset($_GET['saleid'])) {
				$url = "?id={$id}&saleid=" . $this->_user['m_uid'] . '&sharetime=' . time();
				header("Location: $url");
				exit();
			}

			// 销售
			$this->view->set('is_sale', 1);
		}

		$this->_output('campaign/view');
	}
}

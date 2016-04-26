<?php
/**
 * sendrp.php
 * 补发
 * $Author$
 * $Id$
 */
class voa_c_frontend_redpack_sendrp extends voa_c_frontend_redpack_base {

	public function _before_action($action) {

		exit("no privileges");
		$this->_require_login = false;
		return parent::_before_action($action);
	}

	public function execute() {

		// 载入微信支付红包类
		$redpack = new voa_wepay_redpack();

		// 分配红包
		$uda_rp = new voa_uda_frontend_redpack_send();

		$serv_log = &service::factory('voa_s_oa_redpack_log');
		$list = $serv_log->list_by_conds(array('sendst' => 0), 10);
		foreach ($list as $_v) {
			$result = array();
			$params = array(
				'redpack_id' => $_v['redpack_id'],
				'uid' => $_v['m_uid'],
				'openid' => $_v['openid']
			);

			// 如果订单缺失
			if (empty($_v['mch_billno'])) {
				$_v['mch_billno'] = voa_h_redpack::billno($this->_setting['mchid']);
				$serv_log->update($_v['id'], array(
					'mch_billno' => $_v['mch_billno'],
					'appid' => $this->_p_sets['wxappid']
				));
			}

			//print_r($params);exit;
			try {
				$uda_rp->doit($params, $result);
			} catch (Exception $e) {
				//var_dump($e);exit;
			}
		}

		if (empty($list)) {
			$js = 'all over...';
		} else {
			$js = '补发中...';
		}

		$this->_success_message($js, '/frontend/redpack/sendrp');
	}

}

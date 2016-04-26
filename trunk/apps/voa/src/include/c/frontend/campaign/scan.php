<?php
/**
 * 扫描二维码后显示: 活动签到页
 * $Author$
 * $Id$
 */
class voa_c_frontend_campaign_scan extends voa_c_frontend_campaign_base {

	public function execute() {

		$regid = rintval($this->request->get('regid')); // 报名表id

		$d = new voa_d_oa_campaign_reg();
		$reg = $d->get($regid);

		// 已经签到过的
		if ($reg['is_sign']) {
			$this->view->set('is_sign', 1);
		} else {
			// 执行签到
			$rs = $d->update($reg['id'], array('is_sign' => 1, 'signtime' => time()));
			if (! $rs) {
				$this->_error_message('签到操作失败');
			}

			// 统计签到数
			$total = new voa_d_oa_campaign_total();
			$total->signs($reg['actid'], $reg['saleid']);
		}

		$this->view->set('reg', $reg);
		$this->_output('campaign/sign');
	}
}

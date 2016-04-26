<?php
/**
 * 转发报告
 * $Author$
 * $Id$
 */

class voa_c_frontend_dailyreport_forward extends voa_c_frontend_dailyreport_base {

	public function execute() {
		// 如果不是 post 提交
		if (! $this->_is_post()) {
			$this->_error_message('submit_invalid');
			return false;
		}

		// 报告ID
		$dr_id = rintval($this->request->get('dr_id'));

		// 读取报告信息
		$serv = &service::factory('voa_s_oa_dailyreport', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$dailyreport = $serv->fetch_by_id($dr_id);
		if (empty($dr_id) || empty($dailyreport)) {
			$this->_error_message('dailyreport_is_not_exists');
		}

		// 备注
		$remark = (string) $this->request->get('message');
		if (! empty($remark)) {
			$dailyreport['remark'] = $remark;
		}

		// 转发人信息
		$cculist = array();
		$p_sets = voa_h_cache::get_instance()->get('plugin.dailyreport.setting', 'oa'); // 读日报配置缓存
		$dailyreport[$dailyreport['dr_type']] = $p_sets['daily_type'][$dailyreport['dr_type']][0];
		// 报告信息入库
		$uda = &uda::factory('voa_uda_frontend_dailyreport_insert');
		$post = array();
		if (! $uda->dailyreport_forward($dailyreport, $cculist)) {
			$this->_error_message($uda->error);
			return false;
		}

		// 发送消息通知
		$uda->send_wxqymsg_news($this->session, $dailyreport, 'forward', startup_env::get('wbs_uid'), $cculist);

		// 提示操作成功
		$this->_success_message('转发报告成功', "/dailyreport/view/$dr_id");
	}
}

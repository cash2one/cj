<?php
/**
 * 转发报告接口
 * $Author$
 * $Id$
 */

class voa_c_api_dailyreport_post_forward extends voa_c_api_dailyreport_base {

	public function execute() {
		/*需要的参数*/
		$fields = array(
			// 列表搜索结束时间
			'dr_id' => array('type'=>'int','required'=>true),
			// 报告类型
			'message' => array('type' => 'string', 'required' => false),
			// 用户id
			'carboncopyuids' => array('type' => 'string', 'required' => false),
		);
		//检查参数
		if (!$this->_check_params($fields)) {
			return false;
		}
		// 报告ID
		$dr_id = $this->_params['dr_id'];

		// 读取报告信息
		$serv = &service::factory('voa_s_oa_dailyreport', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$dailyreport = $serv->fetch_by_id($dr_id);
		if (empty($dr_id) || empty($dailyreport)) {
			$this->_error_message('dailyreport_is_not_exists');
		}

		// 备注
		$remark = $this->_params['message'];
		if (! empty($remark)) {
			$dailyreport['remark'] = $remark;
		}

		// 转发人信息
		$dailyreport['carboncopyuids'] = $this->_params['carboncopyuids'];
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
		//$this->_success_message('转发报告成功', "/dailyreport/view/$dr_id");
		/** 重组返回json数组 */
		$this->_result = array();
		return true;
	}
}

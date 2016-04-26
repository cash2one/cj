<?php
/**
 * 统计我发起的,以及待我处理的记录数量
 * $Author$
 * $Id$
 */
class voa_c_api_askfor_get_count extends voa_c_api_askfor_base {


	public function execute() {
		
		$serv = &service::factory('voa_s_oa_askfor', array('pluginid' => $this->_pluginid));
		$conditions = array(
			'm_uid' => startup_env::get('wbs_uid'),
		);
		$my_total = $serv->count_by_conditions($conditions);
		
		//审批表
		$proc = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => $this->_pluginid));
		$deal_total = $proc->count_by_conditions(startup_env::get('wbs_uid'), 1);
		return $this->_result = array(
			'my'	=>	intval($my_total),
			'deal'	=>	intval($deal_total),
		);
	}
}

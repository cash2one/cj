<?php
/**
 * 报告列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_dailyreport_search extends voa_c_frontend_dailyreport_base {
	protected $_start;
	protected $_perpage;
	protected $_page;
	protected $_updated;
	protected $_type;

	public function execute() {
	    $p_sets = voa_h_cache::get_instance()->get('plugin.dailyreport.setting', 'oa');//读日报配置缓存

		$this->_type = (string)$this->request->get('type');//报告类型value
		$this->_type = trim($this->_type);

		$this->type_val = (string)$this->request->get('type_val');//报告类型key

 		$acs = array('recv', 'mine');
		$ac = (string)$this->request->get('ac');
		if (!in_array($ac, $acs)) {
			$ac = 'mine';
		}

		switch ($ac) {
			case 'mine':
				$this->view->set('navtitle', '我发出的报告');
			break;
			case 'recv':
				$this->view->set('navtitle', '我收到的报告');
			break;
			default:
				$this->view->set('navtitle', '报告列表');
		}

		// 搜索条件
		$conditions = array('ac' => $ac, 'updated' => $this->_updated);
		//$this->_so_conditions($conditions);

		$this->view->set('type_val', $this->type_val);
		$this->view->set('type', $this->_type);
		$this->view->set('dailyType',$p_sets['daily_type']);//日报类型数组
		$this->view->set('ac', $ac);
		// 模板
		$tpl = 'mobile/dailyreport/search';

		$this->_output($tpl);
	}

	/**
	 * 我发出的
	 */
	protected function _mine() {
		$conditions = array('m_uid' =>startup_env::get('wbs_uid'));
		$this->_so_conditions($conditions);

		// 读取报告内容
		$serv = &service::factory('voa_s_oa_dailyreport', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->fetch_by_conditions($conditions, $this->_start, $this->_perpage);

		return $list;
	}

	/**
	 * 我接收的
	 */
	protected function _recv() {
		$conditions = array();
		$this->_so_conditions($conditions);

		// 读取报告内容
		$serv = &service::factory('voa_s_oa_dailyreport_mem', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->fetch_by_conditions($conditions, $this->_start, $this->_perpage);

		return $list;
	}

	/**
	 * 搜索条件
	 * @param array $conditions
	 * @return boolean
	 */
	protected function _so_conditions(&$conditions) {
		// 判断是否为时间格式
		$report_time = rstrtotime($this->_sotext);
		if (0 < $report_time) {
			$datetime = explode(' ',$this->_sotext);
			$conditions['reporttime'] = rstrtotime($datetime[0]);
			return false;
		}
		if($this->_sotext != 0 || empty($this->_sotext)){
		   $conditions['dr_type'] = $this->_sotext;
		}
		return true;
	}

}

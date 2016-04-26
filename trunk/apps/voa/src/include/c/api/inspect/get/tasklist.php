<?php
/**
 * 巡店计划列表信息
 * voa_c_api_inspect_get_tasklist
 * $Author$
 * $Id$
 */

class voa_c_api_inspect_get_tasklist extends voa_c_api_inspect_base {
	/** 起始位置 */
	protected $_start;
	protected $_perpage;
	protected $_page;

	public function execute() {

		/*需要的参数*/
		$fields = array(
			/*当前页码*/
			'page' => array('type' => 'int', 'required' => false),
			/*每页显示数据数*/
			'limit' => array('type' => 'int', 'required' => false),
		);
		if (!$this->_check_params($fields)) {
			/*检查参数*/
			return false;
		}

		if ($this->_params['page'] < 1) {
			/*设定当前页码的默认值*/
			$this->_params['page'] = 1;
		}

		if ($this->_params['limit'] < 1) {
			/*设定每页数据条数的默认值*/
			$this->_params['limit'] = 10;
		}

		/*获取分页参数*/
		list($this->_start, $this->_params['limit'], $this->_params['page']) = voa_h_func::get_limit($this->_params['page'], $this->_params['limit'], 100);

		/*更新时间*/
		$this->_updated = startup_env::get('timestamp') + 10;

		/** 读取计划列表 */
		$serv_ins = &service::factory('voa_s_oa_inspect', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv_ins->list_by_uid(startup_env::get('wbs_uid'), array(
			voa_d_oa_inspect::STATUS_WAITING,
			voa_d_oa_inspect::STATUS_DOING
		), $this->_start, $this->_perpage);

		$total = $serv_ins->count_by_conditions(array(
			'm_uid' => startup_env::get('wbs_uid'),
			'ins_status' => array(array(voa_d_oa_inspect::STATUS_WAITING, voa_d_oa_inspect::STATUS_DOING), 'in')
		));


		// 输出结果
		$this->_result = array(
			'total' => $total,
			'limit' => $this->_params['limit'],
			'page' => $this->_params['page'],
			'data' => $list
		);

		return true;
	}

}

<?php
/**
 * 巡店列表信息
 * voa_c_api_inspect_get_list
 * $Author$
 * $Id$
 */

class voa_c_api_inspect_get_list extends voa_c_api_inspect_base {
	/** 时间戳 */
	protected $_updated = 0;
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
			/*每页显示数据数*/
			'ac' => array('type' => 'string', 'required' => false),
		);

		if (!$this->_check_params($fields)) {
			// 检查参数
			return false;
		}

		if ($this->_params['page'] < 1) {
			// 设定当前页码的默认值
			$this->_params['page'] = 1;
		}

		if ($this->_params['limit'] < 1) {
			// 设定每页数据条数的默认值
			$this->_params['limit'] = 10;
		}

		if (empty($this->_params['ac'])) {
			// 设置当前的动作
			$this->_params['ac'] = 'mine';
		}
		/**
		 * 动作
		 * mine: 我的巡店记录
		 * recv: 我收到的
		 */
		$acs = array('mine', 'recv');
		/** 获取操作 */
		if (!in_array($this->_params['ac'], $acs)) {
			// 设置默认动作为 我的申请列表
			return $this->_set_errcode(voa_errcode_api_inspect::LIST_UNDEFINED_ACTION, $this->_params['action']);
		}

		// 获取分页参数
		list($this->_start, $this->_params['limit'], $this->_params['page']) = voa_h_func::get_limit($this->_params['page'], $this->_params['limit'], 100);

		// 更新时间
		$this->_updated = startup_env::get('timestamp') + 10;
		// 调用处理方法
		$list = array();
		$func = '_'.$this->_params['ac'];
		if (!method_exists($this, $func)) {
			return $this->_set_errcode(voa_errcode_api_inspect::LIST_UNDEFINED_FUNCTION, $this->_params['action']);
		}

		// 呼叫对应动作方法
		call_user_func(array($this, $func));

		/** 过滤 */
		$fmt = &uda::factory('voa_uda_frontend_inspect_format');
		if (!$fmt->inspect_list($this->_list)) {
			//$this->_error_message($fmt->error);
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}

		// 输出结果
		$this->_result = array(
			'total' => $this->_total,
			'limit' => $this->_params['limit'],
			'page' => $this->_params['page'],
			'data' => $this->_list,
			//'shops' => $this->_shops
		);

		return true;
	}

	/** 获取我的巡店列表 */
	protected function _mine() {
		$serv = &service::factory('voa_s_oa_inspect', array('pluginid' => startup_env::get('pluginid')));
		$this->_list = $serv->list_by_uid(startup_env::get('wbs_uid'), voa_d_oa_inspect::STATUS_DONE, $this->_start, $this->_perpage);
		$this->_total = $serv->count_by_conditions(
			array(
				'm_uid' => startup_env::get('wbs_uid'),
				'ins_status' => array(array(voa_d_oa_inspect::STATUS_DONE), 'in')
			)
			);
	}

	/** 读取我收到的 */
	protected function _recv() {
		$serv = &service::factory('voa_s_oa_inspect_mem', array('pluginid' => startup_env::get('pluginid')));
		$this->_list = $serv->list_recv_by_uid(startup_env::get('wbs_uid'), $this->_start, $this->_perpage);
		$this->_total = $serv->count_by_conditions(
			array(
				'm_uid' => startup_env::get('wbs_uid'),
			)
			);
	}

}

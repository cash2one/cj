<?php
/**
 * voa_c_api_askoff_get_list
 * 请假列表
 * $Author$
 * $Id$
 */
class voa_c_api_askoff_get_list extends voa_c_api_askoff_base {

	/** 当前查询的数据起始行 */
	protected $_start;
	/** 数据总数 */
	protected $_total;
	/** 数据列表 */
	protected $_list;
	/** 请假表 */
	protected $_serv;
	/** 最后更新时间 */
	protected $_updated = 0;

	public function execute() {

		// 需要的参数
		$fields = array(
			// 当前页码
			'page' => array('type' => 'int', 'required' => false),
			// 每页显示数据数
			'limit' => array('type' => 'int', 'required' => false),
			// 读取的列表类型
			'action' => array('type' => 'string', 'required' => false)
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

		if (empty($this->_params['action'])) {
			// 设置当前的动作
			$this->_params['action'] = 'my';
		}

		// 获取分页参数
		list($this->_start, $this->_params['limit'], $this->_params['page']) = voa_h_func::get_limit($this->_params['page'], $this->_params['limit'], 100);

		// 更新时间
		$this->_updated = startup_env::get('timestamp') + 10;

		// 调用处理方法
		$list = array();
		$func = '_'.$this->_params['action'];
		if (!method_exists($this, $func)) {
			return $this->_set_errcode(voa_errcode_api_askoff::LIST_UNDEFINED_FUNCTION, $this->_params['action']);
		}

		// 请假表
		$this->_serv = &service::factory('voa_s_oa_askoff', array('pluginid' => $this->_pluginid));

		// 呼叫对应动作方法
		$this->$func();

		/** 整理输出 */
		$uda_fmt = &uda::factory('voa_uda_frontend_askoff_format');
		// 整理数据
		$data = array();
		foreach ($this->_list as $_p) {
			$uda_fmt->askoff($_p);
			$data[] = array(
				'ao_id' => $_p['ao_id'],
				'uid' => $_p['m_uid'],
				'username' => $_p['m_username'],
				'avatar' => voa_h_user::avatar($_p['m_uid']),
				'type' => $_p['ao_type'],
				'begintime' => $_p['ao_begintime'],
				'endtime' => $_p['ao_endtime'],
				'status_tip' => $_p['_status_tip'],
				'days' => $_p['_days']
			);
		}

		// 输出结果
		$this->_result = array(
			'total' => $this->_total,
			'limit' => $this->_params['limit'],
			'page' => $this->_params['page'],
			'data' => $data
		);

		return true;
	}

	/** 读取我发起的请假 */
	public function _my() {
		$this->_list = $this->_serv->fetch_mine(startup_env::get('wbs_uid'), $this->_start, $this->_params['limit']);
		$conditions = array(
			'm_uid' => startup_env::get('wbs_uid')
		);
		$this->_total = $this->_serv->count_by_conditions($conditions);
	}

	/** 获取待批复的请假列表 */
	public function _deal() {
		//审批表
		$serv = &service::factory('voa_s_oa_askoff_proc', array('pluginid' => $this->_pluginid));
		$this->_list = $serv->_deal(startup_env::get('wbs_uid'), 1, $this->_start, $this->_params['limit']);

		$this->_total = $serv->count_by_conditions(startup_env::get('wbs_uid'), 1);
	}
	/** 已完成列表 */
	public function _finish() {
		//审批表
		$serv = &service::factory('voa_s_oa_askoff_proc', array('pluginid' => $this->_pluginid));
		$this->_list = $serv->_deal(startup_env::get('wbs_uid'), array(2,3,4), $this->_start, $this->_params['limit']);
		$this->_total = $serv->count_by_conditions(startup_env::get('wbs_uid'), array(2,3,4));
	}

}

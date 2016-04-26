<?php
/**
 * voa_c_api_reimburse_list
 * 搜索报销列表
 * $Author$
 * $Id$
 */

class voa_c_api_reimburse_get_list extends voa_c_api_reimburse_base {
	/** 当前查询的数据起始行 */
	protected $_start;
	/** 数据总数 */
	protected $_total;
	/** 数据列表 */
	protected $_list;
	/** 最后更新时间 */
	protected $_updated = 0;

	public function execute() {
		/*需要的参数*/
		$fields = array(
			/*当前页码*/
			'page' => array('type' => 'int', 'required' => false),
			/*每页显示数据数*/
			'limit' => array('type' => 'int', 'required' => false),
			// 读取的列表类型
			'action' => array('type' => 'string', 'required' => false)
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

		if (empty($this->_params['action'])) {
			// 设置当前的动作
			$this->_params['action'] = 'mine';
		}

		/**
		 * 动作集合
		 * mine: 我发起
		 * dealing: 审批中
		 * dealed: 审批完成
		 */
		$acs = array('mine', 'dealing', 'dealed');
		if (!in_array($this->_params['action'], $acs)) {
			// 设置默认动作为 我的申请列表
			return $this->_set_errcode(voa_errcode_api_reimburse::LIST_UNDEFINED_ACTION, $this->_params['action']);
		}

		/*获取分页参数*/
		list($this->_start, $this->_perpage, $this->_params['page']) = voa_h_func::get_limit($this->_params['page'], $this->_params['limit'], 100);

		/*更新时间*/
		$this->_updated = startup_env::get('timestamp') + 10;

		// 调用处理方法
		$list = array();
		$func = '_'.$this->_params['action'];
		if (!method_exists($this, $func)) {
			return $this->_set_errcode(voa_errcode_api_reimburse::LIST_UNDEFINED_FUNCTION, $this->_params['action']);
		}

		// 报销表
		$this->_serv = &service::factory('voa_s_oa_reimburse_proc', array('pluginid' => $this->_pluginid));

		// 呼叫对应动作方法
		call_user_func(array($this, $func));

		// 整理json数据
		foreach ($this->_list as $_rb_id => $_p) {
			$data[] = array(
				'id' => $_rb_id,// 报销ID
				'uid' => $_p['m_uid'],// 创建者uid
				'username' => $_p['m_username'],// 创建者名字
				'status' => $_p['rb_status'],// 报销进度状态
				'created' => $_p['rb_created'],// 报销申请时间
				'subject' => $_p['rb_subject'],// 报销主题
				'expend' => round($_p['rbb_expend'] / 100, 2),// 费用（元）
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

	/** 获取自己申请的列表 */
	function _mine() {
		$conditions = array(
			'm_uid' => startup_env::get('wbs_uid')
			);
		$this->_total = $this->_serv->count_by_conditions($conditions);
		$this->_list = $this->_serv->fetch_mine(startup_env::get('wbs_uid'), $this->_updated, $this->_start, $this->_perpage);
	}

	/** 获取审核中的列表 */
	function _dealing() {
		$sts = array(
				voa_d_oa_reimburse_proc::STATUS_NORMAL
			);
		$conditions = array(
			'm_uid' => startup_env::get('wbs_uid'),
			'rbpc_status' => array($sts, 'in')
			);
		$this->_total = $this->_serv->count_closed_by_uids_updated($conditions);
		$this->_list = $this->_serv->fetch_deal(startup_env::get('wbs_uid'), $this->_updated, $sts, $this->_start, $this->_perpage);
	}

	/** 读取已完成的 */
	function _dealed() {
		$sts = array(
				voa_d_oa_reimburse_proc::STATUS_APPROVE,
				voa_d_oa_reimburse_proc::STATUS_REFUSE,
				voa_d_oa_reimburse_proc::STATUS_TRANSMIT
			);
		$conditions = array(
			'm_uid' => startup_env::get('wbs_uid'),
			'rbpc_status' => array($sts, 'in')
			);
		$this->_total = $this->_serv->count_done_by_uids_updated($conditions);
		$this->_list = $this->_serv->fetch_deal(startup_env::get('wbs_uid'), $this->_updated, $sts, $this->_start, $this->_perpage);
	}
}

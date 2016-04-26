<?php
/**
 * search.php
 * uda/移动派单/搜索
 * + page 请求的页码 !!必须外部验证!!!
 * + limit 每页请求的数据量 !!必须外部验证!!!
 * + uid 请求查询的人员ID !!必须外部有效验证!!!
 * + sender 派单人
 * + senderid 派单人id
 * + operator 执行人
 * + operatorid 执行人id
 * + receiver 接收人
 * + receiverid 接收人id
 * + woid 工单编号
 * + ordertime 派单时间
 * + ordertime_start 派单开始时间
 * + ordertime_end 派单结束时间
 * + wostate 工单状态id
 * ... 参看成员 $_extrequest 的定义
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_workorder_action_search extends voa_uda_frontend_workorder_abstract {

	/**
	 * 定义内部查询需要的参数、默认值、校验方法
	 *
	 * 校验规则是，先找是否存在定义了的校验方法完整名，不存在则尝试找以键名命名的方法，
	 *
	 * 如果都不存在，则直接使用外部提交的参数值
	 * @var array
	 */
	protected $_define_params = array(
		'page' => array('', 1),// array(校验方法完整名,默认值)
		'limit' => array('', 10),
		'uid' => array('', 0),
		'sender' => array('__check_username', false),
		'senderid' => array('__check_uid', false),
		'operator' => array('__check_username', false),
		'operatorid' => array('__check_uid', false),
		'receiver' => array('__check_username', false),
		'receiverid' => array('__check_uid', false),
		'woid' => array('', false),
		'ordertime' => array('__check_date', false),
		'ordertime_start' => array('__check_date', false),
		'ordertime_end' => array('__check_date', false),
		'wostate' => array('', false),
	);

	/** 经过校验的外部请求查询参数 */
	protected $_request_params = array();
	/** 符合条件的结果列表 */
	protected $_list = array();
	/** 符合条件的结果总数 */
	protected $_count = 0;
	/** 实际生效的查询条件，用于输出 */
	protected $_conditions = array();
	/** 格式化后的查询条件 */
	protected $_find_conditions = array();
	/** 符合ORM查询的条件 */
	protected $_query_conds = array();
	/** 查询结果 */
	protected $_result = array();
	/** 是否是管理模式请求 */
	protected $_request_admin = false;

	/**
	 * 搜索结果
	 * @param array $request 请求的查询条件参数
	 * @param array $result (返回结果)搜索结果
	 * @param array $conditions (返回结果)实际生效的查询条件
	 * @param boolean $is_admin 是否是管理模式
	 * @return boolean
	 */
	public function result($request, &$result, &$conditions, $is_admin = false) {

		// 校验请求的参数
		if (!$this->_validator_request($request)) {
			return false;
		}

		$this->_request_admin = $is_admin;

		// 处理查询条件
		$this->_format_conditions();
		// 输出实际有效的查询条件
		$conditions = $this->_conditions;
		// 构造查询条件
		$this->_set_conditions();

		// 查询结果总数
		$this->_get_count();

		// 没有结果直接返回
		if (!$this->_count) {
			$this->_set_result($request['page'], $request['limit']);
			$result = $this->_result;
			return true;
		}

		// 列出结果
		$this->_get_list($request['page'], $request['limit']);
		// 设置输出结果
		$this->_set_result($request['page'], $request['limit']);
		$result = $this->_result;

		return true;
	}

	/**
	 * 校验请求的查询条件
	 * @return boolean
	 */
	protected function _validator_request($request) {

		// 遍历内部需要的参数，初始化外部请求的参数，避免无定义的键名
		foreach ($this->_define_params as $_key => $_init) {

			// 未请求该参数 或 明确定义该参数值为false，则设为默认值，避免键名未定义
			if (!isset($request[$_key])) {
				$this->_request_params[$_key] = $_init[1];
				continue;
			}

			// 请求了该参数，则尝试校验
			if ($_init[0]) {
				// 指定了校验方法

				$validator_method = $_init[0];
				// 指定了方法名，但不存在，则返回错误
				if (!method_exists($this, $validator_method)) {
					return $this->set_errmsg(voa_errcode_oa_workorder::SEARCH_VALIDATOR_METHOD_NOT_EXISTS
							, $_init[0]);
				}
			} else {
				// 未指定校验方法，尝试使用参数名

				$validator_method = '__validator_'.$_key;
				// 校验方法不存在，说明不需要进行验证，直接使用提供的值
				if (!method_exists($this, $validator_method)) {
					$this->_request_params[$_key] = $request[$_key];
					continue;
				}
			}

			// 校验方法存在，则进行验证检查，通过检查则使用该值
			if ($this->$validator_method($request[$_key], $_key)) {
				$this->_request_params[$_key] = $request[$_key];
				continue;
			}

			// 验证未通过，则使用默认值
			$this->_request_params[$_key] = $_init[1];
		}
		unset($_key, $_init);

		return true;
	}

	/**
	 * 格式化查询条件并返回用户请求的查询条件
	 * $this->_conditions
	 * $this->_find_conditions
	 * @return void
	 */
	protected function _format_conditions() {

		$this->_conditions = array();
		$this->_find_conditions = array(
			'uid' => array(),
			'operator_uid' => array(),
			'receiver_uid' => array(),
			'woid' => '',
			'ordertime' => '',
			'wostate' => '',
		);

		// 派单人名字找uid
		$senderid = 0;
		if ($this->_request_params['sender'] !== false && $this->_find_uid($this->_request_params['sender']
				, $senderid)) {
			$this->_find_conditions['uid'][] = $senderid;
			$this->_conditions['sender'] = $this->_request_params['sender'];
		}
		// 派单人uid
		if ($this->_request_params['senderid'] !== false) {
			$this->_find_conditions['uid'][] = $this->_request_params['senderid'];
			$this->_conditions['senderid'] = $this->_request_params['senderid'];
		}
		/// End

		// 执行人名字找uid
		$operatorid = 0;
		if ($this->_request_params['operator'] !== false
				&& $this->_find_uid($this->_request_params['operator'], $operatorid)) {
			$this->_find_conditions['operator_uid'][] = $operatorid;
			$this->_conditions['operator'] = $this->_request_params['operator'];
		}
		// 执行人uid
		if ($this->_request_params['operatorid'] !== false) {
			$this->_find_conditions['operator_uid'][] = $this->_request_params['operatorid'];
			$this->_conditions['operator_uid'] = $this->_request_params['operatorid'];
		}
		/// End

		// 工单编号
		if ($this->_request_params['woid'] !== false) {
			$this->_find_conditions['woid'] = $this->_request_params['woid'];
			$this->_conditions['woid'] = $this->_request_params['woid'];
		}
		/// End

		// 派单时间
		if ($this->_request_params['ordertime']) {
			// 使用指定日期查询

			$time = rstrtotime($this->_request_params['ordertime']);
			$this->_find_conditions['ordertime_start'] = $time;
			$this->_find_conditions['ordertime_end'] = $time + 86400;
			$this->_conditions['ordertime'] = $this->_request_params['ordertime'];
		} else {
			if ($this->_request_params['ordertime_start']) {
				// 设置了最早时间

				$this->_find_conditions['ordertime_start'] = rstrtotime($this->_request_params['ordertime_start']);
				$this->_conditions['ordertime_start'] = $this->_request_params['ordertime_start'];
			}
			if ($this->_request_params['ordertime_end']) {
				// 设置了最晚时间

				$this->_find_conditions['ordertime_end'] = rstrtotime($this->_request_params['ordertime_end']) + 86400;
				$this->_conditions['ordertime_end'] = $this->_request_params['ordertime_end'];
			}
		}
		if (!isset($this->_find_conditions['ordertime_start'])) {
			$this->_find_conditions['ordertime_start'] = 0;
		}
		if (!isset($this->_find_conditions['ordertime_end'])) {
			$this->_find_conditions['ordertime_end'] = 0;
		}
		if (!isset($this->_find_conditions['ordertime'])) {
			$this->_find_conditions['ordertime'] = 0;
		}
		/// End

		// 工单状态
		if ($this->_request_params['wostate'] !== false) {
			$this->_find_conditions['wostate'] = $this->_request_params['wostate'];
			$this->_conditions['wostate'] = $this->_request_params['wostate'];
		}
		/// End
	}

	/**
	 * 设定查询条件
	 * @return void
	 */
	protected function _set_conditions() {

		// 符合orm的查询条件
		$this->_query_conds = array(
			'workorder' => array(),
			'workorder_operator' => array()
		);

		// 查询工单ID
		if ($this->_find_conditions['woid']) {
			if (strpos($this->_find_conditions['woid'], '*') === false) {
				// 完整工单号查询
				$this->_query_conds['workorder']['woid'] = $this->_find_conditions['woid'];
			} else {
				// 模糊查询
				$this->_query_conds['workorder']['woid LIKE ?'] = str_replace('*', '%'
						, $this->_find_conditions['woid']);
			}
		}

		// 构造派单人查询
		if ($this->_find_conditions['uid']) {
			$this->_query_conds['workorder']['uid'] = $this->_find_conditions['uid'];
		}

		// 执行人查询
		if ($this->_find_conditions['operator_uid']) {
			$this->_query_conds['workorder']['operator_uid'] = $this->_find_conditions['operator_uid'];
		}

		// 查询派单时间
		if ($this->_find_conditions['ordertime']) {
			// 指定日期查询
			$this->_query_conds['workorder']['ordertime'] = $this->_find_conditions['ordertime'];
		} elseif ($this->_find_conditions['ordertime_start'] || $this->_find_conditions['ordertime_end']) {
			// 指定查询区间

			if ($this->_find_conditions['ordertime_start'] == $this->_find_conditions['ordertime_end']) {
				// 起至时间相同，按指定日期来查询

				$this->_query_conds['workorder']['ordertime'] = $this->_find_conditions['ordertime_start'];
			} else {
				// 按区间查询
				// 起始时间
				if ($this->_find_conditions['ordertime_start']) {
					$this->_query_conds['workorder']['ordertime >= ?'] = $this->_find_conditions['ordertime_start'];
				}
				// 结束时间
				if ($this->_find_conditions['ordertime_end']) {
					$this->_query_conds['workorder']['ordertime <= ?'] = $this->_find_conditions['ordertime_end'];
				}
			}
		}

		// 查询状态
		if ($this->_find_conditions['wostate']) {
			$this->_query_conds['workorder']['wostate'] = $this->_find_conditions['wostate'];
		}
	}

	/**
	 * 符合条件的结果总数
	 * $this->_count
	 * @return void
	 */
	protected function _get_count() {

		$d_workorder = new voa_d_oa_workorder();
		if (!empty($this->_query_conds['workorder'])) {
			$this->_count = $d_workorder->count_by_conds($this->_query_conds['workorder']);
		} else {
			$this->_count = $d_workorder->count();
		}
	}

	/**
	 * 符合条件的结果列表
	 * $this->_list
	 * @return void
	 */
	protected function _get_list($page, $limit) {

		$start = ($page - 1) * $limit;
		$orderby = array('ordertime' => 'DESC');
		$d_workorder = new voa_d_oa_workorder();
		if (!empty($this->_query_conds['workorder'])) {
			$this->_list = $d_workorder->list_by_conds($this->_query_conds['workorder']
					, array($start, $limit), $orderby);
		} else {
			$this->_list = $d_workorder->list_all(array($start, $limit), $orderby);
		}
	}

	/**
	 * 构造输出结果
	 * $this->_result
	 * @return void
	 */
	protected function _set_result($page, $limit) {

		$this->_result = array(
			'page' => $page,
			'limit' => $limit,
			'count' => $this->_count,
			'conditions' => $this->_conditions,
			'list' => $this->_list,
		);

	}

	/**
	 * 校验用户名
	 * @param string $username 用户名字符串
	 * @param string $key 查询的是哪个参数名
	 * @return boolean
	 */
	private function __check_username(&$username, $key) {

		$username = (string)$username;
		$username = trim($username);
		if (empty($username)) {
			return false;
		}

		return true;
	}

	/**
	 * 校验用户ID
	 * @param number $uid
	 * @param string $key
	 */
	private function __check_uid(&$uid, $key) {

		$uid = (int)$uid;
		if ($uid < 1) {
			return false;
		}

		return true;
	}

	/**
	 * 校验日期字符串
	 * @param string $date
	 * @param string $key
	 * @return boolean
	 */
	private function __check_date(&$date, $key) {

		$date = (string)$date;
		$date = trim($date);
		if (rstrtotime($date) === 0) {
			return false;
		}

		return true;
	}

	/**
	 * 检查工单状态
	 * @param number $wostate
	 * @param string $key
	 * @return boolean
	 */
	private function __validator_wostate(&$wostate, $key) {

		$uda_validator = &uda::factory('voa_uda_frontend_workorder_validator');
		if (!$uda_validator->workorder_wostate($wostate)) {
			return false;
		}

		return true;
	}

	/**
	 * 检查工单编号
	 * @param string $woid
	 * @param string $key
	 * @return boolean
	 */
	private function __validator_woid(&$woid, $key) {

		$woid = (string)$woid;
		$woid = trim($woid);
		// 移除所有空格 并 清理连续的星号、_
		$woid = preg_replace(array('/[\s]+/', '/[\*]+/', '/[\_]+/'), array('', '*', ''), $woid);
		// 首位和末尾的星号替换为_
		$woid = preg_replace(array('/^[\*]/', '/[\*]$/'), '_', $woid);
		// 清理其他位置的星号
		$woid = str_replace('*', '', $woid);
		// 恢复星号，形成 *xxxx*的格式
		$woid = str_replace('_', '*', $woid);

		// 不是大于3位的数字则忽略
		if (!preg_match('/^[\*]?\d{3,}[\*]?/', $woid)) {
			return false;
		}

		// 如果小于4位且没使用模糊搜索模式则按模糊搜索
		if (strpos($woid, '*') === false && strlen(str_replace('*', '', $woid)) < 4) {
			$woid = '*'.$woid.'*';
		}

		return true;
	}

	/**
	 * 通过用户名找到uid
	 * @param string $username
	 * @param number $uid (引用结果)用户UID
	 * @return boolean
	 */
	private function _find_uid($username, &$uid) {

		$uid = 0;
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$member = $serv_m->fetch_by_username($username);
		if ($member) {
			$uid = $member['m_uid'];
		}

		return true;
	}

}

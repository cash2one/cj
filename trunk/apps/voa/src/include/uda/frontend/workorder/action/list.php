<?php
/**
 * list.php
 * 工单列表内部统一调用方法
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_workorder_action_list extends voa_uda_frontend_workorder_abstract {

	/** 请求的参数 */
	protected $_extrequest = array(
		'source' => 'sent',
		'page' => 1,
		'limit' => 10,
		'type' => 'wait_confirm',
		'uid' => 0,
	);

	/** 输出的数据结果 */
	protected $_result = array();
	/** 列表类型 */
	protected $_types = array(
		'wait_confirm' => '待确认',
		'wait_complete' => '待完成',
		'completed' => '已完成',
		'refused' => '已拒绝',
		'canceled' => '已撤回',
	);
	/** 是否是管理模式请求 */
	protected $_request_admin = false;

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 根据请求输出工单列表
	 * @param array $request
	 * + page 当前请求的页码，请求的外部必须做验证！！
	 * + limit 当前页面请求的数据数量，请求的外部必须做验证！！
	 * + source 请求的数据来源：sent=已发送,received=收到的
	 * + type 当前请求的工单类型（wait_confirm|wait_complete|completed|refused|canceled）
	 * + uid 当前请求浏览的人
	 * @param array $result (引用结果)输出结果
	 * @return boolean
	 */
	public function output($request, &$result, $is_admin = false) {

		// 请求的参数存在与否性验证
		foreach ($this->_extrequest as $key => $default_value) {
			if (isset($request[$key])) {
				$this->_extrequest[$key] = $request[$key];
			} else {
				if ($default_value === null) {
					return $this->set_errmsg(voa_errcode_oa_workorder::LIST_REQUEST_PARAM_LOSE, $key);
				} else {
					$this->_extrequest[$key] = $default_value;
				}
			}
		}

		$this->_request_admin = $is_admin;

		// 检查数据源请求是否合法
		if (!in_array($this->_extrequest['source'], array('sent', 'received'))) {
			return $this->set_errmsg(voa_errcode_oa_workorder::LIST_REQUEST_SOURCE_UNKNOW
					, $this->_extrequest['source']);
		}

		// 检查列表类型请求是否合法
		if (!isset($this->_types[$this->_extrequest['type']])) {
			return $this->set_errmsg(voa_errcode_oa_workorder::LIST_REQUEST_TYPE_ERROR
					, $this->_extrequest['type']);
		}

		// 需要使用的请求列表方法
		$method = '_list_'.$this->_extrequest['source'];

		// 获取数据列表
		if (!$this->$method()) {
			return false;
		}

		// 输出列表
		$result = $this->_result;
		unset($result);

		return true;
	}

	/**
	 * 获取已发送相关列表
	 * $this->_result
	 * @return boolean
	 */
	protected function _list_sent() {

		// 构造查询条件
		$conds = array();
		// 浏览当前请求的人
		$conds['uid'] = $this->_extrequest['uid'];
		// 确定浏览的状态
		switch ($this->_extrequest['type']) {
			case 'wait_confirm':// 待确认
				$conds['wostate'] = voa_d_oa_workorder::WOSTATE_WAIT;
				break;
			case 'wait_complete':// 待完成
				$conds['wostate'] = voa_d_oa_workorder::WOSTATE_CONFIRM;
				break;
			case 'completed'://已完成
				$conds['wostate'] = voa_d_oa_workorder::WOSTATE_COMPLETE;
				break;
			case 'refused':// 已拒绝
				$conds['wostate'] = voa_d_oa_workorder::WOSTATE_REFUSE;
				break;
			case 'canceled':// 已撤回
				$conds['wostate'] = voa_d_oa_workorder::WOSTATE_CANCEL;
				break;
		}

		// 引入数据类
		$d_workorder = new voa_d_oa_workorder();

		// 获取当前请求的数据总数
		$total = $d_workorder->count_by_conds($conds);
		$list = array();
		// 如果无数据，则直接返回
		if (!$total) {
			$this->_output_result(0, $list);
			return true;
		}

		// 数据起始行
		$start = ($this->_extrequest['page'] - 1) * $this->_extrequest['limit'];
		// 排序规则，需要注意，如果请求的状态wostate为多态，请不要使用下面的符合排序（因无索引），单独使用ordertime
		$orderby = array(
			'uid' => 'DESC',
			'wostate' => 'DESC',
			'ordertime' => 'DESC',
		);
		// 获取数据
		$list = $d_workorder->list_by_conds($conds, array($start, $this->_extrequest['limit']), $orderby);
		$this->_output_result($total, $list);
		unset($list);

		return true;
	}

	/**
	 * 获取已发送相关列表
	 * $this->_result
	 * @return boolean
	 */
	protected function _list_received() {

		// 构造查询条件
		$conds = array();
		// 浏览当前请求的人
		$conds['uid'] = $this->_extrequest['uid'];
		// 请求的状态
		switch ($this->_extrequest['type']) {
			case 'wait_confirm':// 待确认
				$conds['worstate'] = voa_d_oa_workorder_receiver::WORSTATE_WAIT;
				break;
			case 'wait_complete':// 待完成
				$conds['worstate'] = voa_d_oa_workorder_receiver::WORSTATE_CONFIRM;
				break;
			case 'completed'://已完成
				$conds['worstate'] = voa_d_oa_workorder_receiver::WORSTATE_MYCOMPLETE;
				break;
			case 'refused':// 已拒绝
				$conds['worstate'] = voa_d_oa_workorder_receiver::WORSTATE_REFUSE;
				break;
			case 'canceled':// 已撤回
				$conds['worstate'] = voa_d_oa_workorder_receiver::WORSTATE_CANCEL;
				break;
		}

		// 引入数据类
		$d_workorder_receiver = new voa_d_oa_workorder_receiver();

		// 数据总数
		$total = $d_workorder_receiver->count_by_conds($conds);
		$list = array();
		// 无数据直接返回
		if (!$total) {
			$this->_output_result(0, $list);
			return true;
		}

		// 数据起始行
		$start = ($this->_extrequest['page'] - 1) * $this->_extrequest['limit'];
		// 排序规则
		// 如果未来业务需求需要多态查询，不能使用本排序规则！单独使用ordertime
		$orderby = array(
			'uid' =>'DESC',
			'worstate' => 'DESC',
			'ordertime' => 'DESC'
		);

		// 这里通过条件获取到工单ID，后续通过工单ID直接来读取工单列表
		$receivers = $d_workorder_receiver->list_by_conds($conds, array($start, $this->_extrequest['limit']), $orderby);
		unset($conds);

		// 符合条件的工单ID
		$woids = array();
		foreach ($receivers as $_wor) {
			$woids[] = $_wor['woid'];
		}
		unset($receivers, $_wor);

		// 通过工单ID获取实际列表数据
		$d_workorder = new voa_d_oa_workorder();
		$list = $d_workorder->list_by_pks($woids, array('ordertime' => 'DESC'));

		// 输出数据
		$this->_output_result($total, $list);
		unset($list);

		return true;
	}

	/**
	 * 统一输出格式
	 * @param number $total 数据总量
	 * @param array $list 数据列表
	 * @return boolean
	 */
	protected function _output_result($total, $list) {

		$this->_result = array(
			'total' => $total,
			'limit' => $this->_extrequest['limit'],
			'page' => $this->_extrequest['page'],
			'pages' => ceil($total/$this->_extrequest['limit']),
			'list' => $list
		);

		return true;
	}

}

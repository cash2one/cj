<?php
/**
 * format.php
 * 派单 - 数据输出格式化
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_workorder_format extends voa_uda_frontend_workorder_abstract {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 格式化工单详情
	 * @param array $workorder 工单详情
	 * @param array $format (引用结果)格式化后的工单详情
	 * @param string $date_format 时间显示格式，默认: Y-m-d H:i，如果为空，则不格式化时间
	 * @return boolean
	 */
	public function workorder($workorder, &$format, $date_format = 'Y-m-d H:i') {

		// 工单执行状态文字描述
		$workorder['wostate_name'] = '';
		$this->get_wostate_name($workorder['wostate_name'], $workorder['wostate']);

		// 格式化时间输出
		if ($date_format) {

			// 派单时间
			$workorder['ordertime'] = $workorder['ordertime'] ? rgmdate($workorder['ordertime'], $date_format) : '';
			// 确认时间
			$workorder['confirmtime'] = $workorder['confirmtime'] ? rgmdate($workorder['confirmtime'], $date_format) : '';
			// 撤回时间
			$workorder['canceltime'] = $workorder['canceltime'] ? rgmdate($workorder['canceltime'], $date_format) : '';
			// 完成时间
			$workorder['completetime'] = $workorder['completetime'] ? rgmdate($workorder['completetime'], $date_format) : '';
		}

		$format = array(
			'woid' => $workorder['woid'],
			'wostate' => $workorder['wostate'],
			'wostate_name' => $workorder['wostate_name'],
			'uid' => $workorder['uid'],
			'operator_uid' => $workorder['operator_uid'],
			'ordertime' => $workorder['ordertime'],
			'canceltime' => $workorder['canceltime'],
			'confirmtime' => $workorder['confirmtime'],
			'completetime' => $workorder['completetime'],
			'contacter' => rhtmlspecialchars($workorder['contacter']),
			'phone' => rhtmlspecialchars($workorder['phone']),
			'address' => rhtmlspecialchars($workorder['address']),
			'remark' => nl2br(rhtmlspecialchars($workorder['remark'])),
			'refuse_reason' => isset($workorder['refuse_reason'])
				? nl2br(rhtmlspecialchars($workorder['refuse_reason'])) : '',
		);

		return true;
	}

	/**
	 * 格式化工单详情表输出
	 * @param array $detail
	 * @param array $format (引用结果)格式化后的数据
	 * @param string $date_format
	 * @return boolean
	 */
	public function workorder_detail($detail, &$format, $date_format = 'Y-m-d H:i') {

		// 初始化输出
		$format = array();
		if (empty($detail)) {
			return true;
		}

		// 格式化输出
		$format = array(
			'woid' => $detail['woid'],
			'caption' => nl2br(rhtmlspecialchars($detail['caption']))
		);

		return true;
	}

	/**
	 * 格式化操作工单日志
	 * @param array $log
	 * @param array $format (返回结果)格式化后的日志
	 * @param string $date_format
	 * @return boolean
	 */
	public function workorder_log($log, &$format, $date_format = 'Y-m-d H:i') {

		// 初始化输出
		$format = array();
		if (empty($log)) {
			return true;
		}

		// 执行操作动作名
		$log['action_name'] = '';
		$this->get_action_name($log['action_name'], $log['action']);

		// 格式化时间
		if ($date_format) {
			// 操作时间
			$log['time'] = $log['time'] ? rgmdate($log['time'], $date_format) : '';
		}

		// 格式化输出
		$format = array(
			'wologid' => $log['wologid'],
			'uid' => $log['uid'],
			'action' => $log['action'],
			'action_name' => $log['action_name'],
			'time' => $log['time'],
			'ip' => $log['ip'],
			'location' => $log['location'],
			'reason' => nl2br(rhtmlspecialchars($log['reason']))
		);

		return true;
	}

	/**
	 * 格式化接受者的信息
	 * @param array $receiver 执行人信息
	 * @param array $format (引用结果)执行人信息
	 * @param string $date_format 时间显示格式，默认: Y-m-d H:i，如果为空，则不格式化时间
	 */
	public function workorder_receiver($receiver, &$format, $date_format = 'Y-m-d H:i') {

		// 初始化输出
		$format = array();
		if (empty($receiver)) {
			return true;
		}

		// 执行状态文字描述
		$receiver['worstate_name'] = '';
		$this->get_worstate_name($receiver['worstate_name'], $receiver['worstate']);

		// 格式化时间输出
		if ($date_format) {

			// 派单时间
			$receiver['ordertime'] = $receiver['ordertime'] ? rgmdate($receiver['ordertime'], $date_format) : '';
			// 动作时间
			$receiver['actiontime'] = $receiver['actiontime'] ? rgmdate($receiver['actiontime'], $date_format) : '';
			// 完成时间
			$receiver['completetime'] = $receiver['completetime'] ? rgmdate($receiver['completetime'], $date_format) : '';
		}

		// 格式化输出
		$format = array(
			'worid' => $receiver['worid'],
			'uid' => $receiver['uid'],
			'worstate' => $receiver['worstate'],
			'worstate_name' => $receiver['worstate_name'],
			'ordertime' => $receiver['ordertime'],
			'actiontime' => $receiver['actiontime'],
			'completetime' => $receiver['completetime'],
		);

		return true;
	}

	/**
	 * 格式化工单列表数据
	 * @param array $list
	 * @param array $format_list (返回结果)格式化后的数据
	 * @param string $date_format 日期显示格式
	 * @return boolean
	 */
	public function workorder_list($list, &$format_list, $date_format = 'Y-m-d H:i') {

		// 初始化数据返回
		$format_list = array();
		if (empty($list)) {
			return true;
		}

		// 遍历给定的列表，以单条格式化
		foreach ($list as $wo) {
			$this->workorder($wo, $wo);
			if (!empty($wo)) {
				$format_list[$wo['woid']] = $wo;
			}
		}

		return true;
	}

	/**
	 * 格式化工单执行人列表信息
	 * @param array $list 列表
	 * @param array $format (引用结果)输出格式化的列表
	 * @param string $date_format 输出的时间格式
	 * @return boolean
	 */
	public function workorder_receiver_list($list, &$format_list, $date_format = 'Y-m-d H:i') {

		// 初始化数据返回
		$format_list = array();
		if (empty($list)) {
			return true;
		}

		// 遍历列表以单条格式化
		foreach ($list as $_wor) {
			$this->workorder_receiver($_wor, $_wor, $date_format);
			if (!empty($_wor)) {
				$format_list[] = $_wor;
			}
		}

		return true;
	}

	/**
	 * 格式化工单操作日志列表
	 * @param array $list 列表
	 * @param array $format_list (引用结果)格式化后的列表
	 * @param string $data_format 输出的时间格式
	 * @return boolean
	 */
	public function workorder_log_list($list, &$format_list, $data_format = 'Y-m-d H:i') {

		// 初始化数据返回
		$format_list = array();
		if (empty($list)) {
			return true;
		}

		// 遍历列表以格式化单条数据
		foreach ($list as $_log) {
			$this->workorder_log($_log, $_log, $data_format);
			if (!empty($_log)) {
				$format_list[] = $_log;
			}
		}

		return true;
	}

}

<?php
/**
 * config.php
 * 请假相关配置接口
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_askoff_get_config extends voa_c_api_askoff_base {

	private $_p_setting = array();

	public function execute() {

		$type_list = array();
		$status_list = array();

		$this->__get_status($status_list);
		$this->__get_type($type_list);

		// 输出结果
		$this->_result = array(
			'type' => $type_list,
			'status' => $status_list
		);

		return true;
	}

	/**
	 * 获取请假类型列表
	 * @param array $type_list (引用结果)
	 * @return boolean
	 */
	private function __get_type(array &$type_list) {

		// 读取请假系统配置
		$sets = $this->_p_sets;
		// 初始化结果
		$type_list = array();
		// 如果配置为空
		if (empty($sets['types']) || !is_array($sets['types'])) {
			return true;
		}
		// 遍历请假类型格式输出
		foreach ($sets['types'] as $_k => $_v) {
			$type_list[] = array(
				'id' => $_k,
				'value' => $_v
			);
		}
		unset($_k, $_v);

		return true;
	}

	/**
	 * 获取请假审批状态列表
	 * @param array $status_list (引用结果)
	 * @return boolean
	 */
	private function __get_status(array &$status_list) {

		// 初始化审批列表
		$status_list = array();
		// 格式化输出
		$status_list = array(
			array('id' => voa_d_oa_askoff::STATUS_NORMAL, 'value' => '审批中'),
			array('id' => voa_d_oa_askoff::STATUS_APPROVE, 'value' => '通过审批'),
			array('id' => voa_d_oa_askoff::STATUS_APPROVE_APPLY, 'value' => '通过并转审批'),
			array('id' => voa_d_oa_askoff::STATUS_REFUSE, 'value' => '审批未通过')
		);

		return true;
	}

}



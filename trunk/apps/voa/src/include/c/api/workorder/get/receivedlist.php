<?php
/**
 * receivedlist.php
 * 接收到的列表
 * 包含各种状态
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_workorder_get_receivedlist extends voa_c_api_workorder_abstract {

	public function execute() {

		// 请求的参数
		$fields = array(
			// 当前请求的页码
			'page' => array('type' => 'int', 'required' => true),
			// 当前页面请求的数据条数
			'limit' => array('type' => 'int', 'required' => true),
			// 请求浏览的工单类型
			'type' => array('type' => 'string', 'required' => true),
			// 请求的设备
			'device' => array('type' => 'int', 'required' => false),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}

		// 请求列表时的参数
		$request = array(
			'source' => 'received',// 获取已发送列表
			'page' => $this->_params['page'],
			'limit' => $this->_params['limit'],
			'type' => $this->_params['type'],
			'uid' => $this->_member['m_uid'],
			'admin' => false,
		);

		// 获取到的数据
		$data_list = array();

		// 载入uda获取数据
		$uda = &uda::factory('voa_uda_frontend_workorder_action_list');
		if (!$uda->output($request, $data_list)) {
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}

		// 设置输出的日期格式
		$this->_set_date_format($this->_params['device']);
		// 格式化列表输出
		$this->_format_list($data_list['list'], $data_list['list']);

		// 输出结果
		$this->_result = array(
			'total' => (int)$data_list['total'],
			'page' => (int)$data_list['page'],
			'limit' => (int)$data_list['limit'],
			'pages' => (int)$data_list['pages'],
			'list' => (array)$data_list['list']
		);

		return true;
	}

	/**
	 * 格式化工单输出列表
	 * @param array $source 待格式化的工单列表
	 * @param array $format (返回结果)格式化后的工单列表
	 * @return boolean
	 */
	protected function _format_list($source, &$format) {

		// 引入格式化类
		$uda_format = &uda::factory('voa_uda_frontend_workorder_format');
		// 格式化后的数据
		$format = array();
		// 遍历原始数据，以进行单条格式化
		foreach ($source as $_wo) {
			$uda_format->workorder($_wo, $_wo, $this->_date_format);
			$_wo = array(
				'id' => $_wo['woid'],
				'contacter' => $_wo['contacter'],
				'phone' => $_wo['phone'],
				'address' => $_wo['address'],
				'remark' => $_wo['remark'],
				'ordertime' => $_wo['ordertime']
			);
			$format[] = $_wo;
		}

		return true;
	}

}

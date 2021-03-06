<?php
/**
 * voa_c_admincp_setting_footprint_modify
 * 企业后台/系统设置/销售轨迹/更改设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_footprint_setting extends voa_c_admincp_setting_base {

	/** 轨迹类型名称最大长度，字符数 */
	private $_name_max_length = 10;
	/** 最多允许添加的类型数量 */
	private $_types_max_count = 15;

	public function execute() {

		/** 当前操作的数据表 */
		$this->_current_operation_table = 'footprint';

		/** 定义变量设置数组 */
		$this->_current_keys_setting = array(
				/*
				'perpage' => array(
						'type' => 'number',
						'id' => 'perpage',
						'name' => 'perpage',
						'comment' => '每页显示的销售轨迹主题数量',
						'title' => '每页显示销售轨迹条数',
						'max' => 30,
						'min' => 1
				),*/
				'types' => array(
						'type' => 'custom',
						'id' => 'types',
						'name' => 'types',
						'comment' => '设置销售轨迹的类型，显示顺序按正序排序、类型名称不能超过 '.$this->_name_max_length.' 个字不允许特殊字符，最多允许创建 '.$this->_types_max_count.' 个轨迹类型，字符不符或超出类型数限制则会忽略。类型编号与类型名称是系统自动对应关系，一旦确定不可更改',
						'title' => '销售轨迹类型设置',
						'custom' => array(
								'max_count' => $this->_types_max_count,//最多允许创建的类型数量
								'max_length' => $this->_name_max_length,//名字长度最大字符数
						)
				),

		);

		/** 以后动作交由 voa_c_admincp_setting_base->_after_action()方法来接管 */

		// 当前的设置信息
		$plugin_setting = voa_h_cache::get_instance()->get('plugin.'.$this->_module_plugin['cp_identifier'].'.setting', 'oa', true);

		/** 截取一个变量设置并伪造 */
		if ($this->_is_post()) {

			$_types_displayorder = $this->request->post('_types_displayorder');
			$_types_name = $this->request->post('_types_name');
			$_types_delete = $this->request->post('_types_delete');
			$_types_delete = rintval($_types_delete, true);

			if (!is_array($_types_displayorder) || !is_array($_types_name)) {
				$this->message('error', '请正确提交请求');
			}

			if (!$_types_delete || !is_array($_types_delete)) {
				$_types_delete = array();
			}

			if (!empty($plugin_setting['types']['system'])) {
				// 将系统禁止删除的值，自用户提交的数据中移除
				foreach ($plugin_setting['types']['system'] as $_key) {
					unset($_types_delete[$_key]);
				}
			}

			$_types_displayorder = rintval($_types_displayorder, true);

			/** 经过整理后包含类型编号，新提交的类型数组 */
			$types_tmp = array();
			/** 显示顺序 */
			$types_displayorder = array();
			/** 类型名称 */
			$types_name = array();
			foreach ($_types_name as $_key => $_name) {
				if (in_array($_key, $_types_delete)) {
					//已标记为删除
					continue;
				}
				if (!validator::is_int($_key) || $_key <= 0) {
					//类型id非整型 或 非正数
					continue;
				}
				if (!is_scalar($_name)) {
					//类型名称非标量
					continue;
				}
				$_name = trim($_name);
				if (empty($_name)) {
					//为空
					continue;
				}
				if (!validator::is_string_count_in_range($_name, 1, $this->_name_max_length)) {
					//长度不符合要求
					continue;
				}
				if ($_name != rhtmlspecialchars($_name)) {
					//包含特殊字符
					continue;
				}
				if (stripos(implode('"', array_values($types_name)), $_name) !== false) {
					//存在重名的
					continue;
				}
				$types_tmp[$_key] = array('key' => $_key, 'name' => $_name);
				$types_name[$_key] = $_name;
				$types_displayorder[$_key] = isset($_types_displayorder[$_key]) ? $_types_displayorder[$_key] : 99;
			}

			if (empty($types_name)) {
				$this->message('error', '请正确设置销售轨迹类型');
			}

			if (count($types_name) > $this->_types_max_count) {
				$this->message('error', '系统最多允许添加 '.$this->_types_max_count.' 个销售轨迹类型，请减少后再试');
			}

			array_multisort($types_displayorder, SORT_ASC, SORT_NUMERIC, $types_tmp);

			/** 整理后的真实类型数组，类型编号与类型名称的对应 */
			$types = array();
			foreach ($types_tmp as $_data) {
				$types[$_data['key']] = $_data['name'];
			}

			unset($types_displayorder, $types_name, $types_tmp, $_key, $_name, $_types_displayorder, $_types_name);

			/** 侵入_POST伪造，以供后续 _after_action 方法进行验证处理 */
			$_POST['types'] = array('type' => $types, 'system' => $plugin_setting['types']['system']);

		}

		$this->view->set('plugin_setting', $plugin_setting);
	}

	/**
	 * 验证变量值
	 */
	protected function _validator_setting_value() {
		$setting = $this->_current_keys_setting;
		if (isset($this->_current_change_data['perpage'])) {
			if (!validator::is_int($this->_current_change_data['perpage'])) {
				$this->message('error', $setting['perpage']['title'].' 必须为大于零的整数');
			}
			if ($this->_current_change_data['perpage'] < $setting['perpage']['min'] || $this->_current_change_data['perpage'] > $setting['perpage']['max']) {
				$this->message('error', $setting['perpage']['title'].' 应该设置为'.$setting['perpage']['min'].'到'.$setting['perpage']['max'].'之间的整数');
			}
		}
		if (isset($this->_current_change_data['types'])) {

		}
	}

}

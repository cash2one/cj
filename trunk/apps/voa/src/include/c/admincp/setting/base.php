<?php
/**
 * voa_c_admincp_setting_base
 * 企业后台 - 系统设置 - 基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_setting_base extends voa_c_admincp_base {

	/** 表单文本标题区宽度 bootstrap 2/12 */
	protected $_setting_form_left_cols = 3;
	/** 表单输入域区域宽度 bootstrap 10/12 */
	protected $_setting_form_right_cols = 9;


	/** 当前操作的数据表 */
	protected $_current_operation_table = '';
	/** 当前操作的数据表 变量配置信息 */
	protected $_current_keys_setting = array();
	/** 当前操作改变的变量值keySettings */
	protected $_current_change_data = array();
	/** 当前站点的创建时间 */
	protected $_site_created = 0;
	/** 是否为应用套件授权企业（于2014-12-12 02:40之后注册的企业） */
	protected $_is_suite_auth_site = true;

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->view->set('modifyFormActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));

		// 最高权限组创建时间即为站点启用时间
		$this->_site_created = $this->_usergroup['cag_created'];
		$this->_is_suite_auth_site = $this->_site_created > rstrtotime('2014-12-12 02:40:00');
		// 令所有人都可以使用应用套件授权服务
		$this->_is_suite_auth_site = true;
		// 如果早期用户，但未启用过应用则允许按套件授权方式启用
		if (!$this->_is_suite_auth_site) {
			// 检查是否开启了应用
			$_plugin_list = voa_h_cache::get_instance()->get('plugin', 'oa');
			$tmp = true;
			foreach ($_plugin_list as $_p) {
				if ($_p['cp_available'] > voa_d_oa_common_plugin::AVAILABLE_NEW && $_p['cp_available'] < voa_d_oa_common_plugin::AVAILABLE_NONE) {
					// 存在曾经开启的应用
					$tmp = false;
					break;
				}
			}
			$this->_is_suite_auth_site = $tmp;
		}
		// 特殊的白名单，允许使用套件授权
		if (!$this->_is_suite_auth_site) {
			$specials = array(
				'saifpartners.vchangyi.com', 'hotim.vchangyi.com', 'ctrip.vchangyi.com', 'szjswh.vchangyi.com',
				'xiaodiyi.vchangyi.com', 'xsjsavic.vchangyi.com'
			);
			if (in_array(rstrtolower($_SERVER['HTTP_HOST']), $specials)) {
				$this->_is_suite_auth_site = true;
			}
		}

		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);

		if ($this->_subop == 'servicetype') {
			// 如果是设置服务类型的，则忽略后面的操作
			return true;
		}

		/** 对应设置功能无设置项目 setting表无具体数据 */
		if (empty($this->_current_keys_setting)) {
			$this->message('error', '对不起，该功能尚无可设置项，请返回');
		}

		/** 重新组织设置变量数组 */
		$this->_current_keys_setting = $this->_reset_key_setting($this->_current_keys_setting);

		/** 提交更新 */
		if ($this->_is_post()) {

			/** 获取发生变更的变量 */
			$this->_get_change_setting();

			/** 验证数据合法性 */
			$validatorMethod = '_validator_setting_value';//_for_'.$this->_operation;
			$this->$validatorMethod();

			/** 保存变更到数据库 */
			$this->_setting_submit_save($this->_module_plugin_id);

			/** 强制更新 */
			//FIXME !!!!!!涉及多个应用更新问题
			if ($this->_module_plugin_id) {
				voa_h_cache::get_instance()->get('plugin.'.$this->_module_plugin['cp_identifier'].'.setting', 'oa', true);
			} else {
				voa_h_cache::get_instance()->get('setting', 'oa', true);
			}

			/** 提示信息 */
			$this->message('success', '设置保存操作完毕', get_referer($this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id)), false);
		}

		list($form_rows, $form_custom_fields) = $this->_show_setting_group($this->_current_keys_setting);

		/** 构造变量设置表单界面 */
		$this->view->set('formRows', $form_rows);
		/** 一些特殊格式定义的字段 */
		$this->view->set('formCustomFields', $form_custom_fields);
		$this->view->set('formLeftCols', $this->_setting_form_left_cols);
		$this->view->set('formRightCols', $this->_setting_form_right_cols);
		$this->output('setting/modify_form');
		return true;
	}

	/**
	 * 获取已更新的设置变量和值
	 * @return array
	 */
	protected function _get_change_setting() {
		/** 发生改动的变量和值对应数组 */
		$changeData = array();
		foreach ($this->_current_keys_setting as $k => $s) {
			if (isset($_POST[$k])) {
				//提交中存在此变量
				/** 提交的修改值 */
				$post_value = $this->request->post($k);
				// 如果数据类型未定义，则通过传入的参数值类型来判断
				$data_type = isset($s['data_type']) ? $s['data_type'] : is_array($post_value);
				if (!empty($data_type)) {
					//数组格式储存
					if ($s['value'] != $post_value) {
						$changeData[$k] = serialize($post_value);
					}
				} else {
					//非数组
					if ($s['value'] != $post_value) {
						$changeData[$k] = $post_value;
					}
				}
			} else {
				//提交为空，应对checkbox类型的值
				/** 提交的修改值 */
				$post_value = '';
				if ($s['data_type']) {
					//数组格式储存
					if ($s['value'] != $post_value) {
						$changeData[$k] = serialize($post_value);
					}
				} else {
					//非数组
					if ($s['value'] != $post_value) {
						$changeData[$k] = $post_value;
					}
				}
			}
		}
		if (empty($changeData)) {
			$this->message('error', '设置未发生改动无须提交');
		}
		return $this->_current_change_data = $changeData;
	}

	/**
	 * 更新变量数据到数据库
	 */
	protected function _setting_submit_save($cp_pluginid) {
		return $this->_service_single($this->_current_operation_table.'_setting', $cp_pluginid, 'update_setting', $this->_current_change_data);
	}

	/**
	 * 移除数组键名的前缀
	 * @param string $prefix
	 * @param array $array
	 * @return array
	 */
	protected function _remove_prefix($prefix, $array) {
		if (is_scalar($array)) {
			return str_replace($prefix, '', $array);
		} else {
			$new_array = array();
			foreach ($array as $k => $v) {
				$new_array[$this->_remove_prefix($prefix, $k)] = is_scalar($v) ? $v : $this->_remove_prefix($prefix, $v);
			}
			unset($array);
			return $new_array;
		}
	}

	/**
	 * 为设置变量获取值
	 * @param array $key_etting
	 * @param string $table
	 * @param boolean $cp_pluginid
	 */
	protected function _reset_key_setting($key_setting, $table = NULL, $cp_pluginid = FALSE) {
		if ($table === NULL) {
			$table = $this->_current_operation_table;
		}
		if ($cp_pluginid === FALSE) {
			$cp_pluginid = $this->_module_plugin_id;
		}

		$db_key_setting = $this->_get_setting_values($table, $cp_pluginid);
		foreach ($key_setting AS $k => $s) {
			if (isset($db_key_setting[$k])) {
				$_value = $db_key_setting[$k]['value'];
				if ($db_key_setting[$k]['type']) {
					$_value = @unserialize($_value);
					if (!is_array($_value)) {
						$_value = array();
					}
				}
				$key_setting[$k]['value'] = $_value;
				$key_setting[$k]['data_type'] = $db_key_setting[$k]['type'];
				unset($_value);
			} else {
				$key_setting[$k]['value'] = '';
				//$key_setting[$k]['data_type'] = 0;
			}
		}
		unset($db_key_setting);

		return $key_setting;
	}

	/**
	 * 取得某个设置表的值列表
	 * @param string $table
	 */
	protected function _get_setting_values($table, $cp_pluginid) {
		return $this->_service_single($table.'_setting', $cp_pluginid, 'fetch_all_setting', true);
	}

	/**
	 * 构造表单输入域，多组
	 * @param array $rows
	 * @return array(string, array)
	 */
	protected function _show_setting_group($rows) {
		$form = '';
		$custom_fields = array();
		foreach ($rows AS $param) {
			if (empty($param['type']) || $param['type'] == 'custom') {
				$custom_fields[] = $param;
			} else {
				$form .= $this->_show_setting_single((!empty($param['type']) ? $param['type'] : 'text'), $param);
			}
		}
		return array($form, $custom_fields);
	}

	/**
	 * 单行输入域
	 * @param string $type
	 * @param unknown $extra
	 * @return string
	 */
	protected function _show_setting_single($type = 'input', $extra = array()) {
		$input_comment = !empty($extra['comment']) ? '<p class="help-block">'.$extra['comment'].'</p>' : '';
		switch ($type) {
			case 'text':
			case 'tel':
			case 'date':
			case 'email':
			case 'search':
			case 'url':
			case 'number':
			case 'color':
				return $this->_form_input($type, $input_comment, $extra);
			break;
			case 'textarea':
				return $this->_form_textarea($type, $input_comment, $extra);
			break;
			case 'yesorno':
				return $this->_form_yesorno($type, $input_comment, $extra);
			break;
			case 'memberselect':
				return $this->_form_memberselect($type, $input_comment, $extra);
			break;
			case 'string':
				return $this->_form_string($type, $input_comment, $extra);
			break;
			default:
				return $this->_form_input($type, $input_comment, $extra);
			break;
		}
	}

	/**
	 * 构造文本框输入域
	 * @param string $type
	 * @param array $extra
	 * @return string
	 */
	protected function _form_input($type, $input_comment = '', $extra = array()) {
		/** label标签文字样式类名 */
		$labelTextClassName = '';
		!isset($extra['value']) && $extra['value'] = '';
		/** input标签属性 */
		$attributes = array();
		$attributes[] = 'type="'.(!empty($extra['type']) ? $extra['type'] : 'text').'"';
		$attributes[] = 'class="form-control"';
		$attributes[] = 'id="'.$extra['id'].'"';
		$attributes[] = 'name="'.$extra['name'].'"';
		$attributes[] = 'value="'.rhtmlspecialchars($extra['value']).'"';
		if (!empty($extra['placeholder'])) {
			$attributes[] = 'placeholder="'.$extra['placeholder'].'"';
		}
		if (!empty($extra['maxlength'])) {
			$attributes[] = 'maxlength="'.$extra['maxlength'].'"';
		}
		if (!empty($extra['required'])) {
			$attributes[] = 'required="required"';
			$title .= ' *';
			$labelTextClassName = ' text-danger';
		}
		if (!empty($extra['max'])) {
			$attributes[] = 'max="'.$extra['max'].'"';
		}
		if (!empty($extra['min'])) {
			$attributes[] = 'min="'.$extra['min'].'"';
		}
		$attributes[] = '/';
		/** 构造表单输入域 */
		$form_input = '<input '.implode(' ', $attributes).'>';

		return <<<HTML
	<div class="form-group font12">
		<label for="{$extra['id']}" class="col-sm-{$this->_setting_form_left_cols} control-label{$labelTextClassName} text-right">{$extra['title']}</label>
		<div class="col-sm-{$this->_setting_form_right_cols}">
			{$form_input}{$input_comment}
		</div>
	</div>
HTML;
	}

	/**
	 * 构造静态文本显示区
	 * @param string $type
	 * @param string $input_comment
	 * @param array $extra
	 * @return string
	 */
	protected function _form_string($type, $input_comment = '', $extra = array()) {

		// label标签文字样式类名
		$labelTextClassName = '';
		!isset($extra['value']) && $extra['value'] = '';
		// input标签属性
		$attributes = array();
		$attributes[] = 'class="form-control-static"';
		$attributes[] = 'id="'.$extra['id'].'"';

		$form_input = '<div '.implode(' ', $attributes).'>'.rhtmlspecialchars($extra['value']).'</div>';

		return <<<HTML
	<div class="form-group font12">
		<label for="{$extra['id']}" class="col-sm-{$this->_setting_form_left_cols} control-label{$labelTextClassName} text-right">{$extra['title']}</label>
		<div class="col-sm-{$this->_setting_form_right_cols}">
			{$form_input}{$input_comment}
		</div>
	</div>
HTML;
	}

	/**
	 * 构造文本区输入
	 * @param string $type
	 * @param string $input_comment
	 * @param array $extra
	 * @return string
	 */
	protected function _form_textarea($type, $input_comment = '', $extra = array()) {

		/** label标签文字样式类名 */
		$labelTextClassName = '';
		if (!empty($extra['required'])) {
			$attributes[] = 'required="required"';
			$title .= ' *';
			$labelTextClassName = ' text-danger';
		}

		/** textarea标签属性 */
		$attributes = array();
		$attributes[] = 'class="form-control"';
		$attributes[] = 'id="'.$extra['id'].'"';
		$attributes[] = 'name="'.$extra['name'].'"';
		$attributes[] = 'rows="'.(!empty($extra['rows']) ? $extra['rows'] : 3).'"';
		/** 构造表单输入域 */
		$value = '';
		if (isset($extra['value'])) {
			$extra['value'] = rhtmlspecialchars($extra['value']);
			$value = is_array($extra['value']) ? implode("\n", $extra['value']) : $extra['value'];
		}
		$form_input = '<textarea '.implode(' ', $attributes).'>'.$value.'</textarea>';

		return <<<HTML
	<div class="form-group font12">
		<label for="{$extra['id']}" class="col-sm-{$this->_setting_form_left_cols} control-label{$labelTextClassName} text-right">{$extra['title']}</label>
		<div class="col-sm-{$this->_setting_form_right_cols}">
			{$form_input}{$input_comment}
		</div>
	</div>
HTML;
	}

	/**
	 * 构造单选框按钮
	 * @param string $type
	 * @param string $input_comment
	 * @param array $extra
	 * @return string
	 */
	protected function _form_radio($type, $input_comment = '', $extra = array()) {
		/** label标签文字样式类名 */
		$labelTextClassName = '';
		if (!empty($extra['required'])) {
			$attributes[] = 'required="required"';
			$title .= ' *';
			$labelTextClassName = ' text-danger';
		}
		/** radio组的外围样式类名 */
		$radioGroupClassName = 'radio-inline';
		if (!empty($extra['radio_class_name'])) {
			$radioGroupClassName = $extra['radio_class_name'];
		}
		$form_input = '';
		foreach ($extra['options'] as $v) {
			$form_input .= '<label class="'.$radioGroupClassName.'"><input type="radio" name="'.$extra['name'].'" value="'.$v['value'].'"'
				.(isset($extra['value']) && $extra['value'] == $v['value'] ? ' checked="checked"' : '')
				.' /> '.$v['text'].'</label>';
		}
		return <<<HTML
	<div class="form-group font12">
		<label class="col-sm-{$this->_setting_form_left_cols} control-label{$labelTextClassName} text-right">{$extra['title']}</label>
		<div class="col-sm-{$this->_setting_form_right_cols}">
			{$form_input}
			{$input_comment}
		</div>
	</div>
HTML;
	}

	/**
	 * 构造复选框按钮
	 * @param string $type
	 * @param string $input_comment
	 * @param array $extra
	 * @return string
	 */
	protected function _form_checkbox($type, $input_comment = '', $extra = array()) {
		/** label标签文字样式类名 */
		$labelTextClassName = '';
		if (!empty($extra['required'])) {
			$attributes[] = 'required="required"';
			$title .= ' *';
			$labelTextClassName = ' text-danger';
		}
		/** checkbox组的外围样式类名 */
		$checkboxGroupClassName = 'checkbox-inline';
		if (!empty($extra['checkbox_class_name'])) {
			$checkboxGroupClassName = $extra['checkbox_class_name'];
		}
		if (!isset($extra['value'])) {
			$extra['value'] = array();
		} elseif (!is_array($extra['value'])) {
			$extra['value'] = explode(',', $extra['value']);
		}
		$form_input = '';
		foreach ($extra['options'] as $v) {
			$form_input .= '<label class="'.$checkboxGroupClassName.'"><input type="checkbox" name="'.$extra['name'].'['.$v['value'].']" value="'.$v['value'].'"'
				.(in_array($v['value'], $extra['value']) ? ' checked="checked"' : '')
				.' /> '.$v['text'].'</label>';
		}
		return <<<HTML
	<div class="form-group font12">
		<label class="col-sm-{$this->_setting_form_left_cols} control-label{$labelTextClassName} text-right">{$extra['title']}</label>
		<div class="col-sm-{$this->_setting_form_right_cols}">
			{$form_input}
			{$input_comment}
		</div>
	</div>
HTML;
	}

	/**
	 * 构造 是 和 否 类型的单选框按钮
	 * @param string $type
	 * @param string $input_comment
	 * @param array $extra
	 * @return string
	 */
	protected function _form_yesorno($type, $input_comment = '', $extra = array()) {
		$extra['options'] = array(
				array('value' => '0', 'text' => '否'),
				array('value' => '1', 'text' => '是')
		);
		return $this->_form_radio($type, $input_comment, $extra);
	}

	/**
	 * 复选框选择用户
	 * @param string $type
	 * @param string $input_comment
	 * @param array $extra
	 * @return string
	 */
	protected function _form_memberselect($type, $input_comment = '', $extra = array()) {
		$extra['options'] = array();
		foreach ($this->_service_single('member', 'fetch_all', 0, 0) as $m) {
			$extra['options'][]	=	array('value' => $m['m_uid'], 'text' => $m['m_username']);
		}
		return $this->_form_checkbox($type, $input_comment, $extra);
	}

}

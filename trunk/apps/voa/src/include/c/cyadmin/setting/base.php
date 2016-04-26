<?php
/**
 * voa_c_cyadmin_setting_base
 * 主站后台/系统设置/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_setting_base extends voa_c_cyadmin_base {

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


	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->view->set('form_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop));
		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);

		// 对应设置功能无设置项目 setting表无具体数据
		if (empty($this->_current_keys_setting)) {
			$this->message('error', '对不起，该功能尚无可设置项，请返回');
		}

		// 重新组织设置变量数组
		$this->_current_keys_setting = $this->_reset_key_setting($this->_current_keys_setting);

		// 提交更新
		if ($this->_is_post()) {

			// 获取发生变更的变量
			$this->_get_change_setting();

			// 验证数据合法性
			$validatorMethod = '_validator_setting_value';
			$this->$validatorMethod();

			// 保存变更到数据库
			$this->_setting_submit_save();

			// 强制更新
			$cache_key = $this->_module == 'setting' ? 'setting' : $this->_module;
			voa_h_cache::get_instance()->get($cache_key, 'cyadmin', true);

			// 提示信息
			$this->message('success', '设置保存操作完毕', get_referer($this->cpurl($this->_module, $this->_operation, $this->_subop)), false);
		}

		list($form_rows, $form_custom_fields) = $this->_show_setting_group($this->_current_keys_setting);

		// 构造变量设置表单界面
		$this->view->set('form_rows', $form_rows);
		// 一些特殊格式定义的字段
		$this->view->set('form_custom_fields', $form_custom_fields);
		$this->view->set('form_left_cols', $this->_setting_form_left_cols);
		$this->view->set('form_right_cols', $this->_setting_form_right_cols);
		$this->output('cyadmin/setting/modify_form');
		return true;
	}

	/**
	 * 获取已更新的设置变量和值
	 * @return array
	 */
	protected function _get_change_setting() {

		// 发生改动的变量和值对应数组
		$change_data = array();

		foreach ($this->_current_keys_setting as $k => $s) {
			if (isset($_POST[$k])) {
				// 提交中存在此变量

				// 提交的修改值
				$post_value = $this->request->post($k);
				if (!empty($s['data_type'])) {
					// 数组格式储存
					if ($s['value'] != $post_value) {
						$change_data[$k] = serialize($post_value);
					}
				} else {
					// 非数组
					if ($s['value'] != $post_value) {
						$change_data[$k] = $post_value;
					}
				}
			} else {
				// 提交为空，应对checkbox类型的值

				// 提交的修改值
				$post_value = '';
				if ($s['data_type']) {
					// 数组格式储存
					if ($s['value'] != $post_value) {
						$change_data[$k] = serialize($post_value);
					}
				} else {
					// 非数组
					if ($s['value'] != $post_value) {
						$change_data[$k] = $post_value;
					}
				}
			}
		}
		if (empty($change_data)) {
			$this->message('error', '设置未发生改动无须提交');
		}
		return $this->_current_change_data = $change_data;
	}

	/**
	 * 更新变量数据到数据库
	 */
	protected function _setting_submit_save() {
		$serv = &service::factory('voa_s_cyadmin_'.$this->_current_operation_table.'_setting', array('pluginid' => 0));
		return $serv->update_setting($this->_current_change_data);
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
	 */
	protected function _reset_key_setting($key_setting, $table = NULL) {
		if ($table === NULL) {
			$table = $this->_current_operation_table;
		}

		$db_key_setting = $this->_get_setting_values($table);
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
				$key_setting[$k]['data_type'] = 0;
			}
		}
		unset($db_key_setting);

		return $key_setting;
	}

	/**
	 * 取得某个设置表的变量值列表
	 * @param string $table
	 */
	protected function _get_setting_values($table) {
		$serv = &service::factory('voa_s_cyadmin_'.$table.'_setting', array('pluginid' => 0));
		return $serv->fetch_all_setting();
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
		// label标签文字样式类名
		$labelTextClassName = '';
		!isset($extra['value']) && $extra['value'] = '';
		// input标签属性
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
		// 构造表单输入域
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
	 * 构造文本区输入
	 * @param string $type
	 * @param string $input_comment
	 * @param array $extra
	 * @return string
	 */
	protected function _form_textarea($type, $input_comment = '', $extra = array()) {

		// label标签文字样式类名
		$labelTextClassName = '';
		if (!empty($extra['required'])) {
			$attributes[] = 'required="required"';
			$title .= ' *';
			$labelTextClassName = ' text-danger';
		}

		// textarea标签属性
		$attributes = array();
		$attributes[] = 'class="form-control"';
		$attributes[] = 'id="'.$extra['id'].'"';
		$attributes[] = 'name="'.$extra['name'].'"';
		$attributes[] = 'rows="'.(!empty($extra['rows']) ? $extra['rows'] : 3).'"';
		// 构造表单输入域
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
		// label标签文字样式类名
		$labelTextClassName = '';
		if (!empty($extra['required'])) {
			$attributes[] = 'required="required"';
			$title .= ' *';
			$labelTextClassName = ' text-danger';
		}
		// radio组的外围样式类名
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
		// label标签文字样式类名
		$labelTextClassName = '';
		if (!empty($extra['required'])) {
			$attributes[] = 'required="required"';
			$title .= ' *';
			$labelTextClassName = ' text-danger';
		}
		// checkbox组的外围样式类名
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
		$serv_member = &service::factory('voa_s_oa_member');
		foreach ($serv_member->fetch_all(0, 0) as $m) {
			$extra['options'][]	=	array('value' => $m['m_uid'], 'text' => $m['m_username']);
		}
		return $this->_form_checkbox($type, $input_comment, $extra);
	}

}

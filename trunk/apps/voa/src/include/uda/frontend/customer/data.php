<?php
/**
 * voa_uda_frontend_customer_data
 * 统一数据访问/客户应用/客户信息操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_customer_data extends voa_uda_frontend_customer_abstract {
	// 特殊条件参数
	protected $_special_conds = array();

	/**
	 * 构造方法
	 * @param array $ptname 插件和表格名称
	 * + string plugin 插件名称
	 * + string table 表格名称
	 * + string classes 分类数据
	 */
	public function __construct($ptname = null) {

		parent::__construct($ptname);
		$this->_classes = $ptname['classes'];
		$this->_tablecols = $ptname['columns'];
		$this->_tablecolopts = $ptname['options'];
		// 特殊条件参数
		if (!empty($ptname['conds'])) {
			$this->_special_conds = $ptname['conds'];
		}
	}

	/**
	 * 获取客户列表
	 * @param array $gp 请求数据
	 * @param mixed $page_option 分页参数
	 * @param array &$list 客户列表
	 * @return boolean
	 */
	public function list_all($gp, $page_option, &$list, &$total) {

		// 查询的条件
		$fields = array(
			array('dataid', self::VAR_INT, null, null, true),
			array('classid', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields, $gp)) {
			return false;
		}

		// 获取搜索关键字
		if (!empty($gp['query'])) {
			$query = (string)$gp['query'];
			$conds['truename LIKE ?'] = "%{$query}%";
		}
		$conds['tid'] = $this->_table['tid'];
		$conds = array_merge($conds, $this->_special_conds);
		// 读取数据
		$t_data = new voa_d_oa_customer_data();
		if (!$list = $t_data->list_by_conds($conds, $page_option, array('updated' => 'desc'))) {
			return true;
		}

		// 重新整理数据
		foreach ($list as $_k => &$_v) {
			// 数据转换
			$this->translate_f($_v);
			unset($_v['message']);
			$_v['avatar'] = voa_h_attach::attachment_url($_v['avatarid']);
		}

		$t_data->reset();
		$total = $t_data->count_by_conds($conds);

		return true;
	}

	/**
	 * 根据 dataid 获取表格列属性信息
	 * @param int $dataid 表格id
	 * @param array &$data 表格列属性信息
	 * @return boolean
	 */
	public function get_one($dataid, &$data) {

		$conds = $this->_special_conds;
		$conds['dataid'] = $dataid;
		// 读取数据
		$t = new voa_d_oa_customer_data();
		// 如果数据不存在
		if (!$data = $t->get_by_conds($conds)) {
			$this->set_errmsg(voa_errcode_oa_customer::CUSTOMER_DATA_IS_NOT_EXIST);
			return false;
		}

		// 数据转换
		$this->translate_f($data);
		$data['avatar'] = voa_h_attach::attachment_url($data['avatarid']);

		return true;
	}

	/**
	 * 更新当前表格列属性信息
	 * @param array $member 用户信息
	 * @param array $gp 数据
	 * @param int $dataid 表格id
	 * @throws service_exception
	 * @return boolean
	 */
	public function update($member, $gp, $dataid) {

		// 取数据
		$dataid = (int)$dataid;
		$t = new voa_d_oa_customer_data();
		if (!$cur_data = $t->get($dataid)) {
			$this->set_errmsg(voa_errcode_oa_customer::CUSTOMER_DATA_IS_NOT_EXIST);
			return false;
		}

		// 提取数据
		$data = array();
		$diys = (array)unserialize($cur_data['diys']);
		if (!$this->__parse_gp($gp, $diys, $data, true)) {
			return false;
		}

		$data['diys'] = serialize($diys);
		try {
			// 头像 id 有变化, 则删除旧附件
			if (isset($data['avatarid']) && $cur_data['avatarid'] != $data['avatarid']) {
				$serv_att = &service::factory('voa_s_oa_common_attachment');
				$serv_att->delete_by_ids($cur_data['avatarid']);
			}

			$t->update($dataid, $data);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

	/**
	 * 新增表格列属性
	 * @param array $member 用户信息
	 * @param array $gp 数据
	 * @throws service_exception
	 * @return boolean
	 */
	public function add($member, $gp, &$data) {

		// 提取数据
		$data = array(
			'uid' => $member['m_uid'],
			'tid' => $this->_table['tid']
		);
		$diys = array();
		if (!$this->__parse_gp($gp, $diys, $data)) {
			return false;
		}

		$data = array_merge($data, $this->_special_conds);
		$data['diys'] = serialize($diys);
		// 开始更新
		$dataid = (int)$dataid;
		$t = new voa_d_oa_customer_data();

		try {
			$t->beginTransaction();

			$data = $t->insert($data);

			$t->commit();
		} catch (Exception $e) {
			$t->rollBack();
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		// 数据转换
		$this->translate_f($data);

		return true;
	}

	/**
	 * 删除表格数据
	 * @param int $dataid 数据id
	 * @param array $data 客户数据
	 * @throws service_exception
	 * @return boolean
	 */
	public function delete($dataid, $data = null) {

		// 初始化操作类
		$t_data = new voa_d_oa_customer_data();
		// 读取数据
		if (empty($data) && !$data = $t_data->get($dataid)) {
			return true;
		}

		try {
			$t_data->beginTransaction();

			// 删除附件数据
			if (0 < $data['avatarid']) {
				$serv_att = &service::factory('voa_s_oa_common_attachment');
				$serv_att->delete_by_ids($data['avatarid']);
			}

			// 删除用户数据
			$t_data->delete($dataid);

			$t_data->commit();
		} catch (Exception $e) {
			$t_data->rollBack();
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

	/**
	 * 数据转换
	 * @param unknown $data
	 * @return boolean
	 */
	public function translate_f(&$data) {

		// 解析 diy 数据
		$this->parse_diy($data);

		// 字段类型分类
		$ac = array('attach', 'checkbox');
		$ifrs = array('int', 'float', 'radio', 'select');

		// 先取出所有附件id
		$at_ids = array();
		foreach ($this->_tablecols as $_col) {
			$f_alias = '_'.$_col['tc_id'];
			$field = $_col['field'];
			// 如果字段不存在
			if (empty($data[$field])) {
				// 如果存在别名
				if (empty($data[$f_alias])) {
					$val = isset($data[$_col['fieldalias']]) ? $data[$_col['fieldalias']] : '';
				} else {
					$val = $data[$f_alias];
				}
			} else { // 如果存在
				$val = $data[$field];
			}

			// 如果值为空
			if (empty($val)) {
				continue;
			}

			// 如果是附件
			if ('attach' == $_col['ct_type']) {
				$at_ids = array_merge($at_ids, explode(',', $val));
			}
		}

		// 获取附件信息
		$attachs = array();
		$this->get_att_by_ids($at_ids, $attachs);

		// 遍历所有属性
		foreach ($this->_tablecols as $_col) {
			$f_alias = '_'.$_col['tc_id'];
			$field = $_col['field'];
			// 如果字段不存在
			if (empty($data[$field])) {
				// 如果存在别名
				if (empty($data[$f_alias])) {
					$val = isset($data[$_col['fieldalias']]) ? $data[$_col['fieldalias']] : '';
				} else {
					$val = $data[$f_alias];
				}
			} else { // 如果存在
				$val = $data[$field];
			}

			// 如果是附件
			if ('attach' == $_col['ct_type'] && $val) {
				$data[$f_alias] = array();
				$this->_get_atts($data[$f_alias], $attachs, $val);
			}

			// 如果字段别名不为空
			if (!empty($_col['fieldalias'])) {
				if (isset($data[$field])) { // 如果固定字段有值
					$data[$_col['fieldalias']] = $data[$field];
					unset($data[$field], $data[$f_alias]);
				} else { // 其他字段
					$data[$_col['fieldalias']] = $data[$f_alias];
					unset($data[$f_alias]);
				}
			}

			// 给定默认值
			if (empty($val)) {
				if (!empty($_col['fieldalias'])) { // 如果有字段别名
					$data[$_col['fieldalias']] = in_array($_col['ct_type'], $ac) ? array() : (in_array($_col['ct_type'], $ifrs) ? '0' : '');
				} elseif (!empty($field)) { // 如果是固定字段
					$data[$field] = in_array($_col['ct_type'], $ac) ? array() : (in_array($_col['ct_type'], $ifrs) ? '0' : '');
				} else { // 其他自定义字段
					$data[$f_alias] = in_array($_col['ct_type'], $ac) ? array() : (in_array($_col['ct_type'], $ifrs) ? '0' : '');
				}
			} elseif (in_array($_col['ct_type'], array('int', 'float', 'radio', 'select'))) { // 如果是非字串类型
				if (!empty($_col['fieldalias'])) { // 如果有字段别名
					$data[$_col['fieldalias']] = (string)$data[$_col['fieldalias']];
				} elseif (!empty($field)) { // 如果是固定字段
					$data[$field] = (string)$data[$field];
				} else { // 其他自定义字段
					$data[$f_alias] = (string)$data[$f_alias];
				}
			}
		}

		return true;
	}

	/**
	 * 根据 id 获取附件信息获取
	 * @param array $attachs 附件结果集
	 * @param array $srcs 附件源数据
	 * @param mixed $ids 附件id
	 * @return boolean
	 */
	protected function _get_atts(&$attachs, $srcs, $ids) {

		// 如果非数字
		if (!is_array($ids)) {
			$ids = explode(',', $ids);
		}

		// 遍历
		foreach ($ids as $_id) {
			if (empty($srcs[$_id])) {
				continue;
			}

			$attachs[] = $srcs[$_id];
		}

		return true;
	}

	/**
	 * 从 G/P 中提取数据
	 * @param array $gp 请求数据
	 * @param array $diys diy 数据
	 * @param array $data 数据结果
	 * @param boolean $update 是否更新操作
	 * @return boolean
	 */
	private function __parse_gp($gp, &$diys, &$data, $update = false) {

		$fields = array(
			array('classid', self::VAR_INT, 'chk_classid', null, $update),
			array('avatarid', self::VAR_INT, null, null, $update)
		);
		// 提取数据
		if (!$this->extract_field($data, $fields, $gp)) {
			return false;
		}

		return $this->__parse_by_column($diys, $data, $gp, $this->_tablecols, $update);
	}

	/**
	 * 根据列信息从 G/P 中提取数据
	 * @param array $gp 请求数据
	 * @param array $diys diy 数据
	 * @param array $data 数据结果
	 * @param array $columns 列数组数据
	 * @param boolean $update 是否更新操作
	 * @return boolean
	 */
	private function __parse_by_column(&$diys, &$data, $gp, $columns, $update = false) {

		// 遍历自定义字段
		foreach ($columns as $_v) {
			$f_alias = '_'.$_v['tc_id'];
			// 如果自定义字段信息不存在
			if (!isset($gp[$_v['field']]) && !isset($gp[$f_alias]) && !isset($gp[$_v['fieldalias']])) {
				// 如果该字段必填, 则
				if ($_v['required'] && !$update) {
					$this->set_errmsg(voa_errcode_oa_customer::CUSTOMER_FIELD_REQUIRED, $_v['fieldname']);
					return false;
				}

				continue;
			}

			// 根据类型格式化数据
			$func_chk = '_chk_string';
			if (in_array($_v['ct_type'], array('radio', 'select'))) {
				$func_chk = '_chk_radio_select';
			} elseif ('checkbox' == $_v['ct_type']) {
				$func_chk = '_chk_checkbox';
			} elseif (in_array($_v['ct_type'], array('int', 'float'))) {
				$func_chk = '_chk_int_float';
			} elseif ('attach' == $_v['ct_type']) {
				$func_chk = '_chk_attach';
			}

			// 按类型检查
			$val = empty($gp[$_v['field']]) ? (empty($gp[$f_alias]) ? $gp[$_v['fieldalias']] : $gp[$f_alias]) : $gp[$_v['field']];
			$opts = array_key_exists($_v['tc_id'], $this->_tablecolopts['p2c']) ? $this->_tablecolopts['p2c'][$_v['tc_id']] : array();
			if (!$this->$func_chk($_v, $opts, $val)) {
				return false;
			}

			// 字段为空并且该字段必填, 则
			if (empty($val) && $_v['required']) {
				$this->set_errmsg(voa_errcode_oa_customer::CUSTOMER_FIELD_REQUIRED, $_v['fieldname']);
				return false;
			}

			// 如果有正则表达式
			if ($val && $_v['reg_exp'] && !preg_match("/".$_v['reg_exp']."/", $val)) {
				$this->set_errmsg(voa_errcode_oa_customer::CUSTOMER_FIELD_FORMAT_ERR, $_v['fieldname']);
				return false;
			}

			// 如果字段名称为空, 则
			if (preg_match("/^\_\d+$/", $_v['field'])) {
				$diys[$_v['field']] = (string)$val;
			} else {
				$data[$_v['field']] = (string)$val;
			}
		}

		return true;
	}

	/**
	 * 对数值进行整型/浮点型验证
	 * @param array $coltype 自定义字段类型
	 * @param mixed $val 字段值
	 * @return boolean
	 */
	protected function _chk_int_float($coltype, $options, &$val) {

		if ('int' == $coltype['ct_type']) { // 如果为整型
			$val = (int)$val;
		} elseif ('float' == $coltype['ct_type']) { // 如果为浮点型
			$p_s = explode('.', $val);
			$val = ((int)$p_s[0]).'.'.(isset($p_s[1]) ? (int)$p_s[1] : '0');
		}

		// 判断值是否处在定义的范围内
		if ((0 < $coltype['min'] && $coltype['min'] > $val) || (0 < $coltype['max'] && $coltype['max'] < $val)) {
			$this->set_errmsg(voa_errcode_oa_customer::CUSTOMER_FIELD_VALUE_ERR, $coltype['fieldname'], $coltype['min'], $coltype['max']);
			return false;
		}

		return true;
	}

	/**
	 * 对单选/下拉选择数据进行验证
	 * @param array $coltype 自定义字段类型
	 * @param array $options 字段选项
	 * @param mixed $val 字段值
	 * @return boolean
	 */
	protected function _chk_radio_select($coltype, $options, &$val) {

		$val = is_array($val) ? array_pop($val) : $val;
		$val = (int)$val;

		// 判断值是否为已定义的选项
		if (!empty($val) && !array_key_exists($val, $options)) {
			$this->set_errmsg(voa_errcode_oa_customer::CUSTOMER_FIELD_VALUE_INVALID, $coltype['fieldname']);
			return false;
		}

		return true;
	}

	/**
	 * 对复选框数据进行验证
	 * @param array $coltype 自定义字段类型
	 * @param array $options 字段选项
	 * @param mixed $val 字段值
	 * @return boolean
	 */
	protected function _chk_checkbox($coltype, $options, &$val) {

		$chk_vs = array();
		// 遍历所有选项
		$val = (array)$val;
		foreach ($val as $_opt) {
			// 如果选项不在已定义的范围内
			if (!array_key_exists($_opt, $options)) {
				continue;
			}

			$chk_vs[] = $_opt;
		}

		// 判断选项数是否正确
		$len = count($chk_vs);
		if ((0 < $coltype['min'] && $len < $coltype['min']) || (0 < $coltype['max'] && $len > $coltype['max'])) {
			$this->set_errmsg(voa_errcode_oa_customer::CUSTOMER_FIELD_CHECKED_ERR, $coltype['fieldname'], $coltype['min'], $coltype['max']);
			return false;
		}

		$val = implode(',', $chk_vs);

		return true;
	}

	protected function _chk_attach($coltype, $options, &$val) {

		$ids = array();
		foreach ($val as $_opt) {
			$_opt = (int)$_opt;
			if (empty($_opt)) {
				continue;
			}

			$ids[] = $_opt;
		}

		// 判断选项数是否正确
		$len = count($ids);
		if ((0 < $coltype['min'] && $len < $coltype['min']) || (0 < $coltype['max'] && $len > $coltype['max'])) {
			$this->set_errmsg(voa_errcode_oa_goods::GOODS_FIELD_CHECKED_ERR, $coltype['fieldname'], $coltype['min'], $coltype['max']);
			return false;
		}

		$val = implode(',', $ids);

		return true;
	}

	/**
	 * 对字串进行验证
	 * @param array $coltype 自定义字段类型
	 * @param array $options 字段选项
	 * @param mixed $val 字段值
	 * @return boolean
	 */
	protected function _chk_string($coltype, $options, &$val) {

		$val = (string)$val;
		$len = strlen($val);
		// 判断数据长度是否在已定义的范围内
		if ((0 < $coltype['min'] && $len < $coltype['min']) || (0 < $coltype['max'] && $len > $coltype['max'])) {
			$this->set_errmsg(voa_errcode_oa_customer::CUSTOMER_FIELD_LENGTH_ERR, $coltype['fieldname'], $coltype['min'], $coltype['max']);
			return false;
		}

		return true;
	}

	/**
	 * 检查分类id是否正确
	 * @param string $id 分类id
	 * @param string $err
	 * @return boolean
	 */
	public function chk_classid($id, $err = null) {

		// 如果 classid 不为空, 并且分类不存在时
		if (!empty($id) && !array_key_exists($id, $this->_classes)) {
			$this->set_errmsg(voa_errcode_oa_customer::CUSTOMER_CLASSID_ERR);
			return false;
		}

		return true;
	}

	/**
	 * 解析 diy 信息
	 * @param array $data 数据数组
	 * @return boolean
	 */
	public function parse_diy(&$data) {

		$diys = (array)unserialize($data['diys']);
		unset($data['diys']);
		if (is_array($diys)) {
			$data = array_merge($diys, $data);
		}

		return true;
	}

	public function get_att_by_ids($ids, &$attachs) {

		// 如果不是数组, 则按 , 切开
		if (!is_array($ids)) {
			$ids = explode(',', $ids);
		}

		// 读取附件
		$serv_att = &service::factory('voa_s_oa_common_attachment');
		$atts = $serv_att->fetch_by_ids($ids);

		// 组织返回数据
		foreach ($atts as $_at) {
			$attachs[$_at['at_id']] = array(
				'id' => $_at['at_id'],
				'filename' => $_at['at_filename'],
				'filesize' => $_at['at_filesize'],
				'description' => $_at['at_description'],
				'isimage' => $_at['at_isimage'],
				'width' => $_at['at_width'],
				'thumb' => $_at['at_thumb'],
				'url' => voa_h_attach::attachment_url($_at['at_id'])
			);
		}

		return true;
	}

}


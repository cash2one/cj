<?php
/**
 * type.php
 * service/公共模块/场所管理/类型表相关
 * validator_xx 验证方法，出错抛错
 * format_xx 格式化方法，引用返回结果
 * get_xx 获取数据
 * list_xxx 读取数据列表相关
 * ... 其他自定方法
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_s_oa_common_place_type extends voa_s_oa_common_place_abstract {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 整理数据原型以利于储存数据库(比如存在数组数据，转换成serilaize)
	 * @param array $data (引用结果)
	 * @return boolean
	 */
	public function reset_place_type(&$data) {

		if (isset($data['levels'])) {
			if (empty($data['levels']) || !is_array($data['levels'])) {
				$this->validator_place_type_level_names($data['levels']);
			}
			$data['levels'] = serialize($data['levels']);
		}

		return true;
	}

	/**
	 * 获取类型缓存
	 * @param boolean $update 是否强制读取更新
	 * @return array
	 */
	public function get_place_type_cache($update = false) {

		return voa_h_cache::get_instance()->get($this->p_id.'_type', 'oa', $update);
	}

	/**
	 * 读取指定的类型ID的类型数据
	 * @param number $placetypeid
	 * @param string $from_db 是否强制从数据库读取
	 * @return array
	 */
	public function get_place_type($placetypeid, $from_db = false) {

		if ($placetypeid < 1) {
			return array();
		}

		// 从数据库读取
		if ($from_db) {
			$d_place_type = new voa_d_oa_common_place_type();
			return $d_place_type->get($placetypeid);
		}

		// 从缓存读取
		$data = self::get_place_type_cache($from_db);
		if (!isset($data[$placetypeid])) {
			return array();
		}

		return $data[$placetypeid];
	}

	/**
	 * 格式化类型数据
	 * @param array $pt 原始数据
	 * @param array $format (引用结果)格式化后的数据
	 * @param array $options 扩展配置
	 * + date_format 时间显示格式
	 * @return boolean
	 */
	public function format_place_type($pt, &$format, $options = array()) {

		if (empty($options['date_format'])) {
			$options['date_format'] = 'Y-m-d H:i';
		}

		// 需要转换日期时间格式
		if (!empty($options['date_format'])) {
			$pt['created'] = $pt['created'] ? rgmdate($pt['created'], $options['date_format']) : '';
			$pt['updated'] = $pt['updated'] ? rgmdate($pt['updated'], $options['date_format']) : '';
		}

		// 格式化后的数据
		$date_format = array(
			'name' => $pt['name'],
			'levels' => is_array($pt['levels']) ? $pt['levels'] : @unserialize($pt['levels']),
			'created' => $pt['created'],
			'updated' => $pt['updated']
		);

		return true;
	}

	/**
	 * 格式化类型列表数据
	 * @param array $list 原始数据
	 * @param array $format_list (引用结果)格式化后的数据
	 * @param array $options 扩展配置
	 * + date_format 时间显示格式
	 * @return boolean
	 */
	public function format_place_type_list($list, &$format_list, $options = array()) {

		if (empty($options['date_format'])) {
			$options['date_format'] = 'Y-m-d H:i';
		}

		$format_list = $list;
		foreach ($format_list as $_pt_id => &$_pt) {
			$this->format_place_type($_pt, $_pt, $options);
		}
		unset($list, $_pt_id, $_pt);

		return true;
	}

	/**
	 * 验证指定类型ID是否合法
	 * @param number $placetypeid
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_placetypeid($placetypeid) {

		// ID非法
		if ($placetypeid < 1) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::TYPE_ID_ERROR, $placetypeid);
		}

		return true;
	}

	/**
	 * 判断类型总数是否超过系统限制
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_place_type_amount() {

		$d_place_type = new voa_d_oa_common_place_type();
		// 总数未超过限制，直接返回
		if ($d_place_type->count() < $this->type_max_count) {
			return true;
		}

		// 超出限制，拋错
		return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::TYPE_AMOUNT_TOO_MUCH, $this->type_max_count);
	}

	/**
	 * 检查类型名是否重复
	 * @param string $name
	 * @param number $placetypeid 需要排除的类型ID（多用于编辑类型时）
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_place_type_name_duplicate($name, $placetypeid = 0) {

		$d_place_type = new voa_d_oa_common_place_type();
		$conds = array('name' => $name);
		// 排除指定的类型ID，用于编辑
		if ($placetypeid) {
			$conds['placetypeid<>?'] = $placetypeid;
		}
		// 不存在同名类型，直接返回
		if (!($d_place_type->count_by_conds($conds))) {
			return true;
		}

		// 重复了，拋错
		return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::TYPE_NAME_DUPLICATE, $name);
	}

	/**
	 * 验证类型名称
	 * @param string $name
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_place_type_name(&$name) {

		$name = (string)$name;
		$name = trim($name);
		if ($name != rhtmlspecialchars($name)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::VALIDATOR_PLACE_TYPE_NAME_STRING_ERROR);
		}
		if ($name != raddslashes($name)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::VALIDATOR_PLACE_TYPE_NAME_STRING_SLASHES);
		}

		// 计算最短允许的字符数
		$min = max($this->p_sets['type_name_length_min'], voa_d_oa_common_place_type::LENGTH_NAME_MIN);
		// 计算最长允许的字符数
		$max = min($this->p_sets['type_name_length_max'], voa_d_oa_common_place_type::LENGTH_NAME_MAX);
		// 长度符合要求
		if (validator::is_string_count_in_range($name, $min, $max)) {
			return true;
		}

		// 不符合要求则判断要输出哪种提示内容
		if ($min > 0) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::VALIDATOR_PLACE_TYPE_NAME_LENGTH_RANGE_ERROR
					, $min, $max);
		} else {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::VALIDATOR_PLACE_TYPE_NAME_LENGTH_MAX_ERROR
					, $max);
		}
	}

	/**
	 * 验证类型下的权限称谓名称
	 * @param string $level_name
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_place_type_level_name(&$level_name) {

		$level_name = (string)$level_name;
		$level_name = trim($level_name);
		if ($level_name != rhtmlspecialchars($level_name)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::VALIDATOR_PLACE_LEVEL_NAME_STRING_ERROR);
		}
		if ($level_name != raddslashes($level_name)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::VALIDATOR_PLACE_LEVEL_NAME_STRING_SLASHES);
		}

		// 长度符合要求
		if (validator::is_string_count_in_range($level_name, voa_d_oa_common_place_type::LEVEL_NAME_MIN
				, voa_d_oa_common_place_type::LEVEL_NAME_MAX)) {
			return true;
		}

		// 不符合要求则输出错误
		return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::VALIDATOR_PLACE_LEVEL_NAME_LENGTH_RANGE_ERROR
					, voa_d_oa_common_place_type::LEVEL_NAME_MIN, voa_d_oa_common_place_type::LEVEL_NAME_MAX);
	}

	/**
	 * 检查一组权限称谓是否合法
	 * @param array $levels (引用结果)
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_place_type_level_names(&$levels) {

		// 如果不允许自定义权限级别称谓，则始终将用户提交的数据设置为空，后面检查时会自动使用默认数据填充
		if (empty($this->p_sets['allow_level_name_custom'])) {
			$levels = array();
		}

		// 给定的级别称谓为空或格式非法，则使用默认
		if (empty($levels) || !is_array($levels)) {
			// 如果未设置级别权限称谓则使用默认
			$levels = $this->place_member_levels;
			return true;
		}

		// 遍历默认，以检查给定的类型级别称谓是否正确完整
		foreach ($this->place_member_levels as $_level => $_default_name) {
			if (!isset($levels[$_level]) || !is_scalar($levels[$_level])) {
				return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PLACE_TYPE_ADD_LEVEL_NOT_DEFINED
						, $_level);
			}

			if (!$this->validator_place_type_level_name($levels[$_level])) {
				return false;
			}
		}
		unset($_level, $_default_name);

		// 检查给定的级别称谓是否有无效的数据（并不需要的垃圾数据）
		foreach ($levels as $_level => $_name) {
			if (!isset($this->place_member_levels[$_level])) {
				unset($levels[$_level]);
			}
		}

		return true;
	}

}

<?php
/**
 * region.php
 * service/公共模块/场所管理/场所分区表相关
 * validator_xx 验证方法，出错抛错
 * format_xx 格式化方法，引用返回结果
 * get_xx 获取数据
 * list_xxx 读取数据列表相关
 * ... 其他自定方法
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_s_oa_common_place_region extends voa_s_oa_common_place_abstract {

	/** place region */
	private $__d_place_region = null;

	public function __construct() {
		parent::__construct();
		if ($this->__d_place_region === null) {
			$this->__d_place_region = new voa_d_oa_common_place_region();
		}
	}

	/**
	 * 读取分区缓存
	 * @param boolean $update 是否强制读取更新
	 * @return array
	 */
	public function get_place_region_cache($update = false) {

		return voa_h_cache::get_instance()->get($this->p_id.'_region', 'oa', $update);
	}

	/**
	 * 列出所有全部指定类型的区域
	 * @param number $placetypeid 类型ID
	 * @param number $remove 是否读取已“删除”数据。voa_d_common_place_region::REMOVE_* 或 all，
	 * 默认：voa_d_common_place_region::REMOVE_NO = 只读取未删除的
	 * @return array
	 */
	public function get_place_region_list_all($placetypeid, $remove = voa_d_oa_common_place_region::REMOVE_NO) {

		$conds = array();
		$conds['placetypeid'] = $placetypeid;
		if (in_array($remove, array(voa_d_oa_common_place_region::REMOVE_NO, voa_d_oa_common_place_region::REMOVE_YES))) {
			$conds['remove'] = $remove;
		}

		return $this->__d_place_region->list_by_conds($conds);
	}

	/**
	 * 根据条件找到指定的分区信息（无缓存）
	 * @param number $placetypeid
	 * @param number $parentid
	 * @param string $name
	 * @return array
	 */
	public function get_region_by_conds($placetypeid, $parentid, $name) {

		$conds = array();
		$conds['placetypeid'] = $placetypeid;
		$conds['parentid'] = $parentid;
		$conds['name'] = $name;

		return $this->__d_place_region->get_by_conds($conds);
	}

	/**
	 * 获取指定分区的所有上级区域信息（包含已删除了的）
	 * @param number $placetypeid
	 * @param number $placeregionid
	 * @return array
	 */
	public function get_placeregionid_parent($placetypeid, $placeregionid) {

		static $list_all = array();
		// 读取全部区域信息（包含已删除的数据）
		if (empty($list_all)) {
			$conds = array();
			$conds['placetypeid'] = $placetypeid;
			$list_all = $this->__d_place_region->list_by_conds($conds);
		}

		// 所有分区级别列表
		$parent = array();
		if (empty($list_all) || !isset($list_all[$placeregionid])) {
			return $parent;
		}

		// 当前区域信息
		$place_region = $list_all[$placeregionid];
		// 如果为顶级，则直接返回
		$parent[$place_region['deepin']] = $place_region;
		if ($place_region['parentid'] == 0) {
			return true;
		}

		// 递归提取父级信息（以层级深度为键名）
		$this->__parse_parent_region($place_region['parentid'], $list_all, $parent);

		return $parent;
	}

	/**
	 * 获取一组区域的所有父级区域列表，返回以区域Id为键名
	 * @param number $placetypeid 所在类型
	 * @param array $placeregionids 区域ID列表
	 * @return array
	 */
	public function get_parents($placetypeid, $placeregionids) {

		$parent_list = array();
		foreach ($placeregionids as $_placeregionid) {
			$parent_list[$_placeregionid] = $this->get_placeregionid_parent($placetypeid, $_placeregionid);
		}

		return $parent_list;
	}

	/**
	 * 读取指定分区信息
	 * @param number $placeregionid
	 * @param string $from_db 是否强制从数据库读取
	 * @param boolean $is_remove 是否读取已“删除”的。true=是,false=否，默认：false=否
	 * @return array
	 */
	public function get_place_region($placeregionid, $from_db = false, $remove = false) {

		// ID非法
		if ($placeregionid < 1) {
			return array();
		}

		// 自数据库读取
		if ($from_db) {
			$region = $this->__d_place_region->get($placeregionid);
			if (empty($region) || (!$remove && $region['remove'] == voa_d_oa_common_place_region::REMOVE_YES)) {
				return array();
			}
			return $region;
		}

		// 自缓存读取
		$placeregion_list = $this->get_place_region_cache($from_db);
		// 未删除列表
		$placeregion_display_list = $placeregion_list[voa_d_oa_common_place_region::REMOVE_NO]['data'];
		// 已删除列表
		$placeregion_remove_list = $placeregion_list[voa_d_oa_common_place_region::REMOVE_YES]['data'];
		// 存在于未删除列表，直接返回
		if (!empty($placeregion_display_list[$placeregionid])) {
			return $placeregion_display_list[$placeregionid];
		}
		// 存在于已删除列表 但 要求读取已删除数据，则返回结果
		if (!empty($placeregion_remove_list[$placeregionid]) && $remove) {
			return $placeregion_remove_list[$placeregionid];
		}

		// 未找到缓存
		return array();
	}

	/**
	 * 获取指定类型下的分区总数
	 * @param number $placetypeid 类型ID
	 * @return number
	 */
	public function get_place_region_count_by_placetypeid($placetypeid) {

		$conds = array('placetypeid' => $placetypeid);

		return (int)$this->__d_place_region->count_by_conds($conds);
	}

	/**
	 * 获取一组分区的下级分区数（不递归计算下下级，只计算下级）
	 * @param array $placeregionid 类型ID
	 * @return array
	 */
	public function count_children_by_placeregionids($placeregionids) {

		$list = $this->__d_place_region->count_children_by_placeregionids($placeregionids);
		if (empty($list)) {
			return array();
		}
		$result = array();
		foreach ($list as $_placeregionid => $_arr) {
			$result[$_placeregionid] = $_arr['_count'];
		}
		unset($_placeregionid, $_arr);

		return $result;
	}

	/**
	 * 获取指定类型的级别最大深度值
	 * @param number $placetypeid
	 * @return number
	 */
	public function get_place_region_deepin_max($placetypeid) {
		return $this->__d_place_region->get_max_deepin_by_placetypeid($placetypeid);
	}

	/**
	 * 基本的格式化方法，去除内部参数，如删除状态
	 * @param array $placeregion
	 * @param string $format_date 格式化后的日期格式
	 * @param array $remove_fields 需要移除的字段
	 * @return boolean
	 */
	public function format(&$placeregion, $format_date = 'Y-m-d H:i', $remove_fields = array()) {

		// 如果未定义待移除的字段，则默认：status、deleted等不常用的
		if (!$remove_fields) {
			$remove_fields = array('status', 'deleted');
		}
		// 移除不需要的输出字段
		foreach ($remove_fields as $_key) {
			unset($placeregion[$_key]);
		}
		unset($_key, $placeregion['status'], $placeregion['deleted']);

		// 如果需要转换时间
		if ($format_date) {
			isset($placeregion['created']) && $placeregion['created'] = rgmdate($placeregion['created'], $format_date);
			isset($placeregion['updated']) && $placeregion['updated'] = rgmdate($placeregion['updated'], $format_date);
		}

		return true;
	}

	/**
	 * 基本的格式化列表方法，去除内部参数
	 * @see voa_s_oa_common_place_region::format()
	 * @param array $placeregion_list
	 * @return boolean
	 */
	public function format_list(array &$placeregion_list, $format_date = 'Y-m-d H:i', $remove_fields = array()) {

		foreach ($placeregion_list as &$placeregion) {
			$this->format($placeregion, $format_date, $remove_fields);
		}

		return true;
	}

	/**
	 * 基本验证分区ID合法性
	 * @param number $placeregionid
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_placeregionid($placeregionid) {

		if ($placeregionid < 1) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::REGION_ID_ERROR);
		}

		return true;
	}

	/**
	 * 检查同一类型、同级分区名是否重复
	 * @param number $placetypeid 类型ID
	 * @param number $parentid 上级分区ID
	 * @param string $name 分区名称
	 * @param number $placeregionid 排除该ID的分区（多用于编辑时）
	 * @return boolean
	 */
	public function validator_place_region_name_duplicate($placetypeid, $parentid, $name, $placeregionid = 0) {

		$conds = array(
			'placetypeid' => $placetypeid,
			'parentid' => $parentid,
			'name' => $name,
		);
		// 排除指定的类型ID，用于编辑
		if ($placeregionid) {
			$conds['placeregionid<>?'] = $placeregionid;
		}
		// 不存在同名类型，直接返回
		if (!($this->__d_place_region->count_by_conds($conds))) {
			return true;
		}

		return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::REGION_NAME_DUPLICATE, $name);
	}

	/**
	 * 验证区域名称
	 * @param string $name
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_place_region_name(&$name) {

		$name = (string)$name;
		$name = trim($name);
		if ($name != rhtmlspecialchars($name)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::VALIDATOR_PLACE_REGION_NAME_STRING_ERROR);
		}
		if ($name != raddslashes($name)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::VALIDATOR_PLACE_REGION_NAME_STRING_SLASHES);
		}

		// 计算最短允许的字符数
		$min = max($this->p_sets['region_name_length_min'], voa_d_oa_common_place_region::LENGTH_NAME_MIN);
		// 计算最长允许的字符数
		$max = min($this->p_sets['region_name_length_max'], voa_d_oa_common_place_region::LENGTH_NAME_MAX);
		// 长度符合要求
		if (validator::is_string_count_in_range($name, $min, $max)) {
			return true;
		}

		// 不符合要求则判断要输出哪种提示内容
		if ($min > 0) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::VALIDATOR_PLACE_REGION_NAME_LENGTH_RANGE_ERROR
				, $min, $max);
		} else {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::VALIDATOR_PLACE_REGION_NAME_LENGTH_MAX_ERROR
				, $max);
		}
	}

	/**
	 * 根据给定的父级ID提取所有上级区域信息
	 * @param number $parentid
	 * @param array $parent_list
	 * @param array $parent (引用结果)
	 * @return array
	 */
	private function __parse_parent_region($parentid, $region_list, &$parent) {

		if (isset($region_list[$parentid])) {
			$region = $region_list[$parentid];
			$parent[$region['deepin']] = $region;
			if ($region['parentid']) {
				$this->__parse_parent_region($region['parentid'], $region_list, $parent);
			}
		}

		return true;
	}

}

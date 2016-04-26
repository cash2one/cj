<?php
/**
 * place.php
 * service/公共模块/场所管理/主表相关
 * validator_xx 验证方法，出错抛错
 * check_xx 检查方法，返回boolean
 * format_xx 格式化方法，引用返回结果
 * get_xx 获取数据
 * list_xxx 读取数据列表相关
 * ... 其他自定方法
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_s_oa_common_place extends voa_s_oa_common_place_abstract {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 读取场所缓存
	 * @param boolean $update 是否强制读取更新
	 * @return array
	 */
	public function get_place_cache($update = false) {

		return voa_h_cache::get_instance()->get($this->p_id, 'oa', $update);
	}

	/**
	 * 读取一个地点信息（未删除的）
	 * @param number $placeid
	 * @param boolean $force_db 是否强制从数据库读取。默认：false
	 * @param number $remove 是否读取已删除的。1=是，0=否，all=全部，默认：0=只读取未删除的
	 * @return array
	 */
	public function get_place_by_placeid($placeid, $force_db = false, $remove = 0) {

		// 如果强制从数据库读取
		if ($force_db) {
			$d_place = new voa_d_oa_common_place();
			$conds = array(
				'placeid' => $placeid
			);
			if ($remove == voa_d_oa_common_place::REMOVE_NO || $remove == voa_d_oa_common_place::REMOVE_YES) {
				$conds['remove'] = $remove;
			}
			return $d_place->get_by_conds($conds);
		}

		// 自缓存读取
		$data = $this->get_place_cache();
		// 只读取未删除的
		if ($remove == voa_d_oa_common_place::REMOVE_NO) {
			return isset($data['data'][$placeid]) ? $data['data'][$placeid] : array();
		}
		/** 以下是任意情况的 */
		// 未删除的
		if (isset($data['data'][$placeid])) {
			return $data['data'][$placeid];
		}
		// 已删除的
		if (isset($data['remove'][$placeid])) {
			return $data['remove'][$placeid];
		}

		// 不存在
		return array();
	}

	/**
	 * 计算指定分区（无递归）下的所有场所总数
	 * @param number $regionid
	 * @return number
	 */
	public function get_place_by_regionid($regionid = 0) {

		$d_place = new voa_d_oa_common_place();

		// 查询条件
		$conds = array(
			'placeregionid' => $regionid,
			'remove' => voa_d_oa_common_place::REMOVE_NO
		);

		return (int)$d_place->count_by_conds($conds);
	}

	/**
	 * 计算一组分区下的场所总数
	 * @param array $regionids
	 * @return array array(id => count, ....)
	 */
	public function count_place_by_placeregionids($regionids = array()) {

		$d_place = new voa_d_oa_common_place();
		$list = $d_place->count_place_by_placeregionids($regionids);

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
	 * 验证场所ID的基本合法性
	 * @param number $placeid
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_placeid($placeid) {

		if ($placeid < 1) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PLACE_ID_ERROR, $placeid);
		}

		return true;
	}

	/**
	 * 验证场地类型合法性
	 * @param number $placetypeid
	 * @return boolean
	 */
	public function validator_placetypeid($placetypeid) {

		$s_place_type = new voa_s_oa_common_place_type();
		if (!$s_place_type->get_place_type($placetypeid)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PLACE_VALIDATOR_PLACETYPEID, $placetypeid);
		}

		return true;
	}

	/**
	 * 验证场地所在分区合法性
	 * @param number $placetypeid 所在类型ID
	 * @param number $placeregionid 分区ID
	 * @return boolean
	 */
	public function validator_placeregionid($placetypeid, $placeregionid) {

		// 获取所在类型信息
		$s_place_type = new voa_s_oa_common_place_type();
		$place_type = $s_place_type->get_place_type($placetypeid);
		if (!$place_type) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PLACE_VALIDATOR_PLACETYPEID_BY_REGION, $placetypeid);
		}

		$s_place_region = new voa_s_oa_common_place_region();

		if ($placeregionid > 0) {
			// 选择了分区，则判断分区是否合法

			// 判断分区是否存在
			$place_region = $s_place_region->get_place_region($placeregionid, true);
			if (!$place_region) {
				return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PLACE_REGION_NOT_EXISTS, $placeregionid);
			}

			// 判断类型是否与分区有关联
			if ($place_region['placetypeid'] != $placetypeid) {
				return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PLACE_TYPE_NOT_EXISTS, $placetypeid);
			}

			// 验证分区是否为最深的深度（不允许存放在非最底层的分区下）
			$max_deepin = $s_place_region->get_place_region_deepin_max($placetypeid);
			if ($place_region['deepin'] != $max_deepin) {
				return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PLACE_DEEPIN_ERROR, $place_region['deepin'], $max_deepin);
			}

		} else {
			// 预期希望存放在根分区下

			// 但只要存在分区，就不能允许场地放在根分区下
			// 计算分区总数
			if ($s_place_region->get_place_region_count_by_placetypeid($placetypeid) > 0) {
				return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PLACE_REGION_NULL);
			}
		}

		return true;
	}

	/**
	 * 验证场地名称
	 * @param string $name (引用)场地名称
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_place_name(&$name) {

		$name = (string)$name;
		$name = trim($name);
		if ($name != rhtmlspecialchars($name)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::VALIDATOR_PLACE_NAME_STRING_ERROR);
		}
		if ($name != raddslashes($name)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::VALIDATOR_PLACE_NAME_STRING_SLASHES);
		}

		// 计算最短允许的字符数
		$min = max($this->p_sets['place_name_length_min'], voa_d_oa_common_place::LENGTH_NAME_MIN);
		// 计算最长允许的字符数
		$max = min($this->p_sets['place_name_length_max'], voa_d_oa_common_place::LENGTH_NAME_MAX);
		// 长度符合要求
		if (validator::is_string_count_in_range($name, $min, $max)) {
			return true;
		}

		// 不符合要求则判断要输出哪种提示内容
		if ($min > 0) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::VALIDATOR_PLACE_NAME_LENGTH_RANGE_ERROR, $min, $max);
		} else {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::VALIDATOR_PLACE_NAME_LENGTH_MAX_ERROR, $max);
		}
	}

	/**
	 * 验证场地地址
	 * @param string $address (引用)场地地址
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_place_address(&$address) {

		$address = (string)$address;
		$address = trim($address);
		if ($address != rhtmlspecialchars($address)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::VALIDATOR_PLACE_ADDRESS_STRING_ERROR);
		}
		if ($address != raddslashes($address)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::VALIDATOR_PLACE_ADDRESS_STRING_SLASHES);
		}

		// 计算最短允许的字符数
		$min = max($this->p_sets['place_address_length_min'], voa_d_oa_common_place::LENGTH_ADDRESS_MIN);
		// 计算最长允许的字符数
		$max = min($this->p_sets['place_address_length_max'], voa_d_oa_common_place::LENGTH_ADDRESS_MAX);
		// 长度符合要求
		if (validator::is_string_count_in_range($address, $min, $max)) {
			return true;
		}

		// 不符合要求则判断要输出哪种提示内容
		if ($min > 0) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::VALIDATOR_PLACE_ADDRESS_LENGTH_RANGE_ERROR
				, $min, $max);
		} else {
			return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::VALIDATOR_PLACE_ADDRESS_LENGTH_MAX_ERROR, $max);
		}
	}

	/**
	 * 验证(格式)输入的经纬度
	 * @param string $str (引用结果)经度或纬度
	 * @return boolean
	 */
	public function validator_place_lnglat(&$str) {

		$str = trim($str);
		if (!$str || !preg_match('/^[\-+]*[0-9\.]+$/', $str)) {
			$str = 0;
		}
		$str = number_format($str, 6);

		return true;
	}

	/**
	 * 验证是否是相同的地点
	 * 思路：系统认定同区域下的只有名称和地址完全一致才认为是相同的，否则认为是不同地点。@涂开20141215
	 * @param number $placeregionid
	 * @param string $name
	 * @param string $address
	 * @param number $not_placeid
	 * @return boolean
	 */
	public function validator_place_name_address($placeregionid, $name, $address, $not_placeid = 0) {

		// 场地名称 和 场地地址 小写化处理
		$_name = rstrtolower($name);
		$_address = rstrtolower($address);

		// 此处使用遍历方式来判断是否“重名”，考虑因name和address两字段不适合做索引
		$s_place = new voa_d_oa_common_place();
		$tmp = $s_place->list_all();
		if (empty($tmp)) {
			return true;
		}
		foreach ($tmp as $_place) {
			if (rstrtolower($_place['name']) != $_name) {
				// 名称不同，则肯定不“重名”，跳过
				continue;
			}
			if (rstrtolower($_place['address']) != $_address) {
				// 地址不同，则肯定不“重名”，跳过
				continue;
			}
			if ($not_placeid != $_place['placeid']) {
				// 名称相同、地址相同，地点ID不同，肯定是“重名”的 duplication
				return voa_h_func::throw_errmsg(voa_errcode_oa_common_place::PLACE_DUPLICATION, '存在相同名称和地址的场所');
			}
		}

		return true;
	}

}

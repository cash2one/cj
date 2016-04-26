<?php
/**
 * place.php
 * 场所表 - 主表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_d_oa_common_place extends voa_d_abstruct {

	/** 地点名称，最短字符数 */
	const LENGTH_NAME_MIN = 0;
	/** 地点名称，最长字符数 */
	const LENGTH_NAME_MAX = 240;

	/** 地点地址，最短字符数 */
	const LENGTH_ADDRESS_MIN = 0;
	/** 地点地址，最长字符数 */
	const LENGTH_ADDRESS_MAX = 240;

	/** 数据是否被标记为删除，前台不显示（此状态不同于status，仅标记为不显示） */
	const REMOVE_YES = 1;
	/** 数据为标记为未被删除，正常状态 */
	const REMOVE_NO = 0;

	/** 初始化 */
	public function __construct($cfg = null) {

		// 表名
		$this->_table = 'orm_oa.common_place';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'placeid';

		parent::__construct(null);
	}

	/**
	 * “删除” 指定的场所数据
	 * @param mixed $placeid
	 */
	public function delete_by_placeid($placeid) {
		$data = array(
			'remove' => self::REMOVE_YES
		);
		parent::update($placeid, $data);
	}

	/**
	 * 列表指定条件的场所地点数据
	 * @param array $conds
	 * @return multitype:
	 */
	public function list_by_conds($conds, $page_option = null, $orderby = array()) {

		$_conds = array();
		// 指定了场所ID范围
		if (isset($conds['placeid'])) {
			if (is_array($conds['placeid'])) {
				$_conds['`placeid` IN (?)'] = "'".implode("', '", $conds['placeid'])."'";
			} else {
				$_conds['`placeid`=?'] = $conds['placeid'];
			}
		}
		// 指定了场所区域范围
		if (isset($conds['placeregionid'])) {
			if (is_array($conds['placeregionid'])) {
				$_conds['`placeregionid` IN (?)'] = "'".implode("', '", $conds['placeregionid'])."'";
			} else {
				$_conds['`placeregionid`=?'] = $conds['placeregionid'];
			}
		}
		// 指定了类型
		if (isset($conds['placetypeid'])) {
			$_conds['placetypeid=?'] = $conds['placetypeid'];
		}
		// 地点名称关键词
		if (isset($conds['name']) && !empty($conds['name'])) {
			$_conds['name LIKE ?'] = '%'.$conds['name'].'%';
		}
		// 地址关键词
		if (!empty($conds['address'])) {
			$_conds['address LIKE ?'] = '%'.$conds['address'].'%';
		}
		// 指定是否隐藏
		if (!empty($conds['remove'])) {
			$_conds['remove=?'] = self::REMOVE_YES;
		} else {
			$_conds['remove=?'] = self::REMOVE_NO;
		}

		return parent::_list_by_complex(implode(' AND ',array_keys($_conds)), array_values($_conds), null, array('placeid' => 'DESC'));
	}

	/**
	 * 计算一组区域的下级场所数
	 * @param array $placeregionids
	 * @return array
	 * +
	 *   + _placeregionid 分区ID
	 *   + _count 对应分区下的场所数量
	 */
	public function count_place_by_placeregionids($placeregionids) {

		$this->_condi('placeregionid IN (?)', $placeregionids);
		$this->_condi('remove=?', self::REMOVE_NO);
		$this->_condi('status<?', parent::STATUS_DELETE);
		$this->_group_by('placeregionid');
		return $this->_find_all("`placeregionid`, COUNT(`placeregionid`) as `_count`", 'placeregionid');
	}

}

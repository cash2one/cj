<?php
/**
 * region.php
 * 场所 - 区域/分类表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_d_oa_common_place_region extends voa_d_abstruct {

	/** 数据是否被标记为删除，前台不显示（此状态不同于status，仅标记为不显示） */
	const REMOVE_YES = 1;
	/** 数据为标记为未被删除，正常状态 */
	const REMOVE_NO = 0;

	/** 区域名称最短字符数 */
	const LENGTH_NAME_MIN = 0;
	/** 区域名称最长字符数 */
	const LENGTH_NAME_MAX = 80;

	/** 最多允许创建的分区深度（几级） */
	const DEEPIN_MAX = 3;

	/** 初始化 */
	public function __construct($cfg = null) {

		// 表名
		$this->_table = 'orm_oa.common_place_region';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'placeregionid';

		parent::__construct(null);
	}

	/**
	 * 获取指定类型的最大级别值
	 * @param number $placetypeid
	 * @return number
	 */
	public function get_max_deepin_by_placetypeid($placetypeid) {

		$where = "placetypeid=? AND status<?";
		$data = array($placetypeid, parent::STATUS_DELETE);
		$orderby = array('deepin' => 'DESC');
		// 级别深度最大的一行数据
		$max_deepin_row = parent::_get_by_complex($where, $data, $orderby);

		return isset($max_deepin_row['deepin']) ? $max_deepin_row['deepin'] : 0;
	}

	/**
	 * “移除”指定区域
	 * @param mixed $placeregionid
	 * @return void
	 */
	public function remove($placeregionid) {

		$data = array(
			'remove' => self::REMOVE_YES
		);

		return $this->update($placeregionid, $data);
	}

	/**
	 * 计算区域的下级分区数（只计算下一级，不递归）
	 * @param array $placeregionids
	 * @return array
	 * +
	 *   + _placeregionid 分区ID
	 *   + _count 对应分区下级数量
	 */
	public function count_children_by_placeregionids($placeregionids) {

		$this->_condi('parentid IN (?)', $placeregionids);
		$this->_condi('remove=?', self::REMOVE_NO);
		$this->_condi('status<?', parent::STATUS_DELETE);
		$this->_group_by('parentid');
		return $this->_find_all("`parentid` as `_placeregionid`, COUNT(`parentid`) as `_count`", '_placeregionid');
	}

}

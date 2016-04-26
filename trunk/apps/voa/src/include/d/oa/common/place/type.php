<?php
/**
 * type.php
 * 场所 - 类型表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_d_oa_common_place_type extends voa_d_abstruct {

	/** 类型名称允许的最短字符数 */
	const LENGTH_NAME_MIN = 0;
	/** 类型名称允许的最长字符数 */
	const LENGTH_NAME_MAX = 32;

	/** 权限称谓名称最短字符数 */
	const LEVEL_NAME_MIN = 0;
	/** 权限称谓名称最长字符数 */
	const LEVEL_NAME_MAX = 32;

	/** 默认允许创建的类型数量 */
	const DATA_MAX_TOTAL = 10;

	/** 初始化 */
	public function __construct($cfg = null) {

		// 表名
		$this->_table = 'orm_oa.common_place_type';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'placetypeid';

		parent::__construct(null);
	}

}

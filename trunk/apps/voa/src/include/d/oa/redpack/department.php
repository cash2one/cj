<?php
/**
 * voa_d_oa_redpack_department
 * 红包部门权限表
 * $Author$
 * $Id$
 */

class voa_d_oa_redpack_department extends voa_d_abstruct {

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.redpack_department';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'id';
		// 字段前缀
		$this->_prefield = '';

		parent::__construct();
	}

	/**
	 * 根据红包id和uid读取总数
	 * @param int $redpack_id 红包id
	 * @param array $cd_ids 部门cd_id
	 */
	public function count_by_redpackid_cdid($redpack_id, $cd_ids = array(), $limit = 0) {

		try {
			// limit 设置
			if (0 < $limit) {
				$this->_limit($limit);
			}

			$cd_ids = (array)$cd_ids;
			$cd_ids[] = 0;
			$this->_condi('redpack_id=?', $redpack_id);
			$this->_condi('cd_id IN (?)', $cd_ids);
			// 设置为删除状态
			$this->_condi($this->_prefield.'status<?', self::STATUS_DELETE);

			return $this->_total();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}

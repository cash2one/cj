<?php
/**
 * voa_d_oa_redpack_mem
 * 红包人员权限表
 * $Author$
 * $Id$
 */

class voa_d_oa_redpack_mem extends voa_d_abstruct {

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.redpack_mem';
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
	 * @param array $uids 用户uid
	 */
	public function count_by_redpackid_uid($redpack_id, $uids = array(), $limit = 0) {

		try {
			// limit 设置
			if (0 < $limit) {
				$this->_limit($limit);
			}

			$uids = (array)$uids;
			$uids[] = 0;
			$this->_condi('redpack_id=?', $redpack_id);
			$this->_condi('m_uid IN (?)', $uids);
			// 设置为删除状态
			$this->_condi($this->_prefield.'status<?', self::STATUS_DELETE);

			return $this->_total();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}

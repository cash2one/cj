<?php
/**
 * voa_d_oa_inspect_tasks
 * 巡店配置表
 * $Author$
 * $Id$
 */

class voa_d_oa_inspect_tasks extends voa_d_abstruct {
	// 未开始
	const EXE_STATUS_DRAFT = 1;
	// 执行中
	const EXE_STATUS_DOING = 2;
	// 已撤消
	const EXE_STATUS_ROLLBACK = 3;

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.inspect_tasks';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'it_id';
		// 字段前缀
		$this->_prefield = 'it_';

		parent::__construct();
	}

	/**
	 * 更新次数
	 * @param array $ids ID数组
	 * @throws service_exception
	 * @return boolean
	 */
	public function inspect_fin($ids) {

		try {
			// 自增
			$this->_set('`it_finished_total`=`it_finished_total`+?', 1);
			// 条件
			$this->_condi($this->_pk.' IN (?)', $ids);
			$this->_condi($this->_prefield.'status<?', self::STATUS_DELETE);
			// 更新时间
			if (!isset($data[$this->_prefield.'updated'])) {
				$data[$this->_prefield.'updated'] = startup_env::get('timestamp');
			}

			// 更新状态值
			if (!isset($data[$this->_prefield.'status'])) {
				$data[$this->_prefield.'status'] = self::STATUS_UPDATE;
			}

			return $this->_update($data);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}

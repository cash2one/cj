<?php
/**
 * 快递配置表
 * $Author$
 * $Id$
 */

class voa_d_oa_express_setting extends voa_d_abstruct {

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.express_setting';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'key';
		// 字段前缀
		$this->_prefield = '';

		parent::__construct();
	}

	/**
	 * 更新多个变量值
	 * @param array $data array(key=>value, key2=>value2, ...)
	 * @return boolean
	 */
	public function update_setting($data) {
		try {
			// 更新时间
			if (!isset($data[$this->_prefield.'updated'])) {
				$data[$this->_prefield.'updated'] = startup_env::get('timestamp');
			}

			// 更新状态值
			if (!isset($data[$this->_prefield.'status'])) {
				$data[$this->_prefield.'status'] = self::STATUS_UPDATE;
			}

			// 更新基础数据
			$ups = array(
				$this->_prefield.'updated' => startup_env::get('timestamp'),
				$this->_prefield.'status' => self::STATUS_UPDATE
			);
			// 循环更新

			foreach ($data as $_k => $_v) {
				$this->_condi($this->_prefield.'status<?', self::STATUS_DELETE);
				$this->_condi($this->_prefield.'key=?', $_k);
				$ups[$this->_prefield.'value=?'] = $_v;
				$this->_update($ups);
			}

			return true;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}

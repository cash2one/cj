<?php
/**
 * voa_d_oa_activity
 * 活动报名
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_activity_setting extends voa_d_abstruct {

	/** 数组数据 */
	const TYPE_ARRAY = 1;
	/** 标量数据 */
	const TYPE_NORMAL = 0;

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.activity_setting';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'key';

		parent::__construct(null);
	}

	/**
	 * 更新多个变量值
	 * @param array $data array(key=>value, key2=>value2, ...)
	 * @return boolean
	 */
	public function update_setting($data) {

		return $this->update_settings($data);
	}

	/**
	 * 更新多个变量值
	 * @param array $data array(key=>value, key2=>value2, ...)
	 * @return boolean
	 */
	public function update_settings($data) {

		if (empty($data)) {
			return true;
		}

		try {
			// 确定键名需要更新还是新增
			$list = $this->list_by_pks(array_keys($data));
			// 循环更新
			foreach ($data as $_k => $_v) {

				$ups = array();
				if (is_array($_v)) {
					// 传入的是一个数组
					$_type = self::TYPE_ARRAY;
					$_v = serialize($_v);
				} else {
					$_type = self::TYPE_NORMAL;
				}

				if (isset($list[$_k])) {
					// 更新
					if ($_type == self::TYPE_NORMAL && @unserialize($_v) !== false) {
						$_type = self::TYPE_ARRAY;
					}
					$ups[$this->_prefield.'type'] = $_type;
					$ups[$this->_prefield.'value'] = $_v;
					$this->update($_k, $ups);
				} else {
					// 添加
					if ($_type == self::TYPE_NORMAL && @unserialize($_v) !== false) {
						$_type = self::TYPE_ARRAY;
					}
					$ups[$this->_prefield.'value'] = $_v;
					$ups[$this->_prefield.'type'] = $_type;
					$ups[$this->_prefield.'key'] = $_k;
					$this->insert($ups);
				}
			}

			return true;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}


}


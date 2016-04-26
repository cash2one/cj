<?php
/**
 * setting.php
 * 陈列 - 设置表
 * $Author$
 * $Id$
 */
class voa_d_oa_showroom_setting extends voa_d_abstruct {

	/** 数组数据 */
	const TYPE_ARRAY = 1;
	/** 标量数据 */
	const TYPE_NORMAL = 0;

	public function __construct($cfg = null) {

		// 表名
		$this->_table = 'orm_oa.showroom_setting';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'key';

		parent::__construct(null);
	}

	/**
	 * 更新多个变量值
	 * @param array $data array(key=>value, key2=>value2, ...)
	 * @return boolean
	 */
	public function update_setting($data) {
		$prefix = '';
		/** 确定需要进行更新还是插入 */
		$is_update_keys = array();
		$tmp = $this->list_all();
		foreach ($tmp AS $row) {
			$is_update_keys[$row[$prefix.'key']] = $row[$prefix.'key'];
		}

		foreach ($data as $key => $value) {
			$data = array(
				$prefix.'value' => $value,
				$prefix.'status' => self::STATUS_UPDATE,
				$prefix.'updated' => startup_env::get('timestamp')
			);
			if (isset($is_update_keys[$key])) {
				$this->update($key, $data);
			} else {
				$data[$prefix.'key'] = $key;
				$data[$prefix.'type'] = @unserialize($value) === false ? 0 : 1;
				$data[$prefix.'status'] = self::STATUS_NORMAL;
				$data[$prefix.'created'] = startup_env::get('timestamp');
				$this->insert($data);
			}
		}

		return true;
	}
}

<?php
/**
 * voa_d_oa_sign_batch
 * @author Burce
 *
 */
class voa_d_oa_sign_setting extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.sign_setting';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 **/
		$this->_required_fields = array();
		/**前缀*/
		$this->_prefield = 'ss_';
		/** 主键 */
		$this->_pk = 'ss_key';

		parent::__construct(null);
	}

	/**
	 * 更新多个变量值
	 * @param array $data array(key=>value, key2=>value2, ...)
	 * @return boolean
	 */
	public function update_setting($data) {
		$prefix = 'ss_';
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


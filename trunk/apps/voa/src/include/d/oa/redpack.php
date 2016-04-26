<?php
/**
 * redpack.php
 * 红包领取日志表
 * $Author$
 * $Id$
 */

class voa_d_oa_redpack extends voa_d_abstruct {
	// 随机红包
	const TYPE_RAND = 1;
	// 平均红包
	const TYPE_AVERAGE = 2;
	// 定点红包
	const TYPE_APPOINT = 3;
	// 自由红包
	const TYPE_FREE = 4;

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.redpack';
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
	 * 更新剩余次数和金额
	 * @param int $redpack_id 红包ID
	 * @param int $money 当前发出的红包金额
	 * @param number $times
	 * @return boolean
	 */
	public function update_left_times($redpack_id, $money, $times = 1) {

		$redpack_id = (int)$redpack_id;
		$money = (int)$money;
		$times = (int)$times;
		$sql = "UPDATE `{$this->_table}` SET `left`=`left`+{$money}, `times`=`times`+{$times} WHERE `id`={$redpack_id} AND `status`<" . self::STATUS_DELETE;

		$data = array();
		return $this->_execute($sql, $data);
	}

	/**
	 * 根据 id 读取行锁
	 * @param int $redpack_id 红包id
	 * @return boolean
	 */
	public function get_for_update($redpack_id) {

		$sql = "SELECT * FROM `{$this->_table}` WHERE `id`={$redpack_id} AND `status`<".self::STATUS_DELETE." FOR UPDATE";
		// 执行
		$sth = null;
		if ($this->_execute($sql, $this->_bind_params, $sth)) {
			return $sth->fetch(PDO::FETCH_ASSOC);
		}

		return false;
	}

}

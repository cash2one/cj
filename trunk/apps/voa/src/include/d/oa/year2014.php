<?php
/**
 * year2014.php
 * 年度总结 - 主表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_d_oa_year2014 extends voa_d_abstruct {

	public function __construct($cfg = null) {

		// 表名
		$this->_table = 'orm_oa.year2014';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'id';

		parent::__construct(null);
	}

	/**
	 * 获取指定uid和appkey的数据
	 * @param number $uid
	 * @param string $appkey
	 * @return array
	 */
	public function get_by_uid_appkey($uid = 0, $appkey = '') {

		$conds = array();
		$conds['uid'] = $uid;
		$conds['appkey'] = $appkey;

		$r = parent::get_by_conds($conds);
		if (!empty($r)) {
			return @unserialize($r['data']);
		}

		return false;
	}

	/**
	 * 列表公共数据缓存
	 * @return array
	 */
	public function list_common() {

		$conds = array();
		$conds['uid'] = 0;

		$list = array();
		$h = parent::list_by_conds($conds);
		if (!empty($h) && is_array($h)) {
			foreach ($h as $_data) {
				$list[$_data['appkey']] = @unserialize($_data['data']);
			}
		}

		return $list;
	}

	/**
	 * 更新公共缓存数据
	 * @param string $appkey
	 * @param mixed $data
	 * @return boolean|Ambigous <boolean, array, string, mixed>
	 */
	public function set_common($appkey, $data) {

		// 获取旧数据
		$history = parent::get_by_conds(array('uid' => 0, 'appkey' => $appkey));
		$id = empty($history) ? 0 : $history['id'];

		$updata = array(
			'data' => serialize($data)
		);

		// 判断旧数据是否存在 确定是更新数据还是写入数据
		if ($id > 0) {
			return parent::update($id, $updata);
		} else {
			$updata['uid'] = 0;
			$updata['appkey'] = $appkey;
			return parent::insert($updata);
		}
	}

}

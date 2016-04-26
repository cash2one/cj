<?php
/**
 * EnterpriseModel.class.php
 * $author$
 */

namespace Common\Model;

class EnterpriseModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'ep_';
	}

	// 特殊: 删除标记是 6
	public function get_st_delete() {

		return 6;
	}

	/**
	 * 根据手机号码统计记录数
	 * @param string $mobile 手机号码
	 * @return Ambigous <multitype:, number, mixed>
	 */
	public function count_by_mobile($mobile) {

		return $this->_m->result("SELECT COUNT(*) FROM __TABLE__ WHERE `ep_adminmobilephone`=? AND `ep_status`<?", array(
			$mobile, $this->get_st_delete()
		));
	}

	/**
	 * 根据企业账号读取企业信息
	 * @param string $enumber 企业账号
	 * @return Ambigous <multitype:, unknown>
	 */
	public function get_by_enumber($enumber) {

		return $this->_m->fetch_row("SELECT * FROM __TABLE__ WHERE `ep_enumber`=? AND `ep_status`<?", array(
			$enumber, $this->get_st_delete()
		));
	}

}

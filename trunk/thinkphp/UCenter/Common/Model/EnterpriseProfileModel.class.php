<?php
/**
 * EnterpriseProfileModel.class.php
 * $author$
 */

namespace Common\Model;

class EnterpriseProfileModel extends AbstractModel {

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
	 * 根据企业账号读取企业信息
	 * @param string $corpid 微信企业号corpid
	 * @return Ambigous <multitype:, unknown>
	 */
	public function get_by_corpid($corpid) {

		return $this->_m->fetch_row("SELECT * FROM __TABLE__ WHERE `epp_wxcorpid`=? AND `epp_status`<?", array(
			$corpid, $this->get_st_delete()
		));
	}

}

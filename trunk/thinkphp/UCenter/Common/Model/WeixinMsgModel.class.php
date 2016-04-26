<?php
/**
 * WeixinMsgModel.class.php
 * $author$
 */

namespace Common\Model;

class WeixinMsgModel extends \Common\Model\AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'wm_';
	}

	/**
	 * 根据 $packageid 获取套件信息
	 * @param string $packageid 套件ID
	 * @return Ambigous <multitype:, unknown>
	 */
	public function get_by_packageid($packageid) {

		return $this->_m->fetch_row("SELECT * FROM __TABLE__ WHERE `{$this->prefield}packageid`=? AND `{$this->prefield}status`<?", array(
			$packageid, $this->get_st_delete()
		));
	}

}

<?php
/**
 * SmscodeModel.class.php
 * $author$
 */

namespace Common\Model;

class SmscodeModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'smscode_';
	}

	/**
	 * 根据 IP 读取最近发送记录
	 * @param string $ip IP 地址
	 * @return Ambigous <multitype:, unknown>
	 */
	public function get_last_by_ip($ip) {

		return $this->_m->fetch_row("SELECT * FROM __TABLE__ WHERE `smscode_ip`=? AND `smscode_status`<? ORDER BY `smscode_id` DESC LIMIT 1", array(
			$ip, $this->get_st_delete()
		));
	}

	/**
	 * 根据手机号码读取最近发送记录
	 * @param string $mobile 手机号码
	 * @return Ambigous <multitype:, unknown>
	 */
	public function get_last_by_mobile($mobile) {

		return $this->_m->fetch_row("SELECT * FROM __TABLE__ WHERE `smscode_mobile`=? AND `smscode_status`<? ORDER BY `smscode_id` DESC LIMIT 1", array(
			$mobile, $this->get_st_delete()
		));
	}

	/**
	 * 更新手机验证码记录
	 * @param int $pk 主键id值
	 * @return boolean
	 */
	public function set_used($pk) {

		return $this->update($pk);
	}

}

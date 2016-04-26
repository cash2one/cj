<?php
/**
 * LoginCodeModel.class.php
 * $author$
 */

namespace Common\Model;

class LoginCodeModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'lc_';
	}

	/**
	 * 根据code和ep_id读取最后一条记录
	 * @param int $ep_id 企业ID
	 * @param string $code 临时码
	 * @return Ambigous <multitype:, unknown>
	 */
	public function get_by_ep_id_code($ep_id, $code) {

		return $this->_m->fetch_row("SELECT * FROM __TABLE__ WHERE `lc_code`=? AND `ep_id`=? AND `lc_status`<? ORDER BY `lc_id` DESC", array(
			$code, $ep_id, $this->get_st_delete()
		));
	}

}

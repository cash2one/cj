<?php
/**
 * CommonJobModel.class.php
 * $author$
 */

namespace Common\Model;
use Common\Model\AbstractModel;

class CommonJobModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'cj_';
	}

	/**
	 * 获取表默认数据
	 * @return array
	 */
	public function list_field() {

		$sql = "SHOW FIELDS FROM __TABLE__";

		return $this->_m->fetch_array($sql);
	}

	/**
	 * 根据 $cj_id 读取职位名称
	 * @param string $cj_id
	 * @return boolean
	 */
	public function get_by_cj_id($cj_id) {

		return $this->_m->fetch_row("SELECT cj_name FROM __TABLE__ WHERE cj_id=? AND cj_status<?", array (
			$cj_id,
			$this->get_st_delete()
		));
	}

}

<?php
/**
 * 活动取消参与内容
 * User: Muzhitao
 * Date: 2015/10/9 0009
 * Time: 17:05
 * Email：muzhitao@vchangyi.com
 */
namespace Activity\Model;

class ActivityNopartakeModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 通过报名ID获取单条数据
	 * @param $apid
	 * @return array
	 */
	public function get_anpid_by_apid($apid) {

		// sql语句
		$sql = "SELECT anpid, apply FROM __TABLE__ WHERE apid=? AND status<? LIMIT 1";
		// 条件
		$params = array($apid, $this->get_st_delete());

		return $this->_m->fetch_row($sql, $params);
	}
}

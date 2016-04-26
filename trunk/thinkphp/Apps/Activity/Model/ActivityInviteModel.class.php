<?php
/**
 * Created by PhpStorm.
 * User: Muzhitao
 * Date: 2015/9/30 0030
 * Time: 13:41
 * Email：muzhitao@vchangyi.com
 */

namespace Activity\Model;

class ActivityInviteModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 根据活动ID获取邀请人员列表
	 * @param int $acid
	 * @param string $fields
	 * @return array
	 * */
	public function list_by_acid($acid, $fields="*"){

		$sql = "SELECT {$fields} FROM __TABLE__ WHERE acid=? and `status`<?";
		$param = array($acid, self::ST_DELETE);

		return $this->_m->fetch_array($sql, $param);
	}


}

<?php
/**
 * CommonDepartmentModel.class.php
 * $author$
 */
namespace Common\Model;

use Common\Model\AbstractModel;

class CommonDepartmentModel extends AbstractModel {

	// 仅本部门
	const PERMISSION_SELF = 1;
	// 全公司
	const PERMISSION_ALL = 0;

	// 有附加权限
	const EXTRAPERM_YES = 1;
	// 无附加权限
	const EXTRAPERM_NO = 0;

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'cd_';
	}

	// 权限: 仅本部门
	public function get_permission_self() {

		return self::PERMISSION_SELF;
	}

	// 权限: 全公司
	public function get_permission_all() {

		return self::PERMISSION_ALL;
	}

	// 特殊权限: 有
	public function get_extraperm_yes() {

		return self::EXTRAPERM_YES;
	}

	// 特殊权限: 无
	public function get_extraperm_no() {

		return self::EXTRAPERM_NO;
	}

	/**
	 * 根据部门名称和上级部门id查询
	 * @param $cd_name string 部门名称
	 * @param $upid int 上级部门id
	 */
	public function get_id_by_cdname_upid($cd_name, $upid){

		$sql = "SELECT * FROM __TABLE__";
		// 查询条件
		$where = array(
			'cd_upid = ?',
			'cd_name = ?',
			'cd_status < ?',
		);
		// 参数
		$where_params = array(
			$upid,
			$cd_name,
			$this->get_st_delete()
		);
		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

}

<?php
/**
 * voa_uda_frontend_department_base
 * 统一数据访问/部门表/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_department_base extends voa_uda_frontend_base {

	/**
	 * 全部部门列表
	 * @var array
	 */
	public $department_list = array();

	/** 企业微信根部门ID */
	public $department_qywxparentid = 1;

	/** 部门名长度限制 array(长度单位, min, max) */
	public $department_name_length = array('count', 1, 64);

	/** 显示顺序取值范围 array(min, max) */
	public $department_displayorder = array(0, 99);

	/** 部门表操作对象 */
	public $serv = null;

	public function __construct() {
		parent::__construct();
		$this->department_list = voa_h_cache::get_instance()->get('department', 'oa');
		if ($this->serv === null) {
			$this->serv = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
		}
	}

	/**
	 * 更新部门缓存
	 * @return boolean
	 */
	public function update_cache() {
		voa_h_cache::get_instance()->get('department', 'oa', true);
		return true;
	}

	/**
	 * 判断部门cd_id或者名称cd_name是否合法，获取其真实的cd_id
	 * <p>主要用于在其他业务添加或更新部门信息的操作</p>
	 * <strong style="color:blue">不存在的部门名称则会尝试添加</strong>
	 * @param number $department_id
	 * @param string $department_name
	 * @param number $cd_id <strong style="color:red">(引用结果)</strong> 实际的cd_id
	 * @return boolean
	 */
	public function check_department($department_id, $department_name, &$cd_id) {

		$department_name = (string)$department_name;

		$cd_id = 0;
		$department_uda_update = &uda::factory('voa_uda_frontend_department_update');
		$department_uda_get = &uda::factory('voa_uda_frontend_department_get');

		if (!empty($department_id)) {
			// 选择了部门，验证所选的cd_id是否合法
			$department = array();
			$department_uda_get->department($department_id, $department);
			if (!empty($department['cd_id'])) {
				// 找到了该id的部门
				$cd_id = $department['cd_id'];
				return true;
			}
		}

		if (empty($cd_id) && !empty($department_name)) {
			// 未提供或者未找到选择的部门cd_id，但填写了部门名称，则尝试检查输入的部门名

			$_cd_id = 0;
			if ($department_uda_get->get_cd_id_by_name($department_name, $_cd_id)) {
				// 找到了此名字的部门
				$cd_id = $_cd_id;
				return true;
			} else {
				// 未找到此名字的部门，尝试添加
				$result = array();
				if ($department_uda_update->update(array(), array('cd_name' => $department_name), $result)) {
					// 添加成功
					$cd_id = $result['cd_id'];
					return true;
				} else {
					// 添加失败
					$this->errmsg(1004, '新增部门出错:'.$this->error);
					return false;
				}
			}
		}

		if (empty($cd_id) && empty($department_name)) {
			$this->errmsg(1005, '部门必须填写');
			return false;
		}

		return true;
	}

}

<?php
/**
 * voa_uda_frontend_department_get
 * 统一数据访问/部门/获取
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_department_get extends voa_uda_frontend_department_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 获取指定cd_id的部门信息（如果cd_id为空则返回默认数据）
	 * @param number $cd_id
	 * @param array $department <strong style="color:red">(引用结果)</strong>返回的部门数据
	 * @return boolean
	 */
	public function department($cd_id = 0, &$department = array()) {
		$cd_id = rintval($cd_id, false);
		$uda_format = &uda::factory('voa_uda_frontend_department_format');
		if ($cd_id > 0) {
			$department = $this->serv->fetch($cd_id);
		} else {
			$department = $this->serv->fetch_all_field();
		}
		$uda_format->format($department);

		return true;
	}

	/**
	 * 列出全部部门列表
	 * @param array $list <strong style="color:red">(引用结果)</strong>返回的部门列表数据
	 * @param string $type 输出的数据类型，primary主要字段数据，否则输出全部字段数据
	 */
	public function list_all(&$list, $type = 'primary') {

		$uda_format = &uda::factory('voa_uda_frontend_department_format');
		$list = $this->serv->fetch_all();
		if ($type == 'primary') {
			$uda_format->format_primary_list($list);
		} else {
			$uda_format->format_list($list);
		}

		return true;
	}

	/**
	 * 统计指定部门cd_id的用户数
	 * @param number $cd_id
	 * @param number $count <strong style="color:red">(引用结果)</strong>返回的该部门下的用户数
	 * @return boolean
	 */
	public function count_by_cd_id($cd_id = 0, &$count = 0) {
		$serv_member = &service::factory('voa_s_oa_member');
		$count = $serv_member->count_by_cd_id($cd_id);
		$count = rintval($count, false);
		return true;
	}

	/**
	 * 尝试找到部门名称cd_name对应的cd_id
	 * @param string $cd_name 部门名称
	 * @param number $cd_id <strong style="color:red">(引用结果)</strong>对应的cd_id
	 * @return boolean
	 */
	public function get_cd_id_by_name($cd_name = '', &$cd_id = 0) {
		$department = $this->serv->fetch_by_cd_name($cd_name);
		if (empty($department)) {
			$cd_id = 0;
			return false;
		} else {
			$cd_id = $department['cd_id'];
			return true;
		}
	}

	/**
	 * 尝试找到部门名称cd_name对应的cd_id
	 * @param string $cd_name 部门名称
	 * @param string $cd_upid 部门上级id
	 * @param number $cd_id <strong style="color:red">(引用结果)</strong>对应的cd_id
	 * @return boolean
	 */
	public function get_cd_id_by_name_upid($cd_name, $cd_upid) {
		$department = $this->serv->fetch_by_cd_name_upid($cd_name, $cd_upid);
		if (empty($department)) {
			return 0;
		} else {
			return $department['cd_id'];
		}
	}

	/**
	 * 根据企业微信的部门id 获取本地的部门信息
	 * @param string $qywxid
	 * @param array $department <strong style="color:red">(引用结果)</strong>获取到的部门信息数组
	 * @return boolean
	 */
	public function get_by_qywxid($qywxid, &$department) {
		$department = $this->serv->fetch_by_qywxid($qywxid);
		if (empty($department)) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * 获取指定cd_ids的部门列表
	 * @param array $cd_ids
	 * @param array $department <strong style="color:red">(引用结果)</strong>获取到的部门信息列表
	 * @return boolean
	 */
	public function get_by_cd_ids($cd_ids = array(), &$department = array()) {
		$department = $this->serv->fetch_all_by_key($cd_ids, 'cd_id');
		return true;
	}

    public function get_sub_dp_ids($cd_id) {

        $departments = voa_h_cache::get_instance()->get('department', 'oa');
        $dp_ids = array();
        foreach ($departments as $department) {
            if ($department['cd_upid'] == $cd_id) {
                $dp_ids[] = $department['cd_id'];

                $dp_ids = array_merge($dp_ids, $this->get_sub_dp_ids($department['cd_id']));
            }
        }
        return $dp_ids;
    }


	/**
	 * 获取顶级部门id(公司id)
	 * @return int
	 */
	public function get_top_department_id() {
		$departments = voa_h_cache::get_instance()->get('department', 'oa');
		foreach ($departments as $department) {
			if ($department['cd_upid'] == 0) {
				return $department['cd_id'];
			}
		}
		return 0;
	}

}

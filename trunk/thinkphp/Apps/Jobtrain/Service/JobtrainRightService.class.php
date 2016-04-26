<?php
namespace Jobtrain\Service;

class JobtrainRightService extends AbstractService {

	// 构造方法
	public function __construct() {
		parent::__construct();
		// 实例化相关模型
		$this->_d = D("Jobtrain/JobtrainRight");
	}
	/**
	 * 检查权限
	 * @param int $aid
	 * @param int $cid
	 * @param int $uid
	 * @param int $is_publish
	 * @return bool
	 */
	public function check_right($aid, $cid, $uid, $is_publish){
		$rights = $this->_d->list_by_aid_cid($aid, $cid);
		if(!$rights){
			return false;
		}
		$right_m_uids = array_column($rights, 'm_uid');
		if(in_array($uid, $right_m_uids)){
			return true;
		}
		// 如果是草稿直接返回
		if($is_publish==0){
			return false;
		}
		$right_is_all = array_column($rights, 'is_all');
		if(in_array(1, $right_is_all)){
			return true;
		}

		$right_cd_ids = array_column($rights, 'cd_id');

		//$s_member_department = D('Common/MemberDepartment', 'Service');
        // 获取上级部门
        //$c_p_cdids = $s_member_department->list_cdid_by_uid($uid, true);
        list($c_cdids, $p_cdids) = \Common\Common\Department::instance()->list_cdid_by_uid($uid, true);
        // 合并所属部门 并且去重
        $cdids = array_unique(array_merge($c_cdids, $p_cdids));
        foreach ($cdids as $v) {
        	if(in_array($v, $right_cd_ids)){
				return true;
			}
        }
        return false;
	}
}
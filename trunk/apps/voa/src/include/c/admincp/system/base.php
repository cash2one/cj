<?php
/**
 * voa_c_admincp_system_base
 * 企业后台/系统设置/基本控制
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_base extends voa_c_admincp_base {

	/**
	 * (manage/base) 管理组启用状态映射
	 * @var array
	 */
	public $adminergroup_enables	=	array(
			'yes'	=>	voa_d_oa_common_adminergroup::ENABLE_YES,
			'no'	=>	voa_d_oa_common_adminergroup::ENABLE_NO,
			'sys'	=>	voa_d_oa_common_adminergroup::ENABLE_SYS,
	);

	/**
	 * (manage/base) 管理员锁定状态映射
	 * @var array
	*/
	public $adminer_locked	=	array(
			'no'	=>	voa_d_oa_common_adminer::LOCKED_NO,
			'yes'	=>	voa_d_oa_common_adminer::LOCKED_YES,
			'sys'	=>	voa_d_oa_common_adminer::LOCKED_SYS,
	);

	/**
	 * (manage/base) 管理组最多允许的个数
	 * @var number
	*/
	public $adminergroup_maxcount	=	voa_d_oa_common_adminergroup::COUNT_MAX;

	/**
	 * (manage/base) 最多允许添加的管理员个数
	 * @var number
	 */
	public $adminer_maxcount		=	voa_d_oa_common_adminer::COUNT_MAX;

	/**
	 * (manage/base) 管理组启用状态文字描述
	 * @var array
	 */
	public $adminergroup_enables_desription	=	array();

	/**
	 * (manage/base) 管理员锁定状态文字描述
	 * @var array
	*/
	public $adminer_locked_description		=	array();

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->adminergroup_enables_desription	=	array(
				$this->adminergroup_enables['no']	=>	'禁止登录',
				$this->adminergroup_enables['yes']	=>	'允许登录',
				$this->adminergroup_enables['sys']	=>	'最高权限组',
		);
		$this->adminer_locked_description		=	array(
				$this->adminer_locked['no']			=>	'允许登录',
				$this->adminer_locked['yes']		=>	'禁止登录',
				$this->adminer_locked['sys']		=>	'系统帐号',
		);

		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

	/**
	 * (manage/base) 返回所有管理组详情
	 * @return array
	 */
	protected function _adminergroup_list(){
		$list	=	array();
		$tmp	=	$this->_service_single('common_adminergroup', 'fetch_all', array());
		foreach ( $tmp AS $_cag ) {
			$_cag_id=	intval($_cag['cag_id']);
			$cag	=	array();
			$cag['cag_title']		=	$_cag['cag_title'];
			$cag['cag_enable']		=	$_cag['cag_enable'];
			$cag['cag_description']	=	$_cag['cag_description'];

			$cag['_enable']			=	$this->_adminergroup_enable_status($_cag['cag_enable']);
			$cag['_update']			=	rgmdate($_cag['cag_updated'],'Y-m-d H:i');
			$list[$_cag_id]	=	$cag;
		}
		unset($tmp, $_cag);
		return $list;
	}

	/**
	 * (manage/base) 返回管理组状态文字描述
	 * @param number $enable
	 * @return string
	 */
	protected function _adminergroup_enable_status($enable){
		if ( isset($this->adminergroup_enables_desription[$enable]) ) {
			return $this->adminergroup_enables_desription[$enable];
		} else {
			return '*<del>'.$enable.'</del>*';
		}
	}

	/**
	 * (manage/base) 返回管理员锁定状态文字描述
	 * @param number $locked
	 * @return string
	 */
	protected function _adminer_lock_status($locked){
		if ( isset($this->adminer_locked_description[$locked]) ) {
			return $this->adminer_locked_description[$locked];
		} else {
			return '*<del>'.$locked.'</del>*';
		}
	}

}

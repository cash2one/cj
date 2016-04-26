<?php
/**
 * 从企业微信导入用户信息
 */

class voa_c_admincp_manage_member_impqywx extends voa_c_admincp_manage_member_base {

	public function __construct() {

		parent::__construct();
	}

	public function execute() {

		/** 迭代 2016-01-04 16:23:06 */

		$this->view->set('department', '/PubApi/Apicp/Imwxqy/Department');
		$this->view->set('member', '/PubApi/Apicp/Imwxqy/Member');
		$this->view->set('list', '/admincp/manage/member/list/');

		$this->output('manage/member/imwxqy');

		return true;

		/**
		 * 动作集合
		 * pull: 拉取所有用户
		 * department: 获取部门信息
		 * userinfo: 用户信息入库
		 */
		$acs = array('pull', 'department', 'userinfo');
		// 动作
		$ac = (string)$this->request->get('ac');

		// 执行
		$func = '_pull';
		switch ($ac) {
			case 'pull': $func = '_pull'; break;
			case 'userinfo': $func = '_import_userinfo'; break;
			case 'department': $func = '_import_department'; break;
			default: $func = '_confirm'; break;
		}

		return $this->$func();
	}

	/**
	 * 确认是否开始同步
	 */
	protected function _confirm() {
		$url = $this->cpurl('manage', 'member', 'impqywx', 0, array('ac' => 'department'));
		$list_url = $this->cpurl('manage', 'member', 'list', 0);

		if ($this->request->get('is_first')) {
			$message = '指定应用已经安装完毕，但为确保您的通讯录完整，请先进行通讯录同步操作，该操作会将微信企业号上的人员数据同步至畅移云工作，如果您不进行同步，将无法正常使用已安装的应用。';
		} else {
			$message = '同步通讯录功能是将微信企业号通讯录成员信息同步至畅移云工作，同步操作可能会需要一些时间（与通讯录成员数有关），是否确认进行同步？';
		}

		$this->message('info', '
		<p>'.$message.'</p>
		<a href="'.$url.'" class="btn btn-info">开始同步</a><span class="space"></span><span class="space"></span><a href="'.$list_url.'" class="btn">取消</a>', false);

		return true;
	}

	protected function _import_department() {

		$uda = &uda::factory('voa_uda_frontend_member_impqywx');
		if (!$uda->import_department($this->_user)) {
			$this->_success_message($uda->errmsg);
			return true;
		}

		return $this->_success_message(
			'部门信息导入成功, 请稍候...', '导入中', null, true,
			$this->cpurl('manage', 'member', 'impqywx', 0, array('ac' => 'pull'))
		);
	}

	// 拉取整个通讯录
	protected function _pull() {

		$uda = &uda::factory('voa_uda_frontend_member_impqywx');
		if (!$uda->pull($this->_user)) {
			$this->_error_message($uda->errmsg);
			return true;
		}

		return $this->_success_message(
			'正在同步微信后台的通讯录员工信息，请耐心等待(已完成: <font style="color:red;font-weight:bold;">0</font> 个)'
			.'<br /><br />温馨提示：<br />1. 同步完成之前，如果您离开本页面，同步将被中断。您可以请点击上方 同步微信后台通讯录 回到本页面完成同步。'
			.'<br />2. 如果您今后在微信后台修改或添加通讯录员工信息，请点击 同步微信后台通讯录 ，以便员工能够正常使用相关应用。', '导入中', null, true,
			$this->cpurl('manage', 'member', 'impqywx', 0, array('ac' => 'userinfo'))
		);
	}

	// 从微信读取用户信息, 然后入库
	protected function _import_userinfo() {

		$err_users = '';
		$uda = &uda::factory('voa_uda_frontend_member_impqywx');
		$page = (int)$this->request->get('page');
		$page ++;
		// 返回 false 为导入结束(导入出错或结束)
		if (!$uda->import_userinfo($this->_user, $err_users)) {

			// 导入完成，删除未同步的本地用户
			/**$set = voa_h_cache::get_instance()->get('setting', 'oa', true);
			if (!empty($set['sync_wx_lasttime'])) {
				$uda->delete_member($set['sync_wx_lasttime']);
			}*/

			// 更新部门人数
			$uda_mup = &uda::factory('voa_uda_frontend_member_update');
			$uda_mup->update_department_usernum();

			if ('ok' == $err_users) {
				$this->_success_message($uda->errmsg, '', null, true, $this->cpurl('manage', 'member', 'list'));
			} else {
				$this->_error_message($uda->errmsg."<br />导入失败用户列表:<br />".str_replace("\n", "<br />", $err_users));
			}

			return true;
		}

		return $this->_success_message(
			'正在导入(已完成 '.($page * 100).'个), 请稍候...', '导入中', null, true,
			$this->cpurl('manage', 'member', 'impqywx', 0, array('ac' => 'userinfo', 'page' => $page))
		);
	}

}

<?php
/**
 * 从微信导入用户信息的操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_member_impqywx extends voa_uda_frontend_member_base {
	// 用户信息
	protected $_user;
	// 站点缓存目录
	protected $_cache_path = '';
	// 已关注
	protected $_status_att = 1;

	public function __construct() {

		parent::__construct();
		// 取缓存目录
		$this->_cache_path = voa_h_func::get_sitedir();
	}

	// 部门信息入库
	public function import_department($user) {

		$this->_user = $user;
		// 取部门列表
		$addr = &voa_wxqy_addressbook::instance();
		$qywx_list = array();
		if (!$addr->department_list($qywx_list)) {
			$this->errmsg(100, '您的账号未与企业号绑定，请前往应用中心进行安装绑定');
			//$this->_success_message('读取部门列表失败, 请重新尝试');
			return false;
		}

		//logger::error(var_export($qywx_list, true));
		//exit("test for department, refresh laster please.");
		// 整理部门
		$wx_p2c = array();
		$qywx_dps = array();
		foreach ($qywx_list['department'] as $_dp) {
			if (!isset($wx_p2c[$_dp['parentid']])) {
				$wx_p2c[$_dp['parentid']] = array();
			}

			$wx_p2c[$_dp['parentid']][] = $_dp['id'];
			$qywx_dps[$_dp['id']] = $_dp;
		}

		// 获取已经存在的部门
		$departments = voa_h_cache::get_instance()->get('department', 'oa', true);
		$qywx2local = array();
		foreach ($departments as $_dp) {
			$qywx2local[$_dp['cd_qywxid']] = $_dp['cd_id'];
		}

		//exit("test for department, refresh laster please.");
		// 标记开始更新部门的时间点
		$depart_timestamp = startup_env::get('timestamp');
		// 遍历部门, 并入库
		$this->_update_department(0, $wx_p2c, $qywx_dps, $qywx2local, $departments);

		// 移除开始更新部门时间点之前的数据 by Deepseath
		$this->_delete_department($depart_timestamp);

		// 写入新同步的用户最后更新时间到common_setting表
		/**$serv_setting = &service::factory('voa_s_oa_common_setting');
		$set = voa_h_cache::get_instance()->get('setting', 'oa');
		if (empty($set['sync_wx_lasttime'])) {
			$cs_id = 0;
			$serv_setting->insert(array(
				'cs_key' => 'sync_wx_lasttime',
				'cs_value' => startup_env::get('timestamp'),
				'cs_type' => 0
			), $cs_id, false);
		} else {
			$serv_setting->update(array('cs_value' => startup_env::get('timestamp')), array('cs_key' => 'sync_wx_lasttime'));
		}*/

		// 删除缓存
		@unlink($this->_get_file_path());
		//$set = voa_h_cache::get_instance()->get('setting', 'oa', true);
		// 更新缓存
		voa_h_cache::get_instance()->get('department', 'oa', true);
		return true;
		/**return $this->_success_message(
				'部门信息导入成功, 请稍候...', '导入中', null, true,
				$this->cpurl('manage', 'member', 'impqywx', 0, array('ac' => 'pull'))
		);*/
	}

	// 拉取整个通讯录
	public function pull($user) {

		$this->_user = $user;
		// 取部门中人员列表
		$addr = &voa_wxqy_addressbook::instance();
		$result = array();
		if (!$addr->user_list($result)) {
			$this->errmsg(100, empty($addr->errmsg) ? '读取人员列表失败, 请重新尝试' : $addr->errmsg);
			//$this->_success_message('读取人员列表失败, 请重新尝试');
			return false;
		}

		// userid
		$userids = array();
		foreach ($result['userlist'] as $u) {
			$userids[$u['userid']] = $u['userid'];
		}

		// 把 openid 切成多个数组, 每个数组 200 个
		$serv_m = &service::factory('voa_s_oa_member');
		$chunks = array_chunk($userids, 200);
		foreach ($chunks as $_openids) {
			$serv_m->update_by_conditions(array(), array('m_openid' => $_openids));
		}
		// 删除企业号里不存在的用户(只有在读取到用户的情况下才进行删除操作)
		if (!empty($result['userlist'])) {
			$this->delete_member(startup_env::get('timestamp'));
		}

		// userid 写入临时文件
		file_put_contents($this->_get_file_path(), json_encode($result['userlist']));
		@unlink($this->_get_errlog_file());

		// 转向信息入库连接
		return true;
		/**return $this->_success_message(
			'正在导入(已完成:0 个), 请稍候...', '导入中', null, true,
			$this->cpurl('manage', 'member', 'impqywx', 0, array('ac' => 'userinfo'))
		);*/
	}

	// 从微信读取用户信息, 然后入库
	public function import_userinfo($user, &$err_users) {

		$this->_user = $user;
		// 从缓存中读取 userid
		$users = array();
		if (!$this->_get_users_cache($users)) {
			return false;
		}

		// 取 100 个用户
		$count = 100;
		$tmp_users = array();
		$tmp_ids = array();
		while (0 < $count --) {
			$_u = (array)array_shift($users);
			$tmp_ids[] = $_u['userid'];
			$tmp_users[] = $_u;
			if (empty($users)) {
				break;
			}
		}

		// 获取已经存在的用户
		$serv_m = &service::factory('voa_s_oa_member');
		$local_list = $serv_m->fetch_all_by_conditions(array('m_openid' => array($tmp_ids)));
		$userid_exist = array();
		$uid2openid = array();
		$uids = array();
		foreach ($local_list as $_u) {
			$userid_exist[$_u['m_openid']] = $_u;
			$uid2openid[$_u['m_uid']] = $_u['m_openid'];
			$uids[] = $_u['m_uid'];
		}

		// 读取用户扩展信息
		$serv_mf = &service::factory('voa_s_oa_member_field');
		$mem_fs = $serv_mf->fetch_by_conditions(array('m_uid' => array($uids)));
		foreach ($mem_fs as $_mf) {
			if (!$_mf['m_uid']) {
				continue;
			}

			$_openid = $uid2openid[$_mf['m_uid']];
			$userid_exist[$_openid] = array_merge($userid_exist[$_openid], $_mf);
		}

		// 取对照关系
		$local2qywx_map = config::get('voa.wxqy.local2qywx_map');
		$qywx2local_map = array_flip($local2qywx_map);

		$local_departments = voa_h_cache::get_instance()->get('department', 'oa');
		$qywx2local_id = array();
		foreach ($local_departments as $dp) {
			$qywx2local_id[$dp['cd_qywxid']] = $dp['cd_id'];
		}

		// 遍历
		foreach ($tmp_users as $_u) {
			// 如果用户已经存在, 则更新
			/**if (in_array($_u['userid'], $userid_exist)) {
				continue;
			}*/

			//$result = array();
			// 如果读取用户失败
			/**if (!$addr->user_get($_u['userid'], $result)) {
				logger::error($addr->errcode.':'.$addr->errmsg);
				file_put_contents($this->_get_errlog_file(), $_u['userid']."\t".$addr->errmsg."\n", FILE_APPEND);
				continue;
			}*/

			$uda_up = &uda::factory('voa_uda_frontend_member_update');
			// 取出可用值
			$submit = array();
			foreach ($qywx2local_map as $_k => $_v) {
				// 如果是部门
				if ('cd_id' == $_v) {
					$dps = array();
					foreach ($_u[$_k] as $cd_id) {
						$tmp_d = $qywx2local_id[$cd_id];
						if (!empty($tmp_d)) {
							$dps[] = $tmp_d;
						}
					}
					$submit[$_v] = $dps;
					//$cd_id = array_shift($result[$_k]);
					//$submit[$_v] = $qywx2local_id[$cd_id];
					continue;
				}

				$submit[$_v] = isset($_u[$_k]) ? $_u[$_k] : '';
			}

			// 职位名称
			$submit['cj_name'] = isset($submit['cj_id']) ? trim($submit['cj_id']) : '';
			unset($submit['cj_id']);

			// 判断信息是否存在
			if (empty($submit['m_mobilephone']) && empty($submit['m_email']) && empty($submit['m_weixin'])) {
				file_put_contents($this->_get_errlog_file(), $_u['userid']."\t手机/邮箱/微信号不能全部为空\n", FILE_APPEND);
				continue;
			}

			// 用户信息入库
			$mem = array();
			if (array_key_exists($_u['userid'], $userid_exist)) {
				$submit['m_uid'] = $userid_exist[$_u['userid']]['m_uid'];
			}

			if (!$uda_up->update($submit, $mem, array(), false)) {
				logger::error($uda_up->errcode . ':' . $uda_up->errmsg . var_export($submit, true));
				file_put_contents($this->_get_errlog_file(), $_u['userid'] . "\t" . $uda_up->errmsg . "\n", FILE_APPEND);
			}
			//}
			/* else {
				//$sendmail = $this->_status_att == $result['status'] ? false : true;
				$sendmail = false;
				if (!$uda_ins->add($submit, $mem, $sendmail, false)) {
					logger::error($uda_ins->errcode.':'.$uda_ins->errmsg.var_export($submit, true));
					file_put_contents($this->_get_errlog_file(), $_u['userid']."\t".$uda_ins->errmsg."\n", FILE_APPEND);
				}
			}*/
		}

		// 如果已经完成
		if (empty($users)) {
			@unlink($this->_get_file_path());
			$err_users = @file_get_contents($this->_get_errlog_file());
			if (empty($err_users)) {
				$err_users = 'ok';
			}

			$this->errmsg(200, '用户已经全部导入成功');
			//$this->_success_message('用户已经全部导入成功');
			return false;
		}

		file_put_contents($this->_get_file_path(), json_encode(array_values($users)));

		// 继续入库
		return true;
		/**$this->_success_message(
			'正在导入(已完成 '.($page * 10).'个), 请稍候...', '导入中', null, true,
			$this->cpurl('manage', 'member', 'impqywx', 0, array('ac' => 'userinfo', 'page' => $page))
		);*/
	}

	// 获取错误日志文件
	protected function _get_errlog_file() {

		return $this->_cache_path.'err_'.$this->_user['ca_id'].'.log';
	}

	// 获取缓存文件地址
	protected function _get_file_path() {

		return $this->_cache_path.'users_'.$this->_user['ca_id'].'.json';
	}

	// 从缓存中读取 userid
	protected function _get_users_cache(&$users) {

		// 获取缓存文件
		$file = $this->_get_file_path();
		// 如果文件不存在
		if (!is_file($file)) {
			logger::error("userid:{$this->_user['ca_id']}, file:{$file}");
			$this->errmsg(200, '导入失败[001]');
			return false;
		}

		// 读取文件内容
		$content = file_get_contents($file);
		if (empty($content)) {
			logger::error("content is empty, userid:{$this->_user['ca_id']}, file:{$file}");
			$this->errmsg(200, '导入失败[002]');
			return false;
		}

		// decode
		if (!$users = json_decode($content)) {
			@unlink($file);
			$this->errmsg(100, '数据错误, 请重新进行导入操作');
			return false;
		}

		return true;
	}

	/**
	 * 更新部门信息
	 * @param int $wx_cd_id 微信后台部门id
	 * @param array $wx_p2c 微信后台部门 p => c
	 * @param array $qywx_list 微信后台部门列表
	 * @param array $qywx2local 微信后台和本地部门id对照表
	 * @param array $departments 本地部门信息;
	 * @return boolean
	 */
	protected function _update_department($wx_cd_id, $wx_p2c, $qywx_list, &$qywx2local, &$departments) {

		// 如果部门信息存在
		if (isset($qywx_list[$wx_cd_id])) {
			$exist_cd_id = 0;
			$qywx_dp = $qywx_list[$wx_cd_id];
			foreach ($departments as $dp) {
				// 如果该部门已经存在, 则
				if ($dp['cd_qywxid'] == $wx_cd_id) {
					$exist_cd_id = $wx_cd_id;
				}
			}

			$cd_upid = 0;
			// 取微信部门上级 id
			$wx_parentid = $qywx_dp['parentid'];
			if (isset($qywx2local[$wx_parentid])) {
				$cd_upid = $qywx2local[$wx_parentid];
			}

			$department = array(
				'cd_name' => trim($qywx_dp['name']),
				'cd_upid' => $cd_upid,
				'cd_displayorder' => $wx_cd_id,
				'cd_usernum' => 0,
				'cd_qywxid' => $wx_cd_id,
				'cd_qywxparentid' => $wx_parentid
			);

			$serv_dp = &service::factory('voa_s_oa_common_department');
			if (0 < $exist_cd_id) {
				$serv_dp->update_by_conditions($department, array('cd_id' => $qywx2local[$wx_cd_id]));
			} else {
				$cd_id = $serv_dp->insert($department, true);
				$department['cd_id'] = $cd_id;
				$departments[$cd_id] = $department;
				$qywx2local[$wx_cd_id] = $cd_id;
			}
		}

		// 如果有子部门
		if (isset($wx_p2c[$wx_cd_id])) {
			foreach ($wx_p2c[$wx_cd_id] as $_id) {
				$this->_update_department($_id, $wx_p2c, $qywx_list, $qywx2local, $departments);
			}
		}

		return true;
	}

	/**
	 * 移除指定时间点之前更新的部门数据
	 * @param number $timestamp
	 * @return boolean
	 */
	protected function _delete_department($timestamp = 0) {

		$serv_dp = &service::factory('voa_s_oa_common_department');
		$serv_dp->delete_by_conditions("`cd_updated`<{$timestamp}");

		return true;
	}

	/**
	 * 移除指定时间点之前更新的用户数据
	 * @param number $timestamp
	 * @return boolean
	 */
	public function delete_member($timestamp = 0) {

		$cur_uid = 0;
		do {
			// 列出所有旧用户，可能需要移除
			$conds = array(
				'm_updated' => array($timestamp, '<'), // 更新时间早于时间点的
				'm_uid' => array($cur_uid, '>')
			);
			// 需要移除的用户
			$remove_member_uids = array();
			$list = $this->serv_member->fetch_all_by_conditions($conds, array('m_uid' => 'ASC'), 0, 1000);
			foreach ($list as $m) {
				// 标记删除的用户
				$remove_member_uids[$m['m_uid']] = $m['m_uid'];
				$cur_uid = $m['m_uid'] > $cur_uid ? $m['m_uid'] : $cur_uid;
			}

			if (! empty($remove_member_uids)) {
				// 存在待删除的用户，同时删除相关用户表数据
				$this->serv_member->delete($remove_member_uids);
				$this->serv_member_department->delete_by_m_uid($remove_member_uids);
				$this->serv_member_field->delete_by_ids($remove_member_uids);
				$this->serv_member_search->delete_by_m_uid($remove_member_uids);
				$this->serv_member_share->delete_by_m_uid($remove_member_uids);
			}
		} while (!empty($remove_member_uids));

		return true;
	}

}

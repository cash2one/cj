<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/12/30
 * Time: 上午10:19
 * 同步微信通讯录
 */

namespace PubApi\Controller\Apicp;

use Com\Pinyin;
use Common\Common\Wxqy;

class ImwxqyController extends AbstractController {

	/** userid 分割数量 */
	const USERID_NUM = 100;
	/** 管理员 */
	protected $_user;
	/** 在职 */
	const ACTIVE_YES = 1;

	/** 是 */
	const YES = 1;
	/** 否 */
	const NO = 0;
	/** 数字类型空值 */
	const INT_NULL = 0;
	/** 人员属性规则 */
	protected $_field = array();
	/** 每次删除多少人 */
	const DELETE_NUM = 50;
	/** 同步开始时间 */
	protected $_start_time = null;

	/** 微信人员缓存路径 */
	protected $_wxqy_cache_route = '';

	public function __construct() {

		parent::__construct();
	}


	/**
	 * 同步部门列表
	 * @return bool
	 */
	public function Department_post() {

		// 更新缓存
		clear_cache();

		// 取微信部门列表
		$qywx = &\Common\Common\Wxqy\Service::instance();
		$addrbook = new Wxqy\Addrbook($qywx);
		if (!$addrbook->department_list($result)) {
			return false;
		}
		if (empty($result['department'])) {
			E('_ERR_NO_DEP_TO_SYNCHRO');
			return false;
		}

		$qywx_dps = array(); // 部门信息
		// 整理部门
		foreach ($result['department'] as $_dp) {
			// 部门ID为键值的数组
			$qywx_dps[$_dp['id']] = $_dp;
		}

		// 微信部门ID为键值 本地部门ID为值
		$qywx2local_dpid = array();
		foreach ($this->_departments as $_dp) {
			$qywx2local_dpid[$_dp['cd_qywxid']] = $_dp['cd_id'];
		}

		// 微信端 部门ID与本地部门ID的差异 并删除
		$diff = array_diff_key($qywx2local_dpid, $qywx_dps);
		if (!empty($diff)) {
			$serv_dep = D('Common/CommonDepartment', 'Service');
			$serv_dep->delete_by_conds(array('cd_id' => $diff));
			$serv_dep->delete_by_conds(array('cd_qywxid' => ''));
		}

		// 遍历部门, 并入库
		$this->__update_department($qywx_dps, $this->_departments, $qywx2local_dpid);

		// 更新缓存
		clear_cache();

		$this->_set_error('同步部门列表成功', 0);
		return true;
	}

	/**
	 * 更新部门信息
	 * @param array $qywx_dps 微信 部门信息
	 * @param array $departments 本地部门信息
	 * @param array $qywx2local_dpid 本地部门上下级关联
	 * @return bool
	 */
	private function __update_department($qywx_dps, &$departments, &$qywx2local_dpid) {

		$serv_dep = D('Common/CommonDepartment', 'Service');
		// 遍历微信部门数据
		foreach ($qywx_dps as $_id => $_dep) {

			// 判断本地是否存在该部门
			$exist = self::NO;
			// 是否需要更新
			$update = self::NO;
			$cd_id = self::INT_NULL;
			foreach ($departments as $_cd_id => $_local_dep) {
				// 存在
				if ($_local_dep['cd_qywxid'] == $_id) {
					// 本地上级部门ID的微信ID
					$_local_wx_upid = array_search($_local_dep['cd_upid'], $qywx2local_dpid);
					if (!$_local_wx_upid) {
						$update = self::YES;
						$cd_id = $_cd_id;

						continue;
					}

					// 是否需要更新
					if ($_local_dep['cd_name'] != $_dep['name'] || $_local_dep['cd_qywxparentid'] != $_dep['parentid']
						// 本地上级部门ID 不等于微信
						|| $_local_dep['cd_qywxparentid'] != $_local_wx_upid) {
						$update = self::YES;
						$cd_id = $_cd_id;
					} else {
						$exist = self::YES;
					}

					break;
				}
			}

			// 存在并且不需要更新
			if ($exist == self::YES) {
				continue;
			}
			// 获取远程部门信息
			$wx_dep = $qywx_dps[$_id];
			// 如果本地有该ID部门(更新), 反之没有(创建)
			if ($update == self::YES) {
				$dep_update = array(
					'cd_name' => trim($wx_dep['name']), // 名称
					'cd_upid' => empty($qywx2local_dpid[$wx_dep['parentid']]) ? 0 : $qywx2local_dpid[$wx_dep['parentid']], // 本地上级部门ID
					'cd_qywxid' => $_id, // 微信部门ID
					'cd_qywxparentid' => $wx_dep['parentid'], // 微信上级部门ID
				);

				$serv_dep->update_by_conds(array('cd_id' => $cd_id), $dep_update);
			} else {
				$dep_insert = array(
					'cd_name' => trim($wx_dep['name']), // 名称
					'cd_upid' => empty($qywx2local_dpid[$wx_dep['parentid']]) ? 0 : $qywx2local_dpid[$wx_dep['parentid']], // 本地上级部门ID
					'cd_usernum' => 0, // 部门人数
					'cd_qywxid' => $_id, // 微信部门ID
					'cd_qywxparentid' => $wx_dep['parentid'], // 微信上级部门ID
				);

				// 写入部门
				$insert_cd_id = $serv_dep->insert($dep_insert);
				// 写入微信部门ID 对应本地部门ID 对应数组
				$qywx2local_dpid[$_id] = $insert_cd_id;
			}
		}

		return true;
	}


	/**
	 * 同步人员
	 * @return bool
	 */
	public function Member_post() {

		// 初始化
		$this->_user = $this->_login->user;

		$this->__get_wxqy_cache($userlist, $usercount);

		// 取每次数量
		$chunk_users = array_splice($userlist, 0, self::USERID_NUM);

		/** 获取所有职位信息 */
		$this->__get_job($chunk_users, $job_id2name);

		/** 获取微信部门对应的本地 ID数组 */
		$this->__wxdp_local($chunk_users, $department_list);

		/** 格式化数据 */
		$this->__format($chunk_users, $job_id2name, $department_list);

		/** 处理获取的数据 */
		$this->__deal_member_data($chunk_users, $job_id2name);

		if (empty($userlist)) {
			// 删除其余人
			$this->__delete_member($this->_start_time);
			// 删除缓存
			@unlink($this->_wxqy_cache_route);
			// 更新部门人数
			$this->_update_department_num();

			$this->_set_error('同步通讯录完成', 0);
		} else {
			// 存回文件
			$back = array(
				'list' => $userlist,
				'count' => $usercount,
				'start_time' => $this->_start_time,
			);
			file_put_contents($this->_wxqy_cache_route, json_encode($back));
			// 进度
			$next_number = count($userlist);
			$schedule = round(($usercount - $next_number) / $usercount, 2) * 100;
			$this->_set_error("{$schedule}", 1);
		}

		return true;
	}

	/**
	 * 人员更新操作
	 * @param array $user_list 本地存在的人员数据
	 * @param array $_user 微信端人员数据
	 * @param array $local_userid 本地存在的人员userid
	 * @param array $job_id2name 职位信息
	 * @return bool
	 */
	private function __member_update($user_list, $_user, $local_userid, $job_id2name) {

		$serv_mem = D('Common/Member', 'Service');
		$serv_mem_dep = D('Common/MemberDepartment', 'Service');
		$serv_search = D('Common/MemberSearch', 'Service');
		$serv_field = D('Common/MemberField', 'Service');

		// userid 和 m_uid 对应数组
		$userid_uid = array();
		foreach ($user_list as $_u_data) {
			$userid_uid[$_u_data['m_openid']] = $_u_data['m_uid'];
		}

		$insert_mem_dep = array(); // 要写入人员部门关联表的数组
		$delete_mem_dep = array(); // 要删除的人员部门关联表 人员ID
		foreach ($_user as $_data) {
			// 找出要更新的数组
			$update_member_data = array();
			if (array_search($_data['userid'], $local_userid) !== false) {
				// 写入搜索表的数据
				$ms_message = array();
				$update_member_data = array(
					'm_username' => $_data['name'],
					'm_mobilephone' => empty($_data['mobile']) ? '' : $_data['mobile'],
					'm_gender' => $_data['gender'],
					'm_email' => empty($_data['email']) ? '' : $_data['email'],
					'm_weixin' => empty($_data['weixinid']) ? '' : $_data['weixinid'],
					'm_face' => empty($_data['avatar']) ? '' : $_data['avatar'],
					'm_active' => $_data['status'] == self::QYWXST_FROZEN ? self::UNALLOW : self::ALLOW,
					'm_qywxstatus' => $_data['status'],
					'cd_id' => $_data['cd_id'],
				);
				// 是否有职位 并且存在
				if (!empty($_data['position']) && isset($job_id2name[$_data['position']])) {
					$update_member_data['cj_id'] = $_data['position'];
					$ms_message[] = $job_id2name[$_data['position']];
				}
				$serv_mem->update_by_conds(array('m_openid' => $_data['userid']), $update_member_data);

				$ms_message[] = $_data['name'];
				$this->_make_name_index($_data['name'], $_data['index']);
				$ms_message[] = $_data['index'];
				$ms_message[] = $update_member_data['m_mobilephone'];
				$ms_message[] = $update_member_data['m_email'];
				$ms_message[] = $update_member_data['m_weixin'];

				$uid = $userid_uid[$_data['userid']];

				$serv_search->update_by_conds(array('m_uid' => $uid), array('ms_message' => implode("\n", $ms_message)));

				// 写入扩展信息表的数据
				$this->_get_field_data($_data, $field_data);
				$serv_field->update_by_conds(array('m_uid' => $uid), $field_data);

				// 写入人员部门关联表的数组
				foreach ($_data['department'] as $_local_cdid) {
					$insert_mem_dep[] = array(
						'm_uid' => $uid,
						'cd_id' => $_local_cdid,
					);
				}
				// 删除的人员部门关联表 人员ID
				$delete_mem_dep[] = $uid;
			}
		}

		// 删除人员部门关联 人员ID 数据
		$serv_mem_dep->delete_by_conds(array('m_uid' => $delete_mem_dep));
		// 写入新的关联
		$serv_mem_dep->insert_all($insert_mem_dep);

		return true;
	}

	/**
	 * 添加人员
	 * @param array $user_list 人员列表
	 * @param array $job_id2name 职位ID 名称对应
	 * @return bool
	 */
	private function __member_insert($user_list, $job_id2name) {

		$serv_mem = D('Common/Member', 'Service');
		$serv_field = D('Common/MemberField', 'Service');

		// 要写入部门关联表的数组
		$insert_dep = array();
		// 要写入搜索表的数组
		$insert_search = array();
		// 处理要写入人员表的数组
		foreach ($user_list as $_user) {
			$insert_member = array(
				'm_username' => $_user['name'],
				'm_openid' => $_user['userid'],
				'cj_id' => empty($_user['position']) ? 0 : $_user['position'],
				'm_mobilephone' => empty($_user['mobile']) ? '' : $_user['mobile'],
				'm_gender' => $_user['gender'],
				'm_email' => empty($_user['email']) ? '' : $_user['email'],
				'm_weixin' => empty($_user['weixinid']) ? '' : $_user['weixinid'],
				'm_face' => empty($_user['avatar']) ? '' : $_user['avatar'],
				'm_facetime' => NOW_TIME,
				'm_active' => $_user['status'] == self::QYWXST_FROZEN ? self::UNALLOW : self::ALLOW,
				'm_qywxstatus' => $_user['status'],
				'm_index' => $_user['index'],
				'm_salt' => $_user['m_salt'],
				'm_password' => $_user['m_password'],
				'cd_id' => $_user['cd_id'],
				'm_updated' => $this->_start_time,
			);

			$m_uid = $serv_mem->insert($insert_member);

			// 写入搜索表的数据
			$ms_message = array(
				$_user['name'],
				$job_id2name[$insert_member['cj_id']],
				$insert_member['m_mobilephone'],
				$insert_member['m_email'],
				$insert_member['m_weixin'],
				$_user['index'],
			);
			$insert_search[] = array(
				'ms_message' => implode("\n", $ms_message),
				'm_uid' => $m_uid
			);

			// 写入扩展信息表的数据
			$this->_get_field_data($_user, $field_data);
			$field_data['m_uid'] = $m_uid;
			// 写入扩展
			$serv_field->insert($field_data);

			// 处理要写入人员部门关联表的数据
			foreach ($_user['department'] as $_dep) {
				$insert_dep[] = array(
					'm_uid' => $m_uid,
					'cd_id' => $_dep,
				);
			}
		}

		// 写入关联
		if (!empty($insert_dep)) {
			$serv_mem_dep = D('Common/MemberDepartment', 'Service');
			$serv_mem_dep->insert_all($insert_dep);
		}

		// 写入搜索表
		if (!empty($insert_search)) {
			$serv_search = D('Common/MemberSearch', 'Service');
			$serv_search->insert_all($insert_search);
		}

		return true;
	}

	/**
	 * 处理人员数据
	 * @param array $user_list 人员列表
	 * @param array $job_id2name 职位ID对应职位名称
	 * @param array $department_list 部门列表
	 * @return bool
	 */
	private function __format(&$user_list, $job_id2name, $department_list) {

		// 格式化数据
		foreach ($user_list as &$_data) {
			// 格式化为本地部门ID
			foreach ($_data['department'] as $_key => $_id) {
				$_data['department'][$_key] = $department_list[$_id];
			}

			// 职位处理
			if (!empty($_data['position'])) {
				$_data['position'] = array_search($_data['position'], $job_id2name);
			}

			// 处理名称首字母
			if (!empty($_data['name'])) {
				$this->_make_name_index($_data['name'], $name_index);
				$_data['index'] = $name_index;
			}

			// 获取密码
			$this->_make_user_password($_data);

			// 默认部门
			$_data['cd_id'] = $_data['department'][0];
		}

		return true;
	}

	/**
	 * 获取职位名称
	 * @param array $userlist 人员列表
	 * @param array $job_id2name 职位id 对应职位名称
	 * @return bool
	 */
	private function __get_job($userlist, &$job_id2name) {

		$serv_job = D('Common/CommonJob', 'Service');

		$jobs = array_unique(array_column($userlist, 'position'));
		// 查询职位
		if (!empty($jobs)) {
			$local_job_list = $serv_job->list_by_conds(array('cj_name' => $jobs));
		} else {
			$local_job_list = array();
		}
		// 职位ID对应名称
		$job_id2name = array();
		if (!empty($local_job_list)) {
			foreach ($local_job_list as $_job) {
				$job_id2name[$_job['cj_id']] = $_job['cj_name'];
			}
		}
		// 获取差异
		$job_diff = array_diff($jobs, $job_id2name);
		if (!empty($job_diff)) {
			// 写入差异职位名称
			foreach ($job_diff as $_cj_name) {
				$cj_id = $serv_job->insert(
					array(
						'cj_name' => $_cj_name
					)
				);
				$job_id2name[$cj_id] = $_cj_name;
			}
		}

		return true;
	}

	/**
	 * 获取微信部门对应的本地 ID数组
	 * @param $userlist
	 * @param $department_list
	 * @return bool
	 */
	private  function __wxdp_local($userlist, &$department_list) {

		$serv_dep = D('Common/CommonDepartment', 'Service');

		$department_list = array();
		// 获取所有提交数据里的部门
		foreach ($userlist as $_data) {
			foreach ($_data['department'] as $_id) {
				$department_list[] = $_id;
			}
		}
		// 去重
		$department_list = array_unique($department_list);
		// 查询本地对应的部门信息
		$local_dep_list = $serv_dep->list_by_conds(array('cd_qywxid' => $department_list));
		// 建立ID对应
		$department_list = array_flip($department_list);
		foreach ($local_dep_list as $_dep) {
			$department_list[$_dep['cd_qywxid']] = (int)$_dep['cd_id'];
		}

		return true;
	}

	/**
	 * 移除指定时间点之前更新的用户数据
	 * @param $start_time
	 * @return bool
	 */
	private function __delete_member($start_time) {

		$serv_mem = D('Common/Member', 'Service');
		$serv_mem_dep = D('Common/MemberDepartment', 'Service');
		$serv_mem_field = D('Common/MemberField', 'Service');
		$serv_mem_search = D('Common/MemberSearch', 'Service');
		$serv_mem_share = D('Common/MemberShare', 'Service');
		$serv_add_dep_connect = D('Common/CommonDepartmentConnect', 'Service');

		// 删除与本次同步无关的数据
		if (!empty($start_time)) {
			$totle = $serv_mem->count_less_than_updated($start_time);
			$num = self::DELETE_NUM;
			$times = ceil($totle / $num);
			for ($i = 1; $i <= $times; $i ++) {
				$delete_list = $serv_mem->list_less_than_updated($start_time, array(0, $num));
				$m_uids = array_column($delete_list, 'm_uid');
				$serv_mem->delete_by_conds(array('m_uid' => $m_uids));
				$serv_add_dep_connect->delete_by_conds(array('m_uid' => $m_uids));
				$serv_mem_dep->delete_by_conds(array('m_uid' => $m_uids));
				$serv_mem_field->delete_by_conds(array('m_uid' => $m_uids));
				$serv_mem_search->delete_by_conds(array('m_uid' => $m_uids));
				$serv_mem_share->delete_by_conds(array('m_uid' => $m_uids));
			}
		}

		return true;
	}

	/**
	 * 获取姓名的首字母
	 * @param string $username
	 * @param string $index <strong style="color:red">(引用结果)</strong>姓名首字母
	 * @return boolean
	 */
	protected function _make_name_index($username, &$index) {

		$pin = new Pinyin\Pinyin();
		$index = $pin->to_ucwords_first($username);

		return true;
	}

	/**
	 * 生成用户密码
	 * @param $member
	 */
	protected function _make_user_password(&$member) {

		$password = 'vchangyi';
		//使用手机后6位为密码
		if (!empty($member['m_mobilephone'])) {
			$password = md5(substr($member['m_mobilephone'], - 6));
		} //使用邮箱为密码
		elseif (!empty($member['m_email'])) {
			$password = md5($member['m_email']);
		} //使用微信id为密码
		elseif (!empty($member['m_weixin'])) {
			$password = md5($member['m_weixin']);
		}
		list($member['m_password'], $member['m_salt']) = generate_password($password, null, false);

	}

	/**
	 * 获取人员扩展信息
	 * @param $data
	 * @return bool
	 */
	protected function _get_field_data($data, &$result) {

		// 获取人员属性规则
		if (empty($this->_field)) {
			$this->_field = $this->_get_field();
		}

		if (!empty($data['extattr']['attrs'])) {
			// 获取规则里的字段名和名称关联数组
			foreach ($this->_field['custom'] as $_key => $_field) {
				$field[$_field['name']] = $_key;
			}
			foreach ($data['extattr']['attrs'] as $_data) {
				if (isset($field[$_data['name']])) {
					if (empty($_data['value'])) {
						continue;
					}
					$result['mf_' . $field[$_data['name']]] = $_data['value'];
				}
			}
		}

		return true;
	}

	/**
	 * 处理用户数据入库
	 * @param $chunk_users
	 * @param $job_id2name
	 * @return bool
	 */
	private function __deal_member_data($chunk_users, $job_id2name) {

		$serv_mem = D('Common/Member', 'Service');

		// 取出userid
		$userid = array_column($chunk_users, 'userid');
		// 查询
		$user_list = $serv_mem->list_by_conds(array('m_openid' => $userid));
		// 取出local_userid
		$local_userid = array_column($user_list, 'm_openid');
		// 获取差异
		$diff = array_diff($userid, $local_userid);

		// 更新人员
		if (!empty($local_userid)) {
			$this->__member_update($user_list, $chunk_users, $local_userid, $job_id2name);
		}

		// 去掉更新的人员
		foreach ($chunk_users as $_key => $_user_data) {
			if (in_array($_user_data['userid'], $local_userid)) {
				unset($chunk_users[$_key]);
			}
		}

		// 添加人员
		if (!empty($diff)) {
			$this->__member_insert($chunk_users, $job_id2name);
		}

		return true;
	}


	/**
	 * 获取微信人员列表缓存 和总数
	 * @param $user_list
	 * @param $count
	 * @return bool
	 */
	private function __get_wxqy_cache(&$user_list, &$count) {

		// 缓存路径
		$this->__get_cache_route();
		// 如果没有缓存则创建,否则返回数据
		if (!file_exists($this->_wxqy_cache_route)) {
			// 获取人员列表
			$result = array();
			// 获取微信人员数据
			$qywx = &\Common\Common\Wxqy\Service::instance();
			$addrbook = new Wxqy\Addrbook($qywx);
			if (!$addrbook->user_list($result)) {
				E('_ERR_WXQY_GET_MEMBER');

				return false;
			}
			$user_list = $result['userlist'];
			if (empty($user_list)) {
				E('_ERR_NO_PEOPLE_TO_SYNCHRO');

				return false;
			}
			// 写入本地文件
			$count = count($user_list); // 总数
			// 同步开始时间
			$this->_start_time = NOW_TIME;
			$list = array(
				'list' => $user_list,
				'count' => $count,
				'start_time' => $this->_start_time,
			);
			$json_result = json_encode($list);
			file_put_contents($this->_wxqy_cache_route, $json_result);
		} else {
			$user_cache = json_decode(file_get_contents($this->_wxqy_cache_route), true);
			if (empty($user_cache)) {
				E('_ERR_WXQY_GET_USER_CACHE');
				@unlink($this->_wxqy_cache_route);
			}
			$this->_start_time = $user_cache['start_time'];
			$user_list = $user_cache['list'];
			$count = $user_cache['count'];
		}

		return true;
	}

	/**
	 * 获取缓存路径
	 * @return bool
	 */
	private function __get_cache_route() {

		if (empty($this->_wxqy_cache_route)) {
			$this->_wxqy_cache_route = get_sitedir() . $this->_setting['ep_id'] . '_wxqy_user_' . $this->_login->user['ca_id'] . '.json';
		}

		return true;
	}
}

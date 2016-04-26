<?php
/**
 * GroupController.class.php
 * @create-time: 2015-07-01
 */
namespace File\Controller\Api;

use Common\Common\Cache;

class GroupController extends AbstractController {

	/**
	 * 新建分组
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function Group_create_post() {

		// 用户提交的参数
		$params = I('request.');

		// 非用户提交的扩展参数
		$extend = array (
			'm_uid'      => $this->_login->user['m_uid'],
			'm_username' => $this->_login->user['m_username']
		);

		// 操作成员表
		$serv_member = D('Common/Member', 'Service');
		// 默认为登录者为组长
		$group_leader_inf = $serv_member->get($extend['m_uid']);

		// 有效性验证
		if (!$group_leader_inf) {
			$this->_set_error('_ERR_manage_ERROR');
			return false;
		}

		// 初始化分组信息数组
		$group_inf = array ();

		// 新增分组
		$serv_group = D('File/Group', 'Service');
		if (!$serv_group->add_group($group_inf, $params, $extend)) {
			$this->_set_error($serv_group->get_errmsg(), $serv_group->get_errcode());
			return false;
		}

		// 分组id
		$f_id = $group_inf['f_id'];
		
		// 返回分组数据
		$group_data = $serv_group->get_filelds($group_inf, array (
			'f_id',
			'f_name',
			'f_description'
		));

		// 格式化数据
		$serv_format = D('File/Format', 'Service');
		$serv_format->group($group_data);

		// 操作权限表
		$serv_filePermission = D('File/FilePermission', 'Service');

		// 构建组长数组信息
		$group_leader = $serv_filePermission->get_leader_array($f_id, $group_leader_inf, $extend);

		// 插入组长信息
		if (!$serv_filePermission->insert_data($group_leader, $extend)) {
			$this->_set_error($serv_filePermission->get_errmsg(), $serv_filePermission->get_errcode());
			return false;

		}

		// 返回数据
		$this->_result = $group_data;
		return true;
	}

	/**
	 * 分组设置
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function Group_edit_post() {

		// 分组信息
		$group_inf = array ();

		// 用户提交的参数
		$params = I('request.');
		$group_id = (int)$params['group_id'];

		// 组长
		$group_leader = (int)$params['group_leader'];
		$group_department = (array)$params['group_department'];
		$group_members = (array)$params['group_members'];

		// 操作分组
		$serv_edit = D('File/Group', 'Service');

		// 判断group_id是否为分组
		if (!$serv_edit->is_group($group_id)) {
			$this->_set_error('_ERR_NO_GROUP');
			return false;
		}

		// 判断权限（只有组长有权操作）
		if (!$this->_get_permission($group_id, \File\Model\FilePermissionModel::MT_CHARGEHAND, $this->_login->user['m_uid'])) {
			$this->_set_error('_ERR_NO_AUTHORITY');
			return false;
		}

		// 非用户提交的扩展参数
		$extend = array (
			'm_uid'      => $this->_login->user['m_uid'],
			'm_username' => $this->_login->user['m_username']
		);

		// 操作成员表
		$serv_member = D('Common/Member', 'Service');

		// 获取所有成员信息
		$all_member = $serv_member->list_all();

		// 获取组长信息
		if(!empty($group_leader)) {
			$group_leader_inf = $serv_member->get($group_leader);
		}else{
			// 默认为登录者
			$group_leader_inf = $serv_member->get($extend['m_uid']);
		}

		// 有效性验证
		if (!$group_leader_inf) {
			$this->_set_error('_ERR_LEADER_ERROR');
			return false;
		}

		// 初始化协作者成员信息数组
		$group_member_inf=array();

		// 获取成员信息 $group_members为协作者成员信息
		if(!empty($group_members)) {
			$group_member_inf = $serv_member->list_by_pks($group_members);
		}

		// 读取部门信息缓存信息
		$cache = &Cache::instance();
		$department_list = $cache->get('Common.department');

		// 查找部门
		$serv_depart = D('Common/CommonDepartment', 'Service');

		// 获取部门信息-$group_department为协作者部门信息
		$department_inf = $serv_depart->department_bl($group_department, $department_list);

		// 更改分组操作
		if (!$serv_edit->edit_group($group_inf, $params, $extend)) {
			$this->_set_error($serv_edit->get_errmsg(), $serv_edit->get_errcode());
			return false;
		}

		// 更改权限表
		$serv_filePermission = D('File/FilePermission', 'Service');

		// 更改组长
		if (!$serv_filePermission->edit_group_leader($group_id, $group_leader_inf, $extend)) {
			$this->_set_error($serv_filePermission->get_errmsg(), $serv_filePermission->get_errcode());
			return false;
		}

		// 删除之前成员信息
		if (!$serv_filePermission->delete_all_member($group_id)) {
			$this->_set_error($serv_filePermission->get_errmsg(), $serv_filePermission->get_errcode());
			return false;
		}

		// 初始化成员数组
		$member_inf = array ();
		$serv_filePermission->get_member_array($group_id, $group_member_inf, $member_inf, $extend);

		// 查找部门成员
		$serv_member->get_by_departs($group_id, $department_inf, $all_member, $member_inf, $extend);

		// 插入成员权限数据
		if (!$serv_filePermission->insert_all_data($member_inf, $extend)) {
			$this->_set_error($serv_filePermission->get_errmsg(), $serv_filePermission->get_errcode());
			return false;
		}

		return true;
	}

	/**
	 * 分组详情
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function Group_info_get() {

		// 获取分组group_id
		$params = I('request.group_id');

		// 操作分组
		$serv_group = D('File/Group', 'Service');

		// 判断group_id是否为分组
		if (!$serv_group->is_group($params)) {
			$this->_set_error('_ERR_NO_GROUP');
			return false;
		}

		// 判断权限（协作者有权操作）
		if (!$this->_get_permission($params, \File\Model\FilePermissionModel::MT_COLLABORATORS, $this->_login->user['m_uid'])) {
			$this->_set_error('_ERR_NO_AUTHORITY');
			return false;
		}

		// 操作成员表
		$serv_member = D('Common/Member', 'Service');

		// 获取成员信息
		$all_member = $serv_member->list_all();

		// 返回查询分组数据
		$group_inf = $serv_group->get($params);

		// 返回分组特定字段分组数据
		$group_data = $serv_group->get_filelds($group_inf, array (
			'f_id',
			'f_name',
			'f_description'
		));

		// 格式化数据
		$serv_format = D('File/Format', 'Service');
		$serv_format->group($group_data);

		// 返回分组信息
		$f_data = $group_data;

		// 操作权限
		$serv_filePermission = D('File/FilePermission', 'Service');

		// 返回查询分组组长
		$leader_inf = $serv_filePermission->get_leader($params);
		$f_data['group_leader'] = $leader_inf['m_uid'];
		$f_data['group_leader_name'] = $leader_inf['m_username'];

		// 和成员表比对获取头像
		$f_data['group_leader_face'] = $this->_seekarr($all_member, 'm_uid', $leader_inf['m_uid'])['m_face'];

		// 初始化返回值
		$return_depart_data = array ();
		$return_member_data = array ();
		$depart_data= array ();
		$member_data= array ();

		// 判断是否为全公司成员
		if ($serv_filePermission->is_all($params)) {
			$f_data['group_members'] = 'all';
		} else {

			// 返回分组成员信息
			$member_inf = $serv_filePermission->list_member($params);
			foreach ($member_inf as $v) {

				// 判断成员id是否存在
				if ($serv_filePermission->is_exist($all_member, 'm_uid', $v['m_uid'], $return_member_data)) {

					// 成员数组
					$member_data[] = array (
						'member_uid'      => $return_member_data['m_uid'],
						'member_username' => $return_member_data['m_username'],
						'member_face'     => $return_member_data['m_face']
					);
				} else {
					// 当成员不存在时记录其id
					$m_uids[] = $v['m_uid'];
				}
			}

			// 当成员不存在时删除其信息
			if(!empty($m_uids)) {
				$serv_filePermission->delete_by_m_uid($m_uids, $params);
			}

			// 读取部门信息缓存信息
			$cache = &Cache::instance();
			$department_list = $cache->get('Common.department');

			// 返回分组部门信息
			$depart_inf = $serv_filePermission->list_depart($params);

			foreach ($depart_inf as $v) {

				// 判断部门id是否存
				if ($serv_filePermission->is_exist($department_list, 'cd_id', $v['cd_id'], $return_depart_data)) {

					// 判断是否为所选顶级部门
					if($v['p_mark_cd_id'] == $v['cd_id']){
						$depart_data[] = array (
							'department_id'   => $return_depart_data['cd_id'],
							'department_name' => $return_depart_data['cd_name']
						);
					}
				} else {

					// 当分组部门不存在时记录其id
					$cd_ids[] = $v['cd_id'];
				}
			}

			// 删除不存在的部门
			if(!empty($cd_ids)) {
				$serv_filePermission->delete_by_cd_id($cd_ids, $params);
			}

			$f_data['group_members'] = $member_data;
			$f_data['group_department'] = $depart_data;
		}

		return $this->_response($f_data);
	}

	/**
	 * 分组成员列表
	 *
	 * @author: liupengwei
	 * @email: liupengwei@vchangyi.com
	 */
	public function List_group_member_get() {

		// 获取分组ID
		$f_id = (int)I('request.group_id');

		// 获取@的成员对象匹配get到的member_username的关键字,用作模糊查询
		$name = I('request.member_username');

		// 分页信息
		$limit = I('get.limit');
		$page = I('get.page');

		// 判断权限（如果成员类型非浏览者，报错）
		if (!$this->_get_permission($f_id, \File\Model\FilePermissionModel::MT_BROWSE, $this->_login->user['m_uid'])) {
			$this->_set_error('_ERR_NO_AUTHORITY');
			return false;
		}

		// 判断每页条数是否正确 ,如果不合法赋予系统默认值
		if ($limit < cfg('LIMITminsize') || $limit > cfg('LIMITmaxsize')) {
			$limit = cfg(limit);
		}

		// 如果搜索name内容为空，取前100条数据
		if(empty($name)){
			$limit = cfg(mrlimit);
		}

		list($start, $limit, $page) = page_limit($page, $limit);
		// 分页参数
		$page_option = array(
			$start,
			$limit
		);

		// 调用查询方法
		$serv_gm = D('File/FilePermission', 'Service');
		$data = $serv_gm->list_members($f_id, $page_option, $name);

		// 格式化
		$serv_fmt = D('File/Format', 'Service');
		foreach ($data as &$_v) {
			$serv_fmt->group($_v);
		}

		unset($_v);

		// 统计总数
		$count = count($data);
		$res = array(
			'total' => $count,
			'limit' => $limit,
			'data' => $data
		);

		return $this->_response($res);

	}

	/**
	 * 文件分组列表
	 *
	 * @author: liupengwei
	 * @email: liupengwei@vchangyi.com
	 */
	public function group_list_get() {

		// 分页信息
		$limit = I('get.limit');
		$page = I('get.page');

		// 判断每页条数是否正确 ,如果不合法赋予系统默认值10
		if ($limit < cfg('LIMITminsize') || $limit > cfg('LIMITmaxsize')) {
			$limit = cfg(limit);
		}

		list($start, $limit, $page) = page_limit($page, $limit);
		// 分页参数
		$page_option = array(
			$start, $limit
		);

		// 获取用户m_uid
		$m_uid = $this->_login->user['m_uid'];

		// 查询文件分组列表
		$serv_gl = D('File/FilePermission', 'Service');
		$data = $serv_gl->list_groups($m_uid, $page_option, array('f_id' => 'ASC'));

		// 格式化
		$serv_fmt = D('File/Format', 'Service');
		foreach ($data as &$_v) {
			$serv_fmt->group($_v);
		}

		unset($_v);

		// 统计总数
		$count = count($data);
		$res = array(
			'total' => $count,
			'limit' => $limit,
			'data' => $data
		);

		$this->_result=$res;
		return $res;

	}

	/**
	 * 解散分组
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function Group_delete_get() {

		// 获取分组group_id
		$params = I('request.group_id');

		// 操作分组信息
		$serv_group = D('File/Group', 'Service');

		// 判断group_id是否为分组
		if (!$serv_group->is_group($params)) {
			$this->_set_error('_ERR_NO_GROUP');
			return false;
		}

		// 判断权限（只有组长有权操作）
		if (!$this->_get_permission($params, \File\Model\FilePermissionModel::MT_CHARGEHAND, $this->_login->user['m_uid'])) {
			$this->_set_error('_ERR_NO_AUTHORITY');
			return false;
		}

		// 删除文件表中分组信息
		if (!$serv_group->delete_all($params)) {
			$this->_set_error($serv_group->get_errmsg(), $serv_group->get_errcode());
			return false;
		}

		// 操作权限表
		$serv_permission = D('File/FilePermission', 'Service');

		// 删除权限表中所有分组成员
		if (!$serv_permission->delete_all($params)) {
			$this->_set_error($serv_permission->get_errmsg(), $serv_permission->get_errcode());
			return false;
		}

		return true;
	}

	/**
	 * 退出分组（分组成员）
	 *
	 * @author: liupengwei
	 * @email: liupengwei@vchangyi.com
	 */
	public function group_exit_get() {

		// 获取分组fid
		$f_id = (int)I('request.group_id');

		// 获取非传入参数
		$extend = array(
			'uid' => $this->_login->user['m_uid']
		);

		// 当前登陆用户id
		$m_uid = $extend['uid'];

		// 操作分组信息
		$serv_group = D('File/Group', 'Service');

		// 判断group_id是否为分组
		if(!$serv_group->is_group($f_id)){
			$this->_set_error('_ERR_NO_GROUP');
			return false;
		}

		// 判断权限（如果成员类型非浏览者,即不是该组成员,报错）
		if (!$this->_get_permission($f_id, \File\Model\FilePermissionModel::MT_BROWSE, $this->_login->user['m_uid'])) {
			$this->_set_error('_ERR_EXIT_ERROR');
			return false;
		}

		// 判断权限（如果是组长要退出，则报错）
		if ($this->_get_permission($f_id,\File\Model\FilePermissionModel::MT_CHARGEHAND,$this->_login->user['m_uid'])) {
			$this->_set_error('_ERR_AUTHOR_EXIT_ERROR');
			return false;
		}

		// 操作文件权限表
		$serv_ge = D('File/FilePermission', 'Service');
		if (!$serv_ge->delete_member($f_id,$m_uid)) {
			$this->_set_error($serv_ge->get_errmsg(), $serv_ge->get_errcode());
			return false;
		}

		return true;
	}


	/**
	 * 比较更新部门成员
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function Compare_depart() {

		// 存放部门表中已删除的
		$member1 = array ();

		// 存放部门表中新增的
		$member2 = array ();

		// 读取部门信息缓存信息
		$cache = &Cache::instance();
		$department_list = $cache->get('Common.department');

		// 获取权限表中部门
		$serv_filePermission = D('File/FilePermission', 'Service');
		$depart_ids = $serv_filePermission->list_depart_id();

		// 判断权限表中部门id是否为空
		if(!empty($depart_ids)) {

			// 转换形式获得权限表中部门id
			$depart_ids = array_column($depart_ids, 'cd_id');

            // 转换形式获得部门表中部门id
			$department_ids = array_column($department_list, 'cd_id');

            // 权限表中较部门表多的id（注：说明该部门已被删除）
			$cd_ids = array_diff($depart_ids, $department_ids);

			// 删除已不存在的部门
			if(!empty($cd_ids)) {
				$serv_filePermission->delete_by_depart($cd_ids);
			}

			// 获取权限表中用户添加的数据
			$data0 = $serv_filePermission->list_all_member_by_user();

			// 实例化member表信息
			$serv_member = D('Common/Member', 'Service');

			// 获取成员表中所有成员
			$all_member = $serv_member->list_all();

			// 比对权限表中用户添加数据和成员表中差异
			$member_ids = $serv_filePermission->list_diff_member_id($data0, $all_member);

            // 比对后删除权限表中多的信息
			if(!empty($member_ids)){
				$serv_filePermission->delete_member_by_id($member_ids);
			}

			// 获取权限表中逻辑添加的数据
			$data1 = $serv_filePermission->list_depart_member($depart_ids);

			// 获取部门成员
			$data2 = $serv_member->list_depart_member($depart_ids, $all_member);

			// 部门成员比对
			if (!$serv_filePermission->compare($data1, $data2, $member1, $member2)) {
				$this->_set_error($serv_filePermission->get_errmsg(), $serv_filePermission->get_errcode());
				return false;
			}

			// 权限表中添加部门新增成员数据
			if (!$serv_filePermission->list_group_member($member2, $data2)) {
				$this->_set_error($serv_filePermission->get_errmsg(), $serv_filePermission->get_errcode());
				return false;
			}

			// 删除权限表中多余的成员
			if (!$serv_filePermission->delete_by_uids($member1)) {
				$this->_set_error($serv_filePermission->get_errmsg(), $serv_filePermission->get_errcode());
				return false;
			}
		}

		return true;
	}
}

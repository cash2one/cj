<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 15/12/18
 * Time: 下午2:03
 */

namespace PubApi\Controller\Apicp;
use Common\Common\Wxqy\Addrbook;
use Common\Common\Department;

class DepartmentController extends AbstractController {

	// 指定部门
	const PERMISSION_APPOINT = 2;
	// 仅本部门
	const PERMISSION_SELF = 1;
	// 全公司
	const PERMISSION_ALL = 0;

	// 有附加权限
	const EXTRAPERM_YES = 1;
	// 无附加权限
	const EXTRAPERM_NO = 0;

	// 部门名长度限制 array(长度单位, min, max)
	public $department_name_length = array('count', 1, 64);
	// 显示顺序最小值
	public $dep_displayorder_min = 0;
	// 显示顺序最大值
	public $dep_displayorder_max = 99;
	// 权限每次入库的记录数
	protected $_max_insert = 200;

	/**
	 * 部门列表接口
	 */
	public function List_get() {

		$params = I('get.');
		$cd_upid = $params['cd_id'];
		if (empty($cd_upid)) {
			// 获取顶级部门id
			Department::instance()->get_top_cdid($cd_upid);
		}

		$search = $params['cd_name'];
		// 如果部门id为空, 则直接返回
		if ($cd_upid < 1) {
			$this->_result = array();
			return true;
		}

		$departments = array();
		// 搜索操作
		if (!empty($search)) {
			// 获取标签列表
			$departments = $this->_search_department($search);
			return true;
		}

		// 遍历所有部门，获取up_id下的所有部门
		$main_cdids = array();
		foreach ($this->_departments as $_dep) {
			if ($_dep['cd_upid'] == $cd_upid) {
				$departments[] = $this->_format_department($_dep);
			}
			// 顶级部门
			if (0 == $_dep['cd_upid']) {
				$main_cdids = $this->_format_department($_dep);
			}
		}

		// 返回值
		$this->_result = array(
			'departments' => $departments,
			'main_cdids' => $main_cdids,
		);

		return true;
	}

	/**
	 * 格式化输出内容
	 * @param array $dep 部门信息;
	 * @return boolean
	 */
	protected function _format_department($dep) {

		return array(
			'cd_id' => $dep['cd_id'],
			'cd_name' => $dep['cd_name'],
			'cd_permission' => $dep['cd_permission'],
			'cd_displayorder' => $dep['cd_displayorder'],
			'cd_lastordertime' => empty($dep['cd_lastordertime']) ? 0 : $dep['cd_lastordertime'],
			'cd_usernum' => $dep['cd_usernum']
		);
	}

	/**
	 * 搜索的方法
	 * @param $search string 搜索的内容
	 * @return bool 接口返回数据
	 */
	protected function _search_department($search) {

		$list = array();
		foreach ($this->_departments as $_cdid => $_dep) {
			// 过滤掉总部门
			if ($_dep['cd_upid'] == 0) {
				$main_cdids = $this->_format_department($_dep);
				continue;
			}
			if (preg_match('/' . $search . '/', $_dep['cd_name'])) {
				$list[] = $this->_format_department($_dep);
			}
		}

		$this->_result = array(
			'departments' => $list,
			'main_cdids' => $main_cdids,
		);

		return true;
	}

	/**
	 * 编辑部门初始化接口
	 */
	public function Initial_get() {

		$params = I('get.');
		$cd_id = $params['cd_id'];
		// 如果部门ID为空, 则报错
		if (empty($cd_id)) {
			E('_ERR_EMPTY_GET_CDID');
			return false;
		}

		// 从缓存中获取部门信息
		$record = $this->_departments[$cd_id];
		// 显示的负责人信息
		$connect_mem = array();
		// 从部门负责人表中读取
		$serv_dep_connect = D('Common/CommonDepartmentConnect', 'Service');
		$conds_dep_connect['cd_id'] = $cd_id;
		$connect_list = $serv_dep_connect->list_by_conds($conds_dep_connect);
		if (!empty($connect_list)) {
			foreach ($connect_list as $_connect) {
				$connect_mem[] = array(
					'm_uid' => $_connect['m_uid'],
					'm_username' => $_connect['m_username'],
					'selected' => (bool)true
				);
			}
		}

		// 附加权限
		$permission_list = array();
		if ($record['cd_permission'] == self::PERMISSION_APPOINT) {
			// 取部门的权限部门
			$serv_dep_permission = D('Common/CommonDepartmentPermission', 'Service');
			$conds_permission = array('cd_id' => $cd_id);
			$per_dep = $serv_dep_permission->list_by_conds($conds_permission);
			if (!empty($per_dep)) {
				foreach ($per_dep as $_per) {
					$permission_list[] = array(
						'id' => $_per['per_id'],
						'name' => $_per['per_name'],
						'isChecked' => (bool)true
					);
				}
			}
		}
		// 负责人数量
		$connect_count = count($connect_list);

		// 返回值
		$this->_result = array(
			'cd_id' => $record['cd_id'],
			'cd_name' => $record['cd_name'],
			'cd_displayorder' => $record['cd_displayorder'],
			'cd_connect' => json_encode($connect_mem),
			'connect_count' => $connect_count,
			'permission_list' => json_encode($permission_list),
			'permission' => $record['cd_permission'],
		);
	}

	/**
	 * 删除部门接口
	 */
	public function Delete_post() {

		$serv_dep = D('Common/CommonDepartment', 'Service');
		$params = I('post.');
		$cd_id = $params['cd_id'];
		// 部门存在
		if (!isset($params['cd_id']) || !is_numeric($params['cd_id'])) {
			E('_ERR_EMPTY_DEPARTMENT_DELETE');
			return false;
		}
		if (!isset($this->_departments[$cd_id])) {
			E('_ERR_DEPARTMENT_HAVE_DELETED');
			return false;
		}
		// 获取顶级部门id
		if ($this->_departments[$cd_id]['cd_upid'] == 0) {
			E('_ERR_NOT_OPERATE_TOPID');
			return false;
		}

		// 该部门信息
		$department = $this->_departments[$cd_id];
		// 判断该部门下是否有人员
		$serv_mem_dep = D('Common/MemberDepartment', 'Service');
		$count_dep = $serv_mem_dep->count_dep_member($department['cd_id']);
		if ($count_dep > 0) {
			E('_ERR_DEPARTMENT_NOT_EMPTY');
			return false;
		}

		// 判断部门下是否有子部门
		foreach ($this->_departments as $_child_val) {
			if ($_child_val['cd_upid'] == $cd_id) {
				E('_ERR_NOT_EMPTY_CHILD_DEPARTMENT');
				return false;
			}
		}
		// 本地存在该部门在企业微信部门id的对应关系，则请求接口删除
		$cache = &\Common\Common\Cache::instance();
		$sets = $cache->get('Common.setting');
		if (!empty($department['cd_qywxid']) && !empty($sets['ep_wxqy'])) {

			// 加载微信通讯录接口
			$qywx = &\Common\Common\Wxqy\Service::instance();
			$addrbook = new Addrbook($qywx);

			// 返回的结果
			$result = array();
			if (!$addrbook->department_delete($result, $department['cd_qywxid'])) {
				E('_ERR_DELETE_DEPARTMENT');
				return false;
			}
		}

		// 删除本地数据
		$serv_dep->delete($cd_id);

		// 删除部门权限表关联数据
		$serv_dep_permission = D('Common/CommonDepartmentPermission', 'Service');
		$conds_permission = array('per_id' => $cd_id);
		$serv_dep_permission->delete_by_conds($conds_permission);
		// 更新缓存
		clear_cache();

		return true;
	}

	/**
	 * 创建和编辑部门接口
	 */
	public function Post_post() {

		$params = I('post.');
		// 数据验证
		$data = $this->_format($params);
		$cd_id = $params['cd_id'];
		$last_data = array();
		$last_data['cd_name'] = $data['cd_name'];
		$last_data['cd_displayorder'] = $data['cd_displayorder'];
		$last_data['cd_lastordertime'] = $data['cd_lastordertime'];
		if (!empty($params['cd_id'])) {
			//判断排序是否改变
			$serv_dep = D('Common/CommonDepartment', 'Service');
			$cur_record = $serv_dep->get($cd_id);
			if ($cur_record['cd_displayorder'] == $params['cd_displayorder']) {
				unset($last_data['cd_lastordertime']);
			}
		}
		if (isset($params['cd_id']) && $this->_departments[$cd_id]['cd_upid'] == 0) {
			E('_ERR_NOT_OPERATE_TOPID');
			return false;
		}

		// 编辑部门时
		if (!empty($params['cd_id']) && isset($this->_departments[$params['cd_id']])) {
			$last_data['cd_upid'] = $this->_departments[$params['cd_id']]['cd_upid'];
			$last_data['cd_id'] = $params['cd_id'];
		} elseif (!empty($params['cd_upid']) && isset($this->_departments[$params['cd_upid']])) { // 新增部门时
			$last_data['cd_upid'] = $params['cd_upid'];
		} else {
			E('_ERR_NO_DEPARTMENT_MATCH');
			return false;
		}
		$last_data['cd_permission'] = $data['cd_permission'];

		$serv_dep = D('Common/CommonDepartment', 'Service');
		// 同步微信和本地
		$department = array();
		$serv_dep->update_dep($last_data, $department);
		if (!empty($department['cd_id'])) {
			$cd_id = $department['cd_id'];
		}

		// 更新扩展浏览权限
		if($data['cd_permission'] == self::PERMISSION_APPOINT) {
			$this->_update_extraperm($data['cd_permission'], $cd_id, $data['permission_list'], $data['permission_cover']);
		} else {
			// 只更新浏览权限
			$this->_update_perm_status($cd_id, $data['cd_permission'], $data['permission_cover']);
		}
		// 更新部门负责人
		$this->_update_connecter($data['cd_id'], $data['cd_connect']);

		// 更新缓存
		clear_cache();
		return true;
	}

	/**
	 * 批量更改子部门权限状态
	 * @param $cd_id int 部门id
	 * @param $code int 状态值
	 * @param int $iscover 是否覆盖子部门, 0: 不覆盖, 1: 覆盖
	 * @return bool
	 */
	protected function _update_perm_status($cd_id, $code, $iscover) {

		$serv_permission = D('Common/CommonDepartmentPermission', 'Service');

		// 待更新部门列表
		$cdids = array($cd_id);
		// 获取所有子部门
		if (1 == $iscover) {
			$cdids = Department::instance()->list_childrens_by_cdid($cd_id);
		}

		// 如果下级部门ID为空
		if (empty($cdids)) {
			return true;
		}

		//删除相关部门的权限部门
		$conds_per = array('cd_id' => $cdids);
		$serv_permission->delete_by_conds($conds_per);

		//更改状态
		$serv_dep = D('Common/CommonDepartment', 'Service');
		$serv_dep->update($cdids, array('cd_permission' => $code));

		return true;
	}

	/**
	 * 更新部门权限
	 * @param int $code 权限标识
	 * @param int $cd_id 当前部门ID
	 * @param array|string $cdids 可浏览权限部门id
	 * @param int $iscover 是否覆盖子部门, 0: 不覆盖, 1: 覆盖
	 */
	protected function _update_extraperm($code, $cd_id, $perm_cdids, $iscover = 0) {

		// 如果非数组, 则按 , 切分
		if (!is_array($perm_cdids)) {
			$perm_cdids = trim($perm_cdids);
			$perm_cdids = explode(',', $perm_cdids);
		}

		// 待更新部门列表
		$cdids = array($cd_id);
		// 获取所有子部门
		if (1 == $iscover) {
			$cdids = Department::instance()->list_childrens_by_cdid($cd_id, true);
		}

		$serv_permission = D('Common/CommonDepartmentPermission', 'Service');
		//更改子部门权限状态
		$this->_update_perm_status($cd_id, $code, $iscover);

		// 权限部门id为空
		if (empty($perm_cdids)) {
			return true;
		}

		// 检查部门id合法
		$departments = array();
		foreach ($perm_cdids as $_cd_id) {
			if (!isset($this->_departments[$_cd_id])) {
				continue;
			}

			$departments[$_cd_id] = $this->_departments[$_cd_id];
		}

		// 为空则直接返回
		if (empty($departments)) {
			return true;
		}

		// 新增新权限部门
		$perm_list = array();
		foreach ($departments as $_dep) {
			foreach ($cdids as $_cd_id) {
				$perm_list[$_cd_id . ',' . $_dep['cd_id']] = array(
					'cd_id' => $_cd_id,
					'per_id' => $_dep['cd_id'],
					'per_name' => $_dep['cd_name'],
				);
			}
		}

		// 防止一次入库数据太多, 每次入库指定的条数
		$perm_list = array_chunk($perm_list, $this->_max_insert);
		foreach ($perm_list as $_list) {
			$serv_permission->insert_all($_list);
		}

		return true;
	}

	/**
	 * 更新联系人
	 * @param array|string $uids 用户UID
	 */
	protected function _update_connecter($cd_id, $uids) {

		// 如果非数组, 则按 ',' 切分
		if (!is_array($uids)) {
			$uids = trim($uids);
			$uids = explode(',', $uids);
		}

		$serv_connect = D('Common/CommonDepartmentConnect', 'Service');
		$conds_con = array('cd_id' => $cd_id);
		// 先删除部门负责人
		$serv_connect->delete_by_conds($conds_con);
		if (empty($uids)) {
			return true;
		}

		// 查询人员信息
		$serv_mem = D('Common/Member', 'Service');
		$conds_mem = array('m_uid' => $uids);
		$mem_list = $serv_mem->list_by_conds($conds_mem);
		if (empty($mem_list)) {
			return true;
		}

		// 转换数组键值
		$mem_list = array_combine_by_key($mem_list, 'm_uid');
		// 添加新负责人
		$connecter_list = array();
		foreach ($mem_list as $_mem) {
			$connecter_list[] = array(
				'cd_id' => $cd_id,
				'm_uid' => $_mem['m_uid'],
				'm_username' => $_mem['m_username']
			);
		}
		$serv_connect->insert_all($connecter_list);

		return true;
	}

	/**
	 * 添加和编辑数据验证
	 * @param $data array 待验证的数据
	 * @return $data array 验证后的数据
	 */
	protected function _format($data) {

		// 部门名称不能为空
		if (empty($data['cd_name'])) {
			E('_ERR_EMPTY_POST_CDNAME');
			return false;
		}
		$data['cd_name'] = (string)$data['cd_name'];
		$data['cd_name'] = trim($data['cd_name']);
		// 如果部门id存在
		if (isset($data['cd_id'])) {
			$data['cd_id'] = (int)$data['cd_id'];
		}

		// 如果部门上级id存在
		if (isset($data['cd_upid'])) {
			$data['cd_upid'] = (int)$data['cd_upid'];
		}
		// 上级部门id和当前部门ID不能同时为空
		if (empty($data['cd_id']) && empty($data['cd_upid'])) {
			E('_ERR_EMPTY_UPID');
			return false;
		}
		// 如果非英文名称, 则剔除空格
		if (!preg_match('/^[\x20-\x7F]+$/i', $data['cd_name'])) {
			$data['cd_name'] = preg_replace('/\s+/s', '', $data['cd_name']);
		}

		// 部门名称长度不合法
		if (!$this->validator_length($data['cd_name'], $this->department_name_length)) {
			E('_ERR_CDNAME_LENGTH_OVER_MAX');
			return false;
		}
		// 部门名称不能包含特殊字符
		if ($data['cd_name'] != rhtmlspecialchars($data['cd_name'])) {
			E('_ERR_CDNAME_NOT_SPECIAL_STR');
			return false;
		}
		// 判断部门名称是否被使用
		foreach ($this->_departments as $dep) {
			if (isset($data['cd_upid']) && $dep['cd_name'] == $data['cd_name']
					&& $dep['cd_upid'] == $data['cd_upid']) {
				E('_ERR_CDNAME_EXISTS');
				return false;
			}
		}
		// 如果未设置排序号
		if (empty($data['cd_displayorder'])) {
			$data['cd_displayorder'] = 1;
		}
		$data['cd_lastordertime'] = NOW_TIME;
		//检查显示顺序取值是否合法
		if (isset($data['cd_displayorder'])) {
			$data['cd_displayorder'] = (int)$data['cd_displayorder'];
			if ($data['cd_displayorder'] < $this->dep_displayorder_min || $data['cd_displayorder'] > $this->dep_displayorder_max) {
				// 显示顺序取值超出范围
				$data['cd_displayorder'] = 99;
			}
		}

		return $data;
	}
}

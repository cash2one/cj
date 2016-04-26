<?php
/**
 * CommonDepartmentService.class.php
 * $author$
 */
namespace Common\Service;

use Common\Service\AbstractService;
use Common\Common\Wxqy\Addrbook;

class CommonDepartmentService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('Common/CommonDepartment');
	}

	// 获取部门id上下级对应关系
	public function list_p2c() {

		// 读取所有部门
		$list = $this->list_all();
		$p2c = array();
		// 遍历部门列表
		foreach ($list as $_dp) {
			if (!isset($p2c[$_dp['cd_upid']])) {
				$p2c[$_dp['cd_upid']] = array();
			}

			$p2c[$_dp['cd_upid']][] = $_dp['cd_id'];
		}

		return $p2c;
	}

	/**
	 * 读取所有部门信息
	 * @see \Com\Service::list_all()
	 */
	public function list_all($page_option = null, $order_option = array()) {

		$list = parent::list_all($page_option, $order_option);

		return array_combine_by_key($list, 'cd_id');
	}

	/**
	 * 根据部门名称和上级id查询
	 * @param $cd_name string 部门名称
	 * @param $upid int 上级部门id
	 * @return mixed
	 */
	public function get_id_by_cdname_upid($cd_name, $upid) {

		$department = $this->_d->get_id_by_cdname_upid($cd_name, $upid);
		if (empty($department)) {
			return 0;
		} else {
			return $department[0]['cd_id'];
		}
	}

	/**
	 * 同步微信部门，增加或编辑
	 * @param array $department 更新获或新建部门数据
	 * @param array $return 返回值
	 * @return bool
	 */
	public function update_dep($department, &$return) {

		// 本地存在该部门在企业微信部门id的对应关系
		$cache = &\Common\Common\Cache::instance();
		$sets = $cache->get('Common.setting');
		$func = !empty($department['cd_id']) ? 'department_update' : 'department_create';

		$cd_id = 0;
		$cache = &\Common\Common\Cache::instance();
		$dep_list = $cache->get('Common.department');
		// 如果 cd_id 存在, 则是编辑操作
		if (!empty($department['cd_id']) && !empty($dep_list[$department['cd_id']])) {
			// 判断部门是否
			$cd_id = $department['cd_id'];
			$department['cd_qywxid'] = $dep_list[$department['cd_id']]['cd_qywxid'];
		} else if (!empty($department['cd_upid']) && !empty($dep_list[$department['cd_upid']])) { // 新增操作
			$department['cd_qywxparentid'] = $dep_list[$department['cd_upid']]['cd_qywxid'];
		} else { // 数据错误
			E('_ERR_NO_DEPARTMENT_MATCH');
			return false;
		}

		// 判断是否有权限
		if ($this->local_to_wxqy($department, $wxqy_data) && !empty($sets['ep_wxqy'])) {
			$result = array();
			// 加载微信通讯录接口
			$qywx = &\Common\Common\Wxqy\Service::instance();
			$addrbook = new Addrbook($qywx);

			$addrbook->$func($result, $wxqy_data);
			$result['name'] = $wxqy_data['name'];
			$result['parentid'] = $department['cd_qywxparentid'];
			// 把企业号数据转换成本地数据
			$this->wxqy_to_local($result, $department);
			$return = $department;
		}

		// 本地操作
		if ($func == 'department_update') {
			unset($department['cd_id']);
			$this->_d->update_by_conds(array('cd_id' => $cd_id), $department);
		} else {
			$return['cd_id'] = $this->_d->insert($department);
		}

		clear_cache();
		return true;
	}

	/**
	 * 将微信企业接口的数据转换为本地数据格式
	 * @param array $wxqy_data 微信传回的数据
	 * @param array $department (引用结果)转换后的数据
	 * @return boolean
	 */
	public function wxqy_to_local($wxqy_data = array(), &$department = array()) {

		if (isset($wxqy_data['id'])) {
			$department['cd_qywxid'] = $wxqy_data['id'];
		}

		if (isset($wxqy_data['name'])) {
			$department['cd_name'] = $wxqy_data['name'];
		}

		if (isset($wxqy_data['parentid'])) {
			$department['cd_qywxparentid'] = $wxqy_data['parentid'];
		}

		return true;
	}

	/**
	 * 从本地获取微信部门信息
	 * @param $department array 提交数据
	 * @param $wxqy_data array 返回值
	 * @return bool
	 */
	public function local_to_wxqy($department, &$wxqy_data) {

		$cache = &\Common\Common\Cache::instance();
		$dep_list = $cache->get('Common.department');

		// 部门名称没有变动
		foreach ($dep_list as $_dep) {
			if ($_dep['cd_name'] == $department['cd_name'] && $department['cd_upid'] == $_dep['cd_upid']) {
				return false;
			}
		}

		// 微信上级部门id
		if (! empty($department['cd_qywxparentid'])) {
			$parentid = $department['cd_qywxparentid'];
		} else {
			$parentid = 1;
		}

		$wxqy_data = array(
			'name' => $department['cd_name'],
			'parentid' => $parentid,
		);
		if (!empty($department['cd_qywxid'])) {
			$wxqy_data['id'] = $dep_list[$department['cd_id']]['cd_qywxid'];
		}

		return true;
	}

}

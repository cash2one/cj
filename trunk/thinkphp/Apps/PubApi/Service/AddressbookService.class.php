<?php
/**
 * AddressbookService.class.php
 * $author$
 */

namespace PubApi\Service;
use Common\Common\Cache;
use Common\Common\User;

class AddressbookService extends AbstractService {

	// 每页人数
	const LIMIT = 300;

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Common/Member");
	}

	/**
	 * 获取用户列表
	 * @param array $data 返回信息
	 * + departments 部门列表
	 * + members 用户列表
	 * @param array $params 请求参数
	 * + cd_id 部门ID
	 * + limit 每页个数
	 * + page 页码
	 * + keyword 关键字
	 * + keyindex 用户名称索引
	 * @return boolean
	 */
	public function list_member(&$data, $params) {

		// 部门id
		$cd_id = (int)$params['cd_id'];
		$limit = (int)$params['limit'];
		$page = (int)$params['page'];
		// 搜索关键字
		$keyword = (string)$params['keyword'];
		// 索引
		$keyindex = (string)$params['keyindex'];

		// 获取部门缓存数据
		$cache = &Cache::instance();
		$p2c = $cache->get('Common.department_p2c');

		// 获取所有子部门ID
		$child_cdids = array();
		if (!empty($cd_id)) {
			$this->_list_all_child_cdid($child_cdids, $cd_id, $p2c);
			// 把自身也推入数组
			$child_cdids[] = $cd_id;
		}

		// 取所有下级部门
		$dep_list = array();

		// 获取起始行, 每页行数, 当前页
		$limit = empty($limit) || $limit > self::LIMIT ? self::LIMIT : $limit;
		list($start, $limit, $page) = page_limit($page, $limit);
		// 取该部门下所有用户
		$kws = array(
			'keyword' => $keyword,
			'keyindex' => $keyindex
		);
		$members = $this->_d->list_by_cdid_kws($child_cdids, $kws, array($start, $limit));

		$mem_list = array();
		// 遍历取人员信息
		foreach ($members as $_mem) {
			$mem_list[] = array(
				'm_uid' => $_mem['m_uid'],
				'm_username' => $_mem['m_username'],
				'm_face' => User::instance()->avatar($_mem['m_uid'], $_mem)
			);
		}

		// 读取总数
		$count = $this->_d->count_by_cdid_kws($child_cdids, $kws);

		// 返回值
		$data = array('members' => $mem_list, 'total' => $count);
		return true;
	}

	/**
	 * 获取子部门方法
	 *
	 * @param int $cd_id 部门id
	 * @return array $dep_list 子部门列表
	 */
	public function list_department(&$dps, $params) {

		// 部门id
		$cd_id = (int)$params['cd_id'];

		// 部门列表初始化
		$dps = array();
		// 获取部门缓存数据
		$cache = &Cache::instance();
		$departments = $cache->get('Common.department');

		// 取第一级部门
		if (0 == $cd_id && empty($params['keyword'])) {
			$this->_get_root_cdid($cd_id);
			$dps[] = array(
				'id' => 0,
				'name' => L('ALL_USER'),
				'count' => $departments[$cd_id]['cd_usernum']
			);
		}

		// 遍历取所有下级部门信息
		foreach ($departments as $_dp) {
			// 如果该部门的父级部门id等于$cd_id
			if ((empty($params['keyword']) && $_dp['cd_upid'] == $cd_id)
					|| ($params['keyword'] && preg_match("/" . preg_quote($params['keyword']) . "/i", $_dp['cd_name']))) {
				$dps[] = array(
					'id' => $_dp['cd_id'],
					'name' => $_dp['cd_name'],
					'count' => $_dp['cd_usernum']
				);
			}
		}

		return true;
	}

	/**
	 * 获取所有子级部门ID
	 * @param array $cdids 部门ID数组
	 * @param int $cdid 部门ID
	 * @param array $p2c 部门ID对应关系
	 * @return boolean
	 */
	protected function _list_all_child_cdid(&$cdids, $cdid, $p2c) {

		// 如果没有下级部门ID
		if (!isset($p2c[$cdid])) {
			return true;
		}

		$cdids = array_merge($cdids, $p2c[$cdid]);
		foreach ($p2c[$cdid] as $_cdid) {
			$this->_list_all_child_cdid($cdids, $_cdid, $p2c);
		}

		return true;
	}

	/**
	 * 获取根目录的部门id
	 * @param int $cd_id 部门id
	 * @return boolean
	 */
	protected function _get_root_cdid(&$cd_id) {

		// 获取部门缓存数据
		$cache = &Cache::instance();
		$departments = $cache->get('Common.department');

		// 遍历所有部门
		foreach ($departments as $_dp) {
			// 如果是第一级
			if (0 == $_dp['cd_upid']) {
				$cd_id = $_dp['cd_id'];
				break;
			}
		}

		return true;
	}

}

<?php

/**
 * voa_uda_frontend_news_read
 * 统一数据访问/新闻公告/获取单个新闻公告阅读人员列表
 * $Author$
 * $Id$
 */
class voa_uda_frontend_news_read extends voa_uda_frontend_news_abstract {
	/** service 类 */
	private $__service = null;

	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_news_read();
		}
	}

	/**
	 * 获取单个新闻公告阅读人员列表
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)信息数组
	 * @return boolean
	 */
	public function list_users(array $request, array &$result) {

		// 定义参数请求规则
		$fields = array(
			'ne_id' => array(
				'ne_id',
				parent::VAR_INT,
				array($this->__service, 'validator_ne_id'),
				null,
				false,
			),
			'page' => array(
				'page',
				parent::VAR_ABS,
				array(),
				null,
				false,
			),
			'limit' => array(
				'limit',
				parent::VAR_ABS,
				array(),
				null,
				false,
			),
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}
		// 取得参数
		$ne_id = $this->__request['ne_id'];
		// 当前页码
		$page = $this->__request['page'];
		// 每页显示数
		$limit = $this->__request['limit'];
		$total = 0;
		$user_total = 0;
		$users = array();
		// 已读人数总数
		//$total = $this->__service->count_read_users($ne_id);
		$total = $this->count_real_read_users($ne_id);
		//获取可阅读总数
		$user_total = $this->count_user_total($ne_id);
		if (!$total) {
			$result = array(
				'total' => $total,
				'user_total' => $user_total,
				'users' => $users,
			);

			return false;
		}
		// 总页码
		$pages = ceil($total / $limit);

		// 起始数据
		$start = ($page - 1) * $limit;


		// 获取阅读人员列表
		$news_read = $this->__service->list_read_users($ne_id, $start, $limit);
		if (!empty($news_read)) {
			//获取所有member记录
			$s_member = new voa_s_oa_member();
			$members = $s_member->fetch_all_by_ids(array_column($news_read, 'm_uid'));
			if (!empty($members)) {
				//获取所有的部门和职位信息
				$departments = voa_h_cache::get_instance()->get('department', 'oa');
				$jobs = voa_h_cache::get_instance()->get('job', 'oa');
				// 整理输出
				foreach ($news_read as $_nr) {
					if (!isset($members[$_nr['m_uid']])) {
						// 用户不存在
						continue;
					}
					//整理人员信息
					$_member = $members[$_nr['m_uid']];
					$users[$_nr['m_uid']] = $_member;
					$users[$_nr['m_uid']]['read_time'] = rgmdate($_nr['created'], 'Y-m-d H:i');
					$users[$_nr['m_uid']]['department'] = '';
					$users[$_nr['m_uid']]['job'] = '';
					if (isset($departments[$_member['cd_id']])) {
						$users[$_nr['m_uid']]['department'] = $departments[$_member['cd_id']]['cd_name'];
					}
					if (isset($jobs[$_member['cj_id']])) {
						$users[$_nr['m_uid']]['job'] = $jobs[$_member['cj_id']]['cj_name'];
					}
				}
			}
		}

		$result = array(
			'ne_id' => $ne_id,
			'page' => $page,
			'limit' => $limit,
			'pages' => $pages,
			'total' => $total,
			'users' => $users,
			'user_total' => $user_total,
		);

		return false;
	}

	/**
	 * 获取单个新闻公告未读人员列表
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)信息数组
	 * @return boolean
	 */
	public function get_unread_users(array $request, array &$result) {
		// 定义参数请求规则
		$fields = array(
			'ne_id' => array(
				'ne_id',
				parent::VAR_INT,
				array($this->__service, 'validator_ne_id'),
				null,
				false,
			),
			'page' => array(
				'page',
				parent::VAR_ABS,
				array(),
				null,
				false,
			),
			'limit' => array(
				'limit',
				parent::VAR_ABS,
				array(),
				null,
				false,
			),
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}
		// 取得参数
		$ne_id = $this->__request['ne_id'];
		// 当前页码
		$page = $this->__request['page'];
		// 每页显示数
		$limit = $this->__request['limit'];
		//获取可阅读总数
		$user_total = $this->count_user_total($ne_id);
		// 已读人数总数
		$total = $this->count_real_read_users($ne_id);

		// 起始数据
		$start = ($page - 1) * $limit;
		$unreads = $this->get_unread_muid($ne_id);
		//获取未读人员总数
		$unread_total = count($unreads);
		// 总页码
		$pages = ceil($unread_total / $limit);

		$s_member = new voa_s_oa_member();
		$unreads = array_slice($unreads, $start, $limit);
		$members = $s_member->fetch_all_by_ids($unreads);
		if (!empty($members)) {
			//获取所有的部门和职位信息
			$departments = voa_h_cache::get_instance()->get('department', 'oa');
			$jobs = voa_h_cache::get_instance()->get('job', 'oa');
			foreach ($unreads as $v) {
				if (!isset($members[$v])) {
					//用户不存在就跳过
					continue;
				}
				//整理人员信息
				$_member = $members[$v];
				$users[$v] = $_member;
				$users[$v]['department'] = '';
				$users[$v]['job'] = '';
				if (isset($departments[$_member['cd_id']])) {
					$users[$v]['department'] = $departments[$_member['cd_id']]['cd_name'];
				}
				if (isset($jobs[$_member['cj_id']])) {
					$users[$v]['job'] = $jobs[$_member['cj_id']]['cj_name'];
				}
			}
		}

		$result = array(
			'ne_id' => $ne_id,
			'page' => $page,
			'limit' => $limit,
			'pages' => $pages,
			'total' => $unread_total,
			'users' => $users,
			'user_total' => $user_total,
		);

		return false;
	}

	/**
	 * 获取真实的已读人员总数
	 * @param int $ne_id
	 * @return int
	 * */
	public function count_real_read_users($ne_id) {

		//获取已阅读人员列表
		$total = $this->__service->count_read_users($ne_id);
		$news_read = array();
		$news_read = $this->__service->list_by_conds(array('ne_id' => $ne_id));
		if ($news_read && $total) {
			$news_read = array_column($news_read, 'm_uid');
			//获取可阅读人员列表
			$users_list = $this->__service->get_read_users($ne_id);
			foreach ($news_read as $k => $v) {
				if (!in_array($v, $users_list)) {
					unset($news_read[$k]);
				}
			}
			$real_total = count($news_read);
		} else {
			$real_total = 0;
		}

		return $real_total;
	}

	/**
	 * 获取真实的未读人员总数
	 * @param int $ne_id
	 * @return int
	 * */
	public function count_real_unusers($ne_id) {
		//获取可阅读总数
		$user_total = $this->count_user_total($ne_id);
		//获取真实的已读人数
		$total = $this->count_real_read_users($ne_id);
		//得到真实未读总数
		$real_total = $user_total - $total;
		$real_total = min($user_total, $real_total);
		$real_total = max(0, $real_total);

		return $real_total;
	}

	/**
	 * 获取真实的未读人员列表
	 * @param $ne_id
	 * @return array
	 */
	public function get_unread_muid($ne_id) {

		$users_list = array();
		$users = array();
		//获取可阅读人员列表
		$users_list = $this->__service->get_read_users($ne_id);
		//获取已阅读人员列表
		$news_read = $this->__service->list_by_conds(array('ne_id' => $ne_id));
		$s_member = new voa_s_oa_member();
		if (!empty($news_read)) {
			$reads = array_column($news_read, 'm_uid');
			//获取未读人员列表
			$unreads = array_diff($users_list, $reads);
		} else {
			$unreads = $users_list;
		}
		$unreads = array_unique($unreads);

		return $unreads;
	}

	//取得可读人员总数
	public function count_user_total($ne_id) {
		$user_total = $this->__service->count_users($ne_id);

		return $user_total;
	}

}

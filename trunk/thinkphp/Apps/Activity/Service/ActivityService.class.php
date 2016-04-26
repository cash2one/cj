<?php
/**
 * ActivityService.class.php
 * $author$
 */

namespace Activity\Service;
use Common\Common\User;

class ActivityService extends AbstractService {

	protected $_partake;
	protected $_outsider;
	protected $_invite;
	const MY_MINE = 'mine';
	const MY_JOIN = 'join';
	const OUTSIDER = 1;	//外部报名
	const ACT_NOT_START = 0;	//活动未开始
	const ACT_START = 1;		//活动已开始
	const ACT_END = 2;			//活动已结束
	const DP_TYPE = 1;			//邀请类型，部门
	const USER_TYPE = 2;		//邀请类型，人员

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Activity/Activity");
		$this->_partake = D('Activity/ActivityPartake');
		$this->_outsider = D('Activity/ActivityOutsider');
		$this->_invite = D('Activity/ActivityInvite');
	}

	/**
	 * 入库
	 * @param array $data
	 * @param int $acid
	 * @return boolen 新增成功
	 */
	public function add($data, $acid = null) {

		// 活动标题不能为空
		if (empty($data['title'])) {
			$this->_set_error('_ERR_TITLE_NULL');
			return false;
		}
		// 活动内容不能为空
		if (empty($data['content'])) {
			$this->_set_error('_ERR_CONTENT_NULL');
			return false;
		}
		// 活动地点不能为空
		if (empty($data['address'])) {
			$this->_set_error('_ERR_ADDRESS_NULL');
			return false;
		}
		// 活动结束时间不得小于活动开始时间
		if ($data['start_time'] > $data['end_time']) {
			$this->_set_error('_ERR_TIME_FALSE');
			return false;
		}

		$result = array(
			'title' => $data['title'],
			'content' => $data['content'],
			'address' => $data['address'],
			'start_time' => $data['start_time'],
			'end_time' => $data['end_time'],
			'cut_off_time' => $data['cut_off_time'],
			'np' => $data['np'],
			'm_uid' => $data['m_uid'],
			'uname' => $data['uname'],
			'at_ids' => $data['at_ids'],
			'outsider' => $data['outsider'],
			'outfield' => $data['outfield'],
		);

		//不邀请内部人员的情况
		if (empty($data['users']) && empty($data['dp'])) {
			if (!isset($result['outsider'])) {
				$result['outsider'] = 0;
			}
			if ($acid) {//更新
				unset($result['outfield']);
				unset($result['outsider']);
				$this->_d->update_by_conds(array('acid' => $acid), $result);
				$res['acid'] = $acid;
			} else {//插入
				if ($result['outfield'] != '') {
					$result['outfield'] = serialize($result['outfield']);
				}
				$res = $this->_d->insert($result);
			}
		}

		//有内部人员的情况
		if (!empty($data['users'])) {
			$uids = $data['users'];
			unset($data['users']);
		}
		if (!empty($data['dp'])) {
			$dp = $data['dp'];
			unset($data['dp']);
		}

		if (!isset($result['outsider'])) {
			$result['outsider'] = 0;
		}

		if ($acid) {//更新
			$this->_d->update_by_conds(array('acid' => $acid), $result);
			$res['acid'] = $acid;
		} else {//插入
			if ($result['outfield'] != '') {
				$result['outfield'] = serialize($result['outfield']);
			}
			$res = $this->_d->insert($result);
		}
		$touser = array();
		$toparty = array();

		if (!empty($uids)) {
			$touser = explode(",", $uids);//数组 由于用的是UID
		}
		if (!empty($dp)) {
			$toparty = explode(",", $dp);//数组 由于用的是dps
		}
		//插入邀请人员
		if (empty($acid)) {
			$this->insertusers($touser, $toparty, $res['acid']);
		}
		/** 发送微信消息 */
		if ($acid) {
			$msg_title = "您报名的活动有更新";
			$partake = $this->_partake->list_by_conds(array("acid" => $acid));
			$touser = array();
			$toparty = array();
			if (!empty($partake)) {
				foreach ($partake as $v) {
					$touser[] = $v['m_uid'];
				}
			}
		} else {
			$msg_title = "您收到1个活动邀请";
		}

		$msg_desc = "主题：【" . $result['title'] . "】\n";
		$msg_desc .= "活动时间：" . rgmdate($result['start_time'], "m-d H:i") . " 到 " . rgmdate($result['end_time'], "m-d H:i") . "\n";
		// 组合微信消息
		$data = array();
		$data['title'] = $msg_title;
		$data['description'] = $msg_desc;
		$data['url'] = $this->view_url($res['acid']);

		// 发送微信消息
		$this->send_msg($data, $touser, $toparty);

		//返回活动ID
		return $res;
	}


	/**
	 *邀请用户
	 */
	public function insertusers($touser, $toparty, $acid) {

		$data = array();
		foreach ($touser as $val) {
			$data[] = array(
				'primary_id' => $val,
				'type' => 2,
				'acid' => $acid
			);
		}
		foreach ($toparty as $val) {
			$data[] = array(
				'primary_id' => $val,
				'type' => 1,
				'acid' => $acid
			);
		}
		$this->_invite->insert($data);
		return true;
	}

	/**
	 * 获取活动详情
	 * @param $acid 公告ID
	 * @param string $field 自定义字段
	 * @return bool
	 */
	public function view($acid, $field = "*"){

		// 判断活动ID是否合法
		if(empty($acid)){
			return $this->_set_error('_ERR_ACID_NOT_LE_LEGAL');
		}

		// 获取活动
		$view = $this->_d->get_detail_by_acid($acid, $field);

		// 判断活动存不存在
		if(!$view){
			return $this->_set_error('_ERR_ACT_NOT_NULL');
		}

		//返回数据
		return $view;
	}

	/**
	 * 格式化活动详情
	 * @param int $acid
	 * @param int $m_uid
	 * @param array $view
	 * @return array
	 */
	public function format_view($acid, $m_uid = 0, $view){

		//判断活动数据是否合法
		if(!is_array($view)){

			return $view;
		}

		$m_uid = (int)$m_uid;
		$acid = (int)$acid;
		// 如果是内部人员就查看报名状态
		if($m_uid){
			$view['user_type'] = $this->_partake->get_user_type($acid, $m_uid);
		}

		$o_total = 0;
		// 查看是否容许外部报名
		if($view['outsider'] == self::OUTSIDER ){
			// 序列化外部报名信息
// 			if($view['outfield']){
// 				$view['outfield'] = unserialize($view['outfield']);
// 			}

			// 获取外部报名人数
			$o_total = $this->_outsider->count_reg_num($acid);
		}

		// 获取内部报名人数
		$p_total = $this->_partake->count_reg_num($acid);
		// 总报名数
		$total = $p_total + $o_total;

		// 处理限制人数
		if($view['np'] == 0){
			$view['np'] = '无限制';
		}

		$view['total'] = $total;

		//查看活动状态
		if ($view['end_time'] < time()) {
			$view['status'] = self::ACT_END; // 已经结束的
		} elseif ($view['start_time'] > time()) {
			$view['status'] = self::ACT_NOT_START; // 未开始的
		} else {
			$view['status'] = self::ACT_START; // 进行中的
		}
		//获取邀请人员和部门
		$invite = array();
		$invite = $this->_invite->list_by_acid($acid);
		//处理邀请人员数据
		$invite = $this->__formart_invite($invite);
		$view['invite'] = $invite;
		//去除多余数据
		unset($view['created'], $view['deleted'], $view['outfield']);

		return $view;
	}

	/**
	 * 活动报名列表
	 * @param int $status
	 * @param $page_option
	 * @param $orderby
	 * @param string $field
	 * @return mixed
	 */
	public function activity_list($status = 0, $page_option, $orderby, $field = "*") {

		/* 筛选查询条件 */
		switch($status) {
			// 全部
			case 0:
				$conds = array();
				break;

			// 未开始
			case 1:
				$conds['start_time > ?'] = time();
				break;

			// 已开始的
			case 2:
				$conds['start_time <= ?'] = time();
				$conds['end_time >= ?'] = time();
				break;

			// 已结束的
			case 3:
				$conds['end_time < ?'] = time();
				break;
		}

		// 自定义字段
		$field = "acid, title, m_uid, uname, start_time, end_time, np, outsider";

		// 查询数据列表
		$result = $this->_d->fetch_all_by_status($conds, $page_option, $orderby, $field);

		// 返回数据
		return $result;
	}

	/**
	 * 格式化活动列表
	 * @param $data
	 * @return bool
	 */
	public function format_list(&$data) {

		// 如果为空 ，返回空数据
		if (empty($data)) {
			return true;
		}

		$acid= array();

		/* 循环获取用户头像，判断活动的状态 */
		foreach ($data as $_k => $_v) {
			$data[$_k]['avator'] = User::instance()->avatar($_v['m_uid']);
			if ($_v['end_time'] < time()) {
				$data[$_k]['status'] = 1; // 已经结束的
			} elseif ($_v['start_time'] > time()) {
				$data[$_k]['status'] = 2; // 未开始的
			} else {
				$data[$_k]['status'] = 3; // 进行中的
			}
			$acid[] = $_v['acid'];
		}

		// 获取内部报名人数
		$p_counts = $this->_partake->list_count_by_acid($acid);
		// 重构数组
		foreach ($p_counts as $k => $v) {
			$p_count[$v['acid']] = $v['_count'];
		}

		// 获取外部人数
		$o_counts = $this->_outsider->list_count_by_acid($acid);
		// 数据重组
		foreach ($o_counts as $k => $v) {
			$o_count[$v['acid']] = $v['_count'];
		}

		// 获取活动参与的人数
		foreach ($data as $_k => $_v) {
			$count = isset($p_count[$_v['acid']])? $p_count[$_v['acid']]:0;
			if ($_v['outsider'] == 1) {
				$count = $count + (isset($o_count[$_v['acid']]) ? $o_count[$_v['acid']] : 0);
			}
			$data[$_k]['counts'] = $count;
		}
	}

	/**
	 * 我的活动列表
	 * @param string $action
	 * @param array $page_option
	 * @param $orderby
	 * @param string $field
	 * @return mixed
	 */
	public function my_list($action, $m_uid, $start, $limit) {

		$action = strtolower((string)$action);

		$list = array();
		$fields = "acid, title, uname, start_time, end_time, updated";

		switch ($action) {

			case self::MY_MINE:

				//获取我发起的活动列表

				$list = $this->_d->list_by_muid($m_uid, $fields, $start, $limit);
			break;

			case self::MY_JOIN:

				//获取我参与的活动列表
				$list = $this->_d->join_list_by_muid($m_uid, $start, $limit);

				break;

			default:

				$list = $this->_d->list_by_muid($m_uid, $fields, $start, $limit);
			break;
		}

		return $list;
	}

	/**
	 * 格式化活动列表
	 * @param $data
	 * @return bool
	 */
	public function format_my(&$data) {

		// 如果为空 ，返回空数据
		if (empty($data)) {
			return true;
		}

		$acid= array();

		/* 循环判断活动的状态 */
		foreach ($data as $_k => $_v) {

			if ($_v['end_time'] < time()) {
				$data[$_k]['status'] = self::ACT_END; // 已经结束的
			} elseif ($_v['start_time'] > time()) {
				$data[$_k]['status'] = self::ACT_NOT_START; // 未开始的
			} else {
				$data[$_k]['status'] = self::ACT_START; // 进行中的
			}

			$acid[] = $_v['acid'];
		}

		// 获取内部报名人数
		$p_counts = $this->_partake->list_count_by_acid($acid);

		// 重构数组
		foreach ($p_counts as $k => $v) {
			$p_count[$v['acid']] = $v['_count'];
		}

		// 获取外部人数
		$o_counts = $this->_outsider->list_count_by_acid($acid);
		// 数据重组
		foreach ($o_counts as $k => $v) {
			$o_count[$v['acid']] = $v['_count'];
		}

		// 获取活动参与的人数
		foreach ($data as $_k => $_v) {
			$count = isset($p_count[$_v['acid']])? $p_count[$_v['acid']]:0;
			if ($_v['outsider'] == self::OUTSIDER) {
				$count = $count + (isset($o_count[$_v['acid']]) ? $o_count[$_v['acid']] : 0);
			}
			$data[$_k]['counts'] = $count;
		}
	}

	/**
	 * @access private
	 * 处理邀请人员数据
	 * @param array $data
	 * @return array
	 * */
	private function __formart_invite($data){

		$result = array();
		$_dps = array();
		$_users = array();
		$dp_names = array();
		$users = array();

		// 判断是否是全公司
		$primary_ids = array_column($data, 'primary_id');

		if (in_array('0', $primary_ids)) {
			$cache = &\Common\Common\Cache::instance();
			$dp = $cache->department();
			// 获取最顶级的部门名称
			foreach ($dp as $v) {
				if ($v['cd_upid'] == 0) {
					$dp_names = array($v['cd_name']);
					break;
				}
			}

			// 返回数据
			$result['dps'] = $dp_names;
			$result['users'] = $users;

			return $result;
		}

		// 循环得到数据
		foreach ($data as $values) {
			// 部门
			if ($values['type'] == self::DP_TYPE) {
				$_dps[] = $values['primary_id'];
			}

			// 人员
			if ($values['type'] == self::USER_TYPE) {
				$_users[] = $values['primary_id'];
			}
		}

		// 获取部门名称
		if (! empty($_dps)) {
			$dp = D("Common/CommonDepartment");
			$dps = array();

			$dps = $dp->list_by_pks($_dps);
			// 如果结果不为空就获取部门名称
			if (! empty($dps)) {
				$dp_names = array_column($dps, 'cd_name');
			}
		}

		// 获取用户名和头像
		if (!empty($_users)) {
			$member = D("Common/Member");
			$users = array();
			$users = $member->list_by_pks($_users);
		}

		// 组合数据
		$result['dps'] = $dp_names;
		$result['users'] = $users;

		return $result;
	}
	/**
	 * 生成二维码
	 */
	public function get_qrcode($url) {

		// 生成二维码
		$this->qrcode($url);

	}
}

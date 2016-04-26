<?php
/**
 * CommonPmService.class.php
 * $author$
 */
namespace Common\Service;

class CommonPmService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Common/CommonPm");
	}

	/**
	 * 根据用户统计未读记录总数
	 * @param int $m_uid 用户id
	 * @return mixed
	 */
	public function count_by_uid_pluginid($m_uid, $plugin_id) {

		return $this->_d->count_by_uid_pluginid($m_uid, $plugin_id);
	}

	/**
	 * 根据用户id和应用id读取记录列表
	 * @param array $params 传入参数
	 * @return array|bool
	 */
	public function list_by_msg_pid($params, $extend = array()) {

		// 用户非空验证
		$m_uid = (int)$extend['m_uid'];
		// 查询条件
		$msg = (string)$params['msg'];
		// 插件id非空验证
		$plugin_id = (int)$params['plugin_id'];

		// 分页数据
		$limit = (int)$params['limit'];
		$page = (int)$params['page'];

		// 判断分页数据是否正确, 如果不合法赋予系统默认值
		if (empty($limit) || $limit < cfg('PAGE_MINSIZE') || $limit > cfg('PAGE_MAXSIZE')) {
			$limit = cfg('LIMIT_DEF');
		}

		// 分页参数
		list($start, $limit, $page) = page_limit($page, $limit);
		$page_option = array($start, $limit);

		// 总数
		$total = $this->_d->count_by_msg_pid($m_uid, $plugin_id, $msg);

		// 列表数据
		$list = $this->_d->list_by_msg_pid($m_uid, $plugin_id, $msg, $page_option, array('pm_isread' => "ASC", 'pm_created' => "DESC"));

		// 返回数据
		return array("total" => $total, "limit" => $limit, "data" => $list);
	}

	/**
	 * 根据记录id标记已/未读状态
	 * @param array $pm_ids 记录ids
	 * @param int $isread 是否已读：0=未读，1=已读
	 * @return bool
	 */
	public function update_isread_by_ids($pm_ids, $isread = 0) {

		// 记录ids非空验证
		if (empty($pm_ids)) {
			$this->_set_error('_ERR_PMID_IS_NOT_EXIST');
			return false;
		}

		// 状态
		if ('' == $isread) {
			$isread = $this->_d->get_st_isread();
		}

		return $this->_d->update_isread_by_ids($pm_ids, $isread);
	}

	/**
	 * 发送消息
	 * @param array $pm 消息信息
	 * @param array $params 传入参数
	 * @param array $extend 扩展参数
	 * @return bool
	 */
	public function add(&$pm, $params, $extend = array()) {

		// 获取参数
		$cp_pluginid = (int)$params['cp_pluginid'];
		$from_uid = (int)$params['from_uid'];
		$pm_title = (string)$params['pm_title'];
		$pm_message = (string)$params['pm_message'];

		// 插件id
		if (empty($cp_pluginid)) {
			$this->_set_error('_ERR_PLUGINID_IS_NOT_EXIST');
			return false;
		}

		// 发送者id不能为空
		if (empty($from_uid)) {
			$this->_set_error('_ERR_FROM_UID_IS_NOT_EXIST');
			return false;
		}

		// 消息标题不能为空
		if (empty($pm_title)) {
			$this->_set_error('_ERR_MSG_TITLE_NOT_NULL');
			return false;
		}

		// 消息内容不能为空
		if (empty($pm_message)) {
			$this->_set_error('_ERR_MSG_CONTENT_NOT_NULL');
			return false;
		}

		// 发送者名称
		$serv_m = D('Common/Member', 'Service');
		$member = $serv_m->get($from_uid);
		$from_username = $member['m_username'];

		// 消息
		$pm = array(
			'cp_pluginid' => $cp_pluginid,
			'm_uid' => (int)$extend['uid'],
			'm_username' => (string)$extend['username'],
			'from_uid' => $from_uid,
			'from_username' => $from_username,
			'pm_title' => $pm_title,
			'pm_message' => $pm_message,
			'pm_isread' => $this->_d->get_st_unread(),
			'pm_status' => $this->_d->get_st_create(),
			'pm_created' => NOW_TIME
		);

		// 执行入库操作
		if (!$id = $this->_d->insert($pm)) {
			$this->_set_error('_ERR_INSERT_ERROR');
			return false;
		}

		// 拼接自增id
		$pm['pm_id'] = $id;
		return true;
	}

	/**
	 * 消息数据格式化
	 * @param array &$data 待格式化数据
	 * @return bool
	 */
	public function format(&$data) {

		// 消息参数反序列化
		if (!empty($data['pm_params'])) {
			$data['pm_params'] = unserialize($data['pm_params']);
		}

		$data['pm_created'] = rgmdate($data['pm_created']);
		return true;
	}

}

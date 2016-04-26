<?php
/**
 * CommentService.class.php
 * $author$
 */
namespace PubApi\Service;

class CommentService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("PubApi/Comment");
	}

	/**
	 * 新增评论
	 * @param $comment 评论数据
	 * @param $params 传入参数
	 * @param array $extend 扩展参数
	 * @return bool
	 */
	public function create_comment(&$comment, $params, $extend = array ()) {

		// 应用id
		$plugin_id = (int)$params['plugin_id'];
		if (empty($plugin_id)) {
			$this->_set_error('_ERR_COMMENT_PLUGIN_ID_IS_NOT_EXIST');
			return false;
		}

		// 评论对象id
		$obj_id = (int)$params['obj_id'];
		if (empty($obj_id)) {
			$this->_set_error('_ERR_COMMENT_OBJ_ID_IS_NOT_EXIST');
			return false;
		}

		// 内容
		$content = $params['content'];
		if (empty($content)) {
			$this->_set_error('_ERR_COMMENT_CONTENT_NOT_NULL');
			return false;
		}

		// 回复评论id
		$reply_id = 0;
		if (isset($params['reply_id']) && !empty($params['reply_id'])) {
			$reply_id = (int)$params['reply_id'];
		}

		// @用户id
		$reply_m_uid = '';
		if (isset($params['reply_m_uid']) && !empty($params['reply_m_uid'])) {
			$reply_m_uid = $params['reply_m_uid'];
		}

		// 待添加评论数据
		$comment = array (
			'obj_id'      => $obj_id,
			'plugin_id'   => $plugin_id,
			'm_uid'       => (int)$extend['m_uid'],
			'm_username'  => $extend['m_username'],
			'reply_id'    => $reply_id,
			'reply_m_uid' => $reply_m_uid,
			'content'     => $content,
			'status'      => $this->_d->get_st_create(),
			'created'     => NOW_TIME
		);

		// 执行入库操作
		if (!$id = $this->_d->insert($comment)) {
			E(L('_ERR_INSERT_ERROR'));
			return false;
		}

		// 拼接返回数据
		$comment['id'] = $id;
		return true;
	}

	public function list_comment($params) {

		// 应用id
		$plugin_id = (int)$params['plugin_id'];
		if (empty($plugin_id)) {
			$this->_set_error('_ERR_COMMENT_PLUGIN_ID_IS_NOT_EXIST');
			return false;
		}

		// 评论对象id
		$obj_id = (int)$params['obj_id'];
		if (empty($obj_id)) {
			$this->_set_error('_ERR_COMMENT_OBJ_ID_IS_NOT_EXIST');
			return false;
		}

		// 分页数据
		$limit = (int)$params['limit'];
		$page = (int)$params['page'];

		// 判断分页数据是否正确, 如果不合法赋予系统默认值
		if (empty($limit) || $limit < cfg('PAGE_MINSIZE') || $limit > cfg('PAGE_MAXSIZE')) {
			$limit = cfg('LIMIT_DEF');
		}

		// 分页参数
		list($start, $limit, $page) = page_limit($page, $limit);
		$page_option = array (
			$start,
			$limit
		);

		// 总数
		$total = $this->_d->count_by_objid_pluginid($obj_id, $plugin_id);

		// 列表数据
		$list = $this->_d->list_by_objid_pluginid($obj_id, $plugin_id, $page_option, array ('created' => "DESC"));

		// 返回数据
		return array (
			"total" => $total,
			"limit" => $limit,
			"data"  => $list
		);
	}
}

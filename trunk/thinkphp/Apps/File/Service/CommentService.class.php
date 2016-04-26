<?php
/**
 * CommentService.class.php
 * @create-time: 2015-07-02
 */
namespace File\Service;

class CommentService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("File/Comment");
	}

	/**
	 * 根据文件$file_id获取文件评论列表
	 * @param int $file_id 当前文件f_id
	 * @param string $fields 查询字段字符串
	 * @param array $page_option 分页
	 * @param array $order_option 排序
	 * @return array 评论列表
	 */
	public function list_comment($file_id, $plugin_id, $f_info, $page_option, $order_option) {

		// 获得分级 f_level
		$level = (int)$f_info['f_level'];

		// 分级常量
		$type_file = \File\Model\FileModel::F_FILE;

		// 判断$f_id 的分级是否是文件 不是返回false,是 查出评论列表
		if ($type_file != $level) {
			$this->_set_error('_ERR_FID_FILE_TYPE');
			return false;
		}

		return $this->_d->list_by_fid($file_id, $plugin_id, $page_option, $order_option);
	}

	/**
	 * 根据文件$file_id获取文件评论列表总数
	 * @param int $file_id 当前文件f_id
	 * @return int 评论列表总数
	 */
	public function count_by_comment($file_id, $plugin_id) {

		return $this->_d->count_by_fid($file_id, $plugin_id);
	}


	/**
	 * 文件评论
	 * @param $comment 传入参数 array()
	 * @return bool 返回结果：true=成功，false=失败
	 */
	public function add_comment(&$comment, $params, $f_info, $extend = array ()) {

		// 获得分级 f_level
		$level = (int)$f_info['f_level'];

		// 分级常量
		$type_file = \File\Model\FileModel::F_FILE;

		// 判断$f_id 的分级是否是文件 不是返回false,是 查出评论列表
		if ($type_file != $level) {
			$this->_set_error('_ERR_FID_FILE_TYPE');
			return false;
		}

		// 获取file_id
		$file_id = (int)$params['file_id'];

		// 获取评论内容
		$content = html_entity_decode((string)$params['content']);

		// 验证评论文件file_id是否为空
		if (empty($file_id)) {
			$this->_set_error('_ERR_FID_NOT_EMPTY');
			return false;
		}

		// 验证评论内容是否为空
		if (empty($content)) {
			$this->_set_error('_ERR_CONTENT_NOT_EMPTY');
			return false;
		}

		// 验证评论内容长度
		if (cfg('contentlength') < mb_strlen($content, 'utf8')) {
			$this->_set_error('_ERR_CONTENT_LENGTH_ERROR');
			return false;
		}

		// 获取被回复评论id
		$reply_comment_id=(int)$params['reply_c_id'];
		$reply_id = 0;
		$reply_m_uid = '';

		// 验证被回复评论id是否为空
		if (!empty($reply_comment_id)) {
			$reply_id = $params['reply_c_id'];
			$reply_m_uid = $params['reply_member_uid'];
		}

		$comment = array (
			'file_id'     => (int)$params['file_id'],
			'plugin_id'   => (int)$extend['plugin_id'],
			'm_uid'       => (int)$extend['m_uid'],
			'm_username'  => (string)$extend['m_username'],
			'reply_id'    => $reply_id,
			'reply_m_uid' => $reply_m_uid,
			'content'     => html_entity_decode($content),
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
}

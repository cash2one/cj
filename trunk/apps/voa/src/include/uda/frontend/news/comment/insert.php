<?php
/**
 * voa_uda_frontend_news_comment_insert
 * 统一数据访问/新闻公告/添加评论
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_news_comment_insert extends voa_uda_frontend_news_abstract {
	/** service 类 */
	private $__service = null;
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_news_comment();
		}
	}

	/**
	 * 新增评论
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)新增的新闻公告
	 * @param array $options 其他额外的参数（扩展用）
	 * @return boolean
	 */
	public function add_comment(array $request, array &$result) {
		// 定义参数请求规则
		$fields = array(
			// 内容
			'content' => array(
				'content', parent::VAR_STR,
				array($this->__service, 'validator_content'),
				null, false,
			),
			// 用户ID
			'm_uid' => array(
				'm_uid', parent::VAR_INT,
				array($this->__service, 'validator_uid'),
				null, false
			),
			// 公告ID
			'ne_id' => array(
				'ne_id', parent::VAR_INT,
				array($this->__service, 'validator_ne_id'),
				null, false
			)
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}

		// 添加评论
		$comment = array(
			'content' => $this->__request['content'],
			'm_uid' => $this->__request['m_uid'],
			'ne_id' => $this->__request['ne_id'],
		);

        // 判断当前回复的上一级作者
        if (!empty($request['p_username'])) {
            $comment['p_username'] =$request['p_username'];
        } else {
            $comment['p_username'] = '';
        }

		$record = $this->__service->insert($comment);
		$result = $this->__service->format_one($record);

		return true;
	}


}

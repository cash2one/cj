<?php
/**
 * add.php
 * 内部api方法/超级报表添加评论
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_uda_frontend_superreport_addcomment extends voa_uda_frontend_superreport_abstract {

	/** 外部请求参数 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** 其他扩展参数 */
	private $__options = array();

	/** 类型 service 类 */
	private $__service = null;

	/**
	 * 初始化
	 * 引入  service 类
	 */
	public function __construct() {
		parent::__construct();

		if ($this->__service === null) {
			$this->__service = new voa_s_oa_superreport_comment();
		}
	}

	/**
	 * 新增一个评论
	 * @param array $request 请求的参数
	 * + comment 评论
	 * + m_uid 用户ID
	 * + s_id 报表ID
	 * @param array $result (引用结果)新增的场所信息数组
	 * @param array $options 其他额外的参数（扩展用）
	 * @return boolean
	 */
	public function add_comment(array $request, array &$result) {

		$request['m_uid'] = $this->member['m_uid'];
		// 定义参数请求规则
		$fields = array(
			// 评论
			'comment' => array(
				'comment', parent::VAR_STR,
				array($this->__service, 'validator_comment'),
				null, false,
			),
			// 用户ID
			'm_uid' => array(
				'm_uid', parent::VAR_INT,
				array($this->__service, 'validator_uid'),
				null, false
			),
			// 报表ID
			'dr_id' => array(
				'dr_id', parent::VAR_INT,
				array($this->__service, 'validator_dr_id'),
				null, false
			),
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}

		// 评论数据
		$data = array(
			'dr_id' => $this->__request['dr_id'],
			'm_uid' => $this->__request['m_uid'],
			'comment' => rhtmlspecialchars($this->__request['comment'])
		);

		// 写入评论
		$result = $this->__service->insert($data);
		if (!$result) {
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::ADD_COMMENT_ERROR);
		}

		return true;
	}

}

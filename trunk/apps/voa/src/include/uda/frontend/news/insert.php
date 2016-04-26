<?php
/**
 * voa_uda_frontend_news_insert
 * 统一数据访问/新闻公告/添加新闻公告
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_news_insert extends voa_uda_frontend_news_abstract {
	/** service 类 */
	private $__service = null;
	private $__request = array();

	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_news();
		}
	}

	/**
	 * 新增一个新闻公告
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)新增的新闻公告
	 * @param array $options 其他额外的参数（扩展用）
	 * @return boolean
	 */
	public function add_news(array $request, array &$result, array $options) {
		// 定义参数请求规则
		$fields = array(
			// 标题
			'title' => array(
				'title', parent::VAR_STR,
				array($this->__service, 'validator_title'),
				null, false,
			),
			//摘要
			'summary' => array(
				'summary',parent::VAR_STR,
				array(), null, false
			),
			// 内容
			'content' => array(
				'content', parent::VAR_STR,
				array($this->__service, 'validator_content'),
				null, false,
			),
			// 用户ID
			'm_uids' => array(
				'm_uids', parent::VAR_ARR,
				array($this->__service, 'validator_uids'),
				null, false
			),
			// 部门ID
			'cd_ids' => array(
				'cd_ids', parent::VAR_ARR,
				array($this->__service, 'validator_cdids'),
				null, false
			),
			// 分类ID
			'nca_id' => array(
				'nca_id', parent::VAR_INT,
				array($this->__service, 'validator_nca_id'),
				null, false
			),
			// 封面ID
			'cover_id' => array(
				'cover_id', parent::VAR_INT,
				array($this->__service, 'validator_cover_id'),
				null, false
			),
			//审核人id
			'check_id' => array(
				'check_id',parent::VAR_ARR,
				array($this->__service,'validotor_m_uids_check'),
				null, false
			),
			//消息保密
			'is_secret' => array(
				'is_secret',parent::VAR_INT,
				array(), null, false
			),
			//是否评论
			'is_comment' => array(
				'is_comment',parent::VAR_INT,
				array(), null, false
			),

			//是否点赞
			'is_like' => array(
				'is_like',parent::VAR_INT,
				array(),null,false
			),

			//全部
			'is_all' => array(
				'is_all',parent::VAR_INT,
				array(), null, false
			),
			//是否审核
			'is_check' => array(
				'is_check',parent::VAR_INT,
				array(), null, false
			),
			//审核详情
			'check_summary' => array(
				'check_summary',parent::VAR_STR,
				array(), null, false
			),
			//是否草稿
			'is_publish' => array(
				'is_publish',parent::VAR_INT,
				array(), null, false
			)
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}

		//添加新闻公告、公告内容、权限
		try {
			$this->__service->begin();
			// 处理上传并写入附件

			if (isset($request['temp-img'])) {
				$uda = &uda::factory('voa_uda_frontend_attachment_insert');
				$attachment = array();
				if (!$uda->upload($attachment, $request['temp-img'], 'remote')) {
					return voa_h_func::throw_errmsg(voa_errcode_api_attachment::UPLOAD_UDA_ERROR);
				}
				$this->__request['cover_id'] = isset($attachment['at_id']) ? $attachment['at_id'] : 0;
			}
			// 添加新闻公告
			$news = array(
				'title' => $this->__request['title'],
				'summary' => $request['summary'],
				'nca_id' => $this->__request['nca_id'],
				'm_uid' => $options['user_id'],
				'cover_id' => $this->__request['cover_id'],
				'is_secret' => $this->__request['is_secret'],
				'is_comment' => $this->__request['is_comment'],
				'is_like' => $this->__request['is_like'],
				'is_all' => $this->__request['is_all'],
				'is_check' => $this->__request['is_check'],
				'check_summary' => $this->__request['check_summary'],
				'is_publish' => $this->__request['is_publish'],
				'published' => ($this->__request['is_publish'] == 1) ? startup_env::get('timestamp') : 0,
				'is_message' => empty($request['is_push']) ? 0 : $request['is_push'],
			);

			$news = $this->__service->insert($news);
			$result = $news;
			$ne_id = $news['ne_id'];
			// 添加新闻公告内容
			$s_news_content = new voa_s_oa_news_content();
			$url = '';
			if (!empty($request['atids']) || !empty($request['temp-img'])) {
				$url = '<p>';
				if ($request['is_text'] != 0 && $request['temp-img']) {
					$this->__request['content'] = '<div style="text-align: center; margin-bottom: -29px;"><img src="' . voa_h_attach::attachment_url($this->__request['cover_id']) . '"/></div> <br />' . $this->__request['content'];
				}
				$pic = explode(',', $request['atids']);
				foreach ($pic as $val) {
					if ($request['is_text'] == 0) {
						$request['is_text'] = 2;
						continue;
					}
					if (!empty($val) && substr($val, 0, 4) != 'http') {
						$url .= '<img src="' . voa_h_attach::attachment_url($val) . '"/>';
					}
				}
				$url .= '</p>';
			}
			$news_content = array(
				'content' => $this->__request['content'] . $url,
				'ne_id' => $ne_id
			);
			$s_news_content->insert($news_content);

			if (!empty($this->__request['check_id']) && $request['is_check'] != 0) {
				/** 添加审核 begin**/
				$s_news_check = new voa_s_oa_news_check();
				foreach ($this->__request['check_id'] as $m_uid) {
					$news_check[] = array(
						'news_id' => $ne_id,
						'm_uid' => $m_uid,
						'is_check' => 0
					);
				}
				$s_news_check->insert_multi($news_check);
				/** 添加审核 end **/
			}

			/**添加权限begin**/
			$s_news_right = new voa_s_oa_news_right();
			$news_right = array();
			//如果is_all=1则是全公司可查看，否则就是有权限的人员部门可查看
			if ($request['is_all'] == 1) {
				$news_right = array(
					'ne_id' => $ne_id,
					'nca_id' => $this->__request['nca_id'],
					'm_uid' => 0,
					'cd_id' => 0,
					'is_all' => 1
				);
				$s_news_right->insert($news_right);
			} else {
				//添加人员权限
				if (!empty($this->__request['m_uids'])) {
					foreach ($this->__request['m_uids'] as $m_uid) {
						$news_right[] = array(
							'ne_id' => $ne_id,
							'nca_id' => $this->__request['nca_id'],
							'm_uid' => $m_uid,
							'cd_id' => 0,
							'is_all' => 0
						);
					}
					if (false == array_search($options['user_id'], $this->__request['m_uids'])) {
						$news_right[] = array(
							'ne_id' => $ne_id,
							'nca_id' => $this->__request['nca_id'],
							'm_uid' => $options['user_id'],
							'cd_id' => 0,
							'is_all' => 0
						);
					}
				} else {
					$news_right[] = array(
						'ne_id' => $ne_id,
						'nca_id' => $this->__request['nca_id'],
						'm_uid' => $options['user_id'],
						'cd_id' => 0,
						'is_all' => 0
					);
				}

				//添加部门权限
				if (!empty($this->__request['cd_ids'])) {
					foreach ($this->__request['cd_ids'] as $cd_id) {
						$news_right[] = array(
							'ne_id' => $ne_id,
							'nca_id' => $this->__request['nca_id'],
							'm_uid' => 0,
							'cd_id' => $cd_id,
							'is_all' => 0
						);
					}
				}
				$s_news_right->insert_multi($news_right);
			}
			/**添加权限end**/

			$this->__service->commit();
		} catch (Exception $e) {
			$this->__service->rollback();
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
		return true;
	}


}


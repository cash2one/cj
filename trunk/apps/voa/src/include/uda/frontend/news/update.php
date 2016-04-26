<?php
/**
 * voa_uda_frontend_news_update
 * 统一数据访问/新闻公告/编辑新闻公告
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_news_update extends voa_uda_frontend_news_abstract {
	/** service 类 */
	private $__service = null;
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_news();
		}
	}

	/**
	 * 编辑新闻公告
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)编辑的新闻公告
	 * @param array $options 其他额外的参数（扩展用）
	 * @return boolean
	 */
	public function edit_news(array $request, array &$result, array $options = array()) {
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
			// 分类ID
			'cover_id' => array(
				'cover_id', parent::VAR_INT,
				array($this->__service, 'validator_cover_id'),
				null, false
			),
			// 新闻公告ID
			'ne_id' => array(
				'ne_id', parent::VAR_INT,
				array($this->__service, 'validator_ne_id'),
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
			//是否评论
			'is_like' => array(
				'is_like',parent::VAR_INT,
				array(), null, false
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
		//格式处理
		$this->__request['content'] = nl2br($this->__request['content']);
		//添加新闻公告、公告内容、权限
		try {
			$this->__service->begin();
			$ne_id = $this->__request['ne_id'];

			// 闻公告内容
			$s_news_content = new voa_s_oa_news_content();

			$url = '';
			if(!empty($request['atids'])) {
				$url = '<p>';
				$pic = explode(',', $request['atids']);
				// 封面处理
				if ($request['is_text'] != 0) {
					$this->__request['content'] = '<div style="text-align: center; margin-bottom: -29px;"><img src="' . voa_h_attach::attachment_url($pic[0]) . '"/></div> <br />
' . $this->__request['content'];
				}
				// 去掉封面
				unset($pic[0]);

				foreach($pic as $val) {
					$url .= '<img src="'.voa_h_attach::attachment_url($val).'"/>';
				}
				$url .= '</p>';
			}
			$news_content = array(
				'content' => $this->__request['content'].$url,
			);
			$s_news_content->update_by_conds(array('ne_id'=> $ne_id), $news_content);

			// 新闻公告
			$news = array(
				'title' => $this->__request['title'],
				'summary' => $this->__request['summary'],
				'nca_id' => $this->__request['nca_id'],
				'm_uid' => $options['user_id'],
				'cover_id' => $this->__request['cover_id'],
				'is_secret' => $this->__request['is_secret'],
				'is_comment' => $this->__request['is_comment'],
				'is_like' => $this->__request['is_like'],
				'is_all' => $this->__request['is_all'],
				'is_check' => $this->__request['is_check'],
				'check_summary' =>  $this->__request['check_summary'],
				'is_publish' => $this->__request['is_publish'],
				'published' => ($this->__request['is_publish'] == 1) ? startup_env::get('timestamp') : 0
			);

			$this->__service->update($ne_id, $news);
			$result = $news;
			$result['ne_id'] = $ne_id;
			/** 添加审核 begin**/
			//获取旧审核人
			$s_news_check = new voa_s_oa_news_check();
			$check_urser = $s_news_check->list_by_conds(array('news_id' => $ne_id));
			$check_urser = array_column($check_urser, 'm_uid');
			$diff_user = array_diff($check_urser, $this->__request['check_id']);
			foreach($diff_user as $m_uid) {
				$diff = array(
					'news_id' => $ne_id,
					'm_uid' => $m_uid,
				);
				$s_news_check->delete_by_conds($diff);
			}

			if( !empty($this->__request['check_id']) ) {
				$news_check = array();
				foreach ($this->__request['check_id'] as $m_uid) {
					if(in_array($m_uid, $check_urser)) {
						continue;
					}
					$news_check[] = array(
						'news_id' => $ne_id,
						'm_uid' => $m_uid,
						'is_check' => 0
					);
				}
				if($news_check) {
					$s_news_check->insert_multi($news_check);
				}
			}
			/**添加权限begin**/
			//先将旧有的权限删除，再新增新的权限
			$s_news_right = new voa_s_oa_news_right();
			$s_news_right->delete_real_records_by_conds(array('ne_id'=> $ne_id));

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
					if(false === array_search($options['user_id'], $this->__request['m_uids'])){
						$news_right[] = array(
							'ne_id' => $ne_id,
							'nca_id' => $this->__request['nca_id'],
							'm_uid' => $options['user_id'],
							'cd_id' => 0,
							'is_all' => 0
						);
					}
				}else {
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

	/**
	 * 获取编辑数据
	 * @param int $ne_id
	 * @return array
	 */
	public function get_news($ne_id) {
		// 获取公告
		$news = $this->__service->get($ne_id);
		if (!$news) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::NEWS_NOT_EXIST);
		}
		//获取公告内容
		$s_news_content = new voa_s_oa_news_content();
		$news_content = $s_news_content->get_by_conds(array('ne_id' => $ne_id));
		//获取封面
		$news_cover = array();
		if($news['cover_id'] !=0) {
			$news_cover = array(
				'cover_id' => $news['cover_id'],// 公共文件附件ID
				'url' => voa_h_attach::attachment_url($news['cover_id'], 0)// 附件文件url
			);
		}
		//获取阅读权限
		$news_right = array();
		if ($news['is_all'] == 0) {
			$s_news_right = new voa_s_oa_news_right();
			$news_right = $s_news_right->list_rights_for_single_news($ne_id);
		}

		//获取审批人
		$news_check = array();
		if ($news['is_check'] != 0) {
			$s_news_check = new voa_s_oa_news_check();
			$news_check = $s_news_check->list_by_conds(array('news_id' => $ne_id));
		}

		//合并输出
		$result = array();
		$result = $news;
		$result['content'] = isset($news_content['content']) ? $news_content['content'] : '';


		$result['cover'] = $news_cover;
		$result['rights'] = $news_right;
		$result['check'] = $news_check;
		return $result;

	}

}


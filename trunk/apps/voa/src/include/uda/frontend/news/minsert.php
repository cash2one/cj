<?php
/**
 * voa_uda_frontend_news_minsert
 * 统一数据访问/新闻公告/新闻公告添加多条
 * @date: 2015年5月13日
 * @author: kk
 * @version:
 */

class voa_uda_frontend_news_minsert extends voa_uda_frontend_news_abstract {

	/** service 类 */
	private $__service = null;
	/** 数据字典 */
	private $__newsData = null;

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
	public function add_news(array $request, array &$result, array $options= array()) {
		$mfield = array(
			//发布人
			'author' => array(
				'author',parent::VAR_INT,
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
			//全部
			'is_all' => array(
				'is_all',parent::VAR_INT,
				array(), null, false
			),
			//是否草稿
			'is_publish' => array(
				'is_publish',parent::VAR_INT,
				array(), null, false
			),
			//多条时间戳
			'multiple' => array(
				'multiple',parent::VAR_INT,
				array(), null, false
			)
		);
		// 检查过滤，参数
		if (!$this->extract_field($this->__newsData, $mfield, $request)) {
			return false;
		}

		$multiple = startup_env::get('timestamp');
		// 定义参数请求规则
		foreach($request['mydata'] as $key => $value){
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
				//多条时间戳
				'multiple' => array(
					'multiple',parent::VAR_INT,
					array(), null, false
				)
			);

			// 检查过滤，参数
			if (!$this->extract_field($this->__request, $fields, $value)) {
				return false;
			}

			//添加新闻公告、公告内容、权限
			try {
				$this->__service->begin();

				// 添加新闻公告
				$news = array(
					'title' => $this->__request['title'],
					'summary' => $this->__request['summary'],
					'nca_id' => $this->__request['nca_id'],
					'm_uid' => $this->__newsData['author'],//发布人
					'cover_id' => $this->__request['cover_id'],
					'is_secret' => $this->__request['is_secret'],
					'is_comment' => $this->__request['is_comment'],
					'is_like' => $this->__request['is_like'],
					'is_all' => $this->__newsData['is_all'],
					'is_check' => $this->__newsData['is_check'],
					'check_summary' => $this->__newsData['check_summary'],
					'is_publish' => $this->__newsData['is_publish'],//判断是否为草稿
					'published' => ($this->__newsData['is_publish'] == 1) ? startup_env::get('timestamp') : 0,
					'multiple' => $multiple
				);
				$news = $this->__service->insert($news);
				$result[] = $news;
				$ne_id = $news['ne_id'];

				// 添加新闻公告内容
				$s_news_content = new voa_s_oa_news_content();
				$news_content = array(
					'content' => $this->__request['content'],
					'ne_id' => $ne_id
				);
				$s_news_content->insert($news_content);

				if (!empty($request['check_id'])) {
					$news_check = array();
					$checkid = explode(',', trim($request['check_id'], ','));
					/** 添加审核 begin**/
					$s_news_check = new voa_s_oa_news_check();
					foreach ($checkid as $m_uid) {
						$news_check[] = array(
							'news_id' => $ne_id,
							'm_uid' => $m_uid,
							'is_check' => 1
						);
					}
					$s_news_check->insert_multi($news_check);
					/** 添加审核 end **/
				}
				
				/**区分多条和单条并多条分组 begin**/
				$news_multiple = array();
				if ($news['multiple'] != 0) {
					$news_multiple = array(
						'ne_id' => $news['ne_id'],
						'multiple' => $news['multiple']
					);
				}
				
				/**区分多条和单条并多条分组 end**/

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
					if (!empty($request['m_uids'])) {
						$purview = explode(',', $request['m_uids']);
						foreach ($purview as $m_uid) {
							$news_right[] = array(
								'ne_id' => $ne_id,
								'nca_id' => $this->__request['nca_id'],
								'm_uid' => $m_uid,
								'cd_id' => 0,
								'is_all' => 0
							);
						}
						if (!in_array($request['author'], $purview)) {
							$news_right[] = array(
								'ne_id' => $ne_id,
								'nca_id' => $this->__request['nca_id'],
								'm_uid' => $request['author'],
								'cd_id' => 0,
								'is_all' => 0
							);
						}
					} else {
						$news_right[] = array(
							'ne_id' => $ne_id,
							'nca_id' => $this->__request['nca_id'],
							'm_uid' => $request['author'],
							'cd_id' => 0,
							'is_all' => 0
						);
					}
					//添加部门权限
					if (!empty($request['cd_ids'])) {
						$purview = explode(',', $request['cd_ids']);
						foreach ($purview as $cd_id) {
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
	}
		return true;
	}
}

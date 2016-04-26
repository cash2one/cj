<?php
/**
 * voa_uda_frontend_jobtrain_article
 * Create By wowxavi
 * $Author$
 * $Id$
 */

class voa_uda_frontend_jobtrain_article extends voa_uda_frontend_base {
	/** service 类 */
	private $__service = null;
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_jobtrain_article();
			$this->_serv_right = new voa_s_oa_jobtrain_right();
			$this->_serv_study = new voa_s_oa_jobtrain_study();
			$this->_serv_category = new voa_s_oa_jobtrain_category();
			$this->_serv_coll = new voa_s_oa_jobtrain_coll();
			$this->_serv_comment = new voa_s_oa_jobtrain_comment();
			$this->_serv_commentzan = new voa_s_oa_jobtrain_commentzan();
		}
	}
	/**
	 * 保存分类
	 * @param array $request 请求的参数
	 * @param array $args 其他额外的参数
	 * @return boolean
	 */
	public function save_article(array $request, $args, &$article) {
		$fields = array(
			'cid' => array(
				'cid', 
				parent::VAR_INT,array($this->__service, 'validator_cid'),
				null, false
			),
			'type' => array(
				'type', parent::VAR_INT,
				array(),
				null, false
			),
			'title' => array(
				'title', parent::VAR_STR,
				array($this->__service, 'validator_title'),
				null, false,
			),
			'summary' => array(
				'summary', parent::VAR_STR,
				array(),
				null, false,
			),
			'author' => array(
				'author', parent::VAR_STR,
				array(),
				null, false,
			),
			'preview_summary' => array(
				'preview_summary', parent::VAR_STR,
				array(),
				null, false,
			),
			'content' => array(
				'content', parent::VAR_STR,
				array(),
				//array($this->__service, 'validator_content'),
				null, false,
			),
			'is_secret' => array(
				'is_secret', parent::VAR_INT,
				array(),
				null, false
			),
			'cover_id' => array(
				'cover_id', parent::VAR_INT,
				array(),
				null, false
			),
			'is_comment' => array(
				'is_comment', parent::VAR_INT,
				array(),
				null, false
			),
			'is_loop' => array(
				'is_loop', parent::VAR_INT,
				array(),
				null, false
			),
			'video_id' => array(
				'video_id', parent::VAR_STR,
				array(),
				null, false,
			)
		);
		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}

		try {
			$this->__service->begin();
			// 标题处理
			$this->__request['title'] = msubstr($this->__request['title'], 0, 25, 'utf-8', false);
			// 获取分类信息
			$cata = $this->_serv_category->get($this->__request['cid']);
			// 格式处理
			$this->__request['content'] = nl2br($this->__request['content']);
			// 摘要处理
			if($this->__request['summary'] == '') {
				$this->__request['summary'] = strip_tags($this->__request['content']);
			}
			$this->__request['summary'] = msubstr($this->__request['summary'], 0, 80);

			// 附件处理
			$attachments = array(
				'attachs' => array(),
				'audimgs' => array()
			);
			// 文件附件处理
			if($request['attachs']) {
				foreach ($request['attachs'] as $k => $v) {
					$attachments['attachs'][] = array(
						'id' => $v['id'],
						'name' => $v['name'],
						'size' => $v['size']
					);
				}
			}
			// 音图附件处理
			if($request['audimgs']) {
				foreach ($request['audimgs'] as $k => $v) {
					if($v['img_id']){
						$attachments['audimgs'][] = array(
							'audio_id' => $v['audio_id'],
							'audio_duration' => $v['audio_duration'],
							'img_id' => $v['img_id'],
						);
					}
				}
			}
			$this->__request['attachments'] = serialize($attachments);
			// 记录发布时间
			$this->__request['publish_time'] = startup_env::get('timestamp');
			// 发布处理
			if($request['pubsubmit']){
				$this->__request['is_publish'] = 1;
				// 统计学习总人数
				$this->__request['study_sum'] = $this->__service->get_study_sum($cata);
			}else{
				$this->__request['is_publish'] = 0;
				$this->__request['study_sum'] = 0;
			}
			// 学习人数清零
			$this->__request['study_num'] = 0;

			// 添加文章和更新文章
			if($args['id']) {
				$this->__service->update($args['id'], $this->__request);
				$article = $this->__request;
				$article['id'] = $args['id'];
			} else {
				// 插入管理员
				$this->__request['m_uid'] = $args['m_uid'];
				$this->__request['m_username'] = $args['m_username'];
				$article = $this->__service->insert($this->__request);
			}
			// 获取发布时是否全部发送
			$article['is_all'] = $request['is_all'];		
			
			// 删除 学习评论收藏
			$this->_serv_study->delete_by_conds(array('aid' => $article['id'] ));
			//$this->_serv_coll->delete_by_conds(array('aid' => $article['id'] ));
			//$this->_serv_comment->delete_by_conds(array('aid' => $article['id'] ));

			// 获取文章数量并更新分类
			$article_count = $this->__service->count_by_conds(array('cid'=>$article['cid'], 'is_publish'=>1));
			$this->_serv_category->update($article['cid'], array('article_num'=>$article_count));
			//$this->_serv_category->update_article_num($article['cid'], $article_count);

			if($request['previewsubmit']){
				// 预览发布
				$article['is_preview'] = 1;
				// 物理 删除 权限
				$this->_serv_right->delete_real_records_by_conds(array('aid' => $article['id'] ));
				// 预览 插入权限
				foreach ($request['preview_id'] as $v) {
					$data_right[] = array(
						'aid' => $article['id'],
						'cid' => 0,
						'm_uid' => $v,
						'cd_id' => 0,
						'is_all' => 0
					);
				}
				$this->_serv_right->insert_multi($data_right);
			}else if($request['pubsubmit']){
				// 正式发布
				// 物理 删除 权限
				$this->_serv_right->delete_real_records_by_conds(array('aid' => $article['id'] ));
			}

			$this->__service->commit();
		} catch (Exception $e) {
			$this->__service->rollback();
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}
	
	/**
	 * 获取列表
	 * @param int $id
	 * @return array
	 */
	public function list_article(&$result, $conds, $pager) {
		$result['list'] =  $this->_list_article_by_conds($conds, $pager);
		$result['total'] = $this->_count_article_by_conds($conds);
		return true;
	}
	/**
	 * 根据条件查找
	 * @param array $conds 条件数组
	 * @param int|array $pager 分页参数
	 * @return array $list
	 */
	protected function _list_article_by_conds($conds, $pager) {
		$list = array();
		$list = $this->__service->list_by_conds($conds, $pager, array('is_publish'=>'ASC', 'updated' => 'DESC'));
		return $list;
	}
	/**
	 * 根据条件计算数据数量
	 * @param array $conds
	 * @return number
	 */
	protected function _count_article_by_conds($conds) {
		$total = $this->__service->count_by_conds($conds);
		return $total;
	}
	/**
	 * 获取数据
	 * @param int $id
	 * @return array
	 */
	public function get_article($id) {
		$result = $this->__service->get($id);
		// 设置属性
		$attachments = unserialize($result['attachments']);
		$result['audimgs'] = $attachments['audimgs'];
		$result['attachs'] = $attachments['attachs'];
		// 设置音图
		$audimgs = array();
		foreach ($result['audimgs'] as $k => $v) {
			$audimgs[] = array(
				'audio_id' => $v['audio_id'],
				'audio_duration' => $v['audio_duration'],
				'audio_src' => $v['audio_id']?voa_h_attach::attachment_url($v['audio_id']):'',
				'img_id' => $v['img_id'],
				'img_src' => voa_h_attach::attachment_url($v['img_id']),
			);
		}
		$result['audimgs_json'] = rjson_encode($audimgs);
		// 设置附件
		$attachs = array();
		$settings = voa_h_cache::get_instance()->get('setting', 'oa');
		foreach ($result['attachs'] as $k => $v) {
			$attachs[] = array(
				'id' => $v['id'],
				'name' => $v['name'],
				'size' => $v['size'],
				//'url' => voa_h_attach::attachment_url($v['id']).'&download=1',
				'url' => 'http://'.$settings['domain'].'/Jobtrain/Apicp/Attach/download?aid='.$v['id']
			);
		}
		$result['picurl'] = voa_h_attach::attachment_url($result['cover_id']);
		$result['attachs_json'] = rjson_encode($attachs);
		return $result;
	}

	/**
	 * 删除
	 * @param arr $ids
	 * @return bool
	 */
	public function del_article($ids) {
		try {
			$this->__service->begin();

			$conds = array(
				'aid' => $ids
			);
			// 获取分类ids
			$article_list = $this->__service->list_by_pks($ids);
			//删除内容
			$this->__service->delete($ids);                 
			// 物理删除权限
			$this->_serv_right->delete_real_records_by_conds($conds);
			// 删除点赞
			$comment_list = $this->_serv_comment->list_by_conds($conds);
			$comment_ids = array_column($comment_list, 'id');
			$this->_serv_commentzan->delete_by_conds(array('comment_id'=>$comment_ids));
			// 删除 学习评论收藏
			$this->_serv_study->delete_by_conds($conds); 
			$this->_serv_coll->delete_by_conds($conds);
			$this->_serv_comment->delete_by_conds($conds);

			// 获取文章
			foreach ($article_list as $k => $v) {
				// 获取文章数量并更新分类
				$article_count = $this->__service->count_by_conds(array('cid'=>$v['cid'], 'is_publish'=>1));
				$this->_serv_category->update($v['cid'], array('article_num'=>$article_count));
				//$this->_serv_category->update_article_num($v['cid'], $article_count);
			}

			$this->__service->commit();
		} catch (Exception $e) {
			$this->__service->rollBack();
			return $this->set_errmsg(voa_errcode_oa_jobtrain::ARTICLE_DEL_FAILED);
		}
		return true;
	}

	
}
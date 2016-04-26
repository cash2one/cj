<?php
/**
 * voa_c_api_news_post_addcontent
 * 添加新闻公告
 * @date: 2015年8月20日
 * @author: muzhitao
 * @version:
 */
class voa_c_api_news_post_addcontent extends voa_c_api_news_abstract {
	public function execute() {

		logger::error($this->_member['m_username']);
		try {
			/** 需要的参数 */
			$fields = array(
				'title' => array('type' => 'string_trim', 'required' => true),
				'summary' => array('type' => 'string_trim', 'required' => false),
				'ne_id' => array('type' => 'string_trim', 'required' => true),
				'nca_id' => array('type' => 'int', 'required' => true),
				'atids' => array('type' => 'string_trim', 'required' => true),
				'temp-img' => array('type' => 'string_trim', 'required' => false),
				'content' => array('type' => 'string', 'required' => false),
			);

			// 基本验证检查
			if (!$this->_check_params($fields)) {
				return false;
			}

            // 标题是否为空
			if (empty($this->_params['title'])) {
				return $this->_set_errcode(voa_errcode_api_news::SUBJECT_NULL);
			}
			// 字数检查
			if (!validator::is_string_count_in_range($this->_params['title'], 1, 64)) {
				return $this->_set_errcode(voa_errcode_api_news::SUBJECT_BEYOND);
			}

            // 摘要检查
			if (!validator::is_string_count_in_range($this->_params['summary'], 0, 120)) {
				return $this->_set_errcode(voa_errcode_api_news::SUMMARY_BEYOND);
			}

			//类型检查
			if (empty($this->_params['nca_id'])) {
				return $this->_set_errcode(voa_errcode_api_news:: MESSCATE_NULL);
			}
			// 内容检查
			if (empty($this->_params['content'])) {
				return $this->_set_errcode(voa_errcode_api_news:: MESSAGE_NULL);
			}
			//图片检查
			if (empty($this->_params['atids']) && empty($this->_params['temp-img'])) {
				return $this->_set_errcode(voa_errcode_api_news:: MESSIMG_NULL);
			}
			// 读取数据
			$data = array(
				'is_text' => 0,       //封面图片正文 1:是 0:否
				'is_secret' => 0,     //消息保密 1:是 0:否
				'is_comment' => 0,    //是否评论 1:是 0:否
				'is_like' => 0,       // 是否点赞 1:是 0:否
 				'is_push' => 0,       //消息推送 1:是 0:否
				'is_check' => 0,      //是否审核 1:是 0:否
				'is_publish' => 1,    //判断草稿 0:草稿 1:发布
				'check_id' => 0,      //审核人id
				'is_all' => 0
			);

			$mydata = $this->request->postx();
			$list = array_merge($data, $mydata);

			list($list['cover_id']) = !empty($list['atids']) ? explode(',', $list['atids']) : array('', '');
			$list['check_id'] = isset($mydata['check_id']) ? explode(',', $mydata['check_id']) : array();
			if(($list['m_uids'] == -1 && $list['cd_ids'] == -1) || ($list['m_uids'] == '' && $list['cd_ids'] == '')){
				$list['is_all'] = 1;
			}

            // 格式化当前选择的人员数据
			$list['m_uids'] = !empty($list['m_uids']) ? explode(',', $list['m_uids']) : array();
			$list['cd_ids'] = !empty($list['cd_ids']) ? explode(',', $list['cd_ids']) : array();

            // 如果不审核 则公告直接发布 否则存为草稿
			$list['is_publish'] = $list['is_check'] == 1 ? 0 : 1;

			$news = array();

            /* 判断当前动作是编辑 还是添加  */
			if($list['action'] != 'edit') {
				$uda = &uda::factory('voa_uda_frontend_news_insert');
				$options['user_id'] = startup_env::get('wbs_uid');

                // 提交新增数据信息
				$uda->add_news($list,  $news, $options);
			}else {
				$uda = &uda::factory('voa_uda_frontend_news_update');
				$options['user_id'] = startup_env::get('wbs_uid');

                // 编辑提交数据信息
				$uda->edit_news($list,  $news, $options);
			}

		} catch (help_exception $h) {
			$this->_errcode = $h->getCode();
			$this->_errmsg = $h->getMessage();
		} catch (Exception $e) {
			logger::error($e);
			return $this->_api_system_message($e);
		}

		$push = $list['is_push'];
		$check = $list['is_check'];

		/** 进行数据推送 */
		$tips = '';
		if ( $check){
			$tips = '预览已发送成功!';
			$this->_to_queue($news);
		} else {
			$tips = '公告发布成功!';
			if ($push) {
				$this->_to_queue($news);
			}
		}

		// 输出结果
		$this->_result = array(
			'result' => $news,
			'tips' => $tips
		);

		return true;
	}
}

//end

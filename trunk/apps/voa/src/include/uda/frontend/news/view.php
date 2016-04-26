<?php
/**
 * voa_uda_frontend_news_view
 * 统一数据访问/新闻公告/获取单个新闻公告
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_news_view extends voa_uda_frontend_news_abstract {
	/** service 类 */
	private $__service = null;
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_news();
		}
	}

	/**
	 * 获取单个新闻公告
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)信息数组
	 * @return boolean
	 */
	public function get_view(array $request, array &$result) {

		// 定义参数请求规则
		$fields = array(
			'ne_id' => array(
				'ne_id', parent::VAR_INT,
				array($this->__service, 'validator_ne_id'),
				null, false,
			)
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}
		// 取得参数
		$ne_id = $this->__request['ne_id'];
		$m_uid = $request['m_uid'];

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
		// 手机端点赞图标 用户刷新的话，会还原默认的状态，这边要传递一个标记，进行过滤
		$s_news_like = new voa_s_oa_news_like();
		$now_m_uid = intval(startup_env::get('wbs_uid'));
		$like_data = array('ne_id' => $ne_id,'m_uid' =>$now_m_uid);
		$like_list = $s_news_like->list_by_conds($like_data,array(0,1),array('created'=>'DESC','like_id'=>'DESC'));

		// 默认是1
		$new_des = 1;
		if($like_list){
			foreach ($like_list as $key => $val) {
				$new_des = $val['description'];
			}
		}

		//获取评论
		$s_news_comment = new voa_s_oa_news_comment();
		$news_comment = $s_news_comment->list_by_conds(array('ne_id' => $ne_id), null, array('created' => 'DESC'));
		//获取浏览量
		$s_news_read = new voa_s_oa_news_read();
		$news_read = $s_news_read->count_by_conds(array('ne_id' => $ne_id));

		//如果是前台浏览，则浏览量+1,如果是后台，则获取阅读权限
		$news_right = array();
		$s_news_right = new voa_s_oa_news_right();
		if ($m_uid != 0) { /** 供前台使用数据 */
			//确认查看权限
			if( $news['is_publish'] == 1 ) {
				$news_right = $s_news_right->confirm_right_for_user($ne_id, $m_uid);
				if (!$news_right) {
					return voa_h_func::throw_errmsg(voa_errcode_oa_news::NEWS_NO_RIGHT);
				}
				$read = $s_news_read->get_by_conds(array('ne_id' => $ne_id, 'm_uid' => $m_uid));
				if (empty($read)){//浏览量+1
					$s_news_read->insert(array('ne_id' => $ne_id,'m_uid' => $m_uid));
				}
			} else {
				//如果是草稿审核查看,并判断是否可以查看,不操作浏览量
				$s_new_check = new voa_s_oa_news_check();
				$news_check = $s_new_check->is_check($ne_id, $m_uid);
				if(!$news_check && $news['m_uid'] != $m_uid) {
					return voa_h_func::throw_errmsg(voa_errcode_oa_news::NEWS_NO_RIGHT);
				}
			}
		} else { /** 供后台使用数据 */
			//获取阅读权限
			if ($news['is_all'] == 0) {
				$news_right = $s_news_right->list_rights_for_single_news($ne_id);
			}
		}
        // 获取作者
        $s_member = new voa_s_oa_member();
        $user = $s_member->fetch_by_uid($news['m_uid']);
        voa_h_user::push($user);

		//合并输出
		$result = array();
		$result = $news;
		$result['published'] = rgmdate($news['published'], 'Y-m-d H:i');
		$result['content'] = isset($news_content['content']) ? $news_content['content'] : '';
		$result['cover'] = $news_cover;
		$result['comments'] = $s_news_comment->format_list($news_comment);
		$result['comment_num'] = $news_comment ? count($news_comment) : 0;
		$result['read_num'] = $news_read;
		$result['rights'] = $news_right;
        $result['author'] = $user['m_username'];
		// 标记
		$result['new_des'] = $new_des;
		return true;
	}

	/**
	 * 获取新闻公告进行编辑
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)信息数组
	 * @return boolean
	 */
	public function get_view_edit(array $request, array &$result) {

		// 定义参数请求规则
		$fields = array(
			'ne_id' => array(
				'ne_id', parent::VAR_INT,
				array($this->__service, 'validator_ne_id'),
				null, false,
			)
		);
		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}
		// 取得参数
		$ne_id = $this->__request['ne_id'];
		$m_uid = $request['m_uid'];

		// 获取公告
		$news = $this->__service->get($ne_id);
		if (!$news) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::NEWS_NOT_EXIST);
		}
		//权限判断
		if (($m_uid != $news['m_uid']) || ($news['is_publish'] != 0)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::NEWS_NO_EDIT);
		}
		$serv_getuserlist = &service::factory('voa_uda_frontend_member_getuserlist');
		$serv_getdplist = &service::factory('voa_uda_frontend_department_get');
		//获取查看人
		$right_users[] = array(
			'm_uid' => '-1',
			'm_face' => '',
			'm_username' => '全部人'
		);
		$right_dps[] = array();
		if ($news['is_all'] != 1) {
			$s_news_right = new voa_s_oa_news_right();
			$news_right = $s_news_right->list_by_conds(array('ne_id' => $ne_id));
			if (!empty($news_right)) {
				$right_users = array();
				foreach ($news_right as $value) {
					$face = array();
					$request['uid'] = $value['m_uid'];
					$serv_getuserlist->doit($request, $face);
					$right_users[] = array(
						'm_uid' => isset($value['m_uid']) ? $value['m_uid'] : '',
						'm_face' => isset($face[$value['m_uid']]['m_face']) ? $face[$value['m_uid']]['m_face'] : '',
						'm_username' => isset($face[$value['m_uid']]['m_username']) ? $face[$value['m_uid']]['m_username'] : ''
					);
					$department = array();
					$serv_getdplist->get($value['cd_id'], $department);
					$right_dps[] = array(
						'cd_id' => isset($value['cd_id']) ? $value['cd_id'] : '',
						'cd_name' => isset($department['cd_name']) ? $department['cd_name'] : ''
					);
				}
			}
		}

		//获取公告内容
		$s_news_content = new voa_s_oa_news_content();
		$news_content = $s_news_content->get_by_conds(array('ne_id' => $ne_id));

		//获取审核信息
		$news_check = array();
		$check_users = array();
		if ($news['is_check'] == 1) {
			$s_news_check = new voa_s_oa_news_check();
			$news_check = $s_news_check->list_by_conds(array('news_id' => $ne_id));

			if (!empty($news_check)) {
				foreach ($news_check as $value) {
					$face = array();
					$request['uid'] = $value['m_uid'];
					$serv_getuserlist->doit($request, $face);
					$check_users[] = array(
						'm_uid' => isset($value['m_uid']) ? $value['m_uid'] : '',
						'm_face' => isset($face[$value['m_uid']]['m_face']) ? $face[$value['m_uid']]['m_face'] : '',
						'm_username' => isset($face[$value['m_uid']]['m_username']) ? $face[$value['m_uid']]['m_username'] : ''
					);
				}
			}
		}

		//获取封面
		$news_cover = array();
		if ($news['cover_id'] != 0) {
			$news_cover[] = array(
				'aid' => $news['cover_id'],// 公共文件附件ID
				'url' => voa_h_attach::attachment_url($news['cover_id'], 0)// 附件文件url
			);
		}
		//读取文件里图片
		$content = isset($news_content['content']) ? $news_content['content'] : '';;
		$content_url = array();
		preg_match_all('/<img\s+src="([^"]+)"[^>]*>/is', $content, $content_url);
		//公告里的图片id
		$cover_id = array();
		//封面是否在公告内
		$is_cover = false;
		if ($content_url[1]) {
			foreach ($content_url[1] as $val) {
				$url_id = substr($val, (strrpos($val, '/') + 1));
				$cover_id[] = $url_id;
				$news_cover[] = array(
					'aid' => $url_id,// 公共文件附件ID
					'url' => voa_h_attach::attachment_url($url_id, 0)// 附件文件url
				);
			}
			if ($news_cover[0]['aid'] == $news['cover_id']) {
				$is_cover = true;
			}
		}
		$new_content = preg_replace('/<img\s+src="([^"]+)"[^>]*>/is','', $content);
		$new_content = str_replace('&nbsp;', ' ', $new_content);

		$new_content = strip_tags($new_content);



		//合并输出
		$result = array();
		$result = $news;
		$result['published'] = rgmdate($news['updated'], 'Y-m-d H:i');
		$result['content'] = isset($new_content) ? $new_content : '';
		$result['cover'] = $news_cover;
		$result['is_cover'] = $is_cover;
		$result['check_users'] = $check_users;
		$result['right_users'] = $right_users;

		return true;
	}

}

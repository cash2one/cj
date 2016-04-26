<?php
/**
 * voa_uda_frontend_news_check
 * 获取新闻公告审核内容
 * Created by PhpStorm.
 * User: kk
 * Date: 2015/5/20
 * Time: 16:08
 */

class voa_uda_frontend_news_check extends voa_uda_frontend_news_abstract {
	/** service 类 */
	const NOT_REVIEWED = 1;
	private $__service = null;
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_news_check();
		}
	}

	/**
	 * 获取公告审核内容
	 * @param array $request
	 */
	public function check_news(array $request, array &$result) {
		$fields = array(
			//用户id
			'm_uid' => array(
				'm_uid', parent::VAR_INT,
				array($this->__service, 'validator_uid'),
				null, false
			),
			//新闻id
			'news_id' => array(
				'news_id', parent::VAR_INT,
				array($this->__service, 'validator_newid'),
				null,false
			)
		);
		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}
		// 取得参数
		$news_id = $this->__request['news_id'];
		$m_uid = $this->__request['m_uid'];
		//获取公告审核说明
		//获取公告内容
		$s_news = new voa_s_oa_news();
		$news = $s_news->get_by_conds(array('ne_id' => $news_id));

		if($m_uid != $news['m_uid']){
			//获取审核用户列表
			$news_check = $this->__service->list_check_users($news_id);
			$news_check = array_column($news_check, 'm_uid');
			//判断审核权限
			if(!in_array($m_uid, $news_check)) {
				return voa_h_func::throw_errmsg(voa_errcode_oa_news::M_UID_CHECK, $m_uid);
			}
		}

		$result = array();
		$result = $news;
		$user_name = voa_h_user::get($news['m_uid']);
		$result['username'] = isset($user_name['m_username'])?$user_name['m_username']:'';
        $result['title'] = rsubstr($news['title'], 26);

		return true;
	}

	/**
	 * 审核人审核内容
	 * @param $request
	 * @param $result
	 */
	public function update_check($request, &$result){
		$data_id = array('news_id'=> $request['ne_id'], 'm_uid'=>$request['m_uid']);
			$data_value = array(
				'check_note'=>$request['content'],
				'is_check' => $request['is_check']
			);
		$result = $this->__service->update_by_conds($data_id, $data_value);
		return true;
	}

	/**
	 * 判断用户是否符合审核权限
	 * @param $requeest 传入数组 array('uid'=>1, 'news_id'=>2)
	 * @return true
	 */
	public function is_check($request) {
		$news_check = $this->__service->get_by_conds($request);
		if ($news_check['is_check'] > self::NOT_REVIEWED) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::NEWS_CHECK);
		}
		return true;
	}
}

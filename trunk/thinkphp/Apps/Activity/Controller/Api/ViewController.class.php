<?php
/**
 * 活动详情
 * ViewController.class.php
 * $author$
 * User: XiaoDingchen
 */

namespace Activity\Controller\Api;

class ViewController extends AbstractController {

	// 不强制登陆
	public function before_action() {

		$this->_require_login = false;
		return parent::before_action();
	}

	/**
	 * 活动详情接口
	 * get方式，容许外部访问
	 * */
	public function View_get() {

		//获取指定活动ID
		$acid = I('get.acid');

		//获取当前登陆人员信息
		$user = array();
		$m_uid = 0;
		$user = $this->_login->user;

		//判断是否已登录
		if(!empty($user)){

			$m_uid = $user['m_uid'];
		}
		//获取活动
		$serv = D('Activity/Activity', 'Service');

		//获取活动详情
		$view = array();
		$view = $serv->view($acid);

		//判断活动是否合法
		if(!is_array($view)){

			return $view;
		}
		$f_view = $serv->format_view($acid, $m_uid, $view);

		//返回数据
		return $this->_response($f_view);

	}
}

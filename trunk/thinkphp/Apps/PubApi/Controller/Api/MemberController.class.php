<?php
/**
 * MemberController.class.php
 * $author$
 */
namespace PubApi\Controller\Api;

use Common\Common\Wxqy\Service;
use Common\Common\Cache;
use Common\Common\User;

class MemberController extends AbstractController {

	/**
	 * 重写分发action之前操作
	 *
	 * @param string $action
	 * @return bool
	 */
	public function before_action($action = '') {

		$this->_require_login = false;
		return parent::before_action($action);
	}

	// 登陆
	public function Login_get() {

		$login_serv = D('PubApi/Login', 'Service');
		$login_serv->check_login($this->_result, I('get.', '', 'trim'));
		return true;
	}

	/**
	 * 模拟用户登陆
	 */
	public function Simulation_Login() {

		if ('qw5.vchangyi.com' == $_SERVER['HTTP_HOST'] || 'test.vchangyi.com' == $_SERVER['HTTP_HOST']) {
			$phone = I('get.phone'); // 用户手机号码

			$result = array();
			// 设置cookie，允许跨域访问
			setcookie('Cookie', $result);
			header("Access-Control-Allow-Headers:Origin, Accept-Language, Accept-Encoding,X-Forwarded-For, Connection, Accept, User-Agent, Host, Referer,Cookie, Content-Type, Cache-Control, *");

			$this->_result = array('header' => $result);
		}

		return true;
	}

	/**
	 * 获取二次分享签名信息
	 *
	 * @return bool
	 */
	public function GetShareSign_get() {

		// 取jsapi授权签名相关
		$serv = &Service::instance();
		$jscfg = array();
		$serv->jsapi_signature_for_share($jscfg, I('get.url', '', 'trim'));

		$this->_result = array('jscfg' => $jscfg);
		return true;
	}


	/**
	 * 名人堂
	 * @return bool
	 */
	public function Fame_List_get() {

		$type = I('get.type', 1, 'intval');/*1-新用户列表 2-积分排行*/
		$page = I('get.page', 1, 'intval');
		$limit = I('get.limit', 10, 'intval');
		$keywords = I('get.keywords', '', 'htmlspecialchars');

		$uid = $this->_login->user['m_uid'];
		list($start, $limit, $page) = page_limit($page, $limit);
		$member = D('PubApi/Member', 'Service');
		// 分页数组
		$page_option = array($start, $limit);
		// 排序
		if ($type == 1) {
			$orderby = array('m_created' => 'DESC');
		} else {
			$orderby = array('total' => 'DESC');
		}

		$conds = array('uid' => $uid, 'type' => $type);
		$list = $member->get_fame_list($conds, $page_option, $orderby, $keywords);
		$total = $member->get_type_total($type, $keywords);
		$this->_result = array(
			'page' => $page,
			'limit' => $limit,
			'total' => $total,
			'list' => $list,
		);
		return true;
	}

}

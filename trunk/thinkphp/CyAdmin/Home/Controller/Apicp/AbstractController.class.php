<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/30
 * Time: 上午10:41
 */
namespace Home\Controller\Apicp;
use Think\Controller\RestController;
use Com\Cookie;

class AbstractController extends \Common\Controller\Apicp\AbstractController {

	// 今天、昨天、明天的整点时间戳
	protected $_today_time = 0;
	protected $_yesterday_time = 0;
	protected $_tomorrow_time = 0;
	// 前置操作
	public function before_action($action = '') {
//		$this->_today_time = rstrtotime(rgmdate(NOW_TIME, 'Y-m-d') . ' 00:00:00');
		$this->_today_time = time();
		$this->_tomorrow_time = $this->_today_time + 86400;
		$this->_yesterday_time = $this->_today_time - 86400;
		return true;
	}

	// 后置操作
	public function after_action($action = '') {

		return true;
	}
}
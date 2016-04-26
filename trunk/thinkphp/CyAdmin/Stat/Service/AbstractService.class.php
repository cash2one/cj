<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/30
 * Time: 下午1:08
 */

namespace Stat\Service;

class AbstractService extends \Common\Service\AbstractService {

	/** 今天、昨天、明天的整点时间戳 */
	protected $_today_time = 0;
	protected $_yesterday_time = 0;
	protected $_tomorrow_time = 0;
	/** 一周天数 */
	const WEEK_DAYS = 7;
	/** 默认列表limit */
	const DEAFULT_LIMIT = 10;
	/** 默认页数 */
	const DEAFULT_PAGE = 1;
	/** 默认开始时间戳 */
	protected $_default_start_time = 0;
	/** 客户等级 */
	protected $_customer_level = array();
	/** 付费状态 */
	protected $_pay_status = array();
	/** 客户状态 */
	protected $_customer_status = array();

	// 构造方法
	public function __construct() {

		parent::__construct();

		$this->_today_time = rstrtotime(rgmdate(NOW_TIME, 'Y-m-d') . ' 00:00:00');
		$this->_tomorrow_time = $this->_today_time + 86400;
		$this->_yesterday_time = $this->_today_time - 86400;
		$this->_default_start_time = $this->_tomorrow_time - self::WEEK_DAYS * 86400;

		// 客户等级
		$this->_customer_level = array(
			1 => '小客户',
			2 => '中型客户',
			3 => '大型客户',
			4 => 'VIP客户'
		);
		// 付费状态
		$this->_pay_status = array(
			1 => '已付费',
			2 => '已付费-即将到期',
			3 => '已付费-已到期',
			5 => '试用期-即将到期',
			6 => '试用期-已到期',
			7 => '试用期'
		);
		// 客户状态
		$this->_customer_status = array(
			1 => '新增客户',
			2 => '初步沟通',
			3 => '见面拜访',
			4 => '确定意向',
			5 => '正式报价',
			6 => '商务谈判',
			7 => '签约成交',
			8 => '售后服务',
			9 => '停滞',
			10 => '流失'
		);
	}
}
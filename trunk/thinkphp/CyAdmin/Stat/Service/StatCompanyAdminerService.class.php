<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/30
 * Time: 下午1:13
 */
namespace Stat\Service;

class StatCompanyAdminerService extends AbstractService {

	public function __construct() {

		parent::__construct();
		$this->_d = D('Stat/StatCompanyAdminer');
	}

	/**
	 * 根据日期和负责人查询记录
	 * @param array $date 日期
	 * @param array $adminer 负责人
	 * @param array $page_option 分页参数
	 * @return array|bool
	 */
	public function list_by_date_adminer($date, $adminer, $page_option) {

		return $this->_d->list_by_date_adminer($date, $adminer, $page_option);
	}

	/**
	 * 格式负责人数据
	 * @param $list array 待格式数据
	 * @return bool
	 */
	public function format_adminer(&$list) {

		if (empty($list)) {
			return true;
		}

		$cache = \Common\Common\Cache::instance();
		$adminer = $cache->get('Common.adminer');
		//负责人名称数组
		$adminer_name = array_column($adminer, 'ca_realname', 'ca_id');
		//格式
		foreach($list as &$val) {
			$val['username'] = isset($adminer_name[$val['ca_id']])?$adminer_name[$val['ca_id']]:'负责人已被删除';
			$val['date'] = rgmdate($val['time'], 'Y-m-d');
			$val['lose_percent'] = $val['lose_percent'] * 100 .'%';
			$val['pay_percent'] = $val['pay_percent'] * 100 . '%';
			$val['activation_percent'] = $val['activation_percent'] * 100 . '%';
		}

		return true;
	}

	/**
	 * 根据时间，负责人统计数据
	 * @param $date array 日期
	 * @param $adminer array 负责人
	 * @return mixed
	 */
	public function count_by_date_adminer($date, $adminer) {

		return $this->_d->count_by_date_adminer($date, $adminer);
	}
}
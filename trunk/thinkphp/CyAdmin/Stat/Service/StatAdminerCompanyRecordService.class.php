<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/30
 * Time: 下午1:13
 */
namespace Stat\Service;

class StatAdminerCompanyRecordService extends AbstractService {

	public function __construct() {

		parent::__construct();
		$this->_d = D('Stat/StatAdminerCompanyRecord');
	}

	/**
	 * 根据时间和负责人查询
	 * @param $date array 日期
	 * @param $page_option array 分页参数
	 * @return mixed
	 */
	public function list_by_time_adminer($date, $ca_id, $page_option) {

		return $this->_d->list_by_time_adminer($date, $ca_id, $page_option);
	}

	/**
	 * 根据时间和负责人查询
	 * @param $date array 日期
	 * @param $page_option array 分页参数
	 * @return mixed
	 */
	public function count_by_time_adminer($date, $ca_id) {

		return $this->_d->count_by_time_adminer($date, $ca_id);
	}
}
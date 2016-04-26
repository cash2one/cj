<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/30
 * Time: 下午1:13
 */
namespace Stat\Service;

class StatCompanyService extends AbstractService {

	public $field_name = array(
		'company_count' => '新增企业数',
		'add_member' => '新增员数',
		'active_company' => '活跃企业数',
		'active_member' => '活跃员工数',
		'activation_count' => '激活企业数',
		'lose_percent' => '企业流失率',
		'pay_count' => '付费企业数',
	);

	public function __construct() {

		parent::__construct();
		$this->_d = D('Stat/StatCompany');
	}

	/**
	 * 读取所有公司数据
	 * @param $params array 参数
	 * @return mixed
	 */
	public function list_by_conds_cp($params, $page_option) {

		return $this->_d->list_by_conds_cp($params, $page_option);
	}


	/**
	 * 统计所有公司数据
	 * @param $params array 参数
	 * @return mixed
	 */
	public function count_by_conds_cp($params) {

		return $this->_d->count_by_conds_cp($params);
	}

	/**
	 * 昨天数据
	 * @return mixed
	 */
	public function get_company() {

		$conds['s_time'] = rgmdate(NOW_TIME - 86400, 'Y-m-d');
		$conds['e_time'] = rgmdate(NOW_TIME - 86400, 'Y-m-d');

		return $this->_d->get_company($conds);
	}

	/**
	 * 获取前天公司数据
	 */
	public function get_yesterday_company() {

		$conds['s_time'] = rgmdate(NOW_TIME - 86400 * 2, 'Y-m-d');
		$conds['e_time'] = rgmdate(NOW_TIME - 86400 * 2, 'Y-m-d');

		return $this->_d->get_company($conds);
	}

	/**
	 * header方法
	 * @param $yesterday array 昨天数据
	 * @param $before_yesterday array 前天数据
	 * @return array|bool
	 */
	public function get_header_data_new($yesterday, $before_yesterday) {

		if (empty($yesterday)) {
			return true;
		}
		//匹配字段
		foreach ($this->field_name as $_field => $field_name) {
			$tmp = array();
			$tmp['field_name'] = $field_name;
			$tmp['number'] = $yesterday[$_field];
			$tmp['percent'] = round(($yesterday[$_field] - $before_yesterday[$_field]) / (empty($before_yesterday[$_field]) ? $yesterday[$_field] : $before_yesterday[$_field]) * 100, 2);
			//是百分比
			if ($_field == 'lose_percent') {
				$tmp['number'] = ($yesterday[$_field]) * 100 . '%';
				$tmp['percent'] = 'delete';
			}
			if ($_field == 'active_member') {
				$tmp['number'] = 0;
				$tmp['percent'] = 0;

			}
			$header[] = $tmp;
		}

		return $header;
	}
}
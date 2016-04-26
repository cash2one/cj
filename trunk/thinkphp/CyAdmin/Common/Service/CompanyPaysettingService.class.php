<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/10/24
 * Time: 下午12:35
 */

namespace Common\Service;

class CompanyPaysettingService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Common/CompanyPaysetting");
	}

	/**
	 * 写入新增企业套件试用数据
	 * @param $probation_data
	 * + ep_id int
	 * + cpg_id int
	 * @return bool|string
	 */
	public function insert_probation_data($probation_data) {

		// 去掉重复 出现的套件记录 (可能存在)
		$this->_d->delete_by_conds(array('ep_id' => $probation_data['ep_id'], 'cpg_id' => $probation_data['cpg_id']));

		return $this->_d->insert_all($probation_data);
	}

	/**
	 * 写入老用户企业套件试用数据
	 * @param array $data 企业套件试用期记录
	 * + ep_id int
	 * + cpg_id int
	 * @return mixed
	 */
	public function insert_old_probation_data($data) {

		// 去掉重复 出现的套件记录 (可能存在)
		$this->_d->delete_by_conds(array('ep_id' => $data['ep_id'], 'cpg_id' => $data['cpg_id']));

		return $this->_d->insert($data);
	}

	/**
	 * 根据时间段查询新增付费公司
	 * @param $date array 日期
	 * @param $page_option array 分页参数
	 * @return mixed
	 */
	public function list_new_pay($date, $page_option) {

		return $this->_d->list_new_pay($date, $page_option);
	}

	/**
	 * 统计付费公司记录
	 * @param $date array 日期
	 * @param $ep_id int 公司id
	 * @return mixed
	 */
	public function count_pay_record($date, $ep_id) {

		return $this->_d->count_pay_record($date, $ep_id);
	}
}
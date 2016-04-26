<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 15/12/18
 * Time: 下午2:39
 */

namespace Common\Service;

class CommonLabelMemberService extends AbstractService {

	// 构造方法
	public function __construct() {

		$this->_d = D("Common/CommonLabelMember");
		parent::__construct();
	}

	/**
	 * 根据标签id和用户名返回人员
	 *
	 * @param $params array 接收参数
	 * @return array 结果集
	 */
	public function list_by_conds_member($params, $page_option) {

		return $this->_d->list_by_conds_member($params, $page_option);
	}

	/**
	 * 根据laid和m_uid删除数据
	 *
	 * @param $uid_list array 用户id
	 * @param $laid int 标签id
	 * @return array 返回值
	 */
	public function delete_by_laid_muid($uid_list, $laid) {

		return $this->_d->delete_by_laid_muid($uid_list, $laid);
	}

	/**
	 * 根据条件统计总数
	 *
	 * @param $params array 条件
	 * @return mixed
	 */
	public function count_by_conds_member($params) {

		return $this->_d->count_by_conds_member($params);
	}

}

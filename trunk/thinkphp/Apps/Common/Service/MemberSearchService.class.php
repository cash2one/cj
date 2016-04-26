<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/12/22
 * Time: 下午3:17
 * 通讯录详情搜索表
 */

namespace Common\Service;

use Common\Service\AbstractService;

class MemberSearchService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('Common/MemberSearch');
	}

	/**
	 * 根据cdid和search搜索数据
	 * @param $cd_id int 部门id
	 * @param $keyword string 关键字
	 * @param $limit int 每页显示数量
	 * @param $page_option array 分页条件
	 * @return $uid_list array 用户id
	 */
	public function list_by_keyword_status($keyword, $status, $page_option, $order_option = array('m_index' => 'ASC')) {

		if (empty($keyword)) {
			E('_ERR_D_PARAMS_ERROR');
			return false;
		}

		return $this->_d->list_by_keyword_status($keyword, $status, $page_option);
	}

	/**
	 * 根据关键字和部门id搜索
	 * @param string $keyword 关键字
	 * @param int|array $cdids 部门ID
	 * @param mixed $page_option 分页
	 * @param mixed $orderby 排序
	 */
	public function list_by_keyword_cdids($keyword, $cdids, $page_option, $order_option = array('m_index' => 'ASC')) {

		if (empty($keyword) || empty($cdids)) {
			E('_ERR_D_PARAMS_ERROR');
			return false;
		}

		return $this->_d->list_by_keyword_cdids($keyword, $cdids, $page_option, $order_option);
	}

	public function count_by_keyword_cdids($keyword, $cdids) {

		return $this->_d->count_by_keyword_cdids($keyword, $cdids);
	}

	/**
	 * 根据关键字搜索
	 * @param string $keyword 关键字
	 * @param mixed $page_option 分页
	 * @param mixed $orderby 排序
	 */
	public function list_by_keyword($keyword, $page_option, $order_option = array('m_index' => 'ASC')) {

		return $this->_d->list_by_keyword($keyword, $page_option, $order_option);
	}

	public function count_by_keyword($keyword) {

		return $this->_d->count_by_keyword($keyword);
	}

	/**
	 * 根据搜索条件和微信关注状态统计
	 * @param string $keyword 关键字
	 * @param int $status 关注状态
	 * @return mixed
	 */
	public function count_by_keyword_status($keyword, $status) {

		if (empty($keyword)) {
			E('_ERR_D_PARAMS_ERROR');
			return false;
		}

		return $this->_d->count_by_keyword_status($keyword, $status);
	}
}

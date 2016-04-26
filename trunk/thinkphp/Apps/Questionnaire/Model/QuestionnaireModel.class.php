<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/3/21
 * Time: 下午4:09
 */
namespace Questionnaire\Model;

class QuestionnaireModel extends AbstractModel {

	/** 是否可分享 1: 可以, 2: 不可以 */
	const SHARE = 1;
	const UN_SHARE = 2;
	/** 发布状态 1.预发布;2.草稿;3.发布; */
	const CRATE_STATUS = 1;
	const DRATE_STATUS = 2;
	const RELEASE_STATUS = 3;
	/** 是否允许重复提交 */
	const REPEAT = 1;
	const UN_REPEAT = 2;
	/** 所有人可见 */
	const IS_ALL = -1;
	/** 实名获取匿名 */
	const ANONYMOUS = 1;
	const REAL_NAME = 2;

	const PROCEED = 0;//问卷进行中
	const FINISH = 1;//问卷结束

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 微信端列表接口
	 * @param $cond
	 * @param $page_option
	 * @param $order_option
	 * @return array|bool
	 */
	public function list_by_condition($cond, $page_option, $order_option) {

		if ($cond['type'] == self::FINISH) {
			// 查询条件
			$where = array(
				'qu.deadline < ?',
			);
		} else {
			$where = array(
				'qu.deadline > ?',
			);
		}
		$where_params[] = time();
		$where[]        = "qu.`release`<?";
		$where_params[] = time();
		$where[]        = 'qu.release_status = ?';
		$where_params[] = self::RELEASE_STATUS;
		// 状态条件
		$where[]        = "qu.`{$this->prefield}status`<?";
		$where_params[] = $this->get_st_delete();
		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}
		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}
		$sql = "SELECT DISTINCT qu.qu_id, qu.* FROM __TABLE__ as qu LEFT JOIN oa_questionnaire_viewrange qv ON qu.qu_id = qv.qu_id WHERE ((qv.view_range_uid = {$cond['m_uid']} ";
		if ($cond['tagid']) {
			$sql .= "OR qv.view_range_label IN ({$cond['tagid']})";
		}
		if ($cond['cd_id']) {
			$sql .= " OR qv.view_range_cdid IN ({$cond['cd_id']})";
		}
		$sql .= ") OR qu.is_all = -1) AND ";
		\Think\Log::record($sql);

		return $this->_m->fetch_array($sql . implode(' AND ', $where) . "{$orderby}{$limit}", $where_params);
	}

	/**
	 * 微信端列表接口-总数
	 * @param $cond
	 * @return array
	 */
	public function total_by_condition($cond) {

		if ($cond['type'] == self::FINISH) {
			// 查询条件
			$where = array(
				'qu.deadline < ?',
			);
		} else {
			$where = array(
				'qu.deadline > ?',
			);
		}
		$where_params[] = time();
		$where[]        = "qu.`release`<?";
		$where_params[] = time();
		$where[]        = 'qu.release_status = ?';
		$where_params[] = self::RELEASE_STATUS;
		// 状态条件
		$where[]        = "qu.`{$this->prefield}status`<?";
		$where_params[] = $this->get_st_delete();
		$sql            = "SELECT count(DISTINCT qu.qu_id) FROM __TABLE__ as qu LEFT JOIN oa_questionnaire_viewrange qv ON qu.qu_id = qv.qu_id WHERE ((qv.view_range_uid = {$cond['m_uid']} ";
		if ($cond['tagid']) {
			$sql .= "OR qv.view_range_label IN ({$cond['tagid']})";
		}
		if ($cond['cd_id']) {
			$sql .= " OR qv.view_range_cdid IN ({$cond['cd_id']})";
		}
		$sql .= ") OR qu.is_all = -1) AND ";

		return $this->_m->result($sql . implode(' AND ', $where), $where_params);
	}

	/**
	 * 后端列表接口
	 * @param $cond
	 * @param $page_option
	 * @param $order_option
	 * @return array|bool
	 */
	public function list_by_conditionBackend($cond, $page_option, $order_option) {

		if ((isset($cond['issueend']) && $cond['issueend'])) {
			//发布结束时间
			$where[]          = 'release_time < ?';
			$where_params[]   = rstrtotime($cond['issueend']) + 86400;
		}
		if (isset($cond['issuestart']) && $cond['issuestart']) {
			//发布起始时间
			$where[]            = 'release_time > ?';
			$where_params[]     = rstrtotime($cond['issuestart']);
		}
		if ((isset($cond['end']) && $cond['end'])|| (isset($cond['end_j']) && $cond['end_j'])) {
			//结束时间
			$where[]        = 'deadline < ?';

			if($cond['end_j']){
				$where_params[] = rstrtotime($cond['end_j']);
			} else {
				$where_params[] = rstrtotime($cond['end']) + 86400;
			}

		}
		if (isset($cond['start']) && $cond['start']) {
			//起始时间
			$where[]        = 'deadline > ?';
			$where_params[] = rstrtotime($cond['start']);
		}
		if (isset($cond['title']) && $cond['title']) {
			//关键字
			$where[]        = "title like ?";
			$where_params[] = "%" . $cond['title'] . "%";
		}
		if (isset($cond['cid']) && $cond['cid'] != '') {
			//类型
			$where[]        = "qc_id = ?";
			$where_params[] = $cond['cid'];
		}
		if (isset($cond['status']) && $cond['status']) {
			//状态
			if ($cond['status'] == 1) {
				$where[] = 'release_time > ?';
				$where_params[]     = NOW_TIME;
			} else {
				$where[]        = "release_status = ?";
				$where_params[] = $cond['status'];
			}
		}
		// 状态条件
		$where[]        = "`{$this->prefield}status`<?";
		$where_params[] = $this->get_st_delete();
		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}
		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}
		$sql = "SELECT * FROM __TABLE__";

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . "{$orderby}{$limit}", $where_params);
	}

	public function total_by_conditionBackend($cond) {

		if ((isset($cond['issueend']) && $cond['issueend'])) {
			//发布结束时间
			$where[]          = 'release_time < ?';
			$where_params[]   = rstrtotime($cond['issueend']) + 86400;
		}
		if (isset($cond['issuestart']) && $cond['issuestart']) {
			//发布起始时间
			$where[]            = 'release_time > ?';
			$where_params[]     = rstrtotime($cond['issuestart']);
		}
		if ((isset($cond['end']) && $cond['end'])|| (isset($cond['end_j']) && $cond['end_j'])) {
			//结束时间
			$where[]        = 'deadline < ?';

			if($cond['end_j']){
				$where_params[] = rstrtotime($cond['end_j']);
			} else {
				$where_params[] = rstrtotime($cond['end']) + 86400;
			}

		}
		if (isset($cond['start']) && $cond['start']) {
			//起始时间
			$where[]        = 'deadline > ?';
			$where_params[] = rstrtotime($cond['start']);
		}
		if (isset($cond['title']) && $cond['title']) {
			//关键字
			$where[]        = "title like ?";
			$where_params[] = "%" . $cond['title'] . "%";
		}
		if (isset($cond['cid']) && $cond['cid'] != '') {
			//类型
			$where[]        = "qc_id = ?";
			$where_params[] = $cond['cid'];
		}
		if (isset($cond['status']) && $cond['status']) {
			//状态
			if ($cond['status'] == 1) {
				$where[] = 'release_time > ?';
				$where_params[]     = NOW_TIME;
			} else {
				$where[]        = "release_status = ?";
				$where_params[] = $cond['status'];
			}
		}
		// 状态条件
		$where[]        = "`{$this->prefield}status` < ?";
		$where_params[] = $this->get_st_delete();
		$sql            = "SELECT COUNT(*) FROM __TABLE__";

		return $this->_m->result($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

}
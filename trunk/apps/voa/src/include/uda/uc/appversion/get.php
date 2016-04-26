<?php
/**
 * get.php
 * 获取版本信息操作
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_uc_appversion_get extends voa_uda_uc_appversion_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 获取指定客户端类型的最后更新信息
	 * @param string $clienttype 客户端类型
	 * @param array $last <strong style="color:red">(引用结果)</strong>最新的版本信息
	 * @return boolean
	 */
	public function last_by_clienttype($clienttype, &$last = array()) {
		$last = $this->serv_uc_appversion->last_by_clienttype($clienttype);
		$this->format($last, $last);

		return true;
	}

	/**
	 * 列出指定客户端类型的更新历史列表
	 * @param string $clienttype 客户端类型
	 * @param number $page 当前页码
	 * @param number $limit 每页列出条数
	 * @param string $order 排序方式
	 * @param array $data <strong style="color:red">(引用结果)</strong>列表
	 * @return boolean
	 */
	public function list_by_clienttype($clienttype = '', $page = 1, $limit = 10, $order = '', &$data = array()) {
		if ($page < 1) {
			$page = 1;
		}
		$total = $this->serv_uc_appversion->count_all_by_clienttype($clienttype);
		$list = array();
		$pages = 1;
		if ($total > 0) {
			$pages = ceil($total/$limit);
			if ($page <= $pages) {
				if (rstrtoupper($order) == 'ASC') {
					$order = 'ASC';
				} else {
					$order = 'DESC';
				}
				$start = ($page - 1) * $limit;
				$list = $this->serv_uc_appversion->fetch_all_by_clienttype($clienttype, $start, $limit, $order);
				$this->format_list($list, $list);
			}
		}
		$data = array(
			'page' => $page,
			'limit' => $limit,
			'pages' => $pages,
			'total' => $total,
			'list' => $list
		);

		return true;
	}

}

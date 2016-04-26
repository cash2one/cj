<?php
/**
 * voa_d_oa_goods_tablecol
 * 表格列属性信息
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_goods_tablecol extends voa_d_abstruct {
	// 单选/复选子类型
	const FTYPE_RDCHK_TEXT = 1; // 文本
	const FTYPE_RDCHK_ATTACH = 2; // 附件
	// 文本子类型
	const FTYPE_TEXT_MULTI = 1; // 多行文本
	const FTYPE_TEXT_FULL = 2; // 富文本
	// 附件子类型
	const FTYPE_ATTACH_TEXT = 1;
	const FTYPE_ATTACH_PIC = 2;
	const FTYPE_ATTACH_VOICE = 4;
	const FTYPE_ATTACH_VIDIO = 8;
	// 使用状态
	const ISUSE_NORMAL = 1; // 启用
	const ISUSE_HIDDEN = 2; // 隐藏
	const ISUSE_DISABLED = 3; // 禁用
	// 字段类型
	const COLTYPE_SYS = 1; // 系统字段
	const COLTYPE_DIY = 2; // 自定义字段

	/** 初始化 */
	public function __construct() {

		/** 表名 */
		$this->_table = 'orm_oa.goods_tablecol';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'tc_id';

		parent::__construct();
	}

	/**
	 * 根据 tid 获取表格列信息
	 * @param mixed $tid 表格id
	 * @param mixed $page_option 分页参数
	 *  + int => limit $page_option
	 *  + array => limit $page_option[0], $page_option[1]
	 * @param array $orderby 排序信息
	 */
	public function list_by_tid($tid, $page_option = null, $orderby = array()) {

		try {
			// 查询条件
			$this->_condi('tid IN (?)', (array)$tid);

			// 只查询未删除的
			$this->_condi('status<?', self::STATUS_DELETE);
			!empty($page_option) && $this->_limit($page_option);

			// 排序
			foreach ($orderby as $_f => $_dir) {
				$this->_order_by($_f, $_dir);
			}

			return $this->_find_all();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}


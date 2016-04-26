<?php
/**
 * 内部api方法/超级报表/编辑报表模板
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_uda_frontend_superreport_edittemplate extends voa_uda_frontend_superreport_abstract {

	/** 外部请求参数 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();

	/** 类型 diy 类 */
	private $__diy = null;

	/**
	 * 初始化
	 * 引入  DIY 类
	 */
	public function __construct() {
		parent::__construct();

		if ($this->__diy === null) {
			$this->__diy = new voa_uda_frontend_diy_column_add();
			$this->_init_diy_data($this->__diy);
		}
	}

	/**
	 * 添加报表模板
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)
	 * @return boolean
	 */
	public function edit_template(array $request, array &$result) {

		if (empty($request)) {
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::NO_TEMPLATE_ERROR);
		}

		$s_col = new voa_s_oa_diy_tablecol();

		try{
			$s_col->begin();

			$tid = $this->table['tid'];

			//删除原有模板
			$s_col->delete_by_conds(array('tid' => $tid));

			//增加新模板
			$k = 1;
			foreach ($request as $item) {
				$template = array();
				$template = array(
					'field' => 'field'.$k,
					'fieldname' => $item['fieldname'],
					'unit' => $item['unit'],
					'required' => $item['required'],
					'ct_type' => $item['ct_type'],
					'orderid' => $k
				);
				// 写入模板
				$result = array();
				$this->__diy->execute($template, $result);
				$k++;
			}

			$s_col->commit();
		} catch (Exception $e) {
			$s_col->rollback();
			logger::error($e);

			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

}

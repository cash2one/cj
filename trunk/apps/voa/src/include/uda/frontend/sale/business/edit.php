<?php
/**
 * voa_uda_frontend_sale_business_edit
 * 应用uda
 * Create By tim_zhang
 * $Author$
 * $Id$
 */
class voa_uda_frontend_sale_business_edit extends voa_uda_frontend_base {

	//商机历史表
	// protected $bslist;

	/**
	 * 商机 service
	 * @var
	 */
	protected $_business;

	public function __construct() {
		parent::__construct();
		// $this->bslist = &service::factory('voa_d_oa_sale_bslist');
		$this->_business = &service::factory('voa_s_oa_sale_business');
	}

	/*
	 *商机表插入或者修改
	 *@param array request
	 *@param array result
	 */
	public function doit(array $request, &$result) {

		$val_business = array (
			'scid' => array('_val_scid', 'int'),
			'type' => array('_val_type', 'int'),
			'title' => array('_val_title', 'string'),
			'amount' => '_val_amount',
			'content' => array('_val_content', 'string')
		);

		!empty($request['m_uid']) && $m_uid = rintval($request['m_uid']);
		if ($m_uid < 1) {
			$this->errmsg('201', '用户id不能为空');
			return false;
		}
		$this->_params = $request;
		$bid = 0;
		$odata = array();

		//数据过滤
		if(!empty($request['bid'])) {
			$bid = $request['bid'];
			$bid = rintval($bid);
			//获取原始数据
			if (!empty($odata)) {
				$odata = $this->get_business_by_id($bid);
			}
		}

		$data = array();
		// 检查客户信息
		if (!$this->_submit2table($val_business, $data, $odata)) {
			return false;
		}


		/*$data = array();
		$bid = '';
		//数据过滤
		if(!empty($request['bid'])) {
			$bid = $request['bid'];
		}
		if(!empty($request['scid'])) {
			$data['scid'] = $request['scid'];
		}
		if(!empty($request['m_uid'])) {
			$data['m_uid'] = $request['m_uid'];
		}
		if(!empty($request['type'])) {
			$data['type'] = $request['type'];
		}
		if(!empty($request['title'])) {
			$data['title'] = $request['title'];
		}
		if(!empty($request['amount'])) {
			$data['amount'] = $request['amount'];
		}
		if(!empty($request['content'])) {
			$data['content'] = $request['content'];
		}
		
		//插入还是更新
		if(!empty($bid)) {
			$this->business->update_by_conds(array('bid' => $bid),$data);
			$result['bid'] = $bid;
		} else {
			$result = $this->business->insert($data);
		}*/

		//插入还是更新
		try {
			if(!empty($bid)) {
				if (!empty($data)) {
					$this->_business->update_by_conds(array('bid' => $bid),$data);
				}
				$result['bid'] = $bid;
			} else {
				$result = $this->_business->insert($data);
			}
		} catch(Exception $e) {
			logger::error($e);
			$this->errmsg('110', '保存失败');
			return false;
		}
		$this->errmsg('0', '保存成功');
		return true;
	}

	/**
	 * 获取客户信息根据主键id
	 * @param $scid
	 * @return null
	 */
	public function get_business_by_id($bid) {
		$bid = rintval($bid);
		if (empty($bid)) {
			return null;
		}
		return $this->_business->get($bid);
	}

	/**
	 * 检查客户
	 * @param $scid
	 * @param $data
	 * @param $odata
	 * @return bool
	 */
	protected function _val_scid(&$scid, &$data, $odata) {

		$scid = trim($scid);

		if (empty($odata) ||
			$odata['scid'] != $scid) {
			$serv_coustmer = &service::factory('voa_s_oa_sale_coustmer');
			if (!$serv_coustmer->get($scid)) {
				$this->errmsg('202', '客户不存在');
				return false;
			}

			$data['scid'] = $scid;
		}
		return true;
	}

	/**
	 * 检查机会进展
	 * @param $type
	 * @param $data
	 * @param $odata
	 * @return bool
	 */
	protected function _val_type(&$type, &$data, $odata) {

		$type = trim($type);

		if (empty($odata) ||
			$odata['type'] != $type) {

			if (empty(voa_d_oa_sale_business::$type[$type])) {
				$this->errmsg('203', '机会进展不存在');
				return false;
			}

			$data['type'] = $type;
		}
		return true;
	}

	/**
	 * 检查机会名称
	 * @param $title
	 * @param $data
	 * @param $odata
	 * @return bool
	 */
	protected function _val_title(&$title, &$data, $odata) {

		$title = trim($title);

		if (empty($odata) ||
			$odata['title'] != $title) {

			if (!validator::is_string_count_in_range($title, 1, 100)) {
				$this->errmsg('204', '机会名称长度介于 1到100 个字符之间');
				return false;
			}

			$data['title'] = $title;
		}
		return true;
	}

	/**
	 * 检查金额
	 * @param $amount
	 * @param $data
	 * @param $odata
	 * @return bool
	 */
	protected function _val_amount(&$amount, &$data, $odata) {

		$amount = trim($amount);

		if (empty($odata) ||
			$odata['amount'] != $amount) {
			$amount = floatval($amount);
			if ($amount < 1) {
				$this->errmsg('205', '金额填写不正确');
				return false;
			}

			$data['amount'] = $amount;
		}
		return true;
	}

	/**
	 * 检查联系人
	 * @param $content
	 * @param $data
	 * @param $odata
	 * @return bool
	 */
	protected function _val_content(&$content, &$data, $odata) {

		$content = trim($content);

		if (empty($odata) ||
			$odata['content'] != $content) {
			$data['content'] = $content;
		}
		return true;
	}
}

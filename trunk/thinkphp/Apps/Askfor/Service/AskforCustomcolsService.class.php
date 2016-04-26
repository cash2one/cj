<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/11/12
 * Time: 上午11:41
 */

namespace Askfor\Service;

class AskforCustomcolsService extends AbstractService {

	//构造方法
	public function __construct() {
		$this->_d = D("Askfor/AskforCustomcols");
		parent::__construct();
	}

	/**
	 * 根据aft_id查询
	 * @return mixed
	 */
	public function list_by_aftid($aft_id) {

		return $this->_d->list_by_aftid($aft_id);
	}

}

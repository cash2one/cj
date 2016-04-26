<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/11/26
 * Time: 下午1:33
 */

namespace Askfor\Model;

class AskforProcRecordModel extends AbstractModel {

	const ASKING = 1; // 审批中
	const ASKPASS = 2; // 审核通过
	const TURNASK = 3; // 转审批
	const ASKFAIL = 4; // 审批不通过
	const COPYASK = 5; // 抄送
	const PRESSASK = 6; // 催办
	const CENCEL = 7; // 已撤销

	const PRESS_TIME = 300;

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'rafp_';
	}
}

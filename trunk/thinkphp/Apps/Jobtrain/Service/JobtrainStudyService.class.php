<?php
namespace Jobtrain\Service;
use Org\Util\String;

class JobtrainStudyService extends AbstractService {

	// 构造方法
	public function __construct() {
		parent::__construct();
		// 实例化相关模型
		$this->_d = D("Jobtrain/JobtrainStudy");
	}
	
}
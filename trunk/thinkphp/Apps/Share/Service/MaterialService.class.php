<?php
namespace Share\Service;
use Org\Util\String;

class MaterialService extends AbstractService {

	// 构造方法
	public function __construct() {
		parent::__construct();
		// 实例化相关模型
		$this->_d = D("Share/Material");
	}


    public function getMaterialList($start, $limit) {
        return $this->_d->getMaterialList($start, $limit);
    }

}
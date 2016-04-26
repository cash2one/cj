<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/3/4
 * Time: 下午5:15
 */

namespace Project\Model;

class ProjectModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();

		$this->prefield = 'p_';
	}

	// 获取删除状态值
	public function get_st_delete() {

		return 5;
	}

}
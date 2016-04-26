<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/1/19
 * Time: 下午11:05
 */

namespace News\Service;

class NewsRightService extends AbstractService {

	public function __construct() {

		parent::__construct();
		$this->_d = D("News/NewsRight");
	}

	public function list_ne_by_ne_id($ne_id) {

		return $this->_d->list_ne_by_ne_id($ne_id);
	}

}

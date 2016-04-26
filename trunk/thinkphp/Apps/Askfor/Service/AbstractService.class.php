<?php
/**
 * AbstractService.class.php
 * $author$
 */
namespace Askfor\Service;

abstract class AbstractService extends \Common\Service\AbstractService {

	const ACTIVE = 1; // 达到状态
	const UNACTIVE = 0; // 没有达到状态

	// 构造方法
	public function __construct() {

		parent::__construct();
	}
}

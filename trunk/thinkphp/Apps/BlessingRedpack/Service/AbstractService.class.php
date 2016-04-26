<?php
/**
 * AbstractService.class.php
 * $author$
 */

namespace BlessingRedpack\Service;

abstract class AbstractService extends \Common\Service\AbstractService {

    // 红包队列缓存key
    const REDPACK_QUEUE_KEY = "cy_redpack_";

    // 已抢到红包的用户的缓存key
    const REDPACK_USER_KEY = "cy_redpack_user_";

    // 存放红包总数缓存key(用于计算抢红包排名)
    const REDPACK_SUM_KEY = "cy_redpack_sum_";

	// 构造方法
	public function __construct() {

		parent::__construct();
	}
}

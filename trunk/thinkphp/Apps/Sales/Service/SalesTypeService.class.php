<?php
/**
 * SalesTypeService.class.php
 * $author$
 */

namespace Sales\Service;

class SalesTypeService extends AbstractService {

    // 构造方法
    public function __construct() {

        parent::__construct();
        $this->_d = D("Sales/SalesType");
    }

    /**
     * 类型配置缓存
     * @email: husendong@vchangyi.com
     */
    public function list_all($page_option = null, $order_option = array ()) {

        $list = parent::list_all($page_option, $order_option);

        return array_combine_by_key($list, 'stp_type');
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: Hu Sendong
 * Email: husendong@vchangyi.com
 */

namespace Sales\Controller\Apicp;

use Sales\Common\Cache;

class SalesTypeController extends AbstractController {

    /**
     * 类型配置列表
     * $author: husendong@vchangyi.com
     */
    public function List_type_get()
    {
        // 用户提交的参数
        $params = I('request.');
        $cache = &Cache::instance();
        $stp_type =(int) $params["stp_type"];
        $list_type = $this->_setting = $cache->get('Sales.salestype');

        // 参数判断
        if($stp_type) {
            $list = array();
            foreach ($list_type as $m => $v) {
                if ((int)$v['stp_type'] == $stp_type) {
                    $list[] = $v;
                }
            }
            $this->_result = $list;
        }else{
            $this->_result = $list_type;
        }

        return true;
	}
}

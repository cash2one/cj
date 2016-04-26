<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace Sales\Controller\Frontend;

class TestController extends AbstractController
{
    public function Index()
    {
        //类型配置列表
        $this->assign('acurl', U('/Sales/Api/SalesType/List_type'));
        //轨迹列表查询
        $this->assign('bcurl', U('/Sales/Api/SalesTrajectory/List_trajectory'));
        //编辑轨迹
        $this->assign('ccurl', U('/Sales/Api/SalesTrajectory/Edit_trajectory'));
        //（批量）删除客户
        $this->assign('dcurl', U('/Sales/Api/SalesCustomer/Delete_customer'));
        $this->assign('quiturl', U('/Sales/Api/Customer/Edit_customer'));

        $this->_output("Frontend/Index/Test");
    }
}
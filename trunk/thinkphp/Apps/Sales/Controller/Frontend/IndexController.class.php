<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace Sales\Controller\Frontend;

class IndexController extends AbstractController {

	// 入口方法Customer.tpl
	public function Customer() {
		$this->assign('add_customer_curl', U('/Sales/Api/SalesCustomer/Add_customer'));
		$this->assign('edit_customer_curl', U('/Sales/Api/SalesCustomer/Edit_customer'));
		$this->assign('add_partner_curl', U('/Sales/Api/SalesPartner/Add_partner'));
		$this->assign('business_detail_curl', U('/Sales/Api/SalesBusiness/Business_detail'));
		$this->assign('list_business_modify_record_url', U('/Sales/Api/SalesRecord/List_business_modify_record'));

		$this->_output("Frontend/Index/Customer");
	}

	// 入口方法
	public function Index() {
		// 先取群组列表
		$serv_gb = D('Sales/SalesCustomer', 'Service');
		//$list = $serv_gb->list_all($this->_plugin->setting['perpage'], array('sc_id' => 'ASC'));
		// 统计总数
		$count = $serv_gb->count() + 100;
		// 分页
		$page = new \Think\Page($count, $this->_plugin->setting['perpage']);
		$multi = $page->show();

		$this->assign('multi', $multi);
		$this->assign('acurl', U('/Sales/Api/SalesType/List_type'));
		$this->assign('quiturl', U('/Sales/Api/Customer/Edit_customer'));

		$this->_output("Frontend/Index/Index");
	}


    public function Test(){
        $this->assign('acurl', U('/Sales/Api/SalesType/List_type'));
        $this->assign('bcurl', U('/Sales/Api/SalesTrajectory/List_trajectory'));
        $this->assign('ccurl', U('/Sales/Api/SalesTrajectory/Edit_trajectory'));
        $this->assign('dcurl', U('/Sales/Api/SalesCustomer/Delete_customer'));
        $this->assign('quiturl', U('/Sales/Api/Customer/Edit_customer'));
        $this->_output("Frontend/Index/Test");
    }
	public function About(){
//		// 先取列表
//		$serv_sb = D('Sales/SalesBusiness', 'Service');
//		// 取页码
//		$page = I('get.' . cfg('VAR_PAGE'));
//		// 获取起始行, 每页行数, 当前页
//		list($start, $limit, $page) = page_limit($page, $this->_plugin->setting['perpage']);
//		// 读取列表
//		$list = $serv_sb->list_all(array($start, $limit), array('id' => 'ASC'));
//
//		// 格式化
//		$serv_fmt = D('Guestbook/Format', 'Service');
//		foreach ($list as &$_v) {
//			$serv_fmt->guestbook($_v);
//		}

		// 输出模板变量
		// 添加商机
		$this->assign('Business', U('/Sales/Api/SalesBusiness/Create_business'));
		// 编辑商机
		$this->assign('Edit_Business', U('/Sales/Api/SalesBusiness/Edit_business'));
		// 商机轨迹
		$this->assign('Add_track', U('/Sales/Api/SalesTrajectory/Add_track'));
		// 商机列表查询
		$this->assign('List_Business', U('/Sales/Api/SalesBusiness/List_business'));
		$this->_output("Frontend/Index/About");
	}

	public function  songtest() {
		$this->assign('Change', U('/Sales/Api/SalesBusiness/Change_manager'));
		$this->assign('data', U('/Sales/Api/SalesBusiness/Data_Management'));
		$this->assign('customer', U('/Sales/Api/SalesCustomer/List_customer'));
		$this->assign('info', U('/Sales/Api/SalesCustomer/Customer_detail'));
		$this->_output("Frontend/Index/songTest");
	}
}

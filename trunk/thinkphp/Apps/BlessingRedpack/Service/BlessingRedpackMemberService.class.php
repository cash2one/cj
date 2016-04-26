<?php
/**
 * BlessingRedpackMemberService.class.php
 * 企业祝福红包 红包用户表
 * @author: anything
 * @createTime: 2015/11/24 20:49
 * @version: $Id$ 
 * @copyright: 畅移信息
 */
namespace BlessingRedpack\Service;


class BlessingRedpackMemberService extends AbstractService{

    // 构造方法
    public function __construct() {

        parent::__construct();

        //实例化模型
        $this->_d = D("BlessingRedpack/BlessingRedpackMember");

    }

}

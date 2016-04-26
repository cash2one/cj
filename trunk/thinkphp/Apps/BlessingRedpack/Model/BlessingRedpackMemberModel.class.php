<?php
/**
 * BlessingRedpackMemberModel.class.php
 * 企业祝福红包 红包用户表
 * @author: anything
 * @createTime: 2015/11/24 20:46
 * @version: $Id$ 
 * @copyright: 畅移信息
 */
namespace BlessingRedpack\Model;

class BlessingRedpackMemberModel extends AbstractModel{

    // 是否是老用户
    const YES = 0; // 是
    // 否
    const NO = 1;

    // 构造方法
    public function __construct() {

        parent::__construct();
    }
}

<?php
/**
 * voa_errcode_oa_travel
 * OA travel 相关 呼叫错误码
 * 使用7位编码
 *
 * $Author$
 * $Id$
 */

class voa_errcode_oa_travel {

	const CUSTOMER_REMARK_IS_NOT_EXIST = '1004000:客户备注信息不存在';
	const CUSTOMER_IS_NOT_EXIST = '1004001:客户信息不存在';
	const REMARK_IS_EMPTY = '1004002:备注信息不能为空';
	const CRK_TYPE_INVALID = '1004003:备注类型错误';

	const CUSTOMER_GOODS_IS_NOT_EXIST = '1004004:客户关注的产品不存在';
	const GOODS_ID_IS_EMPTY = '1004005:产品ID不能为空';
	const GOODS_OR_CUSTOMER_IS_EMPTY = '1004006:产品或客户ID不能为空';
	const CGID_IS_EMPTY = '1004007:产品关注ID不能为空';
	const CUSTOMER_TC_ID_IS_EMPTY = '1004008:客户列ID不能为空';
	const CUSTOMER_TID_IS_EMPTY = '1004009:客户表ID不能为空';
	const CUSTOMER_TCO_ID_IS_EMPTY = '1004010:客户属性选项ID不能为空';
	const GAID_IS_EMPTY = '1004011:附件ID不能为空';
	const CLASSID_IS_EMPTY = '1004012:分类ID不能为空';

	const CRK_ID_IS_EMPTY = '1004013:备注ID不能为空';
	const PLEASE_LOGIN = '1004014:请先登录';
	const DIYINDEX_SUBJECT_IS_EMPTY = '1004015:标题不能为空';
	const DIYINDEX_MESSAGE_IS_EMPTY = '1004016:主页内容不能为空';
	
	const EXPRESSID_IS_EMPTY = '1004017:快递ID不能为空';
}

<?php
/**
 * voa_errcode_oa_customer
 * OA customer 相关 呼叫错误码
 * 使用7位编码
 *
 * $Author$
 * $Id$
 */

class voa_errcode_oa_customer {

	const CUSTOMER_CLASSNAME_IS_EMPTY = '1002000:分类名称不能为空';
	const CUSTOMER_CLASSID_ERR = '1002001:分类ID信息错误';

	const CUSTOMER_TABLENAME_IS_EMPTY = '1002002:表格名称不能为空';
	const CUSTOMER_TABLE_IS_NOT_EXIST = '1002003:表格不存在';
	const CUSTOMER_TABLECOL_REGEXP_ERR = '1002004:正则表达式错误';
	const CUSTOMER_CT_TYPE_IS_NOT_EXIST = '1002005:该字段类型不存在';
	const CUSTOMER_FIELDNAME_IS_EMPTY = '1002006:字段名称显示不能为空';
	const CUSTOMER_TABLECOLOPT_IS_EMPTY = '1002007:字段选项不能为空';
	const CUSTOMER_FIELD_REQUIRED = '1002008:[%s]不能为空';
	const CUSTOMER_FIELD_FORMAT_ERR = '1002009:[%s]格式错误';
	const CUSTOMER_FIELD_LENGTH_ERR = '1002010:[%s]长度必须在%d-%d之间';
	const CUSTOMER_FIELD_VALUE_ERR = '1002011:[%s]必须在%d-%d之间';
	const CUSTOMER_FIELD_CHECKED_ERR = '1002012:[%s]只能选择%d-%d个';
	const CUSTOMER_DATA_IS_NOT_EXIST = '1002013:该客户记录不存在';
	const CUSTOMER_FIELD_VALUE_INVALID = '1002014:[%s]不合法';
	const CUSTOMER_TABLECOL_IS_EMPTY = '1002015:表格字段信息不能为空';
	const CUSTOMER_TUNIQUE_IS_EMPTY = '1002016:表格唯一标识不能为空';
	const CUSTOMER_TUNIQUE_DUPLICATE = '1002017:表格唯一标识不能重复';

	const CUSTOMER_PTNAME_IS_EMPTY = '1002018:插件名称或表格名称错误';

	const CUSTOMER_CP_IDENTIFIER_IS_NOT_EXIST = '1002019:插件不存在, 请返回确认';
	const CUSTOMER_CP_IDENTIFIER_IS_EMPTY = '1002020:插件唯一标识错误, 请返回确认';

	const CUSTOMER_FIELD_IS_SYSTEM = '1002021:系统字段不能删除';
	const CUSTOMER_FIELD_IS_NOT_EXIST = '1002022:该字段不存在';
	const CUSTOMER_ID_IS_EMPTY = '1002023:客户ID不能为空';
	const CLASSID_IS_EMPTY = '1002024:分类ID不能为空';

	const CLASSNAME_DUPLICATE = '1002025:分类名称不能重复';
	const CUSTOMER_CLASS_HAS_CHILD = '1003030:该分类下还有子分类, 不能删除';

	const CUSTOMER_IS_EXIST = '1003031:客户信息已经存在';
}

<?php
/**
 * voa_errcode_oa_diy
 * OA goods 相关 呼叫错误码
 * 使用7位编码
 *
 * $Author$
 * $Id$
 */

class voa_errcode_oa_diy {

	const CLASSNAME_IS_EMPTY = '1007000:分类名称不能为空';
	const CLASSID_ERR = '1007001:分类ID信息错误';

	const TABLENAME_IS_EMPTY = '1007002:表格名称不能为空';
	const TABLE_IS_NOT_EXIST = '1007003:表格不存在';
	const TABLECOL_REGEXP_ERR = '1007004:正则表达式错误';
	const CT_TYPE_IS_NOT_EXIST = '1007005:该字段类型不存在';
	const FIELDNAME_IS_EMPTY = '1007006:字段名称显示不能为空';
	const TABLECOLOPT_IS_EMPTY = '1007007:字段选项不能为空';
	const FIELD_REQUIRED = '1007008:[%s]不能为空';
	const FIELD_FORMAT_ERR = '1007009:[%s]格式错误';
	const FIELD_LENGTH_ERR = '1007010:[%s]长度必须在%d-%d之间';
	const FIELD_VALUE_ERR = '1007011:[%s]值必须在%d-%d之间';
	const FIELD_CHECKED_ERR = '1007012:[%s]只能选择%d-%d个';
	const DATA_IS_NOT_EXIST = '1007013:该商品记录不存在';
	const FIELD_VALUE_INVALID = '1007014:[%s]值不合法';
	const TABLECOL_IS_EMPTY = '1007015:表格字段信息不能为空';
	const TUNIQUE_IS_EMPTY = '1007016:表格唯一标识不能为空';
	const TUNIQUE_DUPLICATE = '1007017:表格唯一标识不能重复';

	const PTNAME_IS_EMPTY = '1007018:插件名称或表格名称错误';

	const CP_IDENTIFIER_IS_NOT_EXIST = '1007019:插件不存在, 请返回确认';
	const CP_IDENTIFIER_IS_EMPTY = '1007020:插件唯一标识错误, 请返回确认';

	const FIELD_IS_SYSTEM = '1007021:系统字段不能删除';
	const FIELD_IS_NOT_EXIST = '1007022:该字段不存在';

	const NO_EDIT_PRIVILEGE = '1007023:没有编辑权限';
	const HAS_FETCHED = '1007024:不能重复添加同一款产品';

	const TCO_ID_IS_EMPTY = '1007025:产品的选项ID不能为空';
	const TC_ID_IS_EMPTY = '1007026:产品属性ID不能为空';
	const DATAID_IS_EMPTY = '1007027:产品ID不能为空';
	const TABLE_ID_IS_EMPTY = '1007028:产品表格ID不能为空';

	const CLASSNAME_DUPLICATE = '1007029:分类名称不能重复';
	const CLASS_HAS_CHILD = '1007030:该分类下还有子分类, 不能删除';
	const NO_PRIVILEGES = '1007031:没有权限';

	const TNAME_IS_EMPTY = '1007032:表格名称错误';

}

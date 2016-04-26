<?php
/**
 * voa_errcode_oa_goods
 * OA goods 相关 呼叫错误码
 * 使用7位编码
 *
 * $Author$
 * $Id$
 */

class voa_errcode_oa_goods {

	const GOODS_CLASSNAME_IS_EMPTY = '1003000:分类名称不能为空';
	const GOODS_CLASSID_ERR = '1003001:分类ID信息错误';

	const GOODS_TABLENAME_IS_EMPTY = '1003002:表格名称不能为空';
	const GOODS_TABLE_IS_NOT_EXIST = '1003003:表格不存在';
	const GOODS_TABLECOL_REGEXP_ERR = '1003004:正则表达式错误';
	const GOODS_CT_TYPE_IS_NOT_EXIST = '1003005:该字段类型不存在';
	const GOODS_FIELDNAME_IS_EMPTY = '1003006:字段名称显示不能为空';
	const GOODS_TABLECOLOPT_IS_EMPTY = '1003007:字段选项不能为空';
	const GOODS_FIELD_REQUIRED = '1003008:[%s]不能为空';
	const GOODS_FIELD_FORMAT_ERR = '1003009:[%s]格式错误';
	const GOODS_FIELD_LENGTH_ERR = '1003010:[%s]长度必须在%d-%d之间';
	const GOODS_FIELD_VALUE_ERR = '1003011:[%s]必须在%d-%d之间';
	const GOODS_FIELD_CHECKED_ERR = '1003012:[%s]只能选择%d-%d个';
	const GOODS_DATA_IS_NOT_EXIST = '1003013:该商品记录不存在';
	const GOODS_FIELD_VALUE_INVALID = '1003014:[%s]不合法';
	const GOODS_TABLECOL_IS_EMPTY = '1003015:表格字段信息不能为空';
	const GOODS_TUNIQUE_IS_EMPTY = '1003016:表格唯一标识不能为空';
	const GOODS_TUNIQUE_DUPLICATE = '1003017:表格唯一标识不能重复';

	const GOODS_PTNAME_IS_EMPTY = '1003018:插件名称或表格名称错误';

	const GOODS_CP_IDENTIFIER_IS_NOT_EXIST = '1003019:插件不存在, 请返回确认';
	const GOODS_CP_IDENTIFIER_IS_EMPTY = '1003020:插件唯一标识错误, 请返回确认';

	const GOODS_FIELD_IS_SYSTEM = '1003021:系统字段不能删除';
	const GOODS_FIELD_IS_NOT_EXIST = '1003022:该字段不存在';

	const NO_EDIT_PRIVILEGE = '1003023:没有编辑权限';
	const GOODS_HAS_FETCHED = '1003024:不能重复添加同一款产品';

	const GOODS_TCO_ID_IS_EMPTY = '1003025:产品的选项ID不能为空';
	const GOODS_TC_ID_IS_EMPTY = '1003026:产品属性ID不能为空';
	const GOODS_DATAID_IS_EMPTY = '1003027:产品ID不能为空';
	const TABLE_ID_IS_EMPTY = '1003028:产品表格ID不能为空';

	const CLASSNAME_DUPLICATE = '1003029:分类名称不能重复';
	const GOODS_CLASS_HAS_CHILD = '1003030:该分类下还有子分类, 不能删除';
	const NO_PRIVILEGES = '1003031:没有权限';
	
	const GOODS_EXPRESSTYPE_IS_EMPTY = '1003031: 快递类型不能为空';
	const EXPRESSTYPE_DUPLICATE = '1003032:快递类型不能重复';

}

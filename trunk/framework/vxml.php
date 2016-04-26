<?php
/**
 * XML 和数组相互转换
 * $Author$
 * $Id$
 */

class vxml {

	/**
	 * xml 转成数组
	 * @param string $xml xml
	 */
	public static function xml2array(&$xml, $isnormal = FALSE) {
		$xml_parser = new vxml_parse($isnormal);
		$data = $xml_parser->parse($xml);
		$xml_parser->destruct();
		return $data;
	}

	/**
	 * 数组转成 xml
	 * @param array $arr 数组
	 * @param boolean $htmlon 启用html
	 * @param boolean $isnormal
	 * @param int $level 深度
	 */
	public static function array2xml($arr, $htmlon = TRUE, $isnormal = FALSE, $level = 1) {
		$s = $level == 1 ? "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n<root>\r\n" : '';
		$space = str_repeat("\t", $level);
		foreach ($arr as $k => $v) {
			if (!is_array($v)) {
				$s .= $space."<item id=\"$k\">".($htmlon ? '<![CDATA[' : '').$v.($htmlon ? ']]>' : '')."</item>\r\n";
			} else {
				$s .= $space."<item id=\"$k\">\r\n".self::array2xml($v, $htmlon, $isnormal, $level + 1).$space."</item>\r\n";
			}
		}

		$s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
		return $level == 1 ? $s."</root>" : $s;
	}
}

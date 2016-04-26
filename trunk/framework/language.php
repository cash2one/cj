<?php
/**
 * language
 *
 * $Author$
 * $Id$
 */

class language {

	/**
	 *  当前语言包
	 *   + zh_tw
	 *   + zh_cn
	 *
	 *  @var string
	 */
	private static $_lang = null;

	/**
	 *  语言数据
	 *
	 *  @var array
	 */
	private static $_lang_data = array();

	/**
	 * 加载的语言文件
	 *
	 * @var array
	 */
	private static $_lang_file = array();

	/**
	 * 公共语言包
	 * 此语言包文件会在所有地方都加载
	 *
	 * @var array
	 */
	private static $_public_lang = array();

	/**
	 * set_lang
	 * 设置语言
	 *
	 * @param  string $lang
	 * @return void
	 */
	public static function set_lang($lang) {
		self::$_lang = $lang;
	}

	/**
	 * get_lang
	 * 获取当前语言
	 *
	 * @return void
	 */
	public static function get_lang() {
		return self::$_lang;
	}

	/**
	 * load_lang
	 * 加载语言文件
	 *
	 * @param  string $lang_files
	 * @return void
	 */
	public static function load_lang($lang_files = null) {
		$files = array();

		/** 公共语言包 */
		$public_lang_files = self::$_public_lang;
		if ($public_lang_files) {
			$files = array_merge($files, $public_lang_files);
		}

		/** 特殊语言包 */
		if ($lang_files) {
			if (!is_array($lang_files)) {
				$lang_files = array($lang_files);
			}

			$files = array_merge($files, $lang_files);
		}

		$startup = &startup::factory();
		$childapp = $startup->get_option('childapp');
		foreach ($files as $file) {
			if (in_array($file, self::$_lang_file)) {
				continue;
			}

			self::$_lang_file[] = $file;

			// 读取子项目语言包
			if (!empty($childapp)) {
				$ar = explode('.', $file);
				$lang_file = APP_PATH.'/src/'.$ar[0].'/languages/'.self::get_lang().'/'.join('/', array_slice($ar, 1)).'.php';
			} else {
				$lang_file = APP_PATH.'/src/languages/'.self::get_lang().'/'.$file.'.php';
			}

			// 引入语言
			if (is_file($lang_file)) {
				$language = array();
				include($lang_file);
				if ($language) {
					self::$_lang_data = array_merge(self::$_lang_data, $language);
					unset($language);
				}
			}
		}
	}

	/**
	 * get
	 * 获取一个语言
	 *
	 * @param  string $key
	 * @param  array $params
	 * @return string
	 */
	public static function get($key, $params = array()) {
		/** 加载语言包 */
		self::load_lang();

		$search = array();
		$replace = array();
		foreach ($params as $s => $r) {
			$search[] = "{%$s}";
			$replace[] = $r;
		}

		return isset(self::$_lang_data[$key]) ? str_replace($search, $replace, self::$_lang_data[$key]) : '';
	}

	/**
	 * set
	 * 设置一个语言
	 *
	 * @param  string $key
	 * @param  string $value
	 * @return void
	 */
	public static function set($key, $value) {
		self::$_lang_data[$key] = $value;
	}

}

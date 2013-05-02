<?php

class SmartyView extends Kohana_SmartyView {

	public static function instance() {
		$_instance = parent::instance();

		$_instance->registerPlugin('function', 'multidomaininclude', 'smarty_function_multidomaininclude');

		return $_instance;
	}
}
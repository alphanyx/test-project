<?php

class SmartyView extends Kohana_SmartyView {

	public static function instance() {
		$_instance = parent::instance();

		$_instance->registerPlugin('compiler', 'multidomaininclude', 'smarty_compiler_multidomaininclude');

		return $_instance;
	}
}
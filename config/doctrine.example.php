<?php defined('SYSPATH') or die('No direct script access.');

extract( (array)Multidomainconfig::instance()->matchDomain() );

$db = (object)Multidomainconfig::instance()->getDatabase($project, $environment);

return array(
	'connection' => array(
		'dbname'   => $db->dbname,
		'host'     => $db->host,
		'user'     => $db->user,
		'password' => $db->password,
	),
	'cache_prefix' => '',
	'model_path' => APPPATH . 'classes/Domain/Model',
);

<?php defined('SYSPATH') or die('No direct script access.');

extract( (array)Multidomain_Multidomainconfig::instance()->matchDomain() );

$db = (object)Multidomain_Multidomainconfig::instance()->getDatabase($project, $environment);

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

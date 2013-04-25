<?php


return array(
	'projects' => array(
		'example-project' => array(
			'settings' => array( // Some Settings for internal use
				'templates'		=> 'example',
				'title'			=> 'Example Project',
			),
			'environments' => array(
				'live' => array(
					'domains' => array( // array or string
						'example-live.de',
						'www.example-live.de'
					),
					'database' => array(
						'dbname'	=> 'example-live',
						'host'		=> 'localhost',
						'user'		=> 'live',
						'password'	=> '123',
					),
					'settings' => array( // Settings for the environment will overwrite the global project settings
						'templates'		=> 'example-live',
						'title'			=> 'Example Project - Live',
					),
				),
				'demo' => array(
					'domains' => array(
						'example-demo.de',
					),
					'database' => array(
						'dbname'	=> 'example-demo',
						'host'		=> 'localhost',
						'user'		=> 'demo',
						'password'	=> '456',
					),
				),
				'local' => array(
					'domains' => 'example.dev',
					'shell' => 'my-host-name', // hostname for the shell execution
					'database' => array(
						'dbname'	=> 'example-local',
						'host'		=> 'localhost',
						'user'		=> 'root',
						'password'	=> 'root',
					),
				),
			),
		),
	)
);

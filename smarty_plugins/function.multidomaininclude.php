<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     compiler.multidomaininclude.php
 * Type:     compiler
 * Name:     multidomaininclude
 * Purpose:  Checks the paths from the multidomain settings and set the right template dir
 * -------------------------------------------------------------
 */
function smarty_function_multidomaininclude($params, &$smarty)
{
	$templateDirs = $smarty->getTemplateDir();

	$file = str_replace('"','',$params['file']);
	unset($params['file']);

	$multidomainPaths = Helper_Config::getSmartyIncludePaths();
	$smartyTemplatePaths = $smarty->getTemplateDir();

	$path = false;
	foreach ($smartyTemplatePaths as $templatePath) {
		if (substr($templatePath,-1) != DIRECTORY_SEPARATOR) {
			$templatePath .= DIRECTORY_SEPARATOR;
		}
		foreach ($multidomainPaths as $domainPath) {
			if ($domainPath == DIRECTORY_SEPARATOR) {
				$domainPath = '';
			}
			if (strlen($domainPath) && substr($domainPath,-1) != DIRECTORY_SEPARATOR) {
				$domainPath .= DIRECTORY_SEPARATOR;
			}
			$tmpPath = $templatePath . $domainPath . $file;
			$realPath = realpath($tmpPath);

			if (file_exists($realPath)) {
				$path = $realPath;
				break;
			}
		}

		if ($path) break;
	}

	$values = array();
	if (!empty($params)) {
		$values = $params;
	}

	array_walk($values, function($value, $key, &$smarty) {
		$smarty->assign($key, $value);
	}, $smarty);

	return $smarty->fetch($path);
}
?>
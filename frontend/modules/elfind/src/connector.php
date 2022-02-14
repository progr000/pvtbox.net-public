<?php
error_reporting(0); // Set E_ALL for debuging
// elFinder autoload
require __DIR__ . '/autoload.php';

function access($attr, $path, $data, $volume) {
	return (strpos(basename($path), '.quarantine') === 0 || strpos(basename($path), '.tmb') === 0)  // if file/folder begins with '.' (dot)
			? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
			:  null;                                    // else elFinder decide it itself
}

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();


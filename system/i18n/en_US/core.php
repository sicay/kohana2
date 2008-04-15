<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'there_can_be_only_one' => 'There can be only one instance of Kohana per page request',
	'uncaught_exception'    => 'Uncaught %s: %s in file %s on line %s',
	'invalid_method'        => 'Invalid method <tt>%s</tt> called in <tt>%s</tt>',
	'cannot_write_log'      => 'Your log.directory config setting does not point to a writable directory',
	'resource_not_found'    => 'The requested %s, <tt>%s</tt>, could not be found',
	'invalid_filetype'      => 'The requested filetype, <tt>.%s</tt>, is not allowed in your view configuration file',
	'view_set_filename'     => 'You must set the the view filename before calling render',
	'no_default_route'      => 'Please set a default route in <tt>config/routes.php</tt>',
	'no_controller'         => 'Kohana was not able to determine a controller to process this request: %s',
	'page_not_found'        => 'The page you requested, <tt>%s</tt>, could not be found.',
	'stats_footer'          => 'Loaded in {execution_time} seconds, using {memory_usage} of memory. Generated by Kohana v{kohana_version}.',
	'error_file_line'       => '<tt>%s <strong>[%s]:</strong></tt>',
	'stack_trace'           => 'Stack Trace',
	'generic_error'         => 'Unable to Complete Request',
	'errors_disabled'       => 'You can go to the <a href="%s">home page</a> or <a href="%s">try again</a>.',

	// Resource names
	'controller'            => 'controller',
	'helper'                => 'helper',
	'library'               => 'library',
	'driver'                => 'driver',
	'model'                 => 'model',
	'view'                  => 'view',
);

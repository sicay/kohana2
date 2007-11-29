<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * This file contains four arrays of user agent data.  It is used by the
 * User Agent library to help identify browser, platform, robot, and
 * mobile device data. The array keys are used to identify the device
 * and the array values are used to set the actual name of the item.
 */
$config['platform'] = array
(
	'windows nt 6.0' => 'Windows Longhorn',
	'windows nt 5.2' => 'Windows 2003',
	'windows nt 5.0' => 'Windows 2000',
	'windows nt 5.1' => 'Windows XP',
	'windows nt 4.0' => 'Windows NT 4.0',
	'winnt4.0'       => 'Windows NT 4.0',
	'winnt 4.0'      => 'Windows NT',
	'winnt'          => 'Windows NT',
	'windows 98'     => 'Windows 98',
	'win98'          => 'Windows 98',
	'windows 95'     => 'Windows 95',
	'win95'          => 'Windows 95',
	'windows'        => 'Unknown Windows OS',
	'os x'           => 'Mac OS X',
	'ppc mac'        => 'Power PC Mac',
	'freebsd'        => 'FreeBSD',
	'ppc'            => 'Macintosh',
	'linux'          => 'Linux',
	'debian'         => 'Debian',
	'sunos'          => 'Sun Solaris',
	'beos'           => 'BeOS',
	'apachebench'    => 'ApacheBench',
	'aix'            => 'AIX',
	'irix'           => 'Irix',
	'osf'            => 'DEC OSF',
	'hp-ux'          => 'HP-UX',
	'netbsd'         => 'NetBSD',
	'bsdi'           => 'BSDi',
	'openbsd'        => 'OpenBSD',
	'gnu'            => 'GNU/Linux',
	'unix'           => 'Unknown Unix OS'
);

// The order of this array should NOT be changed. Many browsers return
// multiple browser types so we want to identify the sub-type first.
$config['browser'] = array
(
	'Opera'             => 'Opera',
	'MSIE'              => 'Internet Explorer',
	'Internet Explorer' => 'Internet Explorer',
	'Shiira'            => 'Shiira',
	'Firefox'           => 'Firefox',
	'Chimera'           => 'Chimera',
	'Phoenix'           => 'Phoenix',
	'Firebird'          => 'Firebird',
	'Camino'            => 'Camino',
	'Netscape'          => 'Netscape',
	'OmniWeb'           => 'OmniWeb',
	'Safari'            => 'Safari',
	'Mozilla'           => 'Mozilla',
	'Konqueror'         => 'Konqueror',
	'icab'              => 'iCab',
	'lynx'              => 'Lynx',
	'links'             => 'Links',
	'hotjava'           => 'HotJava',
	'amaya'             => 'Amaya',
	'IBrowse'           => 'IBrowse'
);

$config['mobile'] = array
(
	'mobileexplorer' => 'Mobile Explorer',
	'openwave'       => 'Open Wave',
	'opera mini'     => 'Opera Mini',
	'operamini'      => 'Opera Mini',
	'elaine'         => 'Palm',
	'palmsource'     => 'Palm',
	'digital paths'  => 'Palm',
	'avantgo'        => 'Avantgo',
	'xiino'          => 'Xiino',
	'palmscape'      => 'Palmscape',
	'nokia'          => 'Nokia',
	'ericsson'       => 'Ericsson',
	'blackBerry'     => 'BlackBerry',
	'motorola'       => 'Motorola'
);

// There are hundreds of bots but these are the most common.
$config['robots'] = array
(
	'googlebot'   => 'Googlebot',
	'msnbot'      => 'MSNBot',
	'slurp'       => 'Inktomi Slurp',
	'yahoo'       => 'Yahoo',
	'askjeeves'   => 'AskJeeves',
	'fastcrawler' => 'FastCrawler',
	'infoseek'    => 'InfoSeek Robot 1.0',
	'lycos'       => 'Lycos'
);
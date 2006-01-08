<?php
// from PEAR: PHP_Compat
if (!function_exists('array_combine')) {
	function array_combine($keys, $values) {
		if (!is_array($keys)) {
			trigger_error('array_combine() expects parameter 1 to be array, ' . gettype($keys) . ' given', E_USER_WARNING);
			return;
		}

		if (!is_array($values)) {
			trigger_error('array_combine() expects parameter 2 to be array, ' . gettype($values) . ' given', E_USER_WARNING);
			return;
		}

		if (count($keys) !== count($values)) {
			trigger_error('array_combine() Both parameters should have equal number of elements', E_USER_WARNING);
			return false;
		}

		if (count($keys) === 0 || count($values) === 0) {
			trigger_error('array_combine() Both parameters should have number of elements at least 0', E_USER_WARNING);
			return false;
		}

		$keys    = array_values($keys);
		$values  = array_values($values);

		$combined = array ();

		for ($i = 0, $cnt = count($values); $i < $cnt; $i++) {
			$combined[$keys[$i]] = $values[$i];
		}

		return $combined;
	}
}

/*
 * Removes unneccesary whitespace and invokes ob_end_flush().
 */
function hsys_ob_end($remove_whitespace) {
	if($remove_whitespace) {
		$output = ob_get_clean();
		$output = preg_replace(array('/\>\s+\</', '/(\s{1})\s{1,}/', '/\s*\n+\s*(?:(?=.*\<textarea)|(?!.*\<\/textarea))/'), array('><', '$1', ''), $output);
		@ob_start('ob_gzhandler');
		echo($output);
	}
	ob_end_flush();
}

/*
 * Valid password?
 */
function passwd_check($input, $hash) {
	if(crypt($input, $hash) == $hash || md5($input) == $hash) {
		return true;
	}
	return false;
}

/*
 * Returns true if given string is in set.
 */
function find_in_set($str, $strlist) {
	// TODO: increase performance of this function
	if(!is_array($strlist))
		$strlist = explode(',', str_replace(' ', '', $strlist));
	return(in_array($str, $strlist));
}

/*
 * if $elem gets changed, submit current form
 */
function ChngS($elem) {
	return(addProp($elem, array('onchange' => 'submit()')));
}

/*
 * returns a list of page references
 */
function getPageList($link, $anz, $perPage, $cur = 0) {
	if(!is_numeric($anz) || !is_numeric($perPage) || $anz == 0 || $perPage == 0)
		return '';
	$pages = max(1, ceil($anz / $perPage));
	if($pages < 2)
		return '&nbsp;&nbsp;';
	$str = array();
	$link = str_replace('%25d', '%d', $link);
	for($i = 1; $i <= $pages; $i++) {
		if($i == $cur)
			$str[] = B(sprintf($link, $i, $i));
		else
			$str[] = sprintf($link, $i, $i);
	}
	return implode('&nbsp;', $str);
}

/*
 * creates a nice self-reference
 */
function mkSelfRef($arr_Add = array()) {
	$qs = array();
	foreach(array_merge($_GET, $arr_Add) as $key => $value) {
		$qs[] = urlencode(trim($key)).'='.urlencode(trim($value));
	}
	if(count($qs) > 0)
		return($_SERVER['PHP_SELF'].'?'.implode('&', $qs));
	else
		return $_SERVER['PHP_SELF'];
}

/*
 * Wrapper to $imap->getquota
 */
function hsys_getQuota(IMAP_Administrator $imap, $username) {
	return $imap->getquota($imap->format_user($username));
}

function hsys_getMaxQuota(IMAP_Administrator $imap, $username) {
	$result = hsys_getQuota($imap, $username);
	return $result['qmax'];
}
function hsys_getUsedQuota(IMAP_Administrator $imap, $username) {
	$result = hsys_getQuota($imap, $username);
	return $result['used'];
}

/*
 * Returned array holds usernames as keys and their rights as value.
 * Better provide the folder's name again as it may contain whitespace and
 * therefore lead to strange results:
 * "INBOX.this nouser read" user lrs -> 2 users detected (user "nouser" is incorrect)
 */
function hsys_getACLInfo($folder, $name = null) {
	$result = array(); $arr = array();

	if(!is_null($name)) {
		$folder = str_replace($name, 'aa', $folder);
	}

	if(preg_match('/\*\sACL\s[^\s]*\s(.*)/', $folder[0], $arr)) {
		if(preg_match_all('/([^\s]*)\s([lrswipcda]*)\s?/', $arr[1], $arr)) {
			$result = array_combine($arr[1], $arr[2]);
		}
	}

	return $result;
}

/*
 * Returns quota of the given user formatted.
 */
function hsys_format_quota(IMAP_Administrator $imap, $mboxname, $delimiter = '/') {
	$result = hsys_getQuota($imap, $mboxname);
	if($result['qmax'] == 'NOT-SET') {
		return '-'.$delimiter.'&infin;';
	} else if(round($result['used']/$result['qmax']*100) > 0) {
		return round($result['used']/$result['qmax']*100).'% '.$delimiter.' '.round($result['qmax']/1024);
	} else if($result['used'] == 0) {
		return '0% '.$delimiter.' '.round($result['qmax']/1024);
	} else {
		return '>1% '.$delimiter.' '.round($result['qmax']/1024);
	}
}

/*
 * Transforms a string like 'INBOX.Archiv.Drafts' to an array['INBOX']['Archiv']['Drafts']
 * and adds ['^'] = string to the top.
 * (^ is a magic token in IMAP-Servers which cannot be part of a mailbox' name.)
 */
function array_stepper($delimiter, $string) {
	$hy['^'] = $string;
	foreach(array_reverse(explode($delimiter, $string)) as $key=>$value) {
		$hy = array($value => $hy);
	}

	return $hy;
}

/*
 * Like array_merge_recursive, but acts on already build arrays
 * and densifies/compresses using given fields as keys.
 */
function array_densify($arr, $dense_field) {
	$j		= 0;
	$dense	= array();

	for($i = 0; $i < count($arr); $i++) {
		$bdens	= false;
		foreach($arr[$i] as $key => $value) {
			$dense[$j][$key][]	= $value;
		}
		if(isset($arr[$i + 1])) {
			foreach($dense_field as $field) {
				$bdens = $bdens || $arr[$i + 1][$field] != $arr[$i][$field];
			}
			if($bdens)
				$j++;
		}
	}
	return $dense;
}

/* display_tree is specialized for /folders.php */
function display_tree($tree) {
	echo('<ul class="tree">');
	foreach($tree as $key=>$value) {
		$of_interest	= isset($_GET['folder']) && stristr($_GET['folder'], $key);
		$this_new	= isset($value['^']) && isset($GLOBALS['to_be_created']) && $value['^'] == $GLOBALS['to_be_created'];
		$has_subfolders	= !(isset($value['^']) && count($value) == 1);

		// Does this folder contain subfolders?
		if($has_subfolders) {
			echo('<li class="container">');
		} else {
			echo('<li class="leaf">');
		}

		// Is the node selectable
		if(isset($value['^'])) {
			// Is this one new? Does it lead to the selected one?
			if($this_new) {
				echo('<span class="new_mbox">');
			} else if($of_interest) {
				echo('<span class="act_mbox">');
			} else {
				echo('<span class="ina_mbox">');
			}
			echo('<a href="'.mkSelfRef(array('folder' => $value['^'])).'">'.$key.'</a></span>');
			unset($value['^']);
		} else { // ... or just a step?
			if($of_interest) {
				echo('<span class="act_mbox">');
			} else {
				echo('<span class="ina_mbox">');
			}
			echo($key.'</span>');
		}

		// If it contains subfolders, display them.
		if($has_subfolders && count($value) > 0) {
			display_tree($value);
		}

		// Finally, the ending tag.
		echo('</li>');
	}
	echo('</ul>');
}

/*
 * Responsible for the nice ACL-rights matrix.
 */
function hsys_ACL_matrix($ACL, $editable = false, $rights = array('l', 'r', 's', 'w', 'i', 'p', 'c', 'd', 'a')) {
	global $cfg, $input;

	$presets = array(	'above'		=> txt('110'),
				'lrs'		=> txt('114').' (lrs)',
				'lrsp'		=> txt('115').' (lrsp)',
				'lrswipcd'	=> txt('116').' (lrswipcd)',
				'lrsip'		=> txt('117').' (lrsip)',
				'lrswipd'	=> txt('118').' (lrswipd)',
				'lrswipcda'	=> txt('119').' (lrswipcda)',
				'none'		=> txt('120'));

	include('./templates/'.$cfg['theme'].'/folders/acl_matrix.tpl');

	return true;
}

/*
 * Just a small text obfuscator.
 * This one encrypts a given string.
 */
function obfuscator_encrypt($cleartext) {
	// If mcrypt is available, use that.
	if(function_exists('mcrypt_module_open')) {
		// Set a "secret" key.
		if(!isset($_COOKIE['obfuscator_key'])) {
			mt_srand(time());
			$key = substr(md5(mt_rand().$cleartext), 0, 24);
			setcookie('obfuscator_key', $key);
			// set it, in case decryption is done within this pagehit
			$_COOKIE['obfuscator_key'] = $key;
		} else {
			$key = $_COOKIE['obfuscator_key'];
		}

		// Here comes the encrpytion.
		$td = mcrypt_module_open('tripledes', '', 'ecb', '');
		$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init($td, $key, $iv);

		$encrypted_data = mcrypt_generic($td, $cleartext);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);

		return $encrypted_data;
	} else {	// rot13 will do the job
		return str_rot13($cleartext);
	}
}

/*
 * Just a small text obfuscator.
 * This one decrypts the obfuscated string.
 */
function obfuscator_decrypt($ciphertext) {
	if(function_exists('mcrypt_module_open')) {
		// Obtain the "secret" key.
		$key = $_COOKIE['obfuscator_key'];

		// Here is the decryption.
		$td = mcrypt_module_open('tripledes', '', 'ecb', '');
		$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		$key = substr($key, 0, mcrypt_enc_get_key_size($td));
		mcrypt_generic_init($td, $key, $iv);

		$decrypted_data = mdecrypt_generic($td, $ciphertext);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);

		return $decrypted_data;
	} else {	// obviously rot13 must have been used
		return str_rot13($ciphertext);
	}
}

/**
 * For combining rows in tables with same content.
 *
 * @param	arr	Array with hashes.
 * @param	field	Field to be counted (in hash).
 * @param	tagname	This field will be added to the hash with number of same following rows. Only first different row wiill carry this tag.
 */
function count_same_cols(&$arr, $fieldname, $tagname) {
	$c	= 0;
	for($i = count($arr)-1; $i >= 0; $i--) {
		++$c;
		if(!isset($arr[$i-1][$fieldname]) || $arr[$i-1][$fieldname] != $arr[$i][$fieldname]) {
			$arr[$i][$tagname]	= $c;
			$c = 0;
		}
	}
}

/**
 * This is so we can use classes and interfaces without typeing long include lists.
 */
function __autoload($class_name) {
	require_once('./inc/lib/'.$class_name.'.php');
}

?>
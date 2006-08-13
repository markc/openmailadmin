<?php
class PasswordAndDataController
	extends AOMAController
	implements INavigationContributor
{
	public function get_navigation_items() {
		$oma = $this->oma;
		return array('link'		=> 'index.php'.($oma->current_user != $oma->authenticated_user ? '?cuser='.$oma->current_user->ID : ''),
				'caption'	=> txt('1'),
				'active'	=> stristr($_SERVER['PHP_SELF'], 'index.php'));
	}

	public function controller_get_shortname() {
		return 'password';
	}

	/**
	 * This requires the former password for authentication if current user and
	 * authenticated user are the same.
	 */
	public function change(User $user, $new, $new_repeat, $old_passwd = null) {
		if($user == $this->oma->authenticated_user
		   && !is_null($old_passwd)
		   && !$user->password->equals($old_passwd)) {
			$this->ErrorHandler->add_error(txt('45'));
		} else if($new != $new_repeat) {
			$this->ErrorHandler->add_error(txt('44'));
		} else if(strlen($new) < $this->oma->cfg['passwd']['min_length']
			|| strlen($new) > $this->oma->cfg['passwd']['max_length']) {
			$this->ErrorHandler->add_error(sprintf(txt('46'), $this->oma->cfg['passwd']['min_length'], $this->oma->cfg['passwd']['max_length']));
		} else {
			// Warn about insecure passwords, but let them pass.
			if(!Password::is_secure($new)) {
				$this->ErrorHandler->add_error(txt('47'));
			}
			if($user->password->set($new)) {
				$this->ErrorHandler->add_info(txt('48'));
				return true;
			}
		}
		return false;
	}

}
?>
<?php

/**
 * @name      ElkArte Forum
 * @copyright ElkArte Forum contributors
 * @license   BSD http://opensource.org/licenses/BSD-3-Clause
 *
 * @version 1.0 Alpha
 *
 */

if (!defined('ELKARTE'))
	die('No access...');

/**
 * Dispatch the request to the function or method registered to handle it.
 * Try first the critical functionality (maintenance, no guest access)
 * Then, in order:
 *   forum's main actions: board index, message index, display topic
 *   the current/legacy file/functions registered by Elkarte core
 * Fall back to naming patterns:
 *   filename=[action].php function=[sa]
 *   filename=[action].controller.php method=action_[sa]
 *   filename=[action]-Controller.php method=action_[sa]
 *
 * An add-on files to handle custom actions will be called if they follow
 * any of these patterns.
 */
class Site_Dispatcher
{
	/**
	 * file name to load
	 */
	private $_file_name;

	/**
	 * function or method to call
	 */
	private $_function_name;

	/**
	 * class name, for object oriented controllers
	 */
	private $_controller_name;

	/**
	 * name of pre_dispatch function, for procedural controllers
	 */
	private $_pre_dispatch_func;

	/**
	 * Create an instance and initialize it.
	 * This does all the work to figure out which file and function/method needs called.
	 */
	public function __construct()
	{
		global $board, $topic, $modSettings, $settings, $user_info, $maintenance;

		// default action of the forum: board index
		// everytime we don't know what to do, we'll do this :P
		$default_action = array(
			'file' => CONTROLLERDIR . '/BoardIndex.controller.php',
			'function' => 'action_boardindex'
		);

		// Maintenance mode: you're out of here unless you're admin
		if (!empty($maintenance) && !allowedTo('admin_forum'))
		{
			// You can only login
			if (isset($_GET['action']) && ($_GET['action'] == 'login2' || $_GET['action'] == 'logout'))
			{
				$this->_file_name = CONTROLLERDIR . '/LogInOut.controller.php';
				$this->_function_name = $_GET['action'] == 'login2' ? 'action_login2' : 'action_logout';
			}
			// "maintenance mode" page
			else
			{
				$this->_file_name = SUBSDIR . '/Auth.subs.php';
				$this->_function_name = 'InMaintenance';
			}
		}
		// If guest access is disallowed, a guest is kicked out... politely. :P
		elseif (empty($modSettings['allow_guestAccess']) && $user_info['is_guest'] && (!isset($_GET['action']) || !in_array($_GET['action'], array('coppa', 'login', 'login2', 'register', 'register2', 'reminder', 'activate', 'help', 'mailq', 'verificationcode', 'openidreturn'))))
		{
			$this->_file_name = SUBSDIR . '/Auth.subs.php';
			$this->_function_name = 'KickGuest';
		}
		elseif (empty($_GET['action']))
		{
			// home page: board index
			if (empty($board) && empty($topic))
			{
				// @todo Unless we have a custom home page registered...

				// was it, wasn't it....
				if (empty($this->_function_name))
				{
					$this->_file_name = $default_action['file'];
					$this->_function_name = $default_action['function'];
				}
			}
			// ?board=b message index
			elseif (empty($topic))
			{
				$this->_file_name = CONTROLLERDIR . '/MessageIndex.controller.php';
				$this->_function_name = 'action_messageindex';
			}
			// board=b;topic=t topic display
			else
			{
				$this->_file_name = CONTROLLERDIR . '/Display.controller.php';
				$this->_function_name = 'Display';
			}
		}

		// now this return won't be cool, but lets do it
		if (!empty($this->_file_name) && !empty($this->_function_name))
			return;

		// Start with our nice and cozy err... *cough*
		// Format:
		// $_GET['action'] => array($file, $function)
		// $_GET['action'] => array($file, $class, $method)
		$actionArray = array(
			'activate' => array('Register.controller.php', 'action_activate'),
			'admin' => array('Admin.php', 'AdminMain'),
			// 'announce' => array('Announce.controller.php', 'action_announce'),
			'attachapprove' => array('ModerateAttachments.controller.php', 'action_attachapprove'),
			'buddy' => array('Members.controller.php', 'action_buddy'),
			'calendar' => array('Calendar.controller.php', 'action_calendar'),
			'collapse' => array('BoardIndex.controller.php', 'action_collapse'),
			'contact' => array('Register.controller.php', 'action_contact'),
			'coppa' => array('Register.controller.php', 'action_coppa'),
			'credits' => array('Who.controller.php', 'action_credits'),
			'deletemsg' => array('RemoveTopic.controller.php', 'action_deletemsg'),
			'dlattach' => array('Attachment.controller.php', 'action_dlattach'),
			'disregardtopic' => array('Notify.controller.php', 'action_disregardtopic'),
			'editpoll' => array('Poll.controller.php', 'action_editpoll'),
			'editpoll2' => array('Poll.controller.php', 'action_editpoll2'),
			// 'emailuser' => array('Emailuser.controller.php', 'action_emailuser'),
			'findmember' => array('Members.controller.php', 'action_findmember'),
			'groups' => array('Groups.controller.php', 'action_groups'),
			'help' => array('Help.controller.php', 'action_help'),
			'quickhelp' => array('Help.controller.php', 'action_quickhelp'),
			'jsmodify' => array('Post.controller.php', 'action_jsmodify'),
			'jsoption' => array('Themes.php', 'action_jsoption'),
			'loadeditorlocale' => array('subs/Editor.subs.php', 'action_loadlocale'),
			'lock' => array('Topic.controller.php', 'action_lock'), // done
			'lockvoting' => array('Poll.controller.php', 'action_lockvoting'),
			'login' => array('LogInOut.controller.php', 'action_login'),
			'login2' => array('LogInOut.controller.php', 'action_login2'),
			'logout' => array('LogInOut.controller.php', 'action_logout'),
			'markasread' => array('Markasread.controller.php', 'markasread'),
			'mergetopics' => array('MergeTopics.controller.php', 'action_mergetopics'),
			'memberlist' => array('Memberlist.controller.php', 'pre_memberlist'),
			'moderate' => array('ModerationCenter.controller.php', 'action_modcenter'),
			'karma' => array('Karma.controller.php', 'action_karma'),
			'movetopic' => array('MoveTopic.controller.php', 'action_movetopic'),
			'movetopic2' => array('MoveTopic.controller.php', 'action_movetopic2'),
			'notify' => array('Notify.controller.php', 'action_notify'),
			'notifyboard' => array('Notify.controller.php', 'action_notifyboard'),
			'openidreturn' => array('OpenID.subs.php', 'action_openidreturn'),
			'pm' => array('PersonalMessage.controller.php', 'action_pm'),
			'post' => array('Post.controller.php', 'action_post'),
			'post2' => array('Post.controller.php', 'action_post2'),
			'printpage' => array('Topic.controller.php', 'action_printpage'), // done
			'profile' => array('Profile.controller.php', 'action_modifyprofile'),
			'quotefast' => array('Post.controller.php', 'action_quotefast'),
			'quickmod' => array('MessageIndex.controller.php', 'action_quickmod'),
			'quickmod2' => array('Display.controller.php', 'action_quickmod2'),
			'recent' => array('Recent.controller.php', 'action_recent'),
			'register' => array('Register.controller.php', 'action_register'),
			'register2' => array('Register.controller.php', 'action_register2'),
			// 'reminder' => array('Reminder.controller.php', ''),
			'removepoll' => array('Poll.controller.php', 'action_removepoll'),
			'removetopic2' => array('RemoveTopic.controller.php', 'action_removetopic2'),
			'reporttm' => array('Emailuser.controller.php', 'action_reporttm'),
			'requestmembers' => array('Members.controller.php', 'action_requestmembers'),
			'restoretopic' => array('RemoveTopic.controller.php', 'action_restoretopic'),
			'search' => array('Search.controller.php', 'action_plushsearch1'),
			'search2' => array('Search.controller.php', 'action_plushsearch2'),
			// 'sendtopic' => array('Emailuser.controller.php', 'action_sendtopic'),
			'suggest' => array('Suggest.controller.php', 'action_suggest'),
			'spellcheck' => array('Post.controller.php', 'action_spellcheck'),
			'splittopics' => array('SplitTopics.controller.php', 'action_splittopics'),
			'stats' => array('Stats.controller.php', 'action_stats'),
			'sticky' => array('Topic.controller.php', 'action_sticky'), // done
			'theme' => array('Themes.php', 'action_thememain'),
			'trackip' => array('ProfileHistory.controller.php', 'action_trackip'),
			'unread' => array('Recent.controller.php', 'action_unread'),
			'unreadreplies' => array('Recent.controller.php', 'action_unread'),
			'verificationcode' => array('Register.controller.php', 'action_verificationcode'),
			'viewprofile' => array('Profile.controller.php', 'action_modifyprofile'),
			'vote' => array('Poll.controller.php', 'action_vote'),
			'viewquery' => array('ViewQuery.php', 'action_viewquery'),
			'viewadminfile' => array('Admin.php', 'action_viewadminfile'),
			'who' => array('Who.controller.php', 'action_who'), // done
			'.xml' => array('News.controller.php', 'action_showfeed'),
			'xmlhttp' => array('Xml.controller.php', 'action_xmlhttp'),
		);

		$adminActions = array ('admin', 'attachapprove', 'jsoption', 'theme', 'viewadminfile', 'viewquery');

		// allow to extend or change $actionArray through a hook
		call_integration_hook('integrate_actions', array(&$actionArray));

		// Is it in core legacy actions?
		if (isset($actionArray[$_GET['action']]))
		{
			// admin files have their own place
			$path = in_array($_GET['action'], $adminActions) ? SOURCEDIR . '/admin' : CONTROLLERDIR;

			// is it an object oriented controller?
			if (isset($actionArray[$_GET['action']][2]))
			{
				$this->_file_name = $path . '/' . $actionArray[$_GET['action']][0];
				$this->_controller_name = $actionArray[$_GET['action']][1];

				// if the method is coded in, use it
				if (!empty($actionArray[$_GET['action']][2]))
					$this->_function_name = $actionArray[$_GET['action']][2];
				// otherwise fall back to naming patterns
				elseif (isset($_GET['sa']) && preg_match('~^\w+$~', $_GET['sa']))
					$this->_function_name = 'action_' . $_GET['sa'];
				else
					$this->_function_name = 'action_index';
			}
			// then it's one of our legacy functions
			else
			{
				$this->_file_name = $path . '/' . $actionArray[$_GET['action']][0];
				$this->_function_name = $actionArray[$_GET['action']][1];
			}
		}
		// fall back to naming patterns.
		// add-ons can use any of them, and it should Just Work (tm).
		elseif (preg_match('~^[a-zA-Z_\\-]+$~', $_GET['action']))
		{
			// action=drafts => Drafts.controller.php
			// sa=save, sa=load, or sa=savepm => action_save(), action_load()
			// ... if it ain't there yet, no problem.
			if (file_exists(CONTROLLERDIR . '/' . ucfirst($_GET['action']) . '.controller.php'))
			{
				$this->_file_name = CONTROLLERDIR . '/' . ucfirst($_GET['action']) . '.controller.php';

				// procedural controller... we might need to pre dispatch to its main function
				// i.e. for action=mergetopics it was MergeTopics(), now it's mergetopics()
				$this->_pre_dispatch_func = 'pre_' . $_GET['action'];

				// then, figure out the function for the subaction
				if (isset($_GET['sa']) && preg_match('~^\w+$~', $_GET['sa']))
					$this->_function_name = 'action_' . $_GET['sa'];
				else
					$this->_function_name = 'action_' . $_GET['action'];
			}
			// or... an add-on can do just this!
			// action=gallery => Gallery-Controller.php
			// sa=upload => action_upload()
			elseif (file_exists(CONTROLLERDIR . '/' . ucfirst($_GET['action']) . '-Controller.php'))
			{
				$this->_file_name = CONTROLLERDIR . '/' . ucfirst($_GET['action']) . '-Controller.php';
				$this->_controller_name = ucfirst($_GET['action']) . '_Controller';
				if (isset($_GET['sa']) && preg_match('~^\w+$~', $_GET['sa']))
					$this->_function_name = 'action_' . $_GET['sa'];
				else
					$this->_function_name = 'action_index';
			}
		}

		// the file and function weren't found yet?
		if (empty($this->_file_name) || empty($this->_function_name))
		{
			// Catch the action with the theme?
			// @todo remove this?
			if (!empty($settings['catch_action']))
			{
				$this->_file_name = SOURCEDIR . '/admin/Themes.php';
				$this->_function_name = 'WrapAction';
			}
			else
			{
				// we still haven't found what we're looking for...
				$this->_file_name = $default_action['file'];
				$this->_function_name = $default_action['function'];
			}
		}
	}

	/**
	 * Relay control to the respective function or method.
	 */
	public function dispatch()
	{
		require_once($this->_file_name);

		if (!empty($this->_controller_name))
		{
			$controller = new $this->_controller_name();

			// pre-dispatch (load templates and stuff)
			if (method_exists($controller, 'pre_dispatch'))
				$controller->pre_dispatch();

			// 3, 2, ... and go
			if (method_exists($controller, $this->_function_name))
				$controller->{$this->_function_name}();
			elseif (method_exists($this->controller, 'action_index'))
				$controller->action_index();
			// fall back
			else
			{
				// things went pretty bad, huh?
				// board index :P
				require_once(CONTROLLERDIR . '/BoardIndex.controller.php');
				return 'action_boardindex';
			}
		}
		else
		{
			// pre-dispatch (load templates and stuff)
			// for procedural controllers, we know this name (instance var)
			if (!empty($this->_pre_dispatch_func) && function_exists($this->_pre_dispatch_func))
				call_user_func($this->_pre_dispatch_func);

			// it must be a good ole' function
			call_user_func($this->_function_name);
		}
	}
}
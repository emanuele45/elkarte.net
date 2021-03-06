<?php

/**
 * @name      ElkArte Forum
 * @copyright ElkArte Forum contributors
 * @license   BSD http://opensource.org/licenses/BSD-3-Clause
 *
 * This software is a derived product, based on:
 *
 * Simple Machines Forum (SMF)
 * copyright:	2011 Simple Machines (http://www.simplemachines.org)
 * license:  	BSD, See included LICENSE.TXT for terms and conditions.
 *
 * @version 1.0 Alpha
 */

// Template for the database maintenance tasks.
function template_maintain_database()
{
	global $context, $settings, $txt, $scripturl, $db_type, $modSettings;

	// If maintenance has finished tell the user.
	if (!empty($context['maintenance_finished']))
		echo '
			<div class="infobox">
				', sprintf($txt['maintain_done'], $context['maintenance_finished']), '
			</div>';

	echo '
	<div id="manage_maintenance">
		<div class="cat_bar">
			<h3 class="catbg">', $txt['maintain_optimize'], '</h3>
		</div>
		<div class="windowbg">
			<div class="content">
				<form action="', $scripturl, '?action=admin;area=maintain;sa=database;activity=optimize" method="post" accept-charset="UTF-8">
					<p>', $txt['maintain_optimize_info'], '</p>
					<input type="submit" value="', $txt['maintain_run_now'], '" class="button_submit" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="hidden" name="', $context['admin-maint_token_var'], '" value="', $context['admin-maint_token'], '" />
				</form>
			</div>
		</div>

		<div class="cat_bar">
			<h3 class="catbg">
			<a href="', $scripturl, '?action=quickhelp;help=maintenance_backup" onclick="return reqOverlayDiv(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.png" class="icon" alt="', $txt['help'], '" /></a> ', $txt['maintain_backup'], '
			</h3>
		</div>

		<div class="windowbg2">
			<div class="content">
				<form action="', $scripturl, '?action=admin;area=maintain;sa=database;activity=backup" method="post" accept-charset="UTF-8">
					<p>', $txt['maintain_backup_info'], '</p>';

	if ($db_type == 'sqlite')
		echo '
					<input type="submit" value="', $txt['maintain_backup_save'], '" id="submitDump" class="button_submit" />';
	else
	{
		if ($context['safe_mode_enable'])
			echo '
					<div class="errorbox">', $txt['safe_mode_enabled'], '</div>';
		else
			echo '
					<div class="', $context['suggested_method'] == 'use_external_tool' || $context['use_maintenance'] != 0 ? 'errorbox' : 'noticebox', '">
						', $txt[$context['suggested_method']],
						$context['use_maintenance'] != 0 ? '<br />' . $txt['enable_maintenance' . $context['use_maintenance']] : '',
					'</div>';

		echo '
					<p>
						<label for="struct"><input type="checkbox" name="struct" id="struct" onclick="document.getElementById(\'submitDump\').disabled = !document.getElementById(\'struct\').checked &amp;&amp; !document.getElementById(\'data\').checked;" class="input_check" checked="checked" /> ', $txt['maintain_backup_struct'], '</label><br />
						<label for="data"><input type="checkbox" name="data" id="data" onclick="document.getElementById(\'submitDump\').disabled = !document.getElementById(\'struct\').checked &amp;&amp; !document.getElementById(\'data\').checked;" checked="checked" class="input_check" /> ', $txt['maintain_backup_data'], '</label><br />
						<label for="compress"><input type="checkbox" name="compress" id="compress" value="gzip"', $context['suggested_method'] == 'zipped_file' ? ' checked="checked"' : '', ' class="input_check" /> ', $txt['maintain_backup_gz'], '</label>
					</p>
					
					<input ', $context['use_maintenance'] == 2 ? 'disabled="disabled" ' : '', 'type="submit" value="', $txt['maintain_backup_save'], '" id="submitDump" onclick="return document.getElementById(\'struct\').checked || document.getElementById(\'data\').checked;" class="button_submit" />';
	}

	echo '
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="hidden" name="', $context['admin-maint_token_var'], '" value="', $context['admin-maint_token'], '" />
				</form>
			</div>
		</div>';

	// Show an option to convert the body column of the post table to MEDIUMTEXT or TEXT
	if (isset($context['convert_to']))
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt[$context['convert_to'] . '_title'], '</h3>
		</div>
		<div class="windowbg">
			<div class="content">
				<form action="', $scripturl, '?action=admin;area=maintain;sa=database;activity=convertmsgbody" method="post" accept-charset="UTF-8">
					<p>', $txt['mediumtext_introduction'], '</p>',
					$context['convert_to_suggest'] ? '<p class="infobox">' . $txt['convert_to_suggest_text'] . '</p>' : '', '
					<input type="submit" name="evaluate_conversion" value="', $txt['maintain_run_now'], '" class="button_submit" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="hidden" name="', $context['admin-maint_token_var'], '" value="', $context['admin-maint_token'], '" />
				</form>
			</div>
		</div>';
	}

	echo '
	</div>';
}

// Template for the routine maintenance tasks.
function template_maintain_routine()
{
	global $context, $settings, $txt, $scripturl, $modSettings;

	// Starts off with general maintenance procedures.
	echo '
	<div id="manage_maintenance">';

	// If maintenance has finished tell the user.
	if (!empty($context['maintenance_finished']))
		echo '
			<div class="infobox">
				', sprintf($txt['maintain_done'], $context['maintenance_finished']), '
			</div>';

	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['maintain_version'], '</h3>
		</div>
		<div class="windowbg">
			<div class="content">
				<form action="', $scripturl, '?action=admin;area=maintain;sa=routine;activity=version" method="post" accept-charset="UTF-8">
					<p>', $txt['maintain_version_info'], '
						<input type="submit" value="', $txt['maintain_run_now'], '" class="button_submit" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					</p>
				</form>
			</div>
		</div>
		<div class="cat_bar">
			<h3 class="catbg">', $txt['maintain_errors'], '</h3>
		</div>
		<div class="windowbg2">
			<div class="content">
				<form action="', $scripturl, '?action=admin;area=repairboards" method="post" accept-charset="UTF-8">
					<p>', $txt['maintain_errors_info'], '
						<input type="submit" value="', $txt['maintain_run_now'], '" class="button_submit" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
						<input type="hidden" name="', $context['admin-maint_token_var'], '" value="', $context['admin-maint_token'], '" />
					</p>
				</form>
			</div>
		</div>
		<div class="cat_bar">
			<h3 class="catbg">', $txt['maintain_recount'], '</h3>
		</div>
		<div class="windowbg">
			<div class="content">
				<form action="', $scripturl, '?action=admin;area=maintain;sa=routine;activity=recount" method="post" accept-charset="UTF-8">
					<p>', $txt['maintain_recount_info'], '
						<input type="submit" value="', $txt['maintain_run_now'], '" class="button_submit" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
						<input type="hidden" name="', $context['admin-maint_token_var'], '" value="', $context['admin-maint_token'], '" />
					</p>
				</form>
			</div>
		</div>
		<div class="cat_bar">
			<h3 class="catbg">', $txt['maintain_logs'], '</h3>
		</div>
		<div class="windowbg2">
			<div class="content">
				<form action="', $scripturl, '?action=admin;area=maintain;sa=routine;activity=logs" method="post" accept-charset="UTF-8">
					<p>', $txt['maintain_logs_info'], '
						<input type="submit" value="', $txt['maintain_run_now'], '" class="button_submit" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
						<input type="hidden" name="', $context['admin-maint_token_var'], '" value="', $context['admin-maint_token'], '" />
					</p>
				</form>
			</div>
		</div>
		<div class="cat_bar">
			<h3 class="catbg">', $txt['maintain_cache'], '</h3>
		</div>
		<div class="windowbg">
			<div class="content">
				<form action="', $scripturl, '?action=admin;area=maintain;sa=routine;activity=cleancache" method="post" accept-charset="UTF-8">
					<p>', $txt['maintain_cache_info'], '
						<input type="submit" value="', $txt['maintain_run_now'], '" class="button_submit" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
						<input type="hidden" name="', $context['admin-maint_token_var'], '" value="', $context['admin-maint_token'], '" />
					</p>
				</form>
			</div>
		</div>
	</div>';
}

// Template for the member maintenance tasks.
function template_maintain_members()
{
	global $context, $settings, $txt, $scripturl;

	echo '
	<script type="text/javascript"><!-- // --><![CDATA[
		var warningMessage = \'\';
		var membersSwap = false;

		function swapMembers()
		{
			membersSwap = !membersSwap;
			var membersForm = document.getElementById(\'membersForm\');

			$("#membersPanel").slideToggle(300);

			document.getElementById("membersIcon").src = smf_images_url + (membersSwap ? "/selected_open.png" : "/selected.png");
			setInnerHTML(document.getElementById("membersText"), membersSwap ? "', $txt['maintain_members_choose'], '" : "', $txt['maintain_members_all'], '");

			for (var i = 0; i < membersForm.length; i++)
			{
				if (membersForm.elements[i].type.toLowerCase() == "checkbox")
					membersForm.elements[i].checked = !membersSwap;
			}
		}

		function checkAttributeValidity()
		{
			origText = \'', $txt['reattribute_confirm'], '\';
			valid = true;

			// Do all the fields!
			if (!document.getElementById(\'to\').value)
				valid = false;
			warningMessage = origText.replace(/%member_to%/, document.getElementById(\'to\').value);

			if (document.getElementById(\'type_email\').checked)
			{
				if (!document.getElementById(\'from_email\').value)
					valid = false;
				warningMessage = warningMessage.replace(/%type%/, \'', addcslashes($txt['reattribute_confirm_email'], "'"), '\').replace(/%find%/, document.getElementById(\'from_email\').value);
			}
			else
			{
				if (!document.getElementById(\'from_name\').value)
					valid = false;
				warningMessage = warningMessage.replace(/%type%/, \'', addcslashes($txt['reattribute_confirm_username'], "'"), '\').replace(/%find%/, document.getElementById(\'from_name\').value);
			}

			document.getElementById(\'do_attribute\').disabled = valid ? \'\' : \'disabled\';

			setTimeout("checkAttributeValidity();", 500);
			return valid;
		}
		setTimeout("checkAttributeValidity();", 500);
	// ]]></script>
	<div id="manage_maintenance">';

	// If maintenance has finished tell the user.
	if (!empty($context['maintenance_finished']))
	echo '
		<div class="infobox">
			', sprintf($txt['maintain_done'], $context['maintenance_finished']), '
		</div>';

	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['maintain_reattribute_posts'], '</h3>
		</div>
		<div class="windowbg2">
			<div class="content">
				<form action="', $scripturl, '?action=admin;area=maintain;sa=members;activity=reattribute" method="post" accept-charset="UTF-8">
					<p><strong>', $txt['reattribute_guest_posts'], '</strong></p>
					<dl class="settings">
						<dt>
							<label for="type_email"><input type="radio" name="type" id="type_email" value="email" checked="checked" class="input_radio" />', $txt['reattribute_email'], '</label>
						</dt>
						<dd>
							<input type="text" name="from_email" id="from_email" value="" onclick="document.getElementById(\'type_email\').checked = \'checked\'; document.getElementById(\'from_name\').value = \'\';" />
						</dd>
						<dt>
							<label for="type_name"><input type="radio" name="type" id="type_name" value="name" class="input_radio" />', $txt['reattribute_username'], '</label>
						</dt>
						<dd>
							<input type="text" name="from_name" id="from_name" value="" onclick="document.getElementById(\'type_name\').checked = \'checked\'; document.getElementById(\'from_email\').value = \'\';" class="input_text" />
						</dd>
					</dl>
					<dl class="settings">
						<dt>
							<label for="to"><strong>', $txt['reattribute_current_member'], ':</strong></label>
						</dt>
						<dd>
							<input type="text" name="to" id="to" value="" class="input_text" />
						</dd>
					</dl>
					<p class="maintain_members">
						<input type="checkbox" name="posts" id="posts" checked="checked" class="input_check" />
						<label for="posts">', $txt['reattribute_increase_posts'], '</label>
					</p>
					<input type="submit" id="do_attribute" value="', $txt['reattribute'], '" onclick="if (!checkAttributeValidity()) return false;
					return confirm(warningMessage);" class="button_submit" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="hidden" name="', $context['admin-maint_token_var'], '" value="', $context['admin-maint_token'], '" />
				</form>
			</div>
		</div>
		<div class="cat_bar">
			<h3 class="catbg">
				<a href="', $scripturl, '?action=quickhelp;help=maintenance_members" onclick="return reqOverlayDiv(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.png" class="icon" alt="', $txt['help'], '" /></a> ', $txt['maintain_members'], '
			</h3>
		</div>
		<div class="windowbg">
			<div class="content">
				<form action="', $scripturl, '?action=admin;area=maintain;sa=members;activity=purgeinactive" method="post" accept-charset="UTF-8" id="membersForm">
					<p><a id="membersLink"></a>', $txt['maintain_members_since1'], '
					<select name="del_type">
						<option value="activated" selected="selected">', $txt['maintain_members_activated'], '</option>
						<option value="logged">', $txt['maintain_members_logged_in'], '</option>
					</select> ', $txt['maintain_members_since2'], ' <input type="text" name="maxdays" value="30" size="3" class="input_text" />', $txt['maintain_members_since3'], '</p>';

	echo '
					<p><a href="#membersLink" onclick="swapMembers();"><img src="', $settings['images_url'], '/selected.png" alt="+" id="membersIcon" /></a> <a href="#membersLink" onclick="swapMembers();" id="membersText" style="font-weight: bold;">', $txt['maintain_members_all'], '</a></p>
					<div style="display: none; padding: 3px" id="membersPanel">';

	foreach ($context['membergroups'] as $group)
		echo '
						<label for="groups', $group['id'], '"><input type="checkbox" name="groups[', $group['id'], ']" id="groups', $group['id'], '" checked="checked" class="input_check" /> ', $group['name'], '</label><br />';

	echo '
					</div>
					<input type="submit" value="', $txt['maintain_old_remove'], '" onclick="return confirm(\'', $txt['maintain_members_confirm'], '\');" class="button_submit" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="hidden" name="', $context['admin-maint_token_var'], '" value="', $context['admin-maint_token'], '" />
				</form>
			</div>
		</div>
		<div class="cat_bar">
			<h3 class="catbg">', $txt['maintain_recountposts'], '</h3>
		</div>
		<div class="windowbg">
			<div class="content">
				<form action="', $scripturl, '?action=admin;area=maintain;sa=members;activity=recountposts" method="post" accept-charset="UTF-8" id="membersRecountForm">
					<p>', $txt['maintain_recountposts_info'], '</p>
					<input type="submit" value="', $txt['maintain_run_now'], '" class="button_submit" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="hidden" name="', $context['admin-maint_token_var'], '" value="', $context['admin-maint_token'], '" />
				</form>
			</div>
		</div>
	</div>

	<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/suggest.js?alp21"></script>
	<script type="text/javascript"><!-- // --><![CDATA[
		var oAttributeMemberSuggest = new smc_AutoSuggest({
			sSelf: \'oAttributeMemberSuggest\',
			sSessionId: smf_session_id,
			sSessionVar: smf_session_var,
			sSuggestId: \'attributeMember\',
			sControlId: \'to\',
			sSearchType: \'member\',
			sTextDeleteItem: \'', $txt['autosuggest_delete_item'], '\',
			bItemList: false
		});
	// ]]></script>';
}

// Template for the topic maintenance tasks.
function template_maintain_topics()
{
	global $scripturl, $txt, $context, $settings, $modSettings;

	// If maintenance has finished tell the user.
	if (!empty($context['maintenance_finished']))
		echo '
			<div class="infobox">
				', sprintf($txt['maintain_done'], $context['maintenance_finished']), '
			</div>';

	// Bit of javascript for showing which boards to prune in an otherwise hidden list.
	echo '
		<script type="text/javascript"><!-- // --><![CDATA[
			var rotSwap = false;
			function swapRot()
			{
				rotSwap = !rotSwap;

				// Toggle icon
				document.getElementById("rotIcon").src = smf_images_url + (rotSwap ? "/selected_open.png" : "/selected.png");
				setInnerHTML(document.getElementById("rotText"), rotSwap ? ', JavaScriptEscape($txt['maintain_old_choose']), ' : ', JavaScriptEscape($txt['maintain_old_all']), ');

				// Toggle panel
				$("#rotPanel").slideToggle(300);

				// Toggle checkboxes
				var rotPanel = document.getElementById(\'rotPanel\');
				var oBoardCheckBoxes = rotPanel.getElementsByTagName(\'input\');
				for (var i = 0; i < oBoardCheckBoxes.length; i++)
				{
					if (oBoardCheckBoxes[i].type.toLowerCase() == "checkbox")
						oBoardCheckBoxes[i].checked = !rotSwap;
				}
			}
		// ]]></script>';

	echo '
	<div id="manage_maintenance">
		<div class="cat_bar">
			<h3 class="catbg">', $txt['maintain_old'], '</h3>
		</div>
		<div class="windowbg">
			<div class="content flow_auto">
				<form action="', $scripturl, '?action=admin;area=maintain;sa=topics;activity=pruneold" method="post" accept-charset="UTF-8">';

	// The otherwise hidden "choose which boards to prune".
	echo '
					<p>
						<a id="rotLink"></a>', $txt['maintain_old_since_days1'], '<input type="text" name="maxdays" value="30" size="3" />', $txt['maintain_old_since_days2'], '
					</p>
					<p>
						<label for="delete_type_nothing"><input type="radio" name="delete_type" id="delete_type_nothing" value="nothing" class="input_radio" /> ', $txt['maintain_old_nothing_else'], '</label><br />
						<label for="delete_type_moved"><input type="radio" name="delete_type" id="delete_type_moved" value="moved" class="input_radio" checked="checked" /> ', $txt['maintain_old_are_moved'], '</label><br />
						<label for="delete_type_locked"><input type="radio" name="delete_type" id="delete_type_locked" value="locked" class="input_radio" /> ', $txt['maintain_old_are_locked'], '</label><br />
					</p>';

	if (!empty($modSettings['enableStickyTopics']))
		echo '
					<p>
						<label for="delete_old_not_sticky"><input type="checkbox" name="delete_old_not_sticky" id="delete_old_not_sticky" class="input_check" checked="checked" /> ', $txt['maintain_old_are_not_stickied'], '</label><br />
					</p>';

		echo '
					<p>
						<a href="#rotLink" onclick="swapRot();"><img src="', $settings['images_url'], '/selected.png" alt="+" id="rotIcon" /></a> <a href="#rotLink" onclick="swapRot();" id="rotText" style="font-weight: bold;">', $txt['maintain_old_all'], '</a>
					</p>
					<div style="display: none;" id="rotPanel" class="flow_hidden">
						<div class="floatleft" style="width: 49%">';

	// This is the "middle" of the list.
	$middle = ceil(count($context['categories']) / 2);

	$i = 0;
	foreach ($context['categories'] as $category)
	{
		echo '
							<fieldset>
								<legend>', $category['name'], '</legend>
								<ul class="reset">';

		// Display a checkbox with every board.
		foreach ($category['boards'] as $board)
			echo '
									<li style="margin-', $context['right_to_left'] ? 'right' : 'left', ': ', $board['child_level'] * 1.5, 'em;"><label for="boards_', $board['id'], '"><input type="checkbox" name="boards[', $board['id'], ']" id="boards_', $board['id'], '" checked="checked" class="input_check" />', $board['name'], '</label></li>';

		echo '
								</ul>
							</fieldset>';

		// Increase $i, and check if we're at the middle yet.
		if (++$i == $middle)
			echo '
						</div>
						<div class="floatright" style="width: 49%;">';
	}

	echo '
						</div>
					</div>
					<input type="submit" value="', $txt['maintain_old_remove'], '" onclick="return confirm(\'', $txt['maintain_old_confirm'], '\');" class="button_submit" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="hidden" name="', $context['admin-maint_token_var'], '" value="', $context['admin-maint_token'], '" />
				</form>
			</div>
		</div>

		<div class="cat_bar">
			<h3 class="catbg">', $txt['maintain_old_drafts'], '</h3>
		</div>
		<div class="windowbg">
			<div class="content">
				<form action="', $scripturl, '?action=admin;area=maintain;sa=topics;activity=olddrafts" method="post" accept-charset="UTF-8">
					<p>', $txt['maintain_old_drafts_days'], '&nbsp;<input type="text" name="draftdays" value="', (!empty($modSettings['drafts_keep_days']) ? $modSettings['drafts_keep_days'] : 30), '" size="3" />&nbsp;', $txt['days_word'], '</p>
					<input type="submit" value="', $txt['maintain_old_remove'], '" onclick="return confirm(\'', $txt['maintain_old_drafts_confirm'], '\');" class="button_submit" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="hidden" name="', $context['admin-maint_token_var'], '" value="', $context['admin-maint_token'], '" />
				</form>
			</div>
		</div>
		<div class="cat_bar">
			<h3 class="catbg">', $txt['move_topics_maintenance'], '</h3>
		</div>
		<div class="windowbg2">
			<div class="content">
				<form action="', $scripturl, '?action=admin;area=maintain;sa=topics;activity=massmove" method="post" accept-charset="UTF-8">
					<p>';

	template_select_boards('id_board_from', $txt['move_topics_from']);

	template_select_boards('id_board_to', $txt['move_topics_to']);

	echo '
					</p>
					<input type="submit" value="', $txt['move_topics_now'], '" onclick="if (document.getElementById(\'id_board_from\').options[document.getElementById(\'id_board_from\').selectedIndex].disabled || document.getElementById(\'id_board_from\').options[document.getElementById(\'id_board_to\').selectedIndex].disabled) return false; var confirmText = \'', $txt['move_topics_confirm'] . '\'; return confirm(confirmText.replace(/%board_from%/, document.getElementById(\'id_board_from\').options[document.getElementById(\'id_board_from\').selectedIndex].text.replace(/^=+&gt;&nbsp;/, \'\')).replace(/%board_to%/, document.getElementById(\'id_board_to\').options[document.getElementById(\'id_board_to\').selectedIndex].text.replace(/^=+&gt;&nbsp;/, \'\')));" class="button_submit" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>
			</div>
		</div>
	</div>';
}

// Simple template for showing results of our optimization...
function template_optimize()
{
	global $context, $settings, $txt, $scripturl;

	echo '
	<div id="manage_maintenance">
		<div class="cat_bar">
			<h3 class="catbg">', $txt['maintain_optimize'], '</h3>
		</div>
		<div class="windowbg">
			<div class="content">
				<p>
					', $txt['database_numb_tables'], '<br />
					', $txt['database_optimize_attempt'], '<br />';

	// List each table being optimized...
	foreach ($context['optimized_tables'] as $table)
		echo '
					', sprintf($txt['database_optimizing'], $table['name'], $table['data_freed']), '<br />';

	// How did we go?
	echo '
					<br />', $context['num_tables_optimized'] == 0 ? $txt['database_already_optimized'] : $context['num_tables_optimized'] . ' ' . $txt['database_optimized'];

	echo '
				</p>
				<p><a href="', $scripturl, '?action=admin;area=maintain">', $txt['maintain_return'], '</a></p>
			</div>
		</div>
	</div>';
}

function template_convert_msgbody()
{
	global $context, $txt, $settings, $scripturl;

	echo '
	<div id="manage_maintenance">
		<div class="cat_bar">
			<h3 class="catbg">', $txt[$context['convert_to'] . '_title'], '</h3>
		</div>
		<div class="windowbg">
			<div class="content">
				<p>', $txt['body_checking_introduction'], '</p>';
	if (!empty($context['exceeding_messages']))
	{
		echo '
				<p class="noticebox">', $txt['exceeding_messages'], '
				<ul>
					<li>
					', implode('</li><li>', $context['exceeding_messages']), '
					</li>
				</ul>';
		if (!empty($context['exceeding_messages_morethan']))
			echo '
				<p>', $context['exceeding_messages_morethan'], '</p>';
	}
	else
		echo '
				<p class="infobox">', $txt['convert_to_text'], '</p>';

	echo '
				<form action="', $scripturl, '?action=admin;area=maintain;sa=database;activity=convertmsgbody" method="post" accept-charset="UTF-8">
				<hr class="hrcolor" />
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				<input type="hidden" name="', $context['admin-maint_token_var'], '" value="', $context['admin-maint_token'], '" />
				<input type="submit" name="do_conversion" value="', $txt['convert_proceed'], '" class="button_submit" />
				</form>
			</div>
		</div>
	</div>';
}
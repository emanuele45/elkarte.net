/**
 * @name      ElkArte Forum
 * @copyright ElkArte Forum contributors
 * @license   BSD http://opensource.org/licenses/BSD-3-Clause
 *
 * This software is a derived product, based on:
 *
 * Simple Machines Forum (SMF)
 * copyright:	2011 Simple Machines (http://www.simplemachines.org)
 * license:		BSD, See included LICENSE.TXT for terms and conditions.
 *
 * @version 1.0 Beta
 *
 * This file contains javascript associated with the posting and previewing
 */

/**
 * A q&d wrapper function to call the correct preview function
 * @todo could make this a class to be cleaner
 */
// These are variables the xml response is going to need
var bPost;
function previewControl()
{
	if (is_ff)
	{
		// Firefox doesn't render <marquee> that have been put it using javascript
		if (document.forms[form_name].elements[post_box_name].value.indexOf('[move]') !== -1)
			return submitThisOnce(document.forms[form_name]);
	}

	// Lets make a background preview request
	bPost = false;

	// call the needed preview function
	switch(preview_area)
	{
		case 'pm':
			previewPM();
			break;
		case 'news':
			previewNews();
			break;
		case 'post':
			bPost = true;
			previewPost();
			break;
	}
	return false;
}

/**
 * Used to preview a post
 */
function previewPost()
{
	// @todo Currently not sending poll options and option checkboxes.
	var textFields = [
		'subject', post_box_name, elk_session_var, 'icon', 'guestname', 'email', 'evtitle', 'question', 'topic'
	];
	var numericFields = [
		'board', 'topic', 'last_msg',
		'eventid', 'calendar', 'year', 'month', 'day',
		'poll_max_votes', 'poll_expire', 'poll_change_vote', 'poll_hide'
	];
	var checkboxFields = [
		'ns'
	];

	// Get the values from the form
	var x = [];
	x = getFields(textFields, numericFields, checkboxFields, form_name);

	sendXMLDocument(elk_prepareScriptUrl(elk_scripturl) + 'action=post2' + (current_board ? ';board=' + current_board : '') + (make_poll ? ';poll' : '') + ';preview;' + elk_session_var + '=' + elk_session_id + ';xml', x.join('&'), onDocSent);

	// Show the preview section and load it with "pending results" text, onDocSent will finish things off
	document.getElementById('preview_section').style.display = '';
	document.getElementById('preview_subject').innerHTML = txt_preview_title;
	document.getElementById('preview_body').innerHTML = txt_preview_fetch;

	return false;
}

/**
 * Used to preview a PM
 */
function previewPM()
{
	// define what we want to get from the form
	var textFields = [
		'subject', post_box_name, 'to', 'bcc'
	];
	var numericFields = [
		'recipient_to[]', 'recipient_bcc[]'
	];
	var checkboxFields = [
		'outbox'
	];

	// And go get them
	var x = [];
	x = getFields(textFields, numericFields, checkboxFields, form_name);

	// Send in document for previewing
	sendXMLDocument(elk_prepareScriptUrl(elk_scripturl) + 'action=pm;sa=send2;preview;xml', x.join('&'), onDocSent);

	// Show the preview section and load it with "pending results" text, onDocSent will finish things off
	document.getElementById('preview_section').style.display = '';
	document.getElementById('preview_subject').innerHTML = txt_preview_title;
	document.getElementById('preview_body').innerHTML = txt_preview_fetch;

	return false;
}

/**
 * Used to preview a News item
 */
function previewNews()
{
	// define what we want to get from the form
	var textFields = [
		'subject', post_box_name
	];
	var numericFields = [
	];
	var checkboxFields = [
		'send_html', 'send_pm'
	];

	// And go get them
	var x = [];
	x = getFields(textFields, numericFields, checkboxFields, form_name);
	x[x.length] = 'item=newsletterpreview';

	// Send in document for previewing
	sendXMLDocument(elk_prepareScriptUrl(elk_scripturl) + 'action=xmlpreview;xml', x.join('&'), onDocSent);

	// Show the preview section and load it with "pending results" text, onDocSent will finish things off
	document.getElementById('preview_section').style.display = '';
	document.getElementById('preview_subject').innerHTML = txt_preview_title;
	document.getElementById('preview_body').innerHTML = txt_preview_fetch;

	return false;
}

/**
 * Gets the form data for the selected fields so they can be posted via ajax
 *
 * @param {array} textFields
 * @param {array} numericFields
 * @param {array} checkboxFields
 * @param {string} form_name
 */
function getFields(textFields, numericFields, checkboxFields, form_name)
{
	var fields = [],
		i = 0,
		n = 0;

	// Get all of the text fields
	for (i = 0, n = textFields.length; i < n; i++)
	{
		if (textFields[i] in document.forms[form_name])
		{
			// Handle the editor.
			if (textFields[i] == post_box_name && $('#' + post_box_name).data('sceditor') !== undefined)
			{
				fields[fields.length] = textFields[i] + '=' + $('#' + post_box_name).data('sceditor').getText().replace(/&#/g, '&#38;#').php_to8bit().php_urlencode();
				fields[fields.length] = 'message_mode=' + $("#message").data("sceditor").inSourceMode();
			}
			else
				fields[fields.length] = textFields[i] + '=' + document.forms[form_name][textFields[i]].value.replace(/&#/g, '&#38;#').php_to8bit().php_urlencode();
		}
	}

	// All of the numeric fields
	for (i = 0, n = numericFields.length; i < n; i++)
	{
		if (numericFields[i] in document.forms[form_name])
		{
			if ('value' in document.forms[form_name][numericFields[i]])
				fields[fields.length] = numericFields[i] + '=' + parseInt(document.forms[form_name].elements[numericFields[i]].value);
			else
			{
				for (var j = 0, num = document.forms[form_name][numericFields[i]].length; j < num; j++)
					fields[fields.length] = numericFields[i] + '=' + parseInt(document.forms[form_name].elements[numericFields[i]][j].value);
			}
		}
	}

	// And the checkboxes
	for (i = 0, n = checkboxFields.length; i < n; i++)
	{
		if (checkboxFields[i] in document.forms[form_name] && document.forms[form_name].elements[checkboxFields[i]].checked)
			fields[fields.length] = checkboxFields[i] + '=' + document.forms[form_name].elements[checkboxFields[i]].value;
	}

	// And some security
	fields[fields.length] = elk_session_var + '=' + elk_session_id;

	return fields;
}

/**
 * Callback function of the XMLhttp request
 *
 * @param {object} XMLDoc
 */
function onDocSent(XMLDoc)
{
	var i = 0,
		n = 0,
		numErrors = 0,
		numCaptions = 0;

	if (!XMLDoc || !XMLDoc.getElementsByTagName('elk')[0])
	{
		document.forms[form_name].preview.onclick = function() {return true;};
		document.forms[form_name].preview.click();
		return true;
	}

	// Read the preview section data from the xml response
	var preview = XMLDoc.getElementsByTagName('elk')[0].getElementsByTagName('preview')[0];

	// Load in the subject
	document.getElementById('preview_subject').innerHTML = preview.getElementsByTagName('subject')[0].firstChild.nodeValue;

	// Load in the body
	var bodyText = '';
	for (i = 0, n = preview.getElementsByTagName('body')[0].childNodes.length; i < n; i++)
		bodyText += preview.getElementsByTagName('body')[0].childNodes[i].nodeValue;

	document.getElementById('preview_body').innerHTML = bodyText;
	document.getElementById('preview_body').className = 'post';

	// Show a list of errors (if any).
	var errors = XMLDoc.getElementsByTagName('elk')[0].getElementsByTagName('errors')[0],
		errorList = '',
		errorCode = '',
		error_area = 'post_error',
		error_list = error_area + '_list';

	// @todo: this should stay together with the rest of the error handling or
	// should use errorbox_handler (at the moment it cannot be used because is not enough generic)
	for (i = 0, numErrors = errors.getElementsByTagName('error').length; i < numErrors; i++)
	{
		errorCode = errors.getElementsByTagName('error')[i].attributes.getNamedItem("code").value;
		errorList += '<li id="' + error_area + '_' + errorCode + '" class="error">' + errors.getElementsByTagName('error')[i].firstChild.nodeValue + '</li>';
	}

	oError_box = $(document.getElementById(error_area));
	if ($.trim(oError_box.children(error_list).html()) === '')
		oError_box.append("<ul id='" + error_list + "'></ul>");

	// Add the error it and show it
	if (numErrors === 0)
		oError_box.css("display", "none");
	else
	{
		document.getElementById(error_list).innerHTML = errorList;
		oError_box.css("display", "");
		oError_box.attr('class', parseInt(errors.getAttribute('serious')) === 0 ? 'warningbox' : 'errorbox');
	}

	// Show a warning if the topic has been locked.
	if (bPost)
		document.getElementById('lock_warning').style.display = errors.getAttribute('topic_locked') === 1 ? '' : 'none';

	// Adjust the color of captions if the given data is erroneous.
	var captions = errors.getElementsByTagName('caption');
	for (i = 0, numCaptions = errors.getElementsByTagName('caption').length; i < numCaptions; i++)
	{
		if (document.getElementById('caption_' + captions[i].getAttribute('name')))
			document.getElementById('caption_' + captions[i].getAttribute('name')).className = captions[i].getAttribute('class');
	}

	if (errors.getElementsByTagName('post_error').length === 1)
		document.forms[form_name][post_box_name].style.border = '1px solid red';
	else if (document.forms[form_name][post_box_name].style.borderColor === 'red' || document.forms[form_name][post_box_name].style.borderColor === 'red red red red')
	{
		if ('runtimeStyle' in document.forms[form_name][post_box_name])
			document.forms[form_name][post_box_name].style.borderColor = '';
		else
			document.forms[form_name][post_box_name].style.border = null;
	}

	// If this is a post preview, then we have some extra work to do
	if (bPost)
	{
		// Set the new last message id.
		if ('last_msg' in document.forms[form_name])
			document.forms[form_name].last_msg.value = XMLDoc.getElementsByTagName('elk')[0].getElementsByTagName('last_msg')[0].firstChild.nodeValue;

		// Remove the new image from old-new replies!
		for (i = 0; i < new_replies.length; i++)
			document.getElementById('image_new_' + new_replies[i]).style.display = 'none';
		new_replies = [];

		var ignored_replies = [],
			ignoring = null,
			newPosts = XMLDoc.getElementsByTagName('elk')[0].getElementsByTagName('new_posts')[0] ? XMLDoc.getElementsByTagName('elk')[0].getElementsByTagName('new_posts')[0].getElementsByTagName('post') : {length: 0},
			numNewPosts = newPosts.length;

		if (numNewPosts !== 0)
		{
			var newPostsHTML = '<span id="new_replies"><' + '/span>';
			for (i = 0; i < numNewPosts; i++)
			{
				new_replies[new_replies.length] = newPosts[i].getAttribute("id");

				ignoring = false;
				if (newPosts[i].getElementsByTagName("is_ignored")[0].firstChild.nodeValue !== 0)
					ignored_replies[ignored_replies.length] = ignoring = newPosts[i].getAttribute("id");

				newPostsHTML += '<div class="windowbg' + (++reply_counter % 2 === 0 ? '2' : '') + ' core_posts"><div class="content" id="msg' + newPosts[i].getAttribute("id") + '"><div class="floatleft"><h5>' + txt_posted_by + ': ' + newPosts[i].getElementsByTagName("poster")[0].firstChild.nodeValue + '</h5><span class="smalltext">&#171;&nbsp;<strong>' + txt_on + ':</strong> ' + newPosts[i].getElementsByTagName("time")[0].firstChild.nodeValue + '&nbsp;&#187;</span> <span class="new_posts" id="image_new_' + newPosts[i].getAttribute("id") + '">' + txt_new + '</span></div>';

				if (can_quote)
					newPostsHTML += '<ul class="quickbuttons" id="msg_' + newPosts[i].getAttribute('id') + '_quote"><li class="listlevel1"><a href="#postmodify" onclick="return insertQuoteFast(' + newPosts[i].getAttribute('id') + ');" class="linklevel1 quote_button">' + txt_bbc_quote + '</a></li></ul>';

				newPostsHTML += '<br class="clear" />';

				if (ignoring)
					newPostsHTML += '<div id="msg_' + newPosts[i].getAttribute("id") + '_ignored_prompt" class="smalltext">' + txt_ignoring_user + '<a href="#" id="msg_' + newPosts[i].getAttribute("id") + '_ignored_link" style="display: none;">' + show_ignore_user_post + '</a></div>';

				newPostsHTML += '<div class="list_posts smalltext" id="msg_' + newPosts[i].getAttribute("id") + '_body">' + newPosts[i].getElementsByTagName("message")[0].firstChild.nodeValue + '<' + '/div></div></div>';
			}
			setOuterHTML(document.getElementById('new_replies'), newPostsHTML);
		}

		var numIgnoredReplies = ignored_replies.length;
		if (numIgnoredReplies !== 0)
		{
			for (i = 0; i < numIgnoredReplies; i++)
			{
				aIgnoreToggles[ignored_replies[i]] = new elk_Toggle({
					bToggleEnabled: true,
					bCurrentlyCollapsed: true,
					aSwappableContainers: [
						'msg_' + ignored_replies[i] + '_body',
						'msg_' + ignored_replies[i] + '_quote'
					],
					aSwapLinks: [
						{
							sId: 'msg_' + ignored_replies[i] + '_ignored_link',
							msgExpanded: '',
							msgCollapsed: show_ignore_user_post
						}
					]
				});
			}
		}
	}

	location.hash = '#' + 'preview_section';

	// Preview video links if the feature is available
	if ($.isFunction($.fn.linkifyvideo))
		$().linkifyvideo(oEmbedtext, 'preview_body');

	// Spoilers, Sweetie
	$('.spoilerheader').click(function(){
		$(this).next().children().slideToggle("fast");
	});
}

/**
 * Add additional poll option fields
 */
function addPollOption()
{
	if (pollOptionNum === 0)
	{
		for (var i = 0, n = document.forms[form_name].elements.length; i < n; i++)
			if (document.forms[form_name].elements[i].id.substr(0, 8) === 'options-')
			{
				pollOptionNum++;
				pollTabIndex = document.forms[form_name].elements[i].tabIndex;
			}
	}

	pollOptionNum++;
	pollOptionId++;
	pollTabIndex++;
	setOuterHTML(document.getElementById('pollMoreOptions'), '<li><label for="options-' + pollOptionId + '">' + txt_option + ' ' + pollOptionNum + '</label>: <input type="text" name="options[' + pollOptionId + ']" id="options-' + pollOptionId + '" value="" size="80" maxlength="255" tabindex="' + pollTabIndex + '" class="input_text" /></li><li id="pollMoreOptions"></li>');
}

/**
 * Add additional attachment selection boxes
 */
function addAttachment()
{
	allowed_attachments = allowed_attachments - 1;
	current_attachment = current_attachment + 1;
	if (allowed_attachments <= 0)
		return alert(txt_more_attachments_error);

	setOuterHTML(document.getElementById("moreAttachments"), '<dd class="smalltext"><input type="file" size="60" name="attachment[]" id="attachment' + current_attachment + '" class="input_file" /> (<a href="javascript:void(0);" onclick="cleanFileInput(\'attachment' + current_attachment + '\');">' + txt_clean_attach + '<\/a>)' + '<\/dd><dd class="smalltext" id="moreAttachments"><a href="#" onclick="addAttachment(); return false;">(' + txt_more_attachments + ')</a></dd>');

	return true;
}

/**
 * A function used to clear the attachments on post page.  For security reasons
 * browsers don't let you set the value of a file input, even to an empty string
 * so this work around lets the user clear a choice.
 *
 * @param {type} idElement
 * @returns {undefined}
 */
function cleanFileInput(idElement)
{
	var oElement = $('#' + idElement);

	// Wrap the element in its own form, then reset the wrapper form
	oElement.wrap('<form>').closest('form').get(0).reset();
    oElement.unwrap();
}

/**
 * Insert a quote to the editor via ajax
 *
 * @param {string} messageid
 */
function insertQuoteFast(messageid)
{
	getXMLDocument(elk_prepareScriptUrl(elk_scripturl) + 'action=quotefast;quote=' + messageid + ';xml;pb=' + post_box_name + ';mode=0', onDocReceived);

	return true;
}

/**
 * callback for the quotefast function
 *
 * @param {object} XMLDoc
 */
function onDocReceived(XMLDoc)
{
	var text = '';

	for (var i = 0, n = XMLDoc.getElementsByTagName('quote')[0].childNodes.length; i < n; i++)
		text += XMLDoc.getElementsByTagName('quote')[0].childNodes[i].nodeValue;

	$('#' + post_box_name).data("sceditor").insert(text);

	ajax_indicator(false);
}

/**
 * Insert text in to the editor
 *
 * @param {string} text
 */
function onReceiveOpener(text)
{
	$('#' + post_box_name).data("sceditor").insert(text);
}

/**
 * The actual message icon selector, shows the chosen icon on the post screen
 */
function showimage()
{
	document.images.icons.src = icon_urls[document.forms.postmodify.icon.options[document.forms.postmodify.icon.selectedIndex].value];
}

/**
 * When using Go Back due to fatal_error, allows the form to be re-submitted with change
 * Done as a pageshow event listener for FF only
 */
function reActivate()
{
	document.forms.postmodify.message.readOnly = false;
}
/*
 Administration Javascript for MainWP Spinner Extention
 */
jQuery( document ).ready(function ($) {
	jQuery( '#spinner_infobox-spin' ).insertBefore( '#mainwp-tabs' );
	jQuery( '#spinner_errorbox-spin' ).insertBefore( '#mainwp-tabs' );

	mainwpspin_spin_article = function () {
		//         jQuery(this).attr('disabled', 'disabled');
		//            jQuery(this).next('.spin_loading').show();
		var fields = jQuery( '#mainwp-spin-meta-box' ).find( 'input[type=hidden], input[type=text], input[type=checkbox]:checked, select, textarea' );
		var send_fields = {};
		for (i = 0; i < fields.size(); i++) {
			field = jQuery( fields[i] );
			if ( ! field.attr( 'disabled' ) && field.attr( 'name' )) {
				var value = field.val();
				var name = field.attr( 'name' );
				if (name == 'mainwpspin_nonce') {
					name = 'nonce';
				}
				send_fields[name] = value;
			}
		}
		send_fields['action'] = "spin_post";
		send_fields['post_id'] = jQuery( '#post_ID' ).val();
		jQuery.post(ajaxurl, send_fields, function (obj) {
			jQuery( '#post_spin_article' ).removeAttr( 'disabled' );
			jQuery( '#post_spin_article' ).next( '.spin_loading' ).hide();
			if (obj.success == 0) {
				mainwpspin_showError( '#spinner_meta_errorbox-spin', obj.text );
				return;
			} else {
				mainwpspin_showInformation( '#spinner_meta_infobox-spin', "Spin successful." );
			}
			if (tinyMCE.activeEditor) {
				tinyMCE.activeEditor.setContent( mainwpspin_highlight_spin_nested( obj.post.post_content ) );
			} else {
				jQuery( '#content' ).val( obj.post.post_content );
			}
			jQuery( '#title' ).val( obj.post.post_title );
			jQuery.post(ajaxurl, {
				action: 'sample-permalink',
				post_id: jQuery( '#post_ID' ).val(),
				new_slug: obj.post.post_name,
				new_title: obj.post.post_title,
				samplepermalinknonce: $( '#samplepermalinknonce' ).val()
				}, function (data) {
					$( '#edit-slug-box' ).html( data );
					//real_slug.attr('value', new_slug);
					$( '#view-post-btn' ).show();
				});
		}, 'json');
		return false;
	};

	jQuery( '#post_spin_article' ).click(function () {
		jQuery( '#spinner_meta_errorbox-spin' ).hide();
		jQuery( '#spinner_meta_infobox-spin' ).hide();
		jQuery( this ).attr( 'disabled', 'disabled' );
		jQuery( this ).next( '.spin_loading' ).show();
		if (typeof autosave_disable_buttons !== 'undefined') {
			mainwpspin_autosave_and_spin();
		} else {
			// fix for compatible with WP 3.9
			//console.log(autosave);
			//console.log(window.wp.autosave);
			window.wp.autosave.server.triggerSave();
			setTimeout(function () {
							mainwpspin_spin_article();
			}, 5000);
		}
		return false;
	});

	mainwpspin_autosave_and_spin = function () {
		// (bool) is rich editor enabled and active
		blockSave = true;
		var rich = (typeof tinymce != "undefined") && tinymce.activeEditor && ! tinymce.activeEditor.isHidden(),
				post_data, doAutoSave, ed, origStatus, successCallback;
		autosave_disable_buttons();
		post_data = {
			action: "autosave",
			post_ID: jQuery( "#post_ID" ).val() || 0,
			post_id: jQuery( "#post_ID" ).val() || 0,
			autosavenonce: jQuery( '#autosavenonce' ).val(),
			post_type: jQuery( '#post_type' ).val() || "",
			autosave: true
		};
		jQuery( '.tags-input' ).each(function () {
			post_data[this.name] = this.value;
		});
		// We always send the ajax request in order to keep the post lock fresh.
		// This (bool) tells whether or not to write the post to the DB during the ajax request.
		doAutoSave = true;
		// No autosave while thickbox is open (media buttons)
		if (jQuery( "#TB_window" ).css( 'display' ) == 'block') {
			doAutoSave = false;
		}
		/* Gotta do this up here so we can check the length when tinymce is in use */
		if (rich && doAutoSave) {
			ed = tinymce.activeEditor;
			// Don't run while the tinymce spellcheck is on. It resets all found words.
			if (ed.plugins.spellchecker && ed.plugins.spellchecker.active) {
				doAutoSave = false;
			} else {
				if ( ('mce_fullscreen' == ed.id) || ('wp_mce_fullscreen' == ed.id) ) {
					tinymce.get( 'content' ).setContent( ed.getContent( {format: 'raw'} ), {format: 'raw'} );
				}
				tinymce.triggerSave();
			}
		}
		if (fullscreen && fullscreen.settings.visible) {
			post_data["post_title"] = jQuery( '#wp-fullscreen-title' ).val() || '';
			post_data["content"] = jQuery( "#wp_mce_fullscreen" ).val() || '';
		} else {
			post_data["post_title"] = jQuery( "#title" ).val() || '';
			post_data["content"] = jQuery( "#content" ).val() || '';
		}
		if (jQuery( '#post_name' ).val()) {
			post_data["post_name"] = jQuery( '#post_name' ).val(); }
		// Nothing to save or no change.
		if ((post_data["post_title"].length == 0 && post_data["content"].length == 0) || post_data["post_title"] + post_data["content"] == autosaveLast) {
			doAutoSave = false;
		}
		origStatus = jQuery( '#original_post_status' ).val();
		goodcats = ([]);
		jQuery( "[name='post_category[]']:checked" ).each(function (i) {
			goodcats.push( this.value );
		});
		post_data["catslist"] = goodcats.join( "," );
		if (jQuery( "#comment_status" ).prop( "checked" )) {
			post_data["comment_status"] = 'open'; }
		if (jQuery( "#ping_status" ).prop( "checked" )) {
			post_data["ping_status"] = 'open'; }
		if (jQuery( "#excerpt" ).size()) {
			post_data["excerpt"] = jQuery( "#excerpt" ).val(); }
		if (jQuery( "#post_author" ).size()) {
			post_data["post_author"] = jQuery( "#post_author" ).val(); }
		if (jQuery( "#parent_id" ).val()) {
			post_data["parent_id"] = jQuery( "#parent_id" ).val(); }
		post_data["user_ID"] = jQuery( "#user-id" ).val();
		if (jQuery( '#auto_draft' ).val() == '1') {
			post_data["auto_draft"] = '1'; }
		if (doAutoSave) {
			autosaveLast = post_data["post_title"] + post_data["content"];
			jQuery( document ).triggerHandler( 'wpcountwords', [post_data["content"]] );
		} else {
			post_data['autosave'] = 0;
		}
		if (post_data["auto_draft"] == '1') {
			successCallback = autosave_saved_new; // new post
		} else {
			successCallback = autosave_saved; // pre-existing post
		}

		autosaveOldMessage = jQuery( '#autosave' ).html();
		jQuery.ajax({
			data: post_data,
			beforeSend: doAutoSave ? autosave_loading : null,
			type: "POST",
			url: ajaxurl,
			success: successCallback
		}).done(function () {
			mainwpspin_spin_article();
		});
	};

	jQuery( '#test_spin' ).click(function () {
		jQuery( this ).attr( 'disabled', 'disabled' );
		jQuery( '#spinner_test-spin-status' ).text( "Testing ..." );
		jQuery( this ).next( '.spin_loading' ).show();
		jQuery.post(ajaxurl, {
			action: 'test_spin',
			sp_spinner: jQuery( '#sp_spinner' ).val()
			}, function (data) {
				jQuery( '#test_spin' ).removeAttr( 'disabled' );
				jQuery( '#test_spin' ).next( '.spin_loading' ).hide();
				jQuery( '#spinner_test-spin-status' ).text( "" );
				if (data.error) {
					mainwpspin_hideBox( '#spinner_infobox-spin' );
					mainwpspin_showError( '#spinner_errorbox-spin', data.text );
					return;
				}
				mainwpspin_showInformation( '#spinner_infobox-spin', data.text );
				mainwpspin_hideBox( '#spinner_errorbox-spin' );
			}, 'json');
	});

});


function spin_auto_save()
{
	if (jQuery( '#mainwp-option-form' ).data( 'changed' ) == 1) {
		var fields = jQuery( '#mainwp-option-form' ).find( 'input[type=hidden], input[type=text], input[type=password], input[type=checkbox]:checked, input[type=radio]:checked, select, textarea' );
		var send_fields = {};
		for (i = 0; i < fields.size(); i++) {
			field = jQuery( fields[i] );
			if ( ! field.attr( 'disabled' ) && field.attr( 'name' )) {
				var value = field.val();
				var name = field.attr( 'name' );
				if (name.match( /\[.*?\]/ )) {
					var k = parseInt( name.match( /\[(.*?)\]/ )[1] );
					name = name.replace( /\[.*?\]/, '' );
					if (typeof (send_fields[name]) == 'undefined') {
						send_fields[name] = new Array(); }
					if ( ! k) {
						k = 0; }
					if (k == 0 && send_fields[name].length > 0) {
						k = send_fields[name].length;
					}
					send_fields[name][k] = value;
				} else {
					send_fields[name] = value;
				}
			}
		}
		if (jQuery( '#mainwp-option-form' ).data( 'changed' ) == 1) {
			jQuery( '#spinner_option-save-status' ).text( "Saving..." );
			send_fields['action'] = 'option_auto_save';
			jQuery.post(ajaxurl, send_fields, function (data) {
				jQuery( '#spinner_option-save-status' ).text( data );
				jQuery( '#mainwp-option-form' ).data( 'changed', 0 );
			});
		}
	}
	setTimeout(function () {
		spin_auto_save();
	}, 5000);
}

function mainwpspin_highlight_spin(content)
{
	return content.replace( /(\{.*?\})/gim, '<span class="spin_text">$1</span>' );
}

function mainwpspin_unhighlight_spin(content)
{
	return content.replace( /<span *class=[''"]spin_text_[0-9]*[''"]>(.*?)<\/span>/gim, '$1' );
}

function mainwpspin_highlight_spin_nested(content)
{
	var lchar = '{', rchar = '}', addStr = '', pos = -1, deep = 0;
	while (pos++ < content.length) {
		if (content.substr( pos, lchar.length ) == lchar) {
			addStr = '<span class="spin_text_' + deep + '">';
			content = content.substr( 0, pos ) + addStr + content.substr( pos, content.length );
			pos += addStr.length;
			deep++;
		} else if (content.substr( pos, rchar.length ) == rchar) {
			addStr = '</span>';
			content = content.substr( 0, pos + rchar.length ) + addStr + content.substr( pos + rchar.length, content.length );
			pos += addStr.length;
			deep--;
		}
	}

	content = content.replace( /\n/gi, "<br>" );
	content = content.replace( /&nbsp;/gi, " " );
	content = content.replace( /&nbsp/gi, " " );
	return content;
}

function mainwpspin_unhighlight_spin_nested(content)
{
	var lstr = '<span', rstr = '</span>', removeStr = '', pos = -1, p1 = 0;
	var checkArr = []; // checking array
	while (pos++ < content.length) {
		if (content.substr( pos, lstr.length ) == lstr) {
			var p1 = content.indexOf( '>', pos );
			if (p1 != -1) {
				removeStr = content.substr( pos, p1 - pos );
				if (removeStr.indexOf( '<span class="spin_text_' ) != -1) {
					content = content.substr( 0, pos ) + content.substr( p1 + 1 ); // position of '>' + 1
				} else {
					checkArr.push( 1 );
				}
			}
		} else if (content.substr( pos, rstr.length ) == rstr) {
			if (checkArr.length == 0) {
				content = content.substr( 0, pos ) + content.substr( pos + rstr.length );
			} else {
				checkArr.pop();
			}
		}
	}
	return content;
}

mainwpspin_hideBox = function (select)
{
	jQuery( select ).hide();
}

mainwpspin_showError = function (select, text)
{
	jQuery( select ).html( text );
	jQuery( select ).show();
	// automatically scroll to error message if it's not visible
	var scrolltop = jQuery( window ).scrollTop();
	var off = jQuery( select ).offset();
	if (scrolltop > (off.top - 140)) {
		jQuery( 'html, body' ).animate({
			scrollTop: off.top - 140
			}, 1000, function ()
			{
				mainwpspin_shake_element( select )
			});
	} else {
		mainwpspin_shake_element( select ); // shake the error message to get attention :)
	}
}

mainwpspin_showInformation = function (select, text)
{
	jQuery( select ).html( text );
	jQuery( select ).show();
	// automatically scroll to error message if it's not visible
	var scrolltop = jQuery( window ).scrollTop();
	var off = jQuery( select ).offset();
	if (scrolltop > (off.top - 140)) {
		jQuery( 'html, body' ).animate({
			scrollTop: off.top - 140
			}, 1000, function ()
			{
				mainwpspin_shake_element( select )
			});
	} else {
		mainwpspin_shake_element( select ); // shake the error message to get attention :)
	}
}

mainwpspin_shake_element = function (select)
{
	var pos = jQuery( select ).position();
	var type = jQuery( select ).css( 'position' );
	if (type == 'static') {
		jQuery( select ).css({
			position: 'relative'
		});
	}
	if (type == 'static' || type == 'relative') {
		pos.top = 0;
		pos.left = 0;
	}
	jQuery( select ).data( 'init-type', type );
	var shake = [
		[0, 5, 60],
		[0, 0, 60],
		[0, -5, 60],
		[0, 0, 60],
		[0, 2, 30],
		[0, 0, 30],
		[0, -2, 30],
		[0, 0, 30]
	];
	for (s = 0; s < shake.length; s++) {
		jQuery( select ).animate({
			top: pos.top + shake[s][0],
			left: pos.left + shake[s][1]
		}, shake[s][2], 'linear');
	}
}

jQuery( document ).ready(function ($) {
	jQuery( '.mainwp-show-tut' ).on('click', function () {
		jQuery( '.mainwp-spin-tut' ).hide();
		var num = jQuery( this ).attr( 'number' );
		console.log( num );
		jQuery( '.mainwp-spin-tut[number="' + num + '"]' ).show();
		mainwp_setCookie( 'spin_quick_tut_number', jQuery( this ).attr( 'number' ) );
		return false;
	});

	jQuery( '#mainwp-spin-quick-start-guide' ).on('click', function () {
		if (mainwp_getCookie( 'spin_quick_guide' ) == 'on') {
			mainwp_setCookie( 'spin_quick_guide', '' ); } else {
			mainwp_setCookie( 'spin_quick_guide', 'on' ); }
			spin_showhide_quick_guide();
			return false;
	});
	jQuery( '#mainwp-spin-tips-dismiss' ).on('click', function () {
		mainwp_setCookie( 'spin_quick_guide', '' );
		spin_showhide_quick_guide();
		return false;
	});

	spin_showhide_quick_guide();

	jQuery( '#mainwp-spin-dashboard-tips-dismiss' ).on('click', function () {
		$( this ).closest( '#mainwp-spin-tips' ).hide();
		mainwp_setCookie( 'spin_dashboard_notice', 'hide', 2 );
		return false;
	});

});

spin_showhide_quick_guide = function (show, tut) {
	var show = mainwp_getCookie( 'spin_quick_guide' );
	var tut = mainwp_getCookie( 'spin_quick_tut_number' );

	if (show == 'on') {
		jQuery( '#mainwp-spin-tips' ).show();
		jQuery( '#mainwp-spin-quick-start-guide' ).hide();
		spin_showhide_quick_tut();
	} else {
		jQuery( '#mainwp-spin-tips' ).hide();
		jQuery( '#mainwp-spin-quick-start-guide' ).show();
	}

	if ('hide' == mainwp_getCookie( 'spin_dashboard_notice' )) {
		jQuery( '#mainwp-spin-dashboard-tips-dismiss' ).closest( '#mainwp-spin-tips' ).hide();
	}
}

spin_showhide_quick_tut = function () {
	var tut = mainwp_getCookie( 'spin_quick_tut_number' );
	jQuery( '.mainwp-spin-tut' ).hide();
	jQuery( '.mainwp-spin-tut[number="' + tut + '"]' ).show();
}



jQuery( document ).ready(function($) {
	mainwp_spinner_check_showhide_sections();

	$( '.mainwp_spinner_postbox .handlediv' ).live('click', function(){
		var pr = $( this ).parent();
		if (pr.hasClass( 'closed' )) {
			mainwp_spinner_set_showhide_section( pr, true ); } else { 			mainwp_spinner_set_showhide_section( pr, false ); }
	});
});

mainwp_spinner_set_showhide_section = function(obj, show) {
	var sec = obj.attr( 'section' );
	if (show) {
		obj.removeClass( 'closed' );
		mainwp_setCookie( 'mainwp_spinner_showhide_section_' + sec, 'show' );
	} else {
		obj.addClass( 'closed' );
		mainwp_setCookie( 'mainwp_spinner_showhide_section_' + sec, '' );
	}
}

mainwp_spinner_check_showhide_sections = function() {
	var pr, sec;
	jQuery( '.mainwp_spinner_postbox .handlediv' ).each(function() {
		pr = jQuery( this ).parent();
		sec = pr.attr( 'section' );
		if (mainwp_getCookie( 'mainwp_spinner_showhide_section_' + sec ) == 'show') {
			mainwp_spinner_set_showhide_section( pr, true );
		} else {
			mainwp_spinner_set_showhide_section( pr, false );
		}
	});
}

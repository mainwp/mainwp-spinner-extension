jQuery( document ).ready(function ($) {

	// Spin process
	mainwpspin_spin_article = function () {
		var fields = jQuery( '#mainwp-spinner-metabox' ).find( 'input[type=hidden], input[type=text], input[type=checkbox]:checked, select, textarea' );
		var send_fields = {};
		jQuery( '#mainwp-spinner-message-zone' ).html( '' ).hide();
		jQuery( '#mainwp-spinner-message-zone' ).removeClass( 'green yellow red' );

		jQuery( '#mainwp-spinner-message-zone' ).html( '<i class="notched circle loading icon"></i> ' + __( 'Working. Please wait...' ) ).show();

		for ( i = 0; i < fields.size(); i++ ) {
			field = jQuery( fields[i] );
			if ( ! field.attr( 'disabled' ) && field.attr( 'name' )) {
				var value = field.val();
				var name = field.attr( 'name' );
				if ( name == 'mainwpspin_nonce' ) {
					name = 'nonce';
				}
				send_fields[name] = value;
			}
		}

		send_fields['action'] = "mainwp_spin_post";
		send_fields['post_id'] = jQuery( '#post_ID' ).val();                
		send_fields['title'] = jQuery( '#title' ).val() || '';
                send_fields['excerpt'] = jQuery( '#excerpt' ).val() || '';
                
                if ( tinyMCE.activeEditor ) {
                        send_fields['content'] = tinyMCE.activeEditor.getContent();
                } else {                        
                        send_fields['content'] = jQuery( '#content' ).val() || ''; 
                }                
                
		jQuery.post( ajaxurl, send_fields, function ( obj ) {
			jQuery( '#post_spin_article' ).removeAttr( 'disabled' );
			jQuery( '#post_spin_article' ).next( '.spin_loading' ).hide();
			if ( obj.success == 0 ) {
				jQuery( '#mainwp-spinner-message-zone' ).html( obj.text ).show();
				jQuery( '#mainwp-spinner-message-zone' ).addClass( 'red' );
				return;
			} else {
				jQuery( '#mainwp-spinner-message-zone' ).html( 'Spinning completed succesfully.' ).show();
				jQuery( '#mainwp-spinner-message-zone' ).addClass( 'green' );
			}

			if ( tinyMCE.activeEditor ) {
				tinyMCE.activeEditor.setContent( mainwpspin_highlight_spin_nested( obj.post.post_content ) );
			} else {
				jQuery( '#content' ).val( obj.post.post_content );
			}
			jQuery( '#title' ).val( obj.post.post_title );
//			jQuery.post( ajaxurl, {
//				action: 'mainwp_spin_sample-permalink',
//				post_id: jQuery( '#post_ID' ).val(),
//				new_slug: obj.post.post_name,
//				new_title: obj.post.post_title,
//				samplepermalinknonce: $( '#samplepermalinknonce' ).val()
//				}, function (data) {
//					$( '#edit-slug-box' ).html( data );
//					$( '#view-post-btn' ).show();
//				});
		}, 'json');
		return false;
	};

	// Trigger the spin process
	jQuery( '#post_spin_article' ).click( function () {
		jQuery( '#mainwp-spinner-message-zone' ).html( '' ).hide();
		jQuery( '#mainwp-spinner-message-zone' ).removeClass( 'green yellow red' );
		jQuery( this ).attr( 'disabled', 'disabled' );
		jQuery( '#mainwp-spinner-message-zone' ).html( '<i class="notched circle loading icon"></i> ' + __ ( 'Working. Please wait...' ) ).show();
//		if ( typeof autosave_disable_buttons !== 'undefined' ) {
//			mainwpspin_autosave_and_spin();
//		} else {
//			window.wp.autosave.server.triggerSave();
//			setTimeout(function () {
				mainwpspin_spin_article();
//			}, 5000);
//		}
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

	// Trigger and execute the Spinner test process.
	jQuery( '#test_spin' ).click(function () {
		jQuery( '#mainwp-message-zone' ).html( '' ).hide();
		jQuery( '#mainwp-message-zone' ).removeClass( 'red yellow green' );

		jQuery( this ).attr( 'disabled', 'disabled' );
		jQuery( '#mainwp-message-zone' ).html( '<i class="notched circle loading icon"></i> ' + 'Testing the spinner settings. Please wait...' ).show();
		jQuery.post( ajaxurl, {
			action: 'test_spin',
			sp_spinner: jQuery( '#sp_spinner' ).val()
			}, function ( data ) {
				jQuery( '#test_spin' ).removeAttr( 'disabled' );
				jQuery( '#mainwp-message-zone' ).html( '' ).hide();
				if ( data.error ) {
					jQuery( '#mainwp-message-zone' ).html( '<i class="close icon"></i> ' + data.text ).show();
					jQuery( '#mainwp-message-zone' ).addClass( 'red' );
					return;
				}
				jQuery( '#mainwp-message-zone' ).html( '<i class="close icon"></i> ' + data.text ).show();
				jQuery( '#mainwp-message-zone' ).addClass( 'green' );
			}, 'json');
	});

});

// Highlight the spun content
function mainwpspin_highlight_spin( content ) {
	return content.replace( /(\{.*?\})/gim, '<span class="spin_text">$1</span>' );
}

function mainwpspin_unhighlight_spin( content ) {
	return content.replace( /<span *class=[''"]spin_text_[0-9]*[''"]>(.*?)<\/span>/gim, '$1' );
}

function mainwpspin_highlight_spin_nested( content ) {
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

function mainwpspin_unhighlight_spin_nested( content ) {
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

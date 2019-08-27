/**
 * MainWP Article Poster plugin
 */
( function () {
	jQuery( '#infobox-spin' ).insertBefore( '#poststuff' );
	jQuery( '#errorbox-spin' ).insertBefore( '#poststuff' );
	var DOM = tinymce.DOM;
	tinymce.create( 'tinymce.plugins.mainwparticle', {
		mceTout: 0,
		init: function (ed, url) {
			ed.onInit.add(function (ed) {
				ed.dom.loadCSS( url + '/css/content.css' );
				var content = ed.getContent();
				ed.setContent( mainwpspin_highlight_spin_nested( content ) );
			});
			ed.onSaveContent.add(function (ed, o) {
				//o.content = mainwpspin_unhighlight_spin(o.content);
				o.content = mainwpspin_unhighlight_spin_nested( o.content );
				return o;
			});
			ed.onKeyUp.add(function (ed, e) {
				// 221: }
				if (e.keyCode == 221) {
					var endId = tinymce.DOM.uniqueId();
					ed.execCommand( 'mceInsertContent', false, '<span id="' + endId + '"></span>' );
					var content = ed.getContent();
					content = mainwpspin_highlight_spin_nested( mainwpspin_unhighlight_spin_nested( content ) );
					ed.setContent( content );
					//horrible hack to put the cursor in the right spot
					ed.focus(); //give the editor focus
					var te = ed.dom.select( '#' + endId )[0];
					ed.selection.select( te ); //select the inserted element
					ed.selection.collapse( 0 ); //collapses the selection to the end of the range, so the cursor is after the inserted element
					ed.dom.remove( te ); //remove the temp id
				}
			});

			ed.addButton( 'mainwparticle', {
				title: 'Single Spin',
				image: url + '<i class="sync icon"></i>',
				onclick: function () {
					/*  var selection = ed.selection.getContent({format: 'text'});  */
					var selection = ed.selection.getContent( {format: 'raw'} );
					var fields = jQuery( '#mainwp-spin-meta-box' ).find( 'input[type=hidden], input[type=text], input[type=checkbox]:checked, select, textarea' );
					var send_fields = {};
					for (i = 0; i < fields.size(); i++) {
						field = jQuery( fields[i] );
						if ( ! field.attr( 'disabled' ) && field.attr( 'name' )) {
							var value = field.val();
							var name = field.attr( 'name' );
							if (name == 'mainwpspin_nonce') {
								name = 'nonce'; }
							send_fields[name] = value;
						}
					}
					send_fields['text'] = selection;
					send_fields['action'] = "spin_text";
					jQuery.post(ajaxurl, send_fields, function (obj) {
						if (obj.success == 0) {
							/* jQuery("div#poststuff").before('<div class="updated below-h2"><p>'+obj.text+'</p></div>');		 */
							jQuery( "#mainwp-message-zone" ).html( '<i class="close icon"></i> ' + obj.text  );
							setTimeout(function () {
								jQuery( "#mainwp-message-zone" ).fadeOut();
							}, 5000);
                 
							return;
						}
						//ed.selection.setContent(mainwpspin_highlight_spin(obj.spun_text));
						ed.selection.setContent( mainwpspin_highlight_spin_nested( obj.spun_text ) );
					}, 'json');
				}
			});
			jQuery( '#edButtonHTML' ).bind('click', function (obj) {
				var content = ed.getContent();
				jQuery( '#content' ).val( mainwpspin_unhighlight_spin( content ) );
			});
			jQuery( '#content-html' ).bind('click', function (obj) {
				var content = ed.getContent();
				jQuery( '#content' ).val( mainwpspin_unhighlight_spin_nested( content ) );
			});

			jQuery( '#content-tmce' ).bind('click', function (obj) {
				var content = ed.getContent();
				content = mainwpspin_highlight_spin_nested( mainwpspin_unhighlight_spin_nested( content ) );
				ed.setContent( content );
			});

			jQuery( '#edButtonPreview' ).bind('click', function (obj) {
				var content = ed.getContent();
				ed.setContent( mainwpspin_highlight_spin( content ) );
			});
		}
	});
	tinymce.PluginManager.add( 'mainwparticle', tinymce.plugins.mainwparticle );
})();

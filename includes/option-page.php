<?php

$sub_blog_nonce = wp_create_nonce( $this->plugin_handle . '-sub-blog-ajax' );
$template_nonce = wp_create_nonce( $this->plugin_handle . '-template-ajax' );

?>
<div class="mainwp_wrap-inside" >        
    <div class="clearfix"></div>    
	<?php if ( isset( $_REQUEST['message'] ) ) :  ?>
        <div class="updated">
            <p>
				<?php
				switch ( $_REQUEST['message'] ) {
					case '1':
						_e( 'Option Saved', 'mainwp-spinner' );
						break;
					case '3':
						_e( 'An error occured while trying to save', 'mainwp-spinner' );
						break;
				}
				?>
            </p>
        </div>
	<?php endif ?>  
	<?php
	$error = $mess = '';
	if ( $this->get_option( 'sp_enable' ) == 1 && $this->get_option( 'sp_error' ) == 1 ) {
		$error = __( $this->get_option( 'sp_error_message' ) );
	}
	if ( $this->get_option( 'sp_message' ) ) {
		$mess = __( $this->get_option( 'sp_message' ) );
	}
	?>
	<div  id="spinner_errorbox-spin" class="mainwp_info-box-red" <?php echo ! empty( $error ) ? ' style="display: block" ' : ''; ?>><?php echo $error; ?></div>         
	<div  id="spinner_infobox-spin" class="mainwp_info-box" <?php echo ! empty( $mess ) ? ' style="display: block" ' : ''; ?>><?php echo $mess; ?></div>    

    <form action=""   method="post" id="mainwp-option-form" enctype="multipart/form-data">
        <div class="settings">
            <div class="postbox mainwp_spinner_postbox" section="1">
				<div class="handlediv"><br /></div>
				<h3 class="hndle mainwp_box_title" id="1"><span><i class="fa fa-cog"></i> <?php _e( 'General Settings', 'mainwp-spinner' ) ?></span></h3>
                <div class="inside">
					<?php
					$this->create_option_field( 'sp_enable', __( 'Enable an Automatic Spinner','mainwp-spinner' ), 'select', 1, array( 'No', 'Yes' ) );
					$this->create_option_field( 'sp_spinner', __( 'Select Spinner','mainwp-spinner' ), 'select', 'bs', $this->spinners );
					$this->create_option_field( 'sp_spin_title', __( 'Auto-spin Article Title','mainwp-spinner' ), 'select', '0', array( 'No', 'Yes' ) );
					?>
                </div>
            </div>
            <div class="postbox mainwp_spinner_postbox" section="2">   
				<div class="handlediv"><br /></div>
				<h3 class="hndle mainwp_box_title"><span><i class="fa fa-cog"></i> <?php _e( 'The Best Spinner Settings','mainwp-spinner' ) ?></span></h3>
                <div class="inside">
					<?php
					$this->create_option_field( 'bs_email_address', __( 'Email Address','mainwp-spinner' ), 'text' );
					$this->create_option_field( 'bs_password', __( 'Password','mainwp-spinner' ), 'password' );
					$this->create_option_field( 'bs_max_synonyms', __( 'Maximum Synonyms per Term','mainwp-spinner' ), 'select', 2, array( 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 ) );
					$this->create_option_field( 'bs_quality', __( 'Replacement Quality','mainwp-spinner' ), 'select', 1, array( 1, 2, 3 ), __( '1 - Most Changes, 3 - Fewest Changes','mainwp-spinner' ) );
					$this->create_option_field( 'bs_exclude_words', __( 'Words not to be Changed','mainwp-spinner' ), 'text', '', '', __( 'Separate each word with comma','mainwp-spinner' ) );
					?>
                    <p><a href="http://thebestspinner.com/">Order The Best Spinner</a></p>
                </div>
            </div>
            <div class="postbox mainwp_spinner_postbox" section="3">  
				<div class="handlediv"><br /></div>
				<h3 class="hndle mainwp_box_title" id="2"><span><i class="fa fa-cog"></i> <?php _e( 'Spinnerchief Settings','mainwp-spinner' ) ?></span></h3>
                <div class="inside">
					<?php
					$this->create_option_field( 'sc_ip_port', __( 'IP and Port','mainwp-spinner' ), 'text', 'api.spinnerchief.com:443' );
					$this->create_option_field( 'sc_api_key', __( 'Spinnerchief API Key','mainwp-spinner' ), 'text', '', '', '<a href="http://developer.spinnerchief.com/" target="_blank">Get Spinnerchief API Key</a>' );
					$this->create_option_field( 'sc_username', __( 'Username','mainwp-spinner' ), 'text' );
					$this->create_option_field( 'sc_password', __( 'Password','mainwp-spinner' ), 'password', '', '', '<a href="http://account.spinnerchief.com/" target="_blank">Register Spinnerchief account freely at here</a>' );
					$this->create_option_field( 'sc_spinfreq', __( 'Word spin frequency','mainwp-spinner' ), 'select', 4, array( 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6 ), __( '1 - Every word will be spun, 3 - 1/3 of all words will be spun, etc ... ','mainwp-spinner' ) );
					$this->create_option_field( 'sc_wordscount', __( 'Words amount in {}','mainwp-spinner' ), 'select', 5, array( 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10 ) );
					$this->create_option_field( 'sc_replace_type', __( 'Replace type','mainwp-spinner' ), 'select', 2, array( 0, 1, 2, 3, 4, 5 ), __( '0 - Replace phrase and word, 1 - Only replace phrase, 2 - Only replace word, 3 - Replace phrase first, then replace word till the article passes copyscape, 4 - Spin the article to most unique, 5 - Spin the article to most readable','mainwp-spinner' ) );
					$this->create_option_field( 'sc_wordquality', __( 'Word quality','mainwp-spinner' ), 'select', 0, array( 0 => 0, 1 => 1, 2 => 2, 3 => 3, 9 => 9 ), __( "0 - use Best Thesaurus to spin, 1 - use Better Thesaurus to spin, 2 - Use Good Thesaurus to spin,  3 - Use All Thesaurus to spin, 9 - Use Everyone's favorite to spin",'mainwp-spinner' ) );
					// always use original word (for respin)
					//$this->create_option_field('sc_use_original_word', __("Use orginal word when spin"), 'select', 1 , array("No", "Yes"));
					$this->create_option_field( 'sc_protect_html', __( 'Protect html','mainwp-spinner' ), 'select', 1, array( 'No', 'Yes' ), __( 'No -  Not spin the words in the html tags. Yes - Spin the words in html tags. ','mainwp-spinner' ) );
					$this->create_option_field( 'sc_use_synonyms_orderly', __( 'Use synonyms Orderly' ), 'checkbox', '', array( '1' => __( 'Uses the thesaurus randomly to spin.','mainwp-spinner' ) ), '', '', '', true );
					$this->create_option_field( 'sc_enable_grammar_ai', __( 'Enable Grammar AI' ), 'checkbox', '', array( '1' => __( 'Use grammar correction','mainwp-spinner' ) ), '', '', '', true );
					$this->create_option_field( 'sc_use_pos', __( 'Use POS analysis' ), 'checkbox', '', array( '1' => __( 'Use \'part of speech\' analysis','mainwp-spinner' ) ), '', '', '', true );
					$this->create_option_field( 'sc_tag_protect', __( 'Tags Protect' ), 'text', '', '', __( '[], (), <- -> , it will protect the text between [ and ], ( and ), <- and ->.','mainwp-spinner' ) );
					$this->create_option_field( 'sc_protect_words', __( 'Protect Words' ), 'text', '', '', __( 'Separate each word with comma','mainwp-spinner' ) );
					?>
                </div>
            </div>
            <div class="postbox mainwp_spinner_postbox" section="4">
				<div class="handlediv"><br /></div>
				<h3 class="hndle mainwp_box_title" id="3"><span><i class="fa fa-cog"></i> <?php _e( 'Chimp Rewriter Settings','mainwp-spinner' ) ?></span></h3>
                <div class="inside">
					<?php
					$this->create_option_field( 'cr_username', __( "User's Chimp Rewriter account email",'mainwp-spinner' ), 'text', '', '', __('Note that the user requires a Chimp Rewriter Pro subscription.','mainwp-spinner') );
					$this->create_option_field( 'cr_api_key', __( "User's API key",'mainwp-spinner' ), 'text', '', '', __('Get one on the  <a href="http://account.akturatech.com/" target="_blank">Chimp Rewriter User Management</a> page.','mainwp-spinner') );
					$this->create_option_field( 'cr_aid', __( 'Application ID','mainwp-spinner' ), 'text', '', '', __('Set this to a string (100 charachers or less) to identify your application to the server.','mainwp-spinner') );
					$this->create_option_field( 'cr_quality', __( 'Quality','mainwp-spinner' ), 'select', 4, array( 5 => 5, 4 => 4, 3 => 3, 2 => 2, 1 => 'All' ), __( 'Spin quality: 5 - Best, 4 - Better, 3 - Good, 2 - Average, 1 - All','mainwp-spinner' ) );
					$this->create_option_field( 'cr_posmatch', __( 'Required Part of Speech (POS) match for a spin','mainwp-spinner' ), 'select', 3, array( 4 => 4, 3 => 3, 2 => 2, 1 => 1, 0 => 0 ), __( '4 - FullSpin, 3 - Full, 2 - Loose, 1 - Extremely Loose, 0 - None.','mainwp-spinner' ) );
					$this->create_option_field( 'cr_protectedterms', __( 'Protected terms','mainwp-spinner' ), 'text', '', '', __( "Comma separated list of words or phrases to protect from spin i.e. 'my main keyword,my second keyword'",'mainwp-spinner' ) );
					$this->create_option_field( 'cr_rewrite', __( 'Rewrite','mainwp-spinner' ), 'select', 0, array( 0 => 'No', 1 => 'Yes' ), __( 'If set to 1, results are returned as a rewritten article with no Spintax. Otherwise, an article with Spintax is returned. Note that with rewrite as 1, the original word will always be removed.','mainwp-spinner' ) );
					$this->create_option_field( 'cr_phraseignorequality', __( 'Phrase ignore quality','mainwp-spinner' ), 'select', 0, array( 0 => 'No', 1 => 'Yes' ), __( 'If set to 1, quality is ignored when finding phrase replacements for phrases. This results in a huge amount of spin, but quality can vary.','mainwp-spinner' ) );

					$this->create_option_field( 'cr_spinwithinspin', __( 'Spin within spin','mainwp-spinner' ), 'select', 0, array( 0 => 'No', 1 => 'Yes' ), __( '1- if there is existing spin syntax in the content you send up, the API will spin any relevant content inside this syntax.  0 - the API will skip over this content and only spin outside of existing syntax.','mainwp-spinner' ) );
					$this->create_option_field( 'cr_spinwithinhtml', __( 'Spin within html','mainwp-spinner' ), 'select', 0, array( 0 => 'No', 1 => 'Yes' ), __( 'Spin inside HTML tags. This includes &lt;p&gt; tags, for example if you send up "&lt;p&gt;Here is a paragraph&lt;/p&gt;", nothing would be spun unless "Spin with in html" is 1.','mainwp-spinner' ) );
					// comment if do not support two this params
					$this->create_option_field( 'cr_applyinstantunique', __( 'Apply instant unique','mainwp-spinner' ), 'select', 0, array( 0 => 'No', 1 => 'Yes' ), __( '<strong>(Extra quota cost)</strong> Runs an instant unique pass over the article once spun. This replaces letters with characters that look like the original letter but have a different UTF8 value, passing copyscape 100% but garbling content to the search engines. It it recommended to protect keywords while using instant unique. Costs one extra query.','mainwp-spinner' ) );
					$this->create_option_field( 'cr_fullcharset', __( 'Full charset','mainwp-spinner' ), 'select', 0, array( 0 => 'No', 1 => 'Yes' ), __( "Only used if 'Apply instant unique' = 1. This causes IU to use the full character set which has a broader range of replacements.",'mainwp-spinner' ) );
					$this->create_option_field( 'cr_spintidy', __( 'Spin tidy','mainwp-spinner' ), 'select', 0, array( 0 => 'No', 1 => 'Yes' ), __( '<strong>(Extra quota cost)</strong> Runs a spin tidy pass over the result article. This fixes any common a/an type grammar mistakes and repeated words due to phrase spinning. Generally increases the quality of the article. Costs one extra query.','mainwp-spinner' ) );
					$this->create_option_field( 'cr_tagprotect', __( 'Tag protect','mainwp-spinner' ), 'text', '', '', __( "Protects anything between any syntax you define. Separate start and end syntax with a pipe '|' and separate multiple tags with a comma ','. For example, you could protect anything in square brackets by setting tagprotect=[|]. You could also protect anything between \"begin\" and \"end\" by setting tagprotect=[|],begin|end.",'mainwp-spinner' ) );
					// comment if do not support nested spin
					$this->create_option_field( 'cr_maxspindepth', __( 'Max spin depth','mainwp-spinner' ), 'select', 0, array( 0 => 'No', 1 => 'Yes' ), __( 'Define a maximum spin level depth in returned article. If set to 1, no nested spin will appear in the spun result. This paramater only matters if rewrite is false. Set to 0 or ignore for no limit on spin depth.','mainwp-spinner' ) );
					?>

                    <div class="spinner_option-list"><label>&nbsp;</label>
                        <div class="spinner_option-field">                                                 
							<?php echo __( "To protect any piece of text, simply wrap it with ###. For example, if you had a certain paragraph or code to protect, simply send \"An intro sentence. ###My protected stuff### and the rest of the article\". Anything inside the hashes will not be spun. Then just replace '###' with an empty string." ); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="postbox mainwp_spinner_postbox" section="5">
				<div class="handlediv"><br /></div>
				<h3 class="hndle mainwp_box_title" id="4"><span><i class="fa fa-cog"></i> <?php _e( 'WordAi Settings', 'mainwp-spinner' ) ?></span></h3>
                <div class="inside">
					<?php
					$this->create_option_field( 'wai_username', __( "User's WordAi account email" ), 'text', '', '', __('Your login email. Used to authenticate.', 'mainwp-spinner') );
					$this->create_option_field( 'wai_passwd', __( 'Password' ), 'password', '', '', __('Your password. You must either use this OR hash (below).', 'mainwp-spinner') );
					$this->create_option_field( 'wai_hash', __( 'Hash' ), 'text', '', '', __('It is a more secure way to send your password if you don\'t want to use your password. Get one on the  <a href="//wordai.com/users/api.php/" target="_blank">WordAi API</a> page.', 'mainwp-spinner') );
					$this->create_option_field( 'wai_quality', __( 'Quality' ), 'select', 0, array( 0 => 'Regular', 1 => 'Unique', 2 => 'Very Unique', 3 => 'Readable', 4 => 'Very Readable' ), __( "'Regular', 'Unique', 'Very Unique', 'Readable', or 'Very Readable' depending on how readable vs unique you want your spin to be.", 'mainwp-spinner' ) );
					$this->create_option_field( 'wai_nonested', __( 'No nested' ), 'select', 1, array( 0 => 'No', 1 => 'Yes' ), __( 'Set to "Yes" to turn off nested spinning (will help readability but hurt uniqueness).', 'mainwp-spinner' ) );
					$this->create_option_field( 'wai_sentence', __( 'Sentence' ), 'select', 1, array( 0 => 'No', 1 => 'Yes' ), __( 'Set to "Yes" if you want paragraph editing, where WordAi will add, remove, or switch around the order of sentences in a paragraph (recommended!).', 'mainwp-spinner' ) );
					$this->create_option_field( 'wai_paragraph', __( 'Paragraph' ), 'select', 1, array( 0 => 'No', 1 => 'Yes' ), __( 'Set to "Yes" if you want WordAi to do paragraph spinning - perfect for if you plan on using the same spintax many times.', 'mainwp-spinner' ) );
					$this->create_option_field( 'wai_returnspin', __( 'Return spin' ), 'select', 0, array( 0 => 'No', 1 => 'Yes' ), __( 'Set to "True" if you want to just receive a spun version of the article you provided. Otherwise it will return spintax.', 'mainwp-spinner' ) );
					$this->create_option_field( 'wai_nooriginal', __( 'No original' ), 'select', 0, array( 0 => 'No', 1 => 'Yes' ), __( 'Set to "Yes" if you do not want to include the original word in spintax (if synonyms are found). This is the same thing as creating a "Super Unique" spin.', 'mainwp-spinner' ) );
					$this->create_option_field( 'wai_protected', __( 'Protected' ), 'text', '', '', __( 'Comma separated protected words (do not put spaces inbetween the words).', 'mainwp-spinner' ) );
					$this->create_option_field( 'wai_synonyms', __( 'Synonyms' ), 'text', '', '', __( 'Add your own synonyms (Syntax: word1|synonym1,word two|first synonym 2|2nd syn). (comma separate the synonym sets and | separate the individuals synonyms).', 'mainwp-spinner' ) );
					?>
                </div>
            </div>
			
			<div class="postbox mainwp_spinner_postbox" section="6">
				<div class="handlediv"><br></div>
				<h3 class="hndle mainwp_box_title" id="5"><span><i class="fa fa-cog"></i> <?php _e( 'Spin Rewriter Settings','mainwp-spinner' ) ?></span></h3>
                <div class="inside">
					<?php
					$this->create_option_field( 'srw_email_address', __( 'Email Address','mainwp-spinner' ), 'text', '', '', __('The email address that you\'re using with Spin Rewriter.', 'mainwp-spinner') );
					$this->create_option_field( 'srw_api_key', __( 'API key','mainwp-spinner' ), 'text', '', '', __('Your unique API key. It can be found on <a href="https://www.spinrewriter.com/cp-api" target="_blank">this page</a>.', 'mainwp-spinner') );
					$this->create_option_field( 'srw_protected_terms', __( 'Protected terms','mainwp-spinner' ), 'textarea', '', '', __('A list of keywords and key phrases that you do NOT want to spin. One term per line.', 'mainwp-spinner') );
					$this->create_option_field( 'srw_auto_protected_terms', __( 'Auto protected terms','mainwp-spinner' ), 'select', 'false', array( 'false' => 'No', 'true' => 'Yes' ), __( 'Should Spin Rewriter automatically protect all Capitalized Words except for those in the title of your original text?', 'mainwp-spinner' ) );
					$this->create_option_field( 'srw_confidence_level', __( 'Confidence level','mainwp-spinner' ), 'select', 'medium', array( 'low' => 'Low', 'medium' => 'Medium', 'high' => 'High' ), __( 'The confidence level of the One-Click Rewrite process. <strong>low</strong>: largest number of synonyms for various words and phrases, least readable unique variations of text. <strong>medium</strong>: relatively reliable synonyms, usually well readable unique variations of text (default setting) <strong>high</strong>: only the most reliable synonyms, perfectly readable unique variations of text.', 'mainwp-spinner' ) );
					$this->create_option_field( 'srw_nested_spintax', __( 'Nested spintax','mainwp-spinner' ), 'select', 'false', array( 'false' => 'No', 'true' => 'Yes' ), __( 'Should Spin Rewriter also spin single words inside already spun phrases? If set to "Yes", the returned spun text might contain 2 levels of nested spinning syntax.', 'mainwp-spinner' ) );
					$this->create_option_field( 'srw_auto_sentences', __( 'Auto sentences','mainwp-spinner' ), 'select', 'false', array( 'false' => 'No', 'true' => 'Yes' ), __( 'Should Spin Rewriter spin complete sentences? If set to "Yes", some sentences will be replaced with a (shorter) spun variation.', 'mainwp-spinner' ) );
					$this->create_option_field( 'srw_auto_paragraphs', __( 'Auto paragraphs','mainwp-spinner' ), 'select', 'false', array( 'false' => 'No', 'true' => 'Yes' ), __( 'Should Spin Rewriter spin entire paragraphs? If set to "Yes", some paragraphs will be replaced with a (shorter) spun variation.', 'mainwp-spinner' ) );
					$this->create_option_field( 'srw_auto_new_paragraphs', __( 'Auto new paragraphs','mainwp-spinner' ), 'select', 'false', array( 'false' => 'No', 'true' => 'Yes' ), __( 'Should Spin Rewriter automatically write additional paragraphs on its own? If set to "Yes", the returned spun text will contain additional paragraphs.', 'mainwp-spinner' ) );
					$this->create_option_field( 'srw_auto_sentence_trees', __( 'Auto sentence trees','mainwp-spinner' ), 'select', 'false', array( 'false' => 'No', 'true' => 'Yes' ), __( 'Should Spin Rewriter automatically change the entire structure of phrases and sentences? If set to "Yes", Spin Rewriter will change "If he is hungry, John eats." to "John eats if he is hungry." and "John eats and drinks." to "John drinks and eats."', 'mainwp-spinner' ) );
					$this->create_option_field( 'srw_use_only_synonyms', __( 'Use only synonyms','mainwp-spinner' ), 'select', 'false', array( 'false' => 'No', 'true' => 'Yes' ), __( 'Should Spin Rewriter use only synonyms of the original words instead of the original words themselves? If set to "Yes", Spin Rewriter will never use any of the original words of phrases if there is a synonym available. This significantly improves the uniqueness of generated spun content.', 'mainwp-spinner' ) );
					$this->create_option_field( 'srw_reorder_paragraphs', __( 'Reorder paragraphs','mainwp-spinner' ), 'select', 'false', array( 'false' => 'No', 'true' => 'Yes' ), __( 'Should Spin Rewriter intelligently randomize the order of paragraphs and unordered lists when generating spun text? If set to "Yes", Spin Rewriter will randomize the order of paragraphs and lists where possible while preserving the readability of the text. This significantly improves the uniqueness of generated spun content.', 'mainwp-spinner' ) );
					?>
                </div>
            </div>
			
        </div>
        <input type="hidden" name="settings_page"   value="1" />           
        <div >
            <div id="spinner_option-save-status"></div>
            <div id="spinner_test-spin-status"></div>
			<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( $this->plugin_handle . '-option' ) ?>" />
			<input type="hidden" name="ajax_nonce" value="<?php echo wp_create_nonce( $this->plugin_handle . '-option-ajax' ) ?>" />
            <p class="submit"><input type="submit" value="Save Settings" class="button-primary" id="submit" name="submit">
                <input type="button" value="Test Selected Spinner" class="button" id="test_spin" name="test_spin">                                            
				<i class="fa fa-spinner fa-pulse spin_loading" style="display: none;"></i>
            </p>     
        </div>
    </form>
</div>

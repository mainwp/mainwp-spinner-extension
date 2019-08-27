<?php
$spinner = $this->get_option( 'sp_spinner' );
?>
<div id="mainwp-spinner-message-zone" class="ui message" style="display:none"></div>
<div id="mainwp-spinner-metabox" class="ui segment">
  <input type="hidden" name="mainwpspin_nonce" id="mainwpspin_nonce" value="<?php echo wp_create_nonce( $this->plugin_handle ) ?>" />
	<h4><?php _e( 'Spinner Options','mainwp-spinner' ) ?></h4>
	<?php if ( !isset( $spinner ) || empty( $spinner ) ) { ?>
    <div class="ui hidden divider"></div>
    <div class="ui hidden divider"></div>
    <h2 class="ui icon header">
      <i class="info circle icon"></i>
      <div class="content">
        <?php _e( 'Spining engine not set.', 'mainwp-spinner' ); ?>
        <div class="sub header"><?php _e('Please configure the prefered spinner on the extension settings page.','mainwp-spinner'); ?></div>
        <div class="ui hidden divider"></div>
        <a href="admin.php?page=Extensions-Mainwp-Spinner" class="ui green button"><?php _e('MainWP Spinner Settings.','mainwp-spinner'); ?></a>
      </div>
    </h2>
    <div class="ui hidden divider"></div>
    <div class="ui hidden divider"></div>
	<?php
  } else {
  	if ( 'bs' == $spinner ) {
  		if ( ! $post || get_post_meta( $post->ID, '_mainwp_spinner_saved_post_options', true ) !== 'yes' ) {
  			$bs_max_synonyms = $this->option['bs_max_synonyms'];
  			$bs_quality = $this->option['bs_quality'];
  			$bs_exclude_words = $this->option['bs_exclude_words'];
  			$sp_spin_title = $this->option['sp_spin_title'];
  		} else {
  			$bs_max_synonyms = get_post_meta( $post->ID, '_mainwp_spinner_bs_max_synonyms', true );
  			$bs_quality = get_post_meta( $post->ID, '_mainwp_spinner_bs_quality', true );
  			$bs_exclude_words = get_post_meta( $post->ID, '_mainwp_spinner_bs_exclude_words', true );
  			$sp_spin_title = get_post_meta( $post->ID, '_mainwp_spinner_sp_spin_title', true );
  		}
  		$this->create_option_field( 'post_bs_max_synonyms', __( 'Maximum Synonyms per Term','mainwp-spinner' ), 'select', $bs_max_synonyms, array( 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 ) );
  		$this->create_option_field( 'post_bs_quality', __( 'Replacement Quality','mainwp-spinner' ), 'select', $bs_quality, array( 1, 2, 3 ), __( '1 - Most Changes, 3 - Fewest Changes','mainwp-spinner' ) );
  		$this->create_option_field( 'post_bs_exclude_words', __( 'Words not to be Changed','mainwp-spinner' ), 'text', $bs_exclude_words, '', __( 'Separate each word with comma','mainwp-spinner' ) );
  		$this->create_option_field( 'post_sp_spin_title', __( 'Auto-spin Article Title','mainwp-spinner' ), 'select', $sp_spin_title, array( 'No', 'Yes' ) );

  	} else if ( 'sc' == $spinner ) {
  		if ( ! $post || get_post_meta( $post->ID, '_mainwp_spinner_saved_post_options', true ) !== 'yes' ) {
  			$sc_spinfreq = $this->option['sc_spinfreq'];
  			$sc_wordscount = $this->option['sc_wordscount'];
  			$sc_protect_html = $this->option['sc_protect_html'];
  			$sc_use_synonyms_orderly = $this->option['sc_use_synonyms_orderly'];
  			$sc_replace_type = $this->option['sc_replace_type'];
  			$sc_wordquality = $this->option['sc_wordquality'];
  			$sc_enable_grammar_ai = $this->option['sc_enable_grammar_ai'];
  			$sc_use_pos = $this->option['sc_use_pos'];
  			$sc_protect_words = $this->option['sc_protect_words'];
  			$sc_tag_protect = $this->option['sc_tag_protect'];
  			$sp_spin_title = $this->option['sp_spin_title'];
  		} else {
  			$sc_spinfreq = get_post_meta( $post->ID, '_mainwp_spinner_sc_spinfreq', true );
  			$sc_wordscount = get_post_meta( $post->ID, '_mainwp_spinner_sc_wordscount', true );
  			$sc_protect_html = get_post_meta( $post->ID, '_mainwp_spinner_sc_protect_html', true );
  			$sc_use_synonyms_orderly = get_post_meta( $post->ID, '_mainwp_spinner_sc_use_synonyms_orderly', true );
  			$sc_replace_type = get_post_meta( $post->ID, '_mainwp_spinner_sc_replace_type', true );
  			$sc_wordquality = get_post_meta( $post->ID, '_mainwp_spinner_sc_wordquality', true );
  			$sc_enable_grammar_ai = get_post_meta( $post->ID, '_mainwp_spinner_sc_enable_grammar_ai', true );
  			$sc_use_pos = get_post_meta( $post->ID, '_mainwp_spinner_sc_use_pos', true );
  			$sc_protect_words = get_post_meta( $post->ID, '_mainwp_spinner_sc_protect_words', true );
  			$sc_tag_protect = get_post_meta( $post->ID, '_mainwp_spinner_sc_tag_protect', true );
  			$sp_spin_title = get_post_meta( $post->ID, '_mainwp_spinner_sp_spin_title', true );
  		}
  		$this->create_option_field( 'post_sc_spinfreq', __( 'Word spin frequency','mainwp-spinner' ), 'select', $sc_spinfreq, array( 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6 ), __( '1 - Every word will be spun, 3 - 1/3 of all words will be spun, etc ... ','mainwp-spinner' ) );
  		$this->create_option_field( 'post_sc_wordscount', __( 'Words amount in {}','mainwp-spinner' ), 'select', $sc_wordscount, array( 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10 ) );
  		$this->create_option_field( 'post_sc_replace_type', __( 'Replace type','mainwp-spinner' ), 'select', $sc_replace_type, array( 0, 1, 2, 3, 4, 5 ), __( '0 - Replace phrase and word, 1 - Only replace phrase, 2 - Only replace word, 3 - Replace phrase first, then replace word till the article passes copyscape, 4 - Spin the article to most unique, 5 - Spin the article to most readable','mainwp-spinner' ) );
  		$this->create_option_field( 'post_sc_wordquality', __( 'Word quality','mainwp-spinner' ), 'select', $sc_wordquality, array( 0 => 0, 1 => 1, 2 => 2, 3 => 3, 9 => 9 ), __( '0 - use Best Thesaurus to spin, 1 - use Better Thesaurus to spin, 2 - Use Good Thesaurus to spin,  3 - Use All Thesaurus to spin, 9 - Use Everyone’s favorite to spin','mainwp-spinner' ) );
  		$this->create_option_field( 'post_sc_protect_html', __( 'Protect html','mainwp-spinner' ), 'select', $sc_protect_html, array( 'No', 'Yes' ), __( 'No -  Not spin the words in the html tags. Yes - Spin the words in html tags.','mainwp-spinner' ) );
  		$this->create_option_field( 'post_sc_use_synonyms_orderly', __( 'Use synonyms Orderly','mainwp-spinner' ), 'checkbox', $sc_use_synonyms_orderly, array( '1' => __( 'Uses the thesaurus randomly to spin.','mainwp-spinner' ) ), '', '', '', true, false );
  		$this->create_option_field( 'post_sc_enable_grammar_ai', __( 'Enable Grammar AI','mainwp-spinner' ), 'checkbox', $sc_enable_grammar_ai, array( '1' => __( 'Use grammar correction','mainwp-spinner' ) ), '', '', '', true, false );
  		$this->create_option_field( 'post_sc_use_pos', __( 'Use POS analysis','mainwp-spinner' ), 'checkbox', $sc_use_pos, array( '1' => __( 'Use ‘part of speech’ analysis','mainwp-spinner' ) ), '', '', '', true, false );
  		$this->create_option_field( 'post_sc_protect_words', __( 'Protect Words','mainwp-spinner' ), 'text', $sc_protect_words, '', __( 'Separate each word with comma','mainwp-spinner' ) );
  		$this->create_option_field( 'post_sc_tag_protect', __( 'Tags Protect','mainwp-spinner' ), 'text', $sc_tag_protect, '', __( '[], (), <- -> , it will protect the text between [ and ], ( and ), <- and ->.','mainwp-spinner' ) );
  		$this->create_option_field( 'post_sp_spin_title', __( 'Auto-spin Article Title','mainwp-spinner' ), 'select', $sp_spin_title, array( 'No', 'Yes' ) );

  	} else if ( 'cr' == $spinner ) {
  		if ( ! $post || get_post_meta( $post->ID, '_mainwp_spinner_saved_post_options', true ) !== 'yes' ) {
  			$cr_quality = $this->get_option( 'cr_quality' );
  			$cr_posmatch = $this->get_option( 'cr_posmatch' );
  			$cr_protectedterms = $this->get_option( 'cr_protectedterms' );
  			$cr_rewrite = $this->get_option( 'cr_rewrite' );
  			$cr_phraseignorequality = $this->get_option( 'cr_phraseignorequality' );
  			$cr_spinwithinspin = $this->get_option( 'cr_spinwithinspin' );
  			$cr_spinwithinhtml = $this->get_option( 'cr_spinwithinhtml' );
  			$cr_applyinstantunique = $this->get_option( 'cr_applyinstantunique' );
  			$cr_fullcharset = $this->get_option( 'cr_fullcharset' );
  			$cr_spintidy = $this->get_option( 'cr_spintidy' );
  			$cr_tagprotect = $this->get_option( 'cr_tagprotect' );
  			$cr_maxspindepth = $this->get_option( 'cr_maxspindepth' );
  			$sp_spin_title = $this->option['sp_spin_title'];
  		} else {
  			$cr_quality = get_post_meta( $post->ID, '_mainwp_spinner_cr_quality', true );
  			$cr_posmatch = get_post_meta( $post->ID, '_mainwp_spinner_cr_posmatch', true );
  			$cr_protectedterms = get_post_meta( $post->ID, '_mainwp_spinner_cr_protectedterms', true );
  			$cr_rewrite = get_post_meta( $post->ID, '_mainwp_spinner_cr_rewrite', true );
  			$cr_phraseignorequality = get_post_meta( $post->ID, '_mainwp_spinner_cr_phraseignorequality', true );
  			$cr_spinwithinspin = get_post_meta( $post->ID, '_mainwp_spinner_cr_spinwithinspin', true );
  			$cr_spinwithinhtml = get_post_meta( $post->ID, '_mainwp_spinner_cr_spinwithinhtml', true );
  			$cr_applyinstantunique = get_post_meta( $post->ID, '_mainwp_spinner_cr_applyinstantunique', true );
  			$cr_fullcharset = get_post_meta( $post->ID, '_mainwp_spinner_cr_fullcharset', true );
  			$cr_spintidy = get_post_meta( $post->ID, '_mainwp_spinner_cr_spintidy', true );
  			$cr_tagprotect = get_post_meta( $post->ID, '_mainwp_spinner_cr_tagprotect', true );
  			$cr_maxspindepth = get_post_meta( $post->ID, '_mainwp_spinner_cr_maxspindepth', true );
  			$sp_spin_title = get_post_meta( $post->ID, '_mainwp_spinner_sp_spin_title', true );
  		}
  		$this->create_option_field( 'post_cr_quality', __( 'Quality','mainwp-spinner' ), 'select', $cr_quality, array( 5 => 5, 4 => 4, 3 => 3, 2 => 2, 1 => 'All' ), __( 'Spin quality: 5 – Best, 4 – Better, 3 – Good, 2 – Average, 1 – All','mainwp-spinner' ) );
  		$this->create_option_field( 'post_cr_posmatch', __( 'Required Part of Speech (POS) match for a spin','mainwp-spinner' ), 'select', $cr_posmatch, array( 4 => 4, 3 => 3, 2 => 2, 1 => 1, 0 => 0 ), __( '4 – FullSpin, 3 – Full, 2 – Loose, 1 – Extremely Loose, 0 – None.','mainwp-spinner' ) );
  		$this->create_option_field( 'post_cr_protectedterms', __( 'Protected terms','mainwp-spinner' ), 'text', $cr_protectedterms, '', __( 'Comma separated list of words or phrases to protect from spin i.e. ‘my main keyword,my second keyword’','mainwp-spinner' ) );
  		$this->create_option_field( 'post_cr_rewrite', __( 'Rewrite','mainwp-spinner' ), 'select', $cr_rewrite, array( 0, 1 ), __( 'If set to 1, results are returned as a rewritten article with no Spintax. Otherwise, an article with Spintax is returned. Note that with rewrite as 1, the original word will always be removed.','mainwp-spinner' ) );
  		$this->create_option_field( 'post_cr_phraseignorequality', __( 'Phrase ignore quality','mainwp-spinner' ), 'select', $cr_phraseignorequality, array( 0, 1 ), __( 'If set to 1, quality is ignored when finding phrase replacements for phrases. This results in a huge amount of spin, but quality can vary.','mainwp-spinner' ) );
  		$this->create_option_field( 'post_cr_spinwithinspin', __( 'Spin within spin','mainwp-spinner' ), 'select', $cr_spinwithinspin, array( 0, 1 ), __( '1- if there is existing spin syntax in the content you send up, the API will spin any relevant content inside this syntax.  0 - the API will skip over this content and only spin outside of existing syntax.','mainwp-spinner' ) );
  		$this->create_option_field( 'post_cr_spinwithinhtml', __( 'Spin within html','mainwp-spinner' ), 'select', $cr_spinwithinhtml, array( 0, 1 ), __( 'Spin inside HTML tags. This includes &lt;p&gt; tags, for example if you send up "&lt;p&gt;Here is a paragraph&lt;/p&gt;", nothing would be spun unless spinwithinhtml is 1.','mainwp-spinner' ) );
  		$this->create_option_field( 'post_cr_applyinstantunique', __( 'Apply instant unique','mainwp-spinner' ), 'select', $cr_applyinstantunique, array( 0, 1 ), __( '<strong>(Extra quota cost)</strong> Runs an instant unique pass over the article once spun. This replaces letters with characters that look like the original letter but have a different UTF8 value, passing copyscape 100% but garbling content to the search engines. It it recommended to protect keywords while using instant unique. Costs one extra query.','mainwp-spinner' ) );
  		$this->create_option_field( 'post_cr_fullcharset', __( 'Full charset','mainwp-spinner' ), 'select', $cr_fullcharset, array( 0, 1 ), __( "Only used if 'Apply instant unique' = 1. This causes IU to use the full character set which has a broader range of replacements.",'mainwp-spinner' ) );
  		$this->create_option_field( 'post_cr_spintidy', __( 'Spin tidy','mainwp-spinner' ), 'select', $cr_spintidy, array( 0, 1 ), __( '<strong>(Extra quota cost)</strong> Runs a spin tidy pass over the result article. This fixes any common a/an type grammar mistakes and repeated words due to phrase spinning. Generally increases the quality of the article. Costs one extra query.','mainwp-spinner' ) );
  		$this->create_option_field( 'post_cr_tagprotect', __( 'Tag protect','mainwp-spinner' ), 'text', $cr_tagprotect, '', __( 'Protects anything between any syntax you define. Separate start and end syntax with a pipe ‘|’ and separate multiple tags with a comma ‘,’. For example, you could protect anything in square brackets by setting tagprotect=[|]. You could also protect anything between “begin” and “end” by setting tagprotect=[|],begin|end.','mainwp-spinner' ) );
  		$this->create_option_field( 'post_cr_maxspindepth', __( 'Max spin depth','mainwp-spinner' ), 'select', $cr_maxspindepth, array( 0, 1 ), __( 'Define a maximum spin level depth in returned article. If set to 1, no nested spin will appear in the spun result. This paramater only matters if rewrite is false. Set to 0 or ignore for no limit on spin depth.','mainwp-spinner' ) );
  		$this->create_option_field( 'post_sp_spin_title', __( 'Auto-spin Article Title','mainwp-spinner' ), 'select', $sp_spin_title, array( 'No', 'Yes' ) );

  	} else if ( 'wai' == $spinner ) {
  		if ( ! $post || get_post_meta( $post->ID, '_mainwp_spinner_saved_post_options', true ) !== 'yes' ) {
  			$wai_quality = $this->get_option( 'wai_quality' );
  			$wai_nonested = $this->get_option( 'wai_nonested' );
  			$wai_sentence = $this->get_option( 'wai_sentence' );
  			$wai_paragraph = $this->get_option( 'wai_paragraph' );
  			$wai_returnspin = $this->get_option( 'wai_returnspin' );
  			$wai_nooriginal = $this->get_option( 'wai_nooriginal' );
  			$wai_protected = $this->get_option( 'wai_protected' );
  			$wai_synonyms = $this->get_option( 'wai_synonyms' );
  			$sp_spin_title = $this->option['sp_spin_title'];
  		} else {
  			$wai_quality = get_post_meta( $post->ID, '_mainwp_spinner_wai_quality', true );
  			$wai_nonested = get_post_meta( $post->ID, '_mainwp_spinner_wai_nonested', true );
  			$wai_sentence = get_post_meta( $post->ID, '_mainwp_spinner_wai_sentence', true );
  			$wai_paragraph = get_post_meta( $post->ID, '_mainwp_spinner_wai_paragraph', true );
  			$wai_returnspin = get_post_meta( $post->ID, '_mainwp_spinner_wai_returnspin', true );
  			$wai_nooriginal = get_post_meta( $post->ID, '_mainwp_spinner_wai_nooriginal', true );
  			$wai_protected = get_post_meta( $post->ID, '_mainwp_spinner_wai_protected', true );
  			$wai_synonyms = get_post_meta( $post->ID, '_mainwp_spinner_wai_synonyms', true );
  			$sp_spin_title = get_post_meta( $post->ID, '_mainwp_spinner_sp_spin_title', true );
  		}
  		$this->create_option_field( 'post_wai_quality', __( 'Quality','mainwp-spinner' ), 'select', $wai_quality, array( 0 => 'Regular', 1 => 'Unique', 2 => 'Very Unique', 3 => 'Readable', 4 => 'Very Readable' ), __( "'Regular', 'Unique', 'Very Unique', 'Readable', or 'Very Readable' depending on how readable vs unique you want your spin to be.", 'mainwp-spinner' ) );
  		$this->create_option_field( 'post_wai_nonested', __( 'No nested','mainwp-spinner' ), 'select', $wai_nonested, array( 0 => 'No', 1 => 'Yes' ), __( 'Set to "Yes" to turn off nested spinning (will help readability but hurt uniqueness).', 'mainwp-spinner' ) );
  		$this->create_option_field( 'post_wai_sentence', __( 'Sentence','mainwp-spinner' ), 'select', $wai_sentence, array( 0 => 'No', 1 => 'Yes' ), __( 'Set to "Yes" if you want paragraph editing, where WordAi will add, remove, or switch around the order of sentences in a paragraph (recommended!).', 'mainwp-spinner' ) );
  		$this->create_option_field( 'post_wai_paragraph', __( 'Paragraph','mainwp-spinner' ), 'select', $wai_paragraph, array( 0 => 'No', 1 => 'Yes' ), __( 'Set to "Yes" if you want WordAi to do paragraph spinning - perfect for if you plan on using the same spintax many times.', 'mainwp-spinner' ) );
  		$this->create_option_field( 'post_wai_returnspin', __( 'Return spin','mainwp-spinner' ), 'select', $wai_returnspin, array( 0 => 'No', 1 => 'Yes' ), __( 'Set to "Yes" if you want to just receive a spun version of the article you provided. Otherwise it will return spintax.', 'mainwp-spinner' ) );
  		$this->create_option_field( 'post_wai_nooriginal', __( 'No original','mainwp-spinner' ), 'select', $wai_nooriginal, array( 0 => 'No', 1 => 'Yes' ), __( 'Set to "Yes" if you do not want to include the original word in spintax (if synonyms are found). This is the same thing as creating a "Super Unique" spin.', 'mainwp-spinner' ) );
  		$this->create_option_field( 'post_wai_protected', __( 'Protected','mainwp-spinner' ), 'text', $wai_protected, '', __( 'Comma separated protected words (do not put spaces inbetween the words).', 'mainwp-spinner' ) );
  		$this->create_option_field( 'post_wai_synonyms', __( 'Synonyms','mainwp-spinner' ), 'text', $wai_synonyms, '', __( 'Add your own synonyms (Syntax: word1|synonym1,word two|first synonym 2|2nd syn). (comma separate the synonym sets and | separate the individuals synonyms).', 'mainwp-spinner' ) );
  		$this->create_option_field( 'post_sp_spin_title', __( 'Auto-spin Article Title','mainwp-spinner' ), 'select', $sp_spin_title, array( 'No', 'Yes' ) );
  	} else if ( 'srw' == $spinner ) {
  		if ( ! $post || get_post_meta( $post->ID, '_mainwp_spinner_saved_post_options', true ) !== 'yes' ) {
  			$srw_protected_terms = $this->get_option( 'srw_protected_terms' );
  			$srw_auto_protected_terms = $this->get_option( 'srw_auto_protected_terms' );
  			$srw_confidence_level = $this->get_option( 'srw_confidence_level' );
  			$srw_nested_spintax = $this->get_option( 'srw_nested_spintax' );
  			$srw_auto_sentences = $this->get_option( 'srw_auto_sentences' );
  			$srw_auto_paragraphs = $this->get_option( 'srw_auto_paragraphs' );
  			$srw_auto_new_paragraphs = $this->get_option( 'srw_auto_new_paragraphs' );
  			$srw_auto_sentence_trees = $this->get_option( 'srw_auto_sentence_trees' );
  			$srw_use_only_synonyms = $this->get_option( 'srw_use_only_synonyms' );
  			$srw_reorder_paragraphs = $this->get_option( 'srw_reorder_paragraphs' );
  			$sp_spin_title = $this->option['sp_spin_title'];
  		} else {
  			$srw_protected_terms = get_post_meta( $post->ID, '_mainwp_spinner_srw_protected_terms', true );
  			$srw_auto_protected_terms = get_post_meta( $post->ID, '_mainwp_spinner_srw_auto_protected_terms', true );
  			$srw_confidence_level = get_post_meta( $post->ID, '_mainwp_spinner_srw_confidence_level', true );
  			$srw_nested_spintax = get_post_meta( $post->ID, '_mainwp_spinner_srw_nested_spintax', true );
  			$srw_auto_sentences = get_post_meta( $post->ID, '_mainwp_spinner_srw_auto_sentences', true );
  			$srw_auto_paragraphs = get_post_meta( $post->ID, '_mainwp_spinner_srw_auto_paragraphs', true );
  			$srw_auto_new_paragraphs = get_post_meta( $post->ID, '_mainwp_spinner_srw_auto_new_paragraphs', true );
  			$srw_auto_sentence_trees = get_post_meta( $post->ID, '_mainwp_spinner_srw_auto_sentence_trees', true );
  			$srw_use_only_synonyms = get_post_meta( $post->ID, '_mainwp_spinner_srw_use_only_synonyms', true );
  			$srw_reorder_paragraphs = get_post_meta( $post->ID, '_mainwp_spinner_srw_reorder_paragraphs', true );
  			$sp_spin_title = get_post_meta( $post->ID, '_mainwp_spinner_sp_spin_title', true );
  		}

  		$this->create_option_field( 'post_srw_protected_terms', __( 'Protected terms','mainwp-spinner' ), 'textarea', $srw_protected_terms, '', __('A list of keywords and key phrases that you do NOT want to spin. One term per line.', 'mainwp-spinner') );
  		$this->create_option_field( 'post_srw_auto_protected_terms', __( 'Auto protected terms','mainwp-spinner' ), 'select', $srw_auto_protected_terms, array( 'false' => 'No', 'true' => 'Yes' ), __( 'Should Spin Rewriter automatically protect all Capitalized Words except for those in the title of your original text?', 'mainwp-spinner' ) );
  		$this->create_option_field( 'post_srw_confidence_level', __( 'Confidence level','mainwp-spinner' ), 'select', $srw_confidence_level, array( 'low' => 'Low', 'medium' => 'Medium', 'high' => 'High' ), __( 'The confidence level of the One-Click Rewrite process. <strong>low</strong>: largest number of synonyms for various words and phrases, least readable unique variations of text. <strong>medium</strong>: relatively reliable synonyms, usually well readable unique variations of text (default setting) <strong>high</strong>: only the most reliable synonyms, perfectly readable unique variations of text.', 'mainwp-spinner' ) );
  		$this->create_option_field( 'post_srw_nested_spintax', __( 'Nested spintax','mainwp-spinner' ), 'select', $srw_nested_spintax, array( 'false' => 'No', 'true' => 'Yes' ), __( 'Should Spin Rewriter also spin single words inside already spun phrases? If set to "Yes", the returned spun text might contain 2 levels of nested spinning syntax.', 'mainwp-spinner' ) );
  		$this->create_option_field( 'post_srw_auto_sentences', __( 'Auto sentences','mainwp-spinner' ), 'select', $srw_auto_sentences, array( 'false' => 'No', 'true' => 'Yes' ), __( 'Should Spin Rewriter spin complete sentences? If set to "Yes", some sentences will be replaced with a (shorter) spun variation.', 'mainwp-spinner' ) );
  		$this->create_option_field( 'post_srw_auto_paragraphs', __( 'Auto paragraphs','mainwp-spinner' ), 'select', $srw_auto_paragraphs, array( 'false' => 'No', 'true' => 'Yes' ), __( 'Should Spin Rewriter spin entire paragraphs? If set to "Yes", some paragraphs will be replaced with a (shorter) spun variation.', 'mainwp-spinner' ) );
  		$this->create_option_field( 'post_srw_auto_new_paragraphs', __( 'Auto new paragraphs','mainwp-spinner' ), 'select', $srw_auto_new_paragraphs, array( 'false' => 'No', 'true' => 'Yes' ), __( 'Should Spin Rewriter automatically write additional paragraphs on its own? If set to "Yes", the returned spun text will contain additional paragraphs.', 'mainwp-spinner' ) );
  		$this->create_option_field( 'post_srw_auto_sentence_trees', __( 'Auto sentence trees','mainwp-spinner' ), 'select', $srw_auto_sentence_trees, array( 'false' => 'No', 'true' => 'Yes' ), __( 'Should Spin Rewriter automatically change the entire structure of phrases and sentences? If set to "Yes", Spin Rewriter will change "If he is hungry, John eats." to "John eats if he is hungry." and "John eats and drinks." to "John drinks and eats."', 'mainwp-spinner' ) );
  		$this->create_option_field( 'post_srw_use_only_synonyms', __( 'Use only synonyms','mainwp-spinner' ), 'select', $srw_use_only_synonyms, array( 'false' => 'No', 'true' => 'Yes' ), __( 'Should Spin Rewriter use only synonyms of the original words instead of the original words themselves? If set to "Yes", Spin Rewriter will never use any of the original words of phrases if there is a synonym available. This significantly improves the uniqueness of generated spun content.', 'mainwp-spinner' ) );
  		$this->create_option_field( 'post_srw_reorder_paragraphs', __( 'Reorder paragraphs','mainwp-spinner' ), 'select', $srw_reorder_paragraphs, array( 'false' => 'No', 'true' => 'Yes' ), __( 'Should Spin Rewriter intelligently randomize the order of paragraphs and unordered lists when generating spun text? If set to "Yes", Spin Rewriter will randomize the order of paragraphs and lists where possible while preserving the readability of the text. This significantly improves the uniqueness of generated spun content.', 'mainwp-spinner' ) );
  		$this->create_option_field( 'post_sp_spin_title', __( 'Auto-spin Article Title','mainwp-spinner' ), 'select', $sp_spin_title, array( 'No', 'Yes' ) );
	  }
	?>
  <div class="ui divider"></div>
	<input type="hidden" name="sp_spinner" value="<?php echo $spinner; ?>" />
    <button id="post_spin_article" type="button" class="ui green rigth floated button"><?php _e( 'Spin Article','mainwp-spinner' ); ?></button>
  <?php
  }
  ?>
</div>

<script>
jQuery( document ).ready( function ($) {
  $( '#titlewrap' ).after( '<div class="ui yellow message"><?php _e( 'Use text mode when spinning HTML the Visual editor will corrupt the spun code', 'mainwp-spinner' ); ?></div>' );
});
</script>

<?php$spinner = $this->get_option('sp_spinner');?><div  id="spinner_errorbox-spin"></div>			<div  id="spinner_infobox-spin"></div>	<div class="spinner_ext-option-list-wrapper" id="mainwp-spin-meta-box">    <input type="hidden" name="mainwpspin_nonce" id="mainwpspin_nonce" value="<?php echo wp_create_nonce($this->plugin_handle) ?>" />            <h4><?php _e("Full Article Spinner Options") ?></h4>    <?php	if(!isset($spinner) || empty($spinner) )	{	    ?>					<div id="spinner_errorbox" style="display:block"> Please setting for Spinner Options first.</div>				<?php 			}			else			{		if ($spinner == 'bs') 		{     			if (!$post || get_post_meta($post->ID, '_saved_spin_post_option', true) !== "yes") 			{				$bs_max_synonyms = $this->option['bs_max_synonyms'];				$bs_quality = $this->option['bs_quality'];				$bs_exclude_words = $this->option['bs_exclude_words'];				$sp_spin_title = $this->option['sp_spin_title'];			} 			else 			{				$bs_max_synonyms = get_post_meta($post->ID, '_ezine_post_bs_max_synonyms', true);				$bs_quality = get_post_meta($post->ID, '_ezine_post_bs_quality', true);				$bs_exclude_words = get_post_meta($post->ID, '_ezine_post_bs_exclude_words', true);				$sp_spin_title = get_post_meta($post->ID, '_ezine_post_sp_spin_title', true);			}			$this->create_option_field('post_bs_max_synonyms', __("Maximum Synonyms per Term"), 'select', $bs_max_synonyms, array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10));			$this->create_option_field('post_bs_quality', __("Replacement Quality"), 'select', $bs_quality, array(1, 2, 3), __("1 - Most Changes, 3 - Fewest Changes"));			$this->create_option_field('post_bs_exclude_words', __("Words not to be Changed"), 'textarea', $bs_exclude_words, '', __("Separate each word with comma"));			$this->create_option_field('post_sp_spin_title', __("Auto-spin Article Title"), 'select', $sp_spin_title, array("No", "Yes"));			?>			<p><a href="admin.php?page=Extensions-Mainwp-Spinner#1" target="_blank">Set The Best Spinner login on the Settings Page</a></p>				<?php		} 		else if ($spinner == 'sc') 		{        			if (!$post || get_post_meta($post->ID, '_saved_spin_post_option', true) !== "yes") 			{				$sc_spinfreq = $this->option['sc_spinfreq'];				$sc_wordscount = $this->option['sc_wordscount'];				//$sc_use_original_word = $this->option['sc_use_original_word'];				$sc_protect_html = $this->option['sc_protect_html'];				$sc_use_synonyms_orderly = $this->option['sc_use_synonyms_orderly'];				$sc_replace_type = $this->option['sc_replace_type'];				$sc_wordquality = $this->option['sc_wordquality'];				$sc_enable_grammar_ai = $this->option['sc_enable_grammar_ai'];				$sc_use_pos = $this->option['sc_use_pos'];				$sc_protect_words = $this->option['sc_protect_words'];				$sc_tag_protect = $this->option['sc_tag_protect'];				$sp_spin_title = $this->option['sp_spin_title'];			} 			else 			{				$sc_spinfreq = get_post_meta($post->ID, '_ezine_post_sc_spinfreq', true);				$sc_wordscount = get_post_meta($post->ID, '_ezine_post_sc_wordscount', true);				///$sc_use_original_word = get_post_meta($post->ID, '_ezine_post_sc_use_original_word', true);				$sc_protect_html = get_post_meta($post->ID, '_ezine_post_sc_protect_html', true);				$sc_use_synonyms_orderly = get_post_meta($post->ID, '_ezine_post_sc_use_synonyms_orderly', true);				$sc_replace_type = get_post_meta($post->ID, '_ezine_post_sc_replace_type', true);				$sc_wordquality = get_post_meta($post->ID, '_ezine_post_sc_wordquality', true);				$sc_enable_grammar_ai = get_post_meta($post->ID, '_ezine_post_sc_enable_grammar_ai', true);				$sc_use_pos = get_post_meta($post->ID, '_ezine_post_sc_use_pos', true);				$sc_protect_words = get_post_meta($post->ID, '_ezine_post_sc_protect_words', true);				$sc_tag_protect = get_post_meta($post->ID, '_ezine_post_sc_tag_protect', true);				$sp_spin_title = get_post_meta($post->ID, '_ezine_post_sp_spin_title', true);			}			$this->create_option_field('post_sc_spinfreq', __("Word spin frequency"), 'select', $sc_spinfreq, array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6), __("1 - Every word will be spun, 3 - 1/3 of all words will be spun, etc ... "));			$this->create_option_field('post_sc_wordscount', __("Words amount in {}"), 'select', $sc_wordscount, array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10));			$this->create_option_field('post_sc_replace_type', __("Replace type"), 'select', $sc_replace_type, array(0, 1, 2, 3, 4, 5), __("0 - Replace phrase and word, 1 - Only replace phrase, 2 - Only replace word, 3 - Replace phrase first, then replace word till the article passes copyscape, 4 - Spin the article to most unique, 5 - Spin the article to most readable"));			$this->create_option_field('post_sc_wordquality', __("Word quality"), 'select', $sc_wordquality, array(0 => 0, 1 => 1, 2 => 2, 3 => 3, 9 => 9), __("0 - use Best Thesaurus to spin, 1 - use Better Thesaurus to spin, 2 - Use Good Thesaurus to spin,  3 - Use All Thesaurus to spin, 9 - Use Everyone’s favorite to spin"));			// always use original word (for respin)			//$this->create_option_field('post_sc_use_original_word', __("Use orginal word when spin"), 'select', $sc_use_original_word, array("No", "Yes"));			$this->create_option_field('post_sc_protect_html', __("Protect html"), 'select', $sc_protect_html, array("No", "Yes"), __("No -  Not spin the words in the html tags. Yes - Spin the words in html tags. "));			$this->create_option_field('post_sc_use_synonyms_orderly', __("Use synonyms Orderly"), 'checkbox', $sc_use_synonyms_orderly, array('1' => "Uses the thesaurus randomly to spin."), '', '', '', true, false);			$this->create_option_field('post_sc_enable_grammar_ai', __("Enable Grammar AI"), 'checkbox', $sc_enable_grammar_ai, array('1' => __('Use grammar correction')), '', '', '', true, false);			$this->create_option_field('post_sc_use_pos', __("Use POS analysis"), 'checkbox', $sc_use_pos, array('1' => __('Use ‘part of speech’ analysis')), '', '', '', true, false);			$this->create_option_field('post_sc_protect_words', __("Protect Words"), 'textarea', $sc_protect_words, '', __("Separate each word with comma"));			$this->create_option_field('post_sc_tag_protect', __("Tags Protect"), 'text', $sc_tag_protect, '', __("[], (), <- -> , it will protect the text between [ and ], ( and ), <- and ->."));			$this->create_option_field('post_sp_spin_title', __("Auto-spin Article Title"), 'select', $sp_spin_title, array("No", "Yes"));        			?>			<p><a href="admin.php?page=Extensions-Mainwp-Spinner#2" target="_blank">Set Spinner Chief login on the Settings Page</a></p>				<?php		} 		else if ($spinner == 'cr') 		{ 				if (!$post || get_post_meta($post->ID, '_saved_spin_post_option', true) !== "yes") 				{                   						$cr_quality = $this->get_option('cr_quality');						$cr_posmatch = $this->get_option('cr_posmatch');						$cr_protectedterms = $this->get_option('cr_protectedterms');						$cr_rewrite = $this->get_option('cr_rewrite');                                           						$cr_phraseignorequality = $this->get_option('cr_phraseignorequality');                   						$cr_spinwithinspin = $this->get_option('cr_spinwithinspin');						$cr_spinwithinhtml = $this->get_option('cr_spinwithinhtml');						$cr_applyinstantunique = $this->get_option('cr_applyinstantunique');						$cr_fullcharset = $this->get_option('cr_fullcharset');						$cr_spintidy = $this->get_option('cr_spintidy');						$cr_tagprotect = $this->get_option('cr_tagprotect'); 						$cr_maxspindepth = $this->get_option('cr_maxspindepth');						 $sp_spin_title = $this->option['sp_spin_title'];			   } 			   else 			   {                   						$cr_quality = get_post_meta($post->ID, '_ezine_post_cr_quality', true);						$cr_posmatch = get_post_meta($post->ID, '_ezine_post_cr_posmatch', true);						$cr_protectedterms = get_post_meta($post->ID, '_ezine_post_cr_protectedterms', true);						$cr_rewrite = get_post_meta($post->ID, '_ezine_post_cr_rewrite', true);                                           						$cr_phraseignorequality = get_post_meta($post->ID, '_ezine_post_cr_phraseignorequality', true);                   						$cr_spinwithinspin = get_post_meta($post->ID, '_ezine_post_cr_spinwithinspin', true);						$cr_spinwithinhtml = get_post_meta($post->ID, '_ezine_post_cr_spinwithinhtml', true);						$cr_applyinstantunique = get_post_meta($post->ID, '_ezine_post_cr_applyinstantunique', true);						$cr_fullcharset = get_post_meta($post->ID, '_ezine_post_cr_fullcharset', true);						$cr_spintidy = get_post_meta($post->ID, '_ezine_post_cr_spintidy', true);						$cr_tagprotect = get_post_meta($post->ID, '_ezine_post_cr_tagprotect', true); 						$cr_maxspindepth = get_post_meta($post->ID, '_ezine_post_cr_maxspindepth', true);						$sp_spin_title = get_post_meta($post->ID, '_ezine_post_sp_spin_title', true);			   }				$this->create_option_field('post_cr_quality', __("Quality"), 'select', $cr_quality, array(5 => 5, 4 => 4 , 3 => 3, 2 => 2, 1 => 'All'), __('Spin quality: 5 – Best, 4 – Better, 3 – Good, 2 – Average, 1 – All'));				$this->create_option_field('post_cr_posmatch', __("Required Part of Speech (POS) match for a spin"), 'select', $cr_posmatch, array(4 => 4 , 3 => 3, 2 => 2, 1 => 1, 0=> 0), __('4 – FullSpin, 3 – Full, 2 – Loose, 1 – Extremely Loose, 0 – None.'));				$this->create_option_field('post_cr_protectedterms', __("Protected terms"), 'text', $cr_protectedterms, '', __("Comma separated list of words or phrases to protect from spin i.e. ‘my main keyword,my second keyword’"));                                            				$this->create_option_field('post_cr_rewrite', __("Rewrite"), 'select', $cr_rewrite, array(0, 1), __('If set to 1, results are returned as a rewritten article with no Spintax. Otherwise, an article with Spintax is returned. Note that with rewrite as 1, the original word will always be removed.'));                                            				$this->create_option_field('post_cr_phraseignorequality', __("Phrase ignore quality"), 'select', $cr_phraseignorequality, array(0, 1), __("If set to 1, quality is ignored when finding phrase replacements for phrases. This results in a huge amount of spin, but quality can vary."));                                            				$this->create_option_field('post_cr_spinwithinspin', __("Spin within spin"), 'select', $cr_spinwithinspin, array(0, 1), __("1- if there is existing spin syntax in the content you send up, the API will spin any relevant content inside this syntax.  0 - the API will skip over this content and only spin outside of existing syntax."));                                                                                                                                    				$this->create_option_field('post_cr_spinwithinhtml', __("Spin within html"), 'select', $cr_spinwithinhtml, array(0, 1),  __('Spin inside HTML tags. This includes &lt;p&gt; tags, for example if you send up "&lt;p&gt;Here is a paragraph&lt;/p&gt;", nothing would be spun unless spinwithinhtml is 1.'));                                                                                                                                    				$this->create_option_field('post_cr_applyinstantunique', __("Apply instant unique"), 'select', $cr_applyinstantunique, array(0, 1), __("<strong>(Extra quota cost)</strong> Runs an instant unique pass over the article once spun. This replaces letters with characters that look like the original letter but have a different UTF8 value, passing copyscape 100% but garbling content to the search engines. It it recommended to protect keywords while using instant unique. Costs one extra query."));                                                                                        				$this->create_option_field('post_cr_fullcharset', __("Full charset"), 'select', $cr_fullcharset, array(0, 1), __("Only used if 'Apply instant unique' = 1. This causes IU to use the full character set which has a broader range of replacements."));                                                                                        				$this->create_option_field('post_cr_spintidy', __("Spin tidy"), 'select', $cr_spintidy, array(0, 1), __("<strong>(Extra quota cost)</strong> Runs a spin tidy pass over the result article. This fixes any common a/an type grammar mistakes and repeated words due to phrase spinning. Generally increases the quality of the article. Costs one extra query."));                                                                                        				$this->create_option_field('post_cr_tagprotect', __("Tag protect"), 'text', $cr_tagprotect, '', __("Protects anything between any syntax you define. Separate start and end syntax with a pipe ‘|’ and separate multiple tags with a comma ‘,’. For example, you could protect anything in square brackets by setting tagprotect=[|]. You could also protect anything between “begin” and “end” by setting tagprotect=[|],begin|end."));                                                                                        			   // currently not support this param, nested spin				$this->create_option_field('post_cr_maxspindepth', __("Max spin depth"), 'select', $cr_maxspindepth, array(0, 1), __("Define a maximum spin level depth in returned article. If set to 1, no nested spin will appear in the spun result. This paramater only matters if rewrite is false. Set to 0 or ignore for no limit on spin depth."));                                                                                        				$this->create_option_field('post_sp_spin_title', __("Auto-spin Article Title"), 'select', $sp_spin_title, array("No", "Yes"));		 ?>			  <div class="spinner_option-list"><label>&nbsp;</label>				<div class="spinner_option-field">                                                 						<?php   echo __("To protect any piece of text, simply wrap it with ###. For example, if you had a certain paragraph or code to protect, simply send “An intro sentence. ###My protected stuff### and the rest of the article”. Anything inside the hashes will not be spun. Then just replace ‘###’ with an empty string.");  ?>				</div>			</div>			<p><a href="admin.php?page=Extensions-Mainwp-Spinner#3" target="_blank">Set Chimp Rewriter login on the Settings Page</a></p>				<?php		}		?>		<input type="hidden" name="sp_spinner" value="<?php echo $spinner; ?>" />		<button id="post_spin_article" class="button">Spin Now</button> <img src="<?php echo admin_url('images/wpspin_light.gif') ?>" alt="loading" class="spin_loading" style="display: none;" />	<?php 	}	?></div><script>    jQuery(document).ready(function($) {        $('#titlewrap').after('<div class="mainwp_info-box-yellow"><?php _e("Use text mode when spinning HTML the Visual editor will corrupt the spun code"); ?></div>');    })    </script>    
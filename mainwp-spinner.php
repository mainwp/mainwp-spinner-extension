<?php
/*
  Plugin Name: MainWP Spinner
  Plugin URI:
  Description: MainWP Extension Plugin allows words to spun {|} when when adding articles and posts to your blogs. Requires the installation of MainWP Main Plugin.
  Version: 1.8.8
  Author: MainWP
  Author URI: http://extensions.mainwp.com
  Support Forum URI: https://mainwp.com/forum/forumdisplay.php?73-Spinner-Extension
  Documentation URI: http://docs.mainwp.com/category/mainwp-extensions/mainwp-spinner/
  Icon URI: http://extensions.mainwp.com/wp-content/uploads/2013/06/spinner-300x300.png
 */

class mainwp_spinner {

    private static $instance = null;
    public $plugin_name = "MainWP Spinner";
    public $plugin_handle = "mainwp-spinner";
    public $spinners = array('bs' => 'The Best Spinner', 'sc' => 'Spinnerchief', 'cr' => 'Chimp Rewriter');
    
    public $plugin_dir;
    protected $plugin_url;
    protected $plugin_admin = '';
    protected $version = 1.152;
    protected $option;
    protected $option_handle = 'mainwp_spin';
    protected $bs_session = '';
    protected $bs_api_url = "http://thebestspinner.com/api.php";
    private $plugin_slug;
  

    /**
     * @static
     * @return mainwp_spinner
     */
    static function Instance() {
        if (mainwp_spinner::$instance == null) {
            mainwp_spinner::$instance = new mainwp_spinner();
        }
        return mainwp_spinner::$instance;
    }

    public function __construct() {
        global $wpdb;
		error_reporting(E_ALL ^ E_NOTICE);
        $this->plugin_dir = plugin_dir_path(__FILE__);
        $this->plugin_url = plugin_dir_url(__FILE__);
        $this->plugin_slug = plugin_basename(__FILE__);

        add_action('admin_init', array(&$this, 'admin_init'));
        add_filter('plugin_row_meta', array(&$this, 'plugin_row_meta'), 10, 2);
        $this->option = get_option($this->option_handle);
        $this->load_modules(); // load all module

		// hook

        add_filter('the_title', array(&$this, 'filter_title'), 0, 2);
        add_filter('the_posts', array(&$this, 'filter_posts'));
        add_filter('single_post_title', array(&$this, 'filter_title'), 0, 2);
        add_filter('wp_insert_post_data', array(&$this, 'filter_post_slug'), 10, 2);
       
    }

    public function admin_init() {
        wp_enqueue_style($this->plugin_handle . '-admin-css', $this->plugin_url . 'css/admin.css');
        wp_enqueue_script($this->plugin_handle . '-admin-js', $this->plugin_url . 'js/admin.js');

// Check if a save option is called
        $this->save_option();
//wp_enqueue_script('thickbox');
// hook                    
        /* add_meta_box('mainwpspin', __("MainWP Spinner Option"), array(&$this, 'metabox'), 'post', 'normal', 'high'); */
        add_meta_box('mainwpspin', __("MainWP Spinner Options"), array(&$this, 'metabox'), 'bulkpost', 'normal', 'high');
        add_meta_box('mainwpspin', __("MainWP Spinner Options"), array(&$this, 'metabox'), 'bulkpage', 'normal', 'high');

        //add_action('post_submitbox_misc_actions', array(&$this, 'submitbox'));        
        add_action('save_post', array(&$this, 'save_post'), 9);
        add_action('wp_ajax_spin_post', array(&$this, 'spin_post'));
        add_action('wp_ajax_spin_text', array(&$this, 'single_spin_text'));
        add_action('wp_ajax_test_spin', array(&$this, 'test_spin'));

        add_filter('mce_external_plugins', array(&$this, 'mce_plugin'));
        add_filter('mce_buttons', array(&$this, 'mce_button'));
        
        // add this action to support spin dripper
        add_action('mainwp_dripper_update_post_meta', array(&$this, 'spinner_update_post_meta'), 10, 2);
        // add this action to support spin boilerplate
        add_action('mainwp_boilerplate_update_post_meta', array(&$this, 'spinner_update_post_meta'), 10, 2);
        
        /* add_filter('tiny_mce_before_init', array(&$this, 'mce_setting')); */
		
		/* Remove filter outside MainWP system 
		1. Remove checkbox "Auto Spun" on "Screen Options"	
		2. Remove filter "auto spun" at All Pages/ All Posts				
		*/
		// post filter       
        /* add_action('posts_where_paged', array(&$this, 'manage_posts_filter')); */
		// column headers
       /*  add_filter("manage_post_posts_columns", array(&$this, "add_column_headers"));
        add_action("manage_posts_custom_column", array(&$this, 'manage_column'), 1, 2); */
    }

    public function plugin_row_meta($plugin_meta, $plugin_file)
        {
            if ($this->plugin_slug != $plugin_file) return $plugin_meta;                        
            $plugin_meta[] = '<a href="?do=checkUpgrade" title="Check for updates.">Check for updates now</a>';
            return $plugin_meta;
        }

    function spinner_update_post_meta($post, $post_type) {
        if (!$post)
            return;        
        if ($post->post_type == $post_type) {
            update_post_meta($post->ID, '_mainwp_spin_me', 'yes');  
        }        
    }
    
    public function option_page() {
        include $this->plugin_dir . '/includes/option_page.php';
    }

    public function save_option() {
        global $wpdb;

        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], $this->plugin_handle . '-option'))
            return;
        if (!current_user_can('manage_options'))
            return;

// All is OK now
        $this->option['saved_time'] = current_time('timestamp');
        if ($_POST['settings_page']) {
            $page_redirect = "Extensions-Mainwp-Spinner";
// General settings
            $this->option['sp_enable'] = intval($_POST['sp_enable']);
            $this->option['sp_spinner'] = $_POST['sp_spinner'];
            $this->option['sp_spin_title'] = intval($_POST['sp_spin_title']);
// Best Spinner Settings                                   
            $this->option['bs_email_address'] = sanitize_text_field($_POST['bs_email_address']);
            $this->option['bs_password'] = sanitize_text_field($_POST['bs_password']);
            $this->option['bs_max_synonyms'] = intval($_POST['bs_max_synonyms']);
            $this->option['bs_quality'] = intval($_POST['bs_quality']);
            $this->option['bs_exclude_words'] = strip_tags($_POST['bs_exclude_words']);
// Spinnerchief Settings                                                                      
            $this->option['sc_ip_port'] = sanitize_text_field($_POST['sc_ip_port']);
            $this->option['sc_api_key'] = sanitize_text_field($_POST['sc_api_key']);
            $this->option['sc_username'] = sanitize_text_field($_POST['sc_username']);
            $this->option['sc_password'] = sanitize_text_field($_POST['sc_password']);
            $this->option['sc_spinfreq'] = intval($_POST['sc_spinfreq']);
            $this->option['sc_wordscount'] = intval($_POST['sc_wordscount']);
            //$this->option['sc_use_original_word'] = intval($_POST['sc_use_original_word']);
            $this->option['sc_protect_html'] = intval($_POST['sc_protect_html']);
            $this->option['sc_use_synonyms_orderly'] = intval($_POST['sc_use_synonyms_orderly']);
            $this->option['sc_replace_type'] = intval($_POST['sc_replace_type']);
            $this->option['sc_wordquality'] = intval($_POST['sc_wordquality']);
            $this->option['sc_enable_grammar_ai'] = intval($_POST['sc_enable_grammar_ai']);
            $this->option['sc_use_pos'] = intval($_POST['sc_use_pos']);
            $this->option['sc_protect_words'] = strip_tags($_POST['sc_protect_words']);
            $this->option['sc_tag_protect'] = stripcslashes($_POST['sc_tag_protect']);
           // Chimp Rewriter settings
            $this->option['cr_username'] = sanitize_text_field($_POST['cr_username']);
            $this->option['cr_api_key'] = sanitize_text_field($_POST['cr_api_key']);
            $this->option['cr_aid'] =sanitize_text_field($_POST['cr_aid']);
            $this->option['cr_quality'] = intval($_POST['cr_quality']);
            $this->option['cr_posmatch'] =  intval($_POST['cr_posmatch']);
            $this->option['cr_protectedterms'] = sanitize_text_field($_POST['cr_protectedterms']);                                            
            $this->option['cr_rewrite'] = intval($_POST['cr_rewrite']);                                           
            $this->option['cr_phraseignorequality'] = intval($_POST['cr_phraseignorequality']);                                         
            $this->option['cr_spinwithinspin'] = intval($_POST['cr_spinwithinspin']);
            $this->option['cr_spinwithinhtml'] = intval($_POST['cr_spinwithinhtml']);
            $this->option['cr_applyinstantunique'] = intval($_POST['cr_applyinstantunique']);
            $this->option['cr_fullcharset'] = intval($_POST['cr_fullcharset']);
            $this->option['cr_spintidy'] = intval($_POST['cr_spintidy']);
            $this->option['cr_tagprotect'] = sanitize_text_field($_POST['cr_tagprotect']);     
            $this->option['cr_maxspindepth'] = intval($_POST['cr_maxspindepth']);                                            
        }

        if (update_option($this->option_handle, $this->option)) {
// check if spinners authentication work
            if ($this->option['sp_enable']) 
                $this->spin_authenticate();
            else
                $this->option['sp_error'] = 0;

            if ($_POST['action'] == 'option_auto_save') {
                echo __(sprintf("Auto-saved at %s", date('F j, Y, g:ia', $this->option['saved_time'])));
                exit;
            }
            wp_redirect('admin.php?page=' . $page_redirect . '&message=1');
        } else {
            if ($_POST['action'] == 'option_auto_save') {
                echo __("Auto-save error!");
                exit;
            }
            wp_redirect('admin.php?page=' . $page_redirect . '&message=3');
        }
        exit;
    }
    
    public function load_modules()
    {
        // load The Best Spinner
        include_once $this->plugin_dir . 'modules/the-best-spinner.php';
        $this->modules['bs'] = new MainWP_Spin_Module_TBS($this, $this->option['bs_email_address'] , $this->option['bs_password']);
        // load Spinner Chief
        include_once $this->plugin_dir . 'modules/spinnerchief.php';
        $this->modules['sc'] = new MainWP_Spin_Module_SC($this, $this->option['sc_ip_port'], $this->option['sc_api_key'], $this->option['sc_username'], $this->option['sc_password']);
         // load The Chimp Rewriter
        include_once $this->plugin_dir . 'modules/chimprewriter.php';
        $this->modules['cr'] = new MainWP_Spin_Module_CR($this, $this->option['cr_username'], $this->option['cr_api_key'],  !empty($this->option['cr_aid']) ? $this->option['cr_aid'] : 'wp-spinchimp');
        
    }
    
    public function get_option($key) {
        if (isset($this->option[$key]))
            return $this->option[$key];
        return '';
    }

    public function set_option($key, $value) {
        $this->option[$key] = $value;
        return update_option($this->option_handle, $this->option);
    }

    public function metabox($post) {
	
        include $this->plugin_dir . '/includes/metabox.php';
    }

    public function submitbox($post) {
        //include $this->plugin_dir . '/includes/submitbox.php';
    }

    public function save_post($post_id) {
        if (!wp_verify_nonce($_POST['mainwpspin_nonce'], $this->plugin_handle))
            return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return $post_id;
        if (!current_user_can('edit_post', $post_id))
            return $post_id; 
        update_post_meta($post_id, '_saved_spin_post_option', 'yes');        
        update_post_meta($post_id, '_ezine_post_sp_spin_title', intval($_POST['post_sp_spin_title']));        
        if ($_POST['sp_spinner'] == 'bs') {
            update_post_meta($post_id, '_ezine_post_bs_max_synonyms', intval($_POST['post_bs_max_synonyms']));
            update_post_meta($post_id, '_ezine_post_bs_quality', intval($_POST['post_bs_quality']));
            update_post_meta($post_id, '_ezine_post_bs_exclude_words', strip_tags($_POST['post_bs_exclude_words']));            
        } else if ($_POST['sp_spinner'] == 'sc') {
            update_post_meta($post_id, '_ezine_post_sc_spinfreq', intval($_POST['post_sc_spinfreq']));
            update_post_meta($post_id, '_ezine_post_sc_wordscount', intval($_POST['post_sc_wordscount']));
            //update_post_meta($post_id, '_ezine_post_sc_use_original_word', intval($_POST['post_sc_use_original_word']));
            update_post_meta($post_id, '_ezine_post_sc_protect_html', intval($_POST['post_sc_protect_html']));
            update_post_meta($post_id, '_ezine_post_sc_use_synonyms_orderly', intval($_POST['post_sc_use_synonyms_orderly']));
            update_post_meta($post_id, '_ezine_post_sc_replace_type', intval($_POST['post_sc_replace_type']));
            update_post_meta($post_id, '_ezine_post_sc_wordquality', intval($_POST['post_sc_wordquality']));
            update_post_meta($post_id, '_ezine_post_sc_enable_grammar_ai', intval($_POST['post_sc_enable_grammar_ai']));
            update_post_meta($post_id, '_ezine_post_sc_use_pos', intval($_POST['post_sc_use_pos']));
            update_post_meta($post_id, '_ezine_post_sc_protect_words', strip_tags($_POST['post_sc_protect_words']));
            update_post_meta($post_id, '_ezine_post_sc_tag_protect', stripcslashes($_POST['post_sc_tag_protect']));            
        } else if ($_POST['sp_spinner'] == 'cr') {
            update_post_meta($post_id, '_ezine_post_cr_quality', intval($_POST['post_cr_quality']));
            update_post_meta($post_id, '_ezine_post_cr_posmatch',  intval($_POST['post_cr_posmatch']));
            update_post_meta($post_id, '_ezine_post_cr_protectedterms', sanitize_text_field($_POST['post_cr_protectedterms']));
            update_post_meta($post_id, '_ezine_post_cr_rewrite', intval($_POST['post_cr_rewrite']));                                           
            update_post_meta($post_id, '_ezine_post_cr_phraseignorequality', intval($_POST['post_cr_phraseignorequality']));                   
            update_post_meta($post_id, '_ezine_post_cr_spinwithinspin', intval($_POST['post_cr_spinwithinspin']));
            update_post_meta($post_id, '_ezine_post_cr_spinwithinhtml', intval($_POST['post_cr_spinwithinhtml']));
            update_post_meta($post_id, '_ezine_post_cr_applyinstantunique', intval($_POST['post_cr_applyinstantunique']));
            update_post_meta($post_id, '_ezine_post_cr_fullcharset', intval($_POST['post_cr_fullcharset']));
            update_post_meta($post_id, '_ezine_post_cr_spintidy', intval($_POST['post_cr_spintidy']));
            update_post_meta($post_id, '_ezine_post_cr_tagprotect', sanitize_text_field($_POST['post_cr_tagprotect'])); 
            update_post_meta($post_id, '_ezine_post_cr_maxspindepth', intval($_POST['post_cr_maxspindepth']));            
        }
    }

    public function spin_post() {
       		if (!wp_verify_nonce($_POST['nonce'], $this->plugin_handle))
            return;
        $post_id = intval($_POST['post_id']);
        if ($post_id < 1)
            return;
        $post = get_post($post_id);			
        if (!$post)
            return;
        $sp = $_POST['sp_spinner'];
        $this->modules[$sp]->spin($post);       
    }
  
     public function filter_spin_text($text)
    {
            $para = array();
            $para['auto'] = 1; // auto spin from Poster extension                         
            try {
              return $this->modules[$this->option['sp_spinner']]->spin_text($text);
            }
            catch(Exception $e)
            {                           
            }
            return $text;         
    }
     
    public function spin_text($text, $params)
    {
            try {                
                return $this->modules[$this->option['sp_spinner']]->spin_text($text, $params);                
            }
            catch(Exception $e)
            {                        
            }
            return $text;
    }
    
    public function single_spin_text() {
        if (!wp_verify_nonce($_POST['nonce'], $this->plugin_handle))
            return;
        $sp = $_POST['sp_spinner'] ;       
        $text = stripslashes($_POST['text']);
        $success = 1; 
        $mess = "";
        try {            
            $spun_text = $this->modules[$sp]->single_spin_text($text);  
        }
        catch (Exception $e)
        {
            $spun_text = $text;
            $success = 0;
            $mess = $e->getMessage();
        }
        echo (json_encode(array('success' => $success,
                                                    'text' => $mess,
                                                    'spun_text' => $spun_text)));
        exit;
    }

    public function test_spin() {
        $this->set_option('sp_spinner', $_POST['sp_spinner']);                
        // test authenticate before to reduce request
        if ($this->spin_authenticate()) {
            $this->do_test_spin();
        }        
        $result = array();
        if ($this->get_option('sp_error') == 1) {
            $result['text'] = $this->get_option('sp_error_message');
            $result['error'] = 1;
        } else {
            $result['text'] =  "Test Spin \"" . $this->spinners[$_POST['sp_spinner']] . "\" success. " . $this->get_option('sp_message');
            $result['error'] = 0;
        }
        exit(json_encode($result));
    }
    
    // do test spin
    public function do_test_spin() {
          $this->modules[$this->option['sp_spinner']]->do_test_spin();
    }

    public function mce_plugin($plugins) {
        global $typenow;
        if(!isset($this->option['sp_spinner']) or empty($this->option['sp_spinner']))
			 return $plugins;
        // do not add editor button for other post types
        if ($typenow != 'bulkpost' && $typenow != 'bulkpage' ) 
            return $plugins;
        
        $plugins['mainwparticle'] = $this->plugin_url . 'js/mce/editor_plugin.js';
        return $plugins;
    }

    public function mce_button($buttons) {
        $buttons[] = "mainwparticle";
        return $buttons;
    }    
    
    public function mce_setting($settings)
    {
        $valid_elem = 'span[id|class]';
        $valid_chil = "p[span]" . 
                                ",span[span|p|a|b|i|u|sup|sub|img]";                
        /* $settings['extended_valid_elements'] = !empty($settings['extended_valid_elements']) ? $settings['extended_valid_elements'] . ',' . $valid_elem : $valid_elem; */
        /* $settings['valid_children'] = !empty($settings['valid_children']) ? $settings['valid_children'] . ',' . $valid_chil : $valid_chil;         */
        $settings['paste_remove_spans'] = false;
        return $settings;
    }
    
    public function manage_posts_filter($where) {
        global $wpdb;
        if (!current_user_can('edit_posts'))
            return $where;
        if (isset($_GET['filter_article_spin']) && $_GET['filter_article_spin'] != 'all') {
            $spin_where = " $wpdb->postmeta.meta_key = '_ezine_spin_auto' AND $wpdb->postmeta.meta_value = 'true' ";
            if ($_GET['filter_article_spin'] == 'spin') {
                $in = "IN";
            } else {
                $in = "NOT IN";
            }
            $where .= " AND $wpdb->posts.ID $in (";
            $where .= " 	SELECT DISTINCT ID FROM $wpdb->posts JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id ) ";
            $where .= " 	WHERE $spin_where";
            $where .= ") ";
        }
        return $where;
    }

    public function add_column_headers($column_headers) {
        $column_headers['mainwp-spin'] = __("Auto-Spun");
        return $column_headers;
    }

    public function manage_column($column_name, $post_id) {
        if ($column_name == 'mainwp-spin') {
            $autospin = get_post_meta($post_id, '_ezine_spin_auto', true);
            if ($autospin == 'true')
                _e("Yes");
            else
                _e("No");
        }
// work with Directory Press
        if ($column_name == 'mainwp-spin') {
            remove_filter('manage_posts_custom_column', 'premiumpress_custom_column');
        }
    }

    public function spin_authenticate() {
        return $this->modules[$this->option['sp_spinner']]->spin_authenticate();        
    }

    public function spin_text_bs($text, $params = null) {
        // auto: pass from Poster extension
        if ($params['auto'] && $this->get_option('sp_enable') == 0) {            
           return $text;
        }
        $bs_max_synonyms = isset($params['bs_max_synonyms']) ? $params['bs_max_synonyms'] : $this->get_option('bs_max_synonyms');
        $bs_quality = isset($params['bs_quality']) ? $params['bs_quality'] : $this->get_option('bs_quality');
        $bs_exclude_words = isset($params['bs_exclude_words']) ? $params['bs_exclude_words'] : $this->get_option('bs_exclude_words');
        if ($this->bs_session === false) {
            return $text;
        } else if (empty($this->bs_session)) {
            // authenticate first            
            if (!$this->spin_authenticate()) {
                throw new MainWPSpinLoginFailed_Exception();
                //return $text;
            }
        }

        if (is_null($bs_max_synonyms))
            $bs_max_synonyms = $this->get_option('bs_max_synonyms');
        if (is_null($bs_quality))
            $bs_quality = $this->get_option('bs_quality');

        $call = wp_remote_post($this->bs_api_url, array(
            'headers' => array(
                "Referer" => $this->bs_api_url
            ),
            'body' => array(
                'action' => "replaceEveryonesFavorites",
                'format' => "php",
                'text' => $this->unspin_text($text),
                'maxsyns' => $bs_max_synonyms + 1,
                'quality' => $bs_quality + 1,
                'session' => $this->bs_session
            ),
            'timeout' => 60
        ));

        if (!$call || is_a($call, "WP_Error")) {
            throw new MainWPSpinSpinFailed_Exception();
            //return $text;
        }    
        $return = unserialize($call['body']);
        if ($return['success'] == "true") {
            $excludes = explode(',', ( is_null($bs_exclude_words) ? $this->get_option('bs_exclude_words') : $bs_exclude_words));
            if (preg_match_all('/(\{(.*?)\})/is', $return['output'], $matches)) {
                $spin_text = $return['output'];
                foreach ($matches[2] as $k => $match) {
                    $syn = explode('|', $match);
                    $spin = '';
// check if the words is excluded
                    foreach ($excludes as $exclude) {
                        if (preg_match('/' . $syn[0] . '/i', trim($exclude)))
                            $spin = $syn[0];
                    }
//if ( empty($spin) )
//	$spin = $syn[array_rand($syn)];
                    $to_match = preg_replace('/([}{|])/is', '\\\$1', $matches[1][$k]);
                    if (!empty($spin))
                        $spin_text = preg_replace('/' . $to_match . '/is', $spin, $spin_text, 1);
                }
                return stripslashes($spin_text);
            }
        }
        return $text;
    }

    public function spin_text_sc($text, $params = null) {
        // auto: pass from Poster extension
        if ($params['auto'] || $this->get_option('sp_enable') == 0) {            
            return $text;
        }

        $sc_wordscount = isset($params['sc_wordscount']) ? $params['sc_wordscount'] : $this->get_option('sc_wordscount');
        $sc_spinfreq = isset($params['sc_spinfreq']) ? $params['sc_spinfreq'] : $this->get_option('sc_spinfreq');
        $sc_excludes = isset($params['sc_protect_words']) ? $params['sc_protect_words'] : $this->get_option('sc_protect_words');
        //$sc_original = isset($params['sc_use_original_word']) ? $params['sc_use_original_word'] : $this->get_option('sc_use_original_word');
        $sc_original = 1;
        $sc_protect_html = isset($params['sc_protect_html']) ? $params['sc_protect_html'] : $this->get_option('sc_protect_html');
        $sc_orderly = isset($params['sc_use_synonyms_orderly']) ? $params['sc_use_synonyms_orderly'] : $this->get_option('sc_use_synonyms_orderly');
        $sc_type = isset($params['sc_replace_type']) ? $params['sc_replace_type'] : $this->get_option('sc_replace_type');
        $sc_wordquality = isset($params['sc_wordquality']) ? $params['sc_wordquality'] : $this->get_option('sc_wordquality');
        $sc_grammar = isset($params['sc_enable_grammar_ai']) ? $params['sc_enable_grammar_ai'] : $this->get_option('sc_enable_grammar_ai');
        $sc_tag_protect = isset($params['sc_tag_protect']) ? $params['sc_tag_protect'] : $this->get_option('sc_tag_protect');
        $sc_use_pos = isset($params['sc_use_pos']) ? $params['sc_use_pos'] : $this->get_option('sc_use_pos');

        $url = $this->option['sc_ip_port'] . "/apikey=" . $this->option['sc_api_key'] . "&username=" . $this->option['sc_username'] . "&password=" . $this->option['sc_password'] .
                "&wordscount=" . $sc_wordscount . "&spinfreq=" . $sc_spinfreq . "&protectwords=" . $sc_excludes . "&original=" . $sc_original . "&orderly=" . $sc_orderly .
                "&replacetype=" . $sc_type . "&wordquality=" . $sc_wordquality . "&UseGrammarAI=" . $sc_grammar . "&protecthtml=" . $sc_protect_html . "&pos=" . $sc_use_pos . "&tagprotect=" . $sc_tag_protect;
        $return = $this->curl_request("http://" . $url, base64_encode($this->unspin_text($text)));
        $return = base64_decode($return);
        if (strpos($return, "error=") !== false && strpos($return, "error=") == 0) {
            $this->set_option('sp_error', 1);
            $mess = "Can't spin by Spinnerchief account: " . str_replace("error=", "", $return);
            $this->set_option('sp_error_message', $mess);
            throw new MainWPSpinSpinFailed_Exception($mess);
            //return $text;
        }
        return stripslashes($return);
    }

    public function unspin_text($text) {
        if (preg_match_all('/(\{(.*?)\})/is', $text, $matches)) {
            $unspin_text = $text;
            foreach ($matches[2] as $k => $match) {
                $syn = explode('|', $match);
                $unspin = $syn[0];
                $to_match = preg_replace('/([}{|])/is', '\\\$1', $matches[1][$k]);
                $unspin_text = preg_replace('/' . $to_match . '/is', $unspin, $unspin_text, 1);
            }
            return $unspin_text;
        }
        return $text;
    }

    public function generate_spin_data($text, $saved_data = array()) {
        if (preg_match_all('/(\{(.*?)\})/is', $text, $matches)) {
            $data = array();
            if (!is_array($saved_data))
                $saved_data = array();
            foreach ($matches[2] as $k => $match) {
                $syn = explode('|', $match);
                $spin = $syn[array_rand($syn)];
                $to_match = $matches[1][$k];
                foreach ($saved_data as $s => $saved) {
                    if ($saved['match'] == $to_match) {
                        $spin = $saved['replace'];
                        unset($saved_data[$s]);
                        break;
                    }
                }
                $data[] = array(
                    'match' => $to_match,
                    'replace' => $spin
                );
            }
            return $data;
        }
        return null;
    }

    public function filter_post_slug($data, $postarr) {
        if ($postarr['ID']) {            
            if (wp_verify_nonce($_POST['mainwpspin_nonce'], $this->plugin_handle)) {
                // we are editing post
                if (wp_verify_nonce($_POST['mainwp_respin_post'], $this->plugin_handle . '-respin')) {
                    update_post_meta($postarr['ID'], '_ezine_spin_content', $this->generate_spin_data($data['post_content']));
                    update_post_meta($postarr['ID'], '_ezine_spin_title', $this->generate_spin_data($data['post_title']));
                } else {
                    update_post_meta($postarr['ID'], '_ezine_spin_content', $this->generate_spin_data($data['post_content'], get_post_meta($postarr['ID'], '_ezine_spin_content', true)));
                    update_post_meta($postarr['ID'], '_ezine_spin_title', $this->generate_spin_data($data['post_title'], get_post_meta($postarr['ID'], '_ezine_spin_title', true)));
                }
            }
            $spun_title = $this->filter_title($data['post_title'], $postarr['ID']);
            if ($spun_title) {
                $slug = sanitize_title_with_dashes($spun_title);
                $data['post_name'] = wp_unique_post_slug($slug, $postarr['ID'], $data['post_status'], $data['post_type'], $data['post_parent']);
            }
        }
        return $data;
    }

    public function filter_title($text, $post_id = 0, $random = false) {
        global $post;
        if ($post_id == 0)
            $post_id = $post->ID;
        $data = get_post_meta($post_id, '_ezine_spin_title', true);
        return $this->filter_text($text, $data, $random);
    }

    public function filter_content($text, $post_id = 0, $random = false) {
        global $post;
        if ($post_id == 0)
            $post_id = $post->ID;
        $data = get_post_meta($post_id, '_ezine_spin_content', true);
        $content = $this->filter_text($text, $data, $random);
        return $content;
    }

    public function filter_posts($posts) {
        $filtered_posts = array();
        foreach ($posts as $post) {           
            $post->post_title = $this->filter_title($post->post_title, $post->ID);
            $post->post_content = $this->filter_content($post->post_content, $post->ID);                 
            $filtered_posts[] = $post;
        }
        return $filtered_posts;
    }
    
    public function filter_bulkpost_data($post_data) {
       $new_post = unserialize(base64_decode($post_data['new_post']));
       
       $spin_me = "";
       if (is_array($new_post) && isset($new_post['mainwp_post_id']) && $pid = $new_post['mainwp_post_id']) {
           $spin_me = get_post_meta($pid, '_mainwp_spin_me', true);
       }
       
        if (is_array($new_post) && ((isset($new_post['id_spin']) && intval($new_post['id_spin']) > 0) || $spin_me === "yes"))   {            
            $new_post['post_title'] = $this->parse_spin_text($new_post['post_title']);
            $new_post['post_content'] = $this->parse_spin_text($new_post['post_content']);
            $post_data['new_post'] = base64_encode(serialize($new_post));
        }            
        return $post_data;
    }
    
    function parse_spin_text($data) {	
	$leftchar = "{";
           $rightchar = "}";
           $splitchar = "|";
           $start_pos = array(); 
           $pos = -1;          
	while ($pos++ < strlen($data)) {
		if (substr($data, $pos, strlen($leftchar)) == $leftchar) {
                                 $start_pos[] = $pos;                                 	
		} elseif (substr($data, $pos, strlen($rightchar)) == $rightchar) {                            
                                 $startPos = array_pop($start_pos);
			$entirespinner = substr($data, $startPos+strlen($leftchar), ($pos - $startPos)-strlen($rightchar));
                                 $syn = explode($splitchar,$entirespinner);                                
                                 $processed = $syn[array_rand($syn)];    
			$data = str_replace($leftchar.$entirespinner.$rightchar,$processed,$data); 
                                 $pos = $startPos; 
		}
	}
	return $data;
}
    
    public function filter_text($text, $data, $random = false) {
        $spin_text = $text;        
        foreach ((array) $data as $d) { 
			if(!is_array($d))
            { 
                 $d = (array)$d;
            }		
            if (isset($d['match'])) {
                $to_match = preg_replace('/([}{|\/])/is', '\\\$1', $d['match']);      
                if ($random) {
                    $match = str_replace(array("{", "}", "\\"), "", $to_match);                                
                    $syn = explode('|', $match);
                    $spin = $syn[array_rand($syn)];                                 
                    $spin_text = preg_replace('/'.$to_match.'/is', $spin, $spin_text, 1);
                }
                else {               
                    if (isset($d['replace']))
                        $spin_text = preg_replace('/' . $to_match . '/is', $d['replace'], $spin_text, 1);          
                }
            }
        }
        return $spin_text;
    }

    protected function create_option_field($name, $label, $type, $default = null, $fields = null, $description = null, $inline = false, $specialchars = false, $single_checkbox = false, $check_option_value = true) {
        echo '<div class="spinner_option-list">';
        echo '<label for="' . $name . '">' . $label . '</label>';
        echo '<div class="spinner_option-field">';
        switch ($type) {
            case "text":
            case "password":
                echo '<input type="' . $type . '" class="text" name="' . $name . '" id="' . $name . '" value="' . (!is_null($default) && !$this->get_option($name) ? $default : $this->get_option($name)) . '" />';
                break;

            case "file":
                echo '<input type="' . $type . '" name="' . $name . '" id="' . $name . '" />';
                break;

            case "textarea":
                if ($specialchars)
                    $value = htmlspecialchars($this->get_option($name));
                else
                    $value = $this->get_option($name);
                echo '<textarea class="text" rows="5" cols="50" name="' . $name . '" id="' . $name . '">' . (!is_null($default) && !$this->get_option($name) ? $default : $value) . '</textarea>';
                break;

            case "select":
                echo '<select name="' . $name . '" id="' . $name . '">';
                foreach ((array) $fields as $val => $field) {
                    echo '<option value="' . $val . '" ' . ( $this->get_option($name) == $val || (!is_null($default) && $this->get_option($name) === '' && $default == $val ) ? 'selected="selected"' : '' ) . '>' . $field . '</option>';
                }
                echo '</select>';
                break;

            case "checkbox":
                if (!$single_checkbox)
                    $name .= '[]';
                foreach ((array) $fields as $val => $field) {
                    $checked = "";
                    if (!$check_option_value) {
                        $checked = ($default !== null && $default !== '' && $val == $default) ? 'checked="checked"' : '';
                    }
                    else
                        $checked = ( in_array($val, (array) $this->get_option($name)) || ( is_array($default) && $this->get_option($name) === '' && in_array($val, $default) ) ? 'checked="checked"' : '' );
                    echo '<label>';
                    echo '<input type="checkbox" name="' . $name . '" value="' . $val . '" ' . $checked . ' /> ';
                    echo $field;
                    echo '</label><br />';
                }
                break;

            case "radio":
                foreach ((array) $fields as $val => $field) {
                    echo '<label>';
                    echo '<input type="radio" name="' . $name . '" value="' . $val . '" ' . ( $val == $this->get_option($name) || (!is_null($default) && $this->get_option($name) === '' && $val == $default ) ? 'checked="checked"' : '' ) . ' /> ';
                    echo $field;
                    echo '</label><br />';
                }
                break;
        }
        if (!is_null($description)) {
            if (!$inline)
                echo '<br />';
            echo '<small>' . $description . '</small>';
        }
        echo '</div>';
        echo '</div>';
    }

    public function is_enabled() {
        return $this->get_option('sp_enable');
    }

}

class MainWPSpinLoginFailed_Exception extends Exception {
    public function __construct() {
        parent::__construct("Login has failed. Automatic Spin could not be completed. Please check the login in the Settings page.");
    }
}

class MainWPSpinSpinFailed_Exception extends Exception {
    public function __construct($message = "") {
        if (empty($message))
            parent::__construct("Automatic Spin could not be completed. Please check the login in the Settings page.");
        else
            parent::__construct($message);
    }
}

register_activation_hook(__FILE__, 'mainwp_spin_extension_activate');
function mainwp_spin_extension_activate()
{   
    update_option('mainwp_spin_extension_activated', 'yes');
}


class MainWPSpinActivator {

    protected $mainwpMainActivated = false;    
    protected $childEnabled = false;
    
    public function __construct() {
        $this->mainwpMainActivated = false;
        $this->mainwpMainActivated = apply_filters('mainwp-activated-check', $this->mainwpMainActivated);
        if ($this->mainwpMainActivated !== false) {
            $this->activateThis();
        } else {
            add_action('mainwp-activated', array(&$this, 'activateThis'));
        }
        add_action('admin_init', array(&$this, 'admin_init'));
        add_action('admin_notices', array(&$this, 'mainwp_error_notice'));
        add_filter('mainwp-getextensions', array(&$this, 'getExtension'));
    }

    function admin_init() {
        if (get_option('mainwp_spin_extension_activated') == 'yes')
        {
            delete_option('mainwp_spin_extension_activated');
            wp_redirect(admin_url('admin.php?page=Extensions'));
            return;
        }        
    }
    
    function getExtension($pArray) {
        $pArray[] = array('plugin' =>  __FILE__, 'api' => 'mainwp-spinner', 'mainwp' => true, 'callback' => array(&$this, 'settings'));
        return $pArray;
    }

    function settings() {
        do_action('mainwp-pageheader-extensions', __FILE__);
         if ($this->childEnabled) {
                ?><div class="mainwp_info-box"><strong>Use this to set Spinner Options.</strong></div><?php
                mainwp_spinner::Instance()->option_page();
         } else {
             ?><div class="mainwp_info-box-yellow"><strong>The Extension has to be enabled to change the settings.</strong></div><?php
        }
        do_action('mainwp-pagefooter-extensions', __FILE__);
    }

    function activateThis() {
        $this->mainwpMainActivated = apply_filters('mainwp-activated-check', $this->mainwpMainActivated);

        $activated = apply_filters('mainwp-activated-sub-check', array('mainwp-spinner'));
        if (!isset($activated['result']) || $activated['result'] != 'VALID') return;
        
        $this->childEnabled = apply_filters('mainwp-extension-enabled-check', __FILE__);
        if (!$this->childEnabled) return;
         
        if (function_exists("mainwp_current_user_can")&& !mainwp_current_user_can("extension", "mainwp-spinner"))
            return; 
        
        $mainwp_spin = mainwp_spinner::Instance();
               
        add_filter('mainwp-pre-posting-posts', array(&$mainwp_spin, 'filter_bulkpost_data'));
        add_filter('mainwp-spinner-is-enabled', array(&$mainwp_spin, 'is_enabled'));        
        add_filter('mainwparticle-spin-text', array(&$mainwp_spin, 'filter_spin_text'));
    }

    function mainwp_error_notice() {
        global $current_screen;
        if ($current_screen->parent_base == 'plugins') {
            if ($this->mainwpMainActivated == false) {
                echo '<div class="error"><p>MainWP Spinner ' . __('requires <a href="http://mainwp.com/" target="_blank">MainWP Plugin</a> to be activated in order to work. Please install and activate <a href="http://mainwp.com/" target="_blank">MainWP Plugin</a> first.') . '</p></div>';
            }
        }
    }

}

new MainWPSpinActivator();




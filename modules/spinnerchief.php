<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if ( ! class_exists( 'MainWP_Spin_Module_SC' ) ) :

	class MainWP_Spin_Module_SC {

		protected $main;
		protected $api_url;
		protected $api_key;
		protected $username;
		protected $passw;

		public function __construct( $main_obj, $ipPort, $apiKey, $userName, $passW ) {
			$this->main = $main_obj;
			$this->api_url = $ipPort;
			$this->api_key = $apiKey;
			$this->username = $userName;
			$this->passw = $passW;
		}

		public function spin( $post ) {
			if ( ! $post ) {
				return; }
			$para = array();
			$spin_title = intval( $_POST['post_sp_spin_title'] );
			$para['sc_spinfreq'] = intval( $_POST['post_sc_spinfreq'] );
			$para['sc_wordscount'] = intval( $_POST['post_sc_wordscount'] );
			$para['sc_protect_html'] = intval( $_POST['post_sc_protect_html'] );
			$para['sc_use_synonyms_orderly'] = intval( $_POST['post_sc_use_synonyms_orderly'] );
			$para['sc_replace_type'] = intval( $_POST['post_sc_replace_type'] );
			$para['sc_wordquality'] = intval( $_POST['post_sc_wordquality'] );
			$para['sc_enable_grammar_ai'] = intval( $_POST['post_sc_enable_grammar_ai'] );
			$para['sc_use_pos'] = intval( $_POST['post_sc_use_pos'] );
			$para['sc_protect_words'] = strip_tags( $_POST['post_sc_protect_words'] );
			$para['sc_tag_protect'] = stripcslashes( $_POST['post_sc_tag_protect'] );
			$this->spin_post( $post, $spin_title, $para );
		}

		public function spin_post( $post, $spin_title = 0, $params = array() ) {
			$post_id = $post->ID;
			$success = 1;
			try {
				$spun_post_content = $this->spin_text( $post->post_content, $params );
				$spun_post_title = ( 1 == $spin_title ) ? $this->spin_text( $post->post_title, $params ) : $this->main->unspin_text( $post->post_title );
			} catch (Exception $e) {
				$spun_post_content = $post->post_content;
				$spun_post_title = ( 1 == $spin_title ) ? $post->post_title : $this->main->unspin_text( $post->post_title );
				$success = 0;
				$mess = $e->getMessage();
			}

			$post_args = array(
				'ID' => $post_id,
				'post_content' => $spun_post_content,
				'post_title' => $spun_post_title,
				'post_status' => $post->post_status,
				'post_date' => $post->post_date,
			);
			$save_post_type = get_post_type( $post_id );
			wp_update_post( $post_args );
			$new_post = get_post( $post_id );
			update_post_meta( $post_id, '_mainwp_spinner_spin_auto', 'false' );
			update_post_meta( $post_id, '_mainwp_spinner_spin_content', $this->main->generate_spin_data( $new_post->post_content ) );
			update_post_meta( $post_id, '_mainwp_spinner_spin_title', $this->main->generate_spin_data( $new_post->post_title ) );
			wp_update_post(array(
				'ID' => $post_id,
				'post_title' => $new_post->post_title,
				'post_type' => $save_post_type,
			));
			echo json_encode(array(
				'success' => $success,
				'text' => $mess,
				'post' => get_post( $post_id ),
			));
			exit;
		}

		public function spin_text( $text, $params = array() ) {
			// auto: pass from Poster extension
			if ( (isset($params['auto']) && $params['auto']) || $this->main->get_option( 'sp_enable' ) == 0 ) {
				return $text;
			}
			$sc_wordscount = isset( $params['sc_wordscount'] ) ? $params['sc_wordscount'] : $this->main->get_option( 'sc_wordscount' );
			$sc_spinfreq = isset( $params['sc_spinfreq'] ) ? $params['sc_spinfreq'] : $this->main->get_option( 'sc_spinfreq' );
			$sc_excludes = isset( $params['sc_protect_words'] ) ? $params['sc_protect_words'] : $this->main->get_option( 'sc_protect_words' );
			//$sc_original = isset($params['sc_use_original_word']) ? $params['sc_use_original_word'] : $this->main->get_option('sc_use_original_word');
			$sc_original = 1;
			$sc_protect_html = isset( $params['sc_protect_html'] ) ? $params['sc_protect_html'] : $this->main->get_option( 'sc_protect_html' );
			$sc_orderly = isset( $params['sc_use_synonyms_orderly'] ) ? $params['sc_use_synonyms_orderly'] : $this->main->get_option( 'sc_use_synonyms_orderly' );
			$sc_type = isset( $params['sc_replace_type'] ) ? $params['sc_replace_type'] : $this->main->get_option( 'sc_replace_type' );
			$sc_wordquality = isset( $params['sc_wordquality'] ) ? $params['sc_wordquality'] : $this->main->get_option( 'sc_wordquality' );
			$sc_grammar = isset( $params['sc_enable_grammar_ai'] ) ? $params['sc_enable_grammar_ai'] : $this->main->get_option( 'sc_enable_grammar_ai' );
			$sc_tag_protect = isset( $params['sc_tag_protect'] ) ? $params['sc_tag_protect'] : $this->main->get_option( 'sc_tag_protect' );
			$sc_use_pos = isset( $params['sc_use_pos'] ) ? $params['sc_use_pos'] : $this->main->get_option( 'sc_use_pos' );
			$url = $this->api_url . '/apikey=' . $this->api_key . '&username=' . $this->username . '&password=' . $this->passw .
					'&wordscount=' . $sc_wordscount . '&spinfreq=' . $sc_spinfreq . '&protectwords=' . $sc_excludes . '&original=' . $sc_original . '&orderly=' . $sc_orderly .
					'&replacetype=' . $sc_type . '&wordquality=' . $sc_wordquality . '&UseGrammarAI=' . $sc_grammar . '&protecthtml=' . $sc_protect_html . '&pos=' . $sc_use_pos . '&tagprotect=' . $sc_tag_protect;
			$return = $this->curl_request( 'http://' . $url, base64_encode( $this->main->unspin_text( $text ) ) );
			$return = base64_decode( $return );
			if ( strpos( $return, 'error=' ) !== false && strpos( $return, 'error=' ) == 0 ) {
				$this->main->set_option( 'sp_error', 1 );
				$mess = "Can't spin by Spinnerchief account: " . str_replace( 'error=', '', $return );
				$this->main->set_option( 'sp_error_message', $mess );
				throw new MainWPSpinSpinFailed_Exception( $mess );
				//return $text;
			}
			return stripslashes( $return );
		}

		public function single_spin_text( $text ) {
			$para = array();
			$para['sc_spinfreq'] = intval( $_POST['post_sc_spinfreq'] );
			$para['sc_wordscount'] = intval( $_POST['post_sc_wordscount'] );
			$para['sc_protect_html'] = intval( $_POST['post_sc_protect_html'] );
			$para['sc_use_synonyms_orderly'] = intval( $_POST['post_sc_use_synonyms_orderly'] );
			$para['sc_replace_type'] = intval( $_POST['post_sc_replace_type'] );
			$para['sc_wordquality'] = intval( $_POST['post_sc_wordquality'] );
			$para['sc_enable_grammar_ai'] = intval( $_POST['post_sc_enable_grammar_ai'] );
			$para['sc_use_pos'] = intval( $_POST['post_sc_use_pos'] );
			$para['sc_protect_words'] = strip_tags( $_POST['post_sc_protect_words'] );
			$para['sc_tag_protect'] = $_POST['post_sc_tag_protect'];
			return $this->spin_text( $text, $para );
		}

		public function do_test_spin() {
			// test spin only
			$url = $this->api_url . '/apikey=' . $this->api_key . '&username=' . $this->username . '&password=' . $this->passw . '&querytimes=2';
			$querytimes = $this->curl_request( 'http://' . $url, 'Test' );
			if ( strpos( $querytimes, 'error=' ) === false ) {
				$this->main->set_option( 'sp_message', 'Remain Spinnerchief queries: ' . base64_decode( $querytimes ) );
			} 
		}

		public function spin_authenticate() {
			$error_sc = false;
			$this->main->set_option( 'sp_error_message', '' );
			$this->main->set_option( 'sp_error', 0 );
			$url = $this->api_url . '/apikey=' . $this->api_key . '&username=' . $this->username . '&password=' . $this->passw;
			// test spin
			$result = $this->curl_request( 'http://' . $url, 'This is test Spinnerchief' );
			if ( ! $result ) {
				$this->main->set_option( 'sp_error_message', "Can't connect to Spinnerchief." );
				$error_sc = true;
			}
			if ( strpos( $result, 'error=' ) === false ) {
				// do nothing
			} else if ( strpos( $result, 'error=' ) == 0 ) {
				$this->main->set_option( 'sp_error_message', "Can't spin by Spinnerchief account, " . str_replace( 'error=', '', $result ) );
				$error_sc = true;
			}
			if ( $error_sc ) {
				$this->main->set_option( 'sp_error', 1 );
				$this->main->set_option( 'sp_message', '' ); // clear message of sc spinner
				return false;
			}
			return true;
		}

		function curl_request( $url, $data ) {
			$req = curl_init();
			curl_setopt( $req, CURLOPT_URL, $url );
			curl_setopt( $req, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $req, CURLOPT_POST, true );
			curl_setopt( $req, CURLOPT_POSTFIELDS, $data );
			curl_setopt( $req, CURLOPT_TIMEOUT, 60 * 20 );
			$result = trim( curl_exec( $req ) );
			curl_close( $req );
			return $result;
		}
	}




endif;

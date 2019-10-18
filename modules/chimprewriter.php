<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if ( ! class_exists( 'MainWP_Spin_Module_CR' ) ) :

	class MainWP_Spin_Module_CR {

		protected $main;
		protected $spin_chimp;

		public function __construct( $main_obj, $userName, $apiKey, $appId = 'wp-spinchimp' ) {
			$this->main = $main_obj;
			require_once( $this->main->plugin_dir . '/libs/spinchimp.class.php' );
			$this->spin_chimp = new SpinChimp( $userName, $apiKey, $appId );
		}

		public function spin( $post ) {
			if ( ! $post ) {
				return; }
			$para = array();
			$spin_title = intval( $_POST['post_sp_spin_title'] );
			$para['cr_quality'] = intval( $_POST['post_cr_quality'] );
			$para['cr_posmatch'] = intval( $_POST['post_cr_posmatch'] );
			$para['cr_protectedterms'] = sanitize_text_field( $_POST['post_cr_protectedterms'] );
			$para['cr_rewrite'] = intval( $_POST['post_cr_rewrite'] );
			$para['cr_phraseignorequality'] = intval( $_POST['post_cr_phraseignorequality'] );
			$para['cr_spinwithinspin'] = intval( $_POST['post_cr_spinwithinspin'] );
			$para['cr_spinwithinhtml'] = intval( $_POST['post_cr_spinwithinhtml'] );
			$para['cr_applyinstantunique'] = intval( $_POST['post_cr_applyinstantunique'] );
			$para['cr_fullcharset'] = intval( $_POST['post_cr_fullcharset'] );
			$para['cr_spintidy'] = intval( $_POST['post_cr_spintidy'] );
			$para['cr_tagprotect'] = sanitize_text_field( $_POST['post_cr_tagprotect'] );
			$para['cr_maxspindepth'] = intval( $_POST['post_cr_maxspindepth'] );
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

			$cr_quality = isset( $params['cr_quality'] ) ? intval( $params['cr_quality'] ) : $this->main->get_option( 'cr_quality' );
			$cr_posmatch = isset( $params['cr_posmatch'] ) ? intval( $_POST['cr_posmatch'] ) : $this->main->get_option( 'cr_posmatch' );
			$cr_protectedterms = isset( $params['cr_protectedterms'] ) ? sanitize_text_field( $params['cr_protectedterms'] ) : $this->main->get_option( 'cr_protectedterms' );
			$cr_rewrite = isset( $params['cr_rewrite'] ) ? intval( $params['cr_rewrite'] ) : $this->main->get_option( 'cr_rewrite' );
			$cr_phraseignorequality = isset( $params['cr_phraseignorequality'] ) ? intval( $params['cr_phraseignorequality'] ) : $this->main->get_option( 'cr_phraseignorequality' );
			$cr_spinwithinspin = isset( $params['cr_spinwithinspin'] ) ? intval( $params['cr_spinwithinspin'] ) : $this->main->get_option( 'cr_spinwithinspin' );
			$cr_spinwithinhtml = isset( $params['cr_spinwithinhtml'] ) ? intval( $params['cr_spinwithinhtml'] ) : $this->main->get_option( 'cr_spinwithinhtml' );
			$cr_applyinstantunique = isset( $params['cr_applyinstantunique'] ) ? intval( $params['cr_applyinstantunique'] ) : $this->main->get_option( 'cr_applyinstantunique' );
			$cr_fullcharset = isset( $params['cr_fullcharset'] ) ? intval( $params['cr_fullcharset'] ) : $this->main->get_option( 'cr_fullcharset' );
			$cr_spintidy = isset( $params['cr_spintidy'] ) ? intval( $params['cr_spintidy'] ) : $this->main->get_option( 'cr_spintidy' );
			$cr_tagprotect = isset( $params['cr_tagprotect'] ) ? sanitize_text_field( $params['cr_tagprotect'] ) : $this->main->get_option( 'cr_tagprotect' );
			$cr_maxspindepth = isset( $params['cr_maxspindepth'] ) ? intval( $params['cr_maxspindepth'] ) : $this->main->get_option( 'cr_maxspindepth' );
			// comment this line if do not nested spin
			$this->spin_chimp->setMaxSpinDepth( $cr_maxspindepth );
			$this->spin_chimp->setPOSMatch( $cr_posmatch );
			$this->spin_chimp->setProtectedTerms( $cr_protectedterms );
			$this->spin_chimp->setSpinQuality( $cr_quality );
			$this->spin_chimp->setTagProtect( $cr_tagprotect );
			$this->spin_chimp->setParam( 'rewrite', $cr_rewrite );
			$this->spin_chimp->setParam( 'phraseignorequality', $cr_phraseignorequality );
			$this->spin_chimp->setParam( 'spinwithinspin', $cr_spinwithinspin );
			// comment this line if do not it's replace letters
			$this->spin_chimp->setParam( 'applyinstantunique', $cr_applyinstantunique );
			// comment this line if do not use applyinstantunique
			$this->spin_chimp->setParam( 'fullcharset', $cr_fullcharset );
			$this->spin_chimp->setParam( 'spintidy', $cr_spintidy );
			$this->spin_chimp->setParam( 'spinwithinhtml', $cr_spinwithinhtml );
			$return = $this->spin_chimp->GlobalSpin( $text, $cr_rewrite );
			if ( ! $return['success'] ) {
				$this->main->set_option( 'sp_error', 1 );
				$mess = "Can't spin by Chimp Rewriter account: " . $return['error'];
				$this->main->set_option( 'sp_error_message', $mess );
				throw new MainWPSpinSpinFailed_Exception( $mess );
			}
			return stripslashes( $return['result'] );
		}

		public function single_spin_text( $text ) {
			$para = array();
			$para['cr_quality'] = intval( $_POST['post_cr_quality'] );
			$para['cr_posmatch'] = intval( $_POST['post_cr_posmatch'] );
			$para['cr_protectedterms'] = sanitize_text_field( $_POST['post_cr_protectedterms'] );
			$para['cr_rewrite'] = intval( $_POST['post_cr_rewrite'] );
			$para['cr_phraseignorequality'] = intval( $_POST['post_cr_phraseignorequality'] );
			$para['cr_spinwithinspin'] = intval( $_POST['post_cr_spinwithinspin'] );
			$para['cr_spinwithinhtml'] = intval( $_POST['post_cr_spinwithinhtml'] );
			$para['cr_applyinstantunique'] = intval( $_POST['post_cr_applyinstantunique'] );
			$para['cr_fullcharset'] = intval( $_POST['post_cr_fullcharset'] );
			$para['cr_spintidy'] = intval( $_POST['post_cr_spintidy'] );
			$para['cr_tagprotect'] = sanitize_text_field( $_POST['post_cr_tagprotect'] );
			$para['cr_maxspindepth'] = intval( $_POST['post_cr_maxspindepth'] );
			return $this->spin_text( $text, $para );
		}

		public function do_test_spin() {
			// test spin only
			$result = $this->spin_chimp->QueryStats( 1 );
			if ( $result['success'] ) {
				$this->main->set_option( 'sp_message', 'Remain Chimp Rewriter queries:  ' . $result['result'] );
			} else {
				$this->main->set_option( 'sp_error_message', 'Test Chimp Rewriter failed: ' . $result['error'] );
				$this->main->set_option( 'sp_error', 1 );				
			}
		}

		public function spin_authenticate() {
			$this->main->set_option( 'sp_error_message', '' );
			$this->main->set_option( 'sp_error', 0 );
			$result = $this->spin_chimp->QueryStats( 1 );
			if ( $result['success'] ) {
				return true;
			} else {
				$this->main->set_option( 'sp_error_message', "Can't spin by Chimp Rewriter account: " . $result['error'][0] );
				$this->main->set_option( 'sp_error', 1 );
				$this->main->set_option( 'sp_message', '' ); // clear message of sc spinner
				return false;
			}
		}
	}




endif;

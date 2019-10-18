<?php

if ( ! class_exists( 'MainWP_Spin_Module_TBS' ) ) :

	class MainWP_Spin_Module_TBS {

		protected $main;
		protected $api_url = 'http://thebestspinner.com/api.php';
		protected $api_email;
		protected $api_passw;
		protected $bs_session;

		public function __construct( $main_obj, $apiEmail, $apiPass ) {
			$this->main = $main_obj;
			$this->api_email = $apiEmail;
			$this->api_passw = $apiPass;
		}

		public function spin( $post ) {
			if ( ! $post ) {
				return; }
			$para = array();
			$spin_title = intval( $_POST['post_sp_spin_title'] );
			$para['bs_max_synonyms'] = intval( $_POST['post_bs_max_synonyms'] );
			$para['bs_quality'] = intval( $_POST['post_bs_quality'] );
			$para['bs_exclude_words'] = strip_tags( $_POST['post_bs_exclude_words'] );
			$this->spin_post( $post, $spin_title, $para );
		}

		public function spin_post( $post, $spin_title = 0, $params = array() ) {
			$post_id = $post->ID;
			$success = 1;
			$mess = '';
			
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
			if ( isset($params['auto']) && $params['auto'] && $this->main->get_option( 'sp_enable' ) == 0 ) {
				return $text;
			}
			$bs_max_synonyms = isset( $params['bs_max_synonyms'] ) ? $params['bs_max_synonyms'] : $this->main->get_option( 'bs_max_synonyms' );
			$bs_quality = isset( $params['bs_quality'] ) ? $params['bs_quality'] : $this->main->get_option( 'bs_quality' );
			$bs_exclude_words = isset( $params['bs_exclude_words'] ) ? $params['bs_exclude_words'] : $this->main->get_option( 'bs_exclude_words' );

			if ( (get_option( 'mainwp_spinner_error_need_to_reauth' ) == 'yes') || (empty( $this->bs_session )) ) {
				//            error_log("re-authentication");
				update_option( 'mainwp_spinner_error_need_to_reauth', '' );
				if ( ! $this->spin_authenticate() ) {
					throw new MainWPSpinLoginFailed_Exception(); }
			}
			if ( is_null( $bs_max_synonyms ) ) {
				$bs_max_synonyms = $this->main->get_option( 'bs_max_synonyms' ); }
			if ( is_null( $bs_quality ) ) {
				$bs_quality = $this->main->get_option( 'bs_quality' ); }
			$call = wp_remote_post($this->api_url, array(
				'headers' => array(
					'Referer' => $this->api_url,
				),
				'body' => array(
					'action' => 'replaceEveryonesFavorites',
					'format' => 'php',
					'text' => $this->main->unspin_text( $text ),
					'maxsyns' => $bs_max_synonyms + 1,
					'quality' => $bs_quality + 1,
					'session' => $this->bs_session,
				),
				'timeout' => 60 * 20,
			));
			if ( ! $call || is_a( $call, 'WP_Error' ) ) {
				throw new MainWPSpinSpinFailed_Exception();
				//return $text;
			}
			$return = unserialize( $call['body'] );
			//error_log(print_r($call, true));
			if ( 'true' == $return['success'] ) {
				$excludes = explode( ',', ( is_null( $bs_exclude_words ) ? $this->main->get_option( 'bs_exclude_words' ) : $bs_exclude_words) );
				if ( preg_match_all( '/(\{(.*?)\})/is', $return['output'], $matches ) ) {
					$spin_text = $return['output'];
					foreach ( $matches[2] as $k => $match ) {
						$syn = explode( '|', $match );
						$spin = '';
						// check if the words is excluded
						foreach ( $excludes as $exclude ) {
							if ( preg_match( '/' . $syn[0] . '/i', trim( $exclude ) ) ) {
								$spin = $syn[0]; }
						}
						$to_match = preg_replace( '/([}{|])/is', '\\\$1', $matches[1][ $k ] );
						if ( ! empty( $spin ) ) {
							$spin_text = preg_replace( '/' . $to_match . '/is', $spin, $spin_text, 1 ); }
					}
					return stripslashes( $spin_text );
				}
			} else {
				if ( isset( $return['error'] ) ) {
					$error = $return['error'];
					if ( stripos( $error, 'Your session has expired' ) !== false ) {
						$error = 'Your session has expired. Please click the Spin Now button to re-authenticate.';
					}
				} else {
					$error = __( 'Error: Undefined error.', 'mainwp' ); }
				update_option( 'mainwp_spinner_error_need_to_reauth', 'yes' );
				throw new Exception( $error );
			}
			return $text;
		}

		public function single_spin_text( $text ) {
			$para = array();
			$para['bs_max_synonyms'] = intval( $_POST['post_bs_max_synonyms'] );
			$para['bs_quality'] = intval( $_POST['post_bs_quality'] );
			$para['bs_exclude_words'] = strip_tags( $_POST['post_bs_exclude_words'] );
			return $this->spin_text( $text, $para );
		}

		// do test spin
		public function do_test_spin() {
			$error = false;
			$call = wp_remote_post($this->api_url, array(
				'headers' => array(
					'Referer' => $this->api_url,
				),
				'body' => array(
					'action' => 'replaceEveryonesFavorites',
					'format' => 'php',
					'text' => 'Try to spin a text to test',
					'maxsyns' => 3,
					'quality' => 3,
					'session' => $this->bs_session,
				),
				'timeout' => 60 * 20,
			));
			if ( ! $call || is_a( $call, 'WP_Error' ) ) {
				$this->main->set_option( 'sp_error_message', "Can't connect to The Best Spinner." );
				$error = true;
			} else {
				$return = unserialize( $call['body'] );
				if ( 'true' == $return['success'] ) {
					$this->main->set_option( 'sp_message', '' );
				} else {
					$this->bs_session = false;
					$this->main->set_option( 'sp_error_message', "Can't spin by The Best Spinner account: " . $return['error'] );
					$error = true;
				}
			}
			if ( $error ) {
				$this->main->set_option( 'sp_error', 1 );
			} else {
				$this->main->set_option( 'sp_error', 0 );
			}
		}

		public function spin_authenticate() {
			$error_bs = false;
			$this->main->set_option( 'sp_error_message', '' );
			$this->main->set_option( 'sp_error', 0 );
			$call = wp_remote_post($this->api_url, array(
				'headers' => array(
					'Referer' => $this->api_url,
				),
				'body' => array(
					'action' => 'authenticate',
					'format' => 'php',
					'username' => $this->api_email,
					'password' => $this->api_passw,
				),
				'timeout' => 60 * 20,
			));
			if ( ! $call || is_a( $call, 'WP_Error' ) ) {
				$this->main->set_option( 'sp_error_message', "Can't connect to The Best Spinner." );
				$error_bs = true;
			} else {
				$return = unserialize( $call['body'] );
				if ( 'true' == $return['success'] ) {
					$this->bs_session = $return['session'];
					$this->main->set_option( 'sp_message', '' );
				} else {
					$this->bs_session = false;
					$this->main->set_option( 'sp_error_message', "Can't authenticate The Best Spinner account, make sure the username and password is correct." );
					$error_bs = true;
				}
			}
			if ( $error_bs ) {
				$this->main->set_option( 'sp_error', 1 );
				return false;
			}
			return true;
		}
	}




endif;

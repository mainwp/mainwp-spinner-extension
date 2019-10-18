<?php

if ( ! class_exists( 'MainWP_Word_AI' ) ) :

	class MainWP_Word_AI {

		protected $main;
		protected $turing_api_url = 'http://wordai.com/users/turing-api.php';
		protected $api_email;
		protected $api_passw;
		protected $api_hash;

		public function __construct( $main_obj, $apiEmail, $apiPass = '', $apiHash = '' ) {
			$this->main = $main_obj;
			$this->api_email = $apiEmail;
			$this->api_passw = $apiPass;
			$this->api_hash = $apiHash;
		}

		public function spin( $post ) {
			if ( ! $post ) {
				return; }
			$para = array();
			$spin_title = intval( $_POST['post_sp_spin_title'] );
			$para['wai_quality'] = intval( $_POST['post_wai_quality'] );
			$para['wai_nonested'] = intval( $_POST['post_wai_nonested'] );
			$para['wai_sentence'] = intval( $_POST['post_wai_sentence'] );
			$para['wai_paragraph'] = intval( $_POST['post_wai_paragraph'] );
			$para['wai_returnspin'] = intval( $_POST['post_wai_returnspin'] );
			$para['wai_nooriginal'] = intval( $_POST['post_wai_nooriginal'] );
			$para['wai_protected'] = sanitize_text_field( $_POST['post_wai_protected'] );
			$para['wai_synonyms'] = sanitize_text_field( $_POST['post_wai_synonyms'] );
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
			if ( isset($params['auto']) && $params['auto'] && $this->main->get_option( 'sp_enable' ) == 0 ) {
				return $text;
			}

			$error_msg = '';

			if (  empty( $this->api_email ) ) {
				$error_msg = 'Make sure the API email are not empty.';
				throw new Exception( $error_msg );
			}

			$hash = ! empty( $this->api_hash ) ? $this->api_hash : ( ! empty( $this->api_passw ) ? md5( substr( md5( $this->api_passw ),0,15 ) ) : '');

			if (  empty( $hash ) ) {
				$error_msg = 'Make sure the password or hash are not empty.';
				throw new Exception( $error_msg );
			}

			$qualities = array( 0 => 'Regular', 1 => 'Unique', 2 => 'Very Unique', 3 => 'Readable', 4 => 'Very Readable' );
			$wai_quality = isset( $params['wai_quality'] ) ? $params['wai_quality'] : $this->main->get_option( 'wai_quality' );

			$args = array();
			$args['quality'] = $qualities[ $wai_quality ];
			$args['email'] = $this->api_email;
			$args['output'] = 'json';
			$args['hash'] = urlencode( stripslashes( $hash ) );
			$args['nonested'] = isset( $params['wai_nonested'] ) ? $params['wai_nonested'] : $this->main->get_option( 'wai_nonested' );
			$args['sentence'] = isset( $params['wai_sentence'] ) ? $params['wai_sentence'] : $this->main->get_option( 'wai_sentence' );
			$args['paragraph'] = isset( $params['wai_paragraph'] ) ? $params['wai_paragraph'] : $this->main->get_option( 'wai_paragraph' );
			$args['returnspin'] = isset( $params['wai_returnspin'] ) ? $params['wai_returnspin'] : $this->main->get_option( 'wai_returnspin' );
			$args['nooriginal'] = isset( $params['wai_nooriginal'] ) ? $params['wai_nooriginal'] : $this->main->get_option( 'wai_nooriginal' );
			$args['protected'] = isset( $params['wai_protected'] ) ? $params['wai_protected'] : $this->main->get_option( 'wai_protected' );
			$args['protected'] = urlencode( stripslashes( $args['protected'] ) );
			$args['synonyms'] = isset( $params['wai_synonyms'] ) ? $params['wai_synonyms'] : $this->main->get_option( 'wai_synonyms' );
			$args['synonyms'] = urlencode( stripslashes( $args['synonyms'] ) );

			$url = 's=' . urlencode( stripslashes( $text ) );
			foreach ( $args as $key => $val ) {
				$url .= '&' . $key . '=' . $val;
			}
			//error_log($url);
			$ch = curl_init( $this->turing_api_url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_POST, 1 );
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 60 * 20 );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $url );
			$result = curl_exec( $ch );
			$err = curl_error( $ch );
			curl_close( $ch );
			//error_log(print_r($result, true));

			if ( false === $result ) {
				throw new MainWPSpinSpinFailed_Exception( $err );
			} else {
				$data = json_decode( $result );
				//error_log(print_r($data, true));
				if ( is_object( $data ) ) {
					if ( property_exists( $data, 'error' ) && ! empty( $data->error ) ) {
						$error_msg = $data->error;
					} else if ( property_exists( $data, 'status' ) && ($data->status == 'Success') ) {
						return $data->text;
					} else {
						$error_msg = 'Error Result.';
					}
				} else {
					$error_msg = ! empty( $err ) ? $err : $result;
				}

				if ( ! empty( $error_msg ) ) {
					$error_msg = "Can't spin by The WordAi account. " . $error_msg;
					throw new Exception( $error_msg );
				}
			}
			return $text;
		}

		public function single_spin_text( $text ) {
			$para = array();
			$para['wai_quality'] = intval( $_POST['post_wai_quality'] );
			$para['wai_nonested'] = intval( $_POST['post_wai_nonested'] );
			$para['wai_sentence'] = intval( $_POST['post_wai_sentence'] );
			$para['wai_paragraph'] = intval( $_POST['post_wai_paragraph'] );
			$para['wai_returnspin'] = intval( $_POST['post_wai_returnspin'] );
			$para['wai_nooriginal'] = intval( $_POST['post_wai_nooriginal'] );
			$para['wai_protected'] = strip_tags( $_POST['post_wai_protected'] );
			$para['wai_synonyms'] = strip_tags( $_POST['post_wai_synonyms'] );
			return $this->spin_text( $text, $para );
		}

		// do test spin
		public function do_test_spin() {

			$error_bs = false;
			$error_msg = '';

			$this->main->set_option( 'sp_error_message', '' );
			$this->main->set_option( 'sp_error', 0 );

			$auth_str = ! empty( $this->api_hash ) ? $this->api_hash : ( ! empty( $this->api_passw ) ? md5( substr( md5( $this->api_passw ),0,15 ) ) : '');
			if ( ! empty( $auth_str ) ) {
				$auth_str = '&hash=' . $auth_str;
			} else {
				$error_msg = 'Make sure the password or hash are not empty.';
				$error_bs = true;
			}

			if ( ! $error_bs ) {
				$text = urlencode( 'Try to spin a text to test' );
				$ch = curl_init( $this->turing_api_url );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
				curl_setopt( $ch, CURLOPT_POST, 1 );
				curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 60 * 20 );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, "s=$text&quality=Reguler&email=" . $this->api_email . $auth_str . '&output=json' );
				$result = curl_exec( $ch );
				$err = curl_error( $ch );
				curl_close( $ch );
				//error_log(print_r($result, true));
				if ( false === $result ) {
					$error_msg = "Can't connect to The WordAi. " . $err;
					$error_bs = true;
				} else {
					$data = json_decode( $result );
					//error_log(print_r($data, true));
					if ( is_object( $data ) ) {
						if ( property_exists( $data, 'error' ) && ! empty( $data->error ) ) {
							$error_msg = $data->error;
							$error_bs = true;
						}
					} else {
						$error_msg = $result;
						$error_bs = true;
					}
					if ( $error_bs ) {
						$error_msg = "Can't spin by The WordAi account. " . $error_msg; }
				}
			}

			if ( $error_bs ) {
				$this->main->set_option( 'sp_error_message', $error_msg );
				$this->main->set_option( 'sp_error', 1 );
				return false;
			}
			return true;
		}

		public function spin_authenticate() {
			return $this->do_test_spin();
		}
	}




endif;

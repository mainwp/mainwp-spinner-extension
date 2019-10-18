<?php

if ( ! class_exists( 'MainWP_Spin_Rewriter' ) ) :

	class MainWP_Spin_Rewriter {

		protected $main;
		protected $api_url = 'http://www.spinrewriter.com/action/api';
		protected $api_email;
		protected $api_key;

		public function __construct( $main_obj, $apiEmail, $apiKey = '' ) {
			$this->main = $main_obj;
			$this->api_email = $apiEmail;
			$this->api_key = $apiKey;
		}

		public function spin( $post ) {
			if ( ! $post ) {
				return; }
			$para = array();

			$spin_title = intval( $_POST['post_sp_spin_title'] );

			$lines = explode( "\n", (string) $_POST['post_srw_protected_terms'] );
			$lines = array_map( 'sanitize_text_field', (array) $lines );
			$para['srw_protected_terms'] = implode( "\n", $lines );
			$para['srw_auto_protected_terms'] = intval( $_POST['post_srw_auto_protected_terms'] );
			$para['srw_confidence_level'] = intval( $_POST['post_srw_confidence_level'] );
			$para['srw_nested_spintax'] = intval( $_POST['post_srw_nested_spintax'] );
			$para['srw_auto_sentences'] = intval( $_POST['post_srw_auto_sentences'] );
			$para['srw_auto_paragraphs'] = intval( $_POST['post_srw_auto_paragraphs'] );
			$para['srw_auto_new_paragraphs'] = intval( $_POST['post_srw_auto_new_paragraphs'] );
			$para['srw_auto_sentence_trees'] = sanitize_text_field( $_POST['post_srw_auto_sentence_trees'] );
			$para['srw_reorder_paragraphs'] = sanitize_text_field( $_POST['post_srw_reorder_paragraphs'] );
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

			$args = array();
			$args['api_key'] = urlencode( stripslashes( $this->api_key ) );
			$args['email_address'] = urlencode( stripslashes( $this->api_email ) );
			$args['action'] = 'text_with_spintax' ;
			$args['protected_terms'] = isset( $params['srw_protected_terms'] ) ? $params['srw_protected_terms'] : $this->main->get_option( 'srw_protected_terms' );
			$args['protected_terms'] = urlencode( stripslashes( $args['protected_terms'] ) );
			$args['auto_protected_terms'] = isset( $params['srw_auto_protected_terms'] ) ? $params['srw_auto_protected_terms'] : $this->main->get_option( 'srw_auto_protected_terms' );
			$args['confidence_level'] = isset( $params['srw_confidence_level'] ) ? $params['srw_confidence_level'] : $this->main->get_option( 'srw_confidence_level' );
			$args['nested_spintax'] = isset( $params['srw_nested_spintax'] ) ? $params['srw_nested_spintax'] : $this->main->get_option( 'srw_nested_spintax' );
			$args['auto_sentences'] = isset( $params['srw_auto_sentences'] ) ? $params['srw_auto_sentences'] : $this->main->get_option( 'srw_auto_sentences' );
			$args['auto_paragraphs'] = isset( $params['srw_auto_paragraphs'] ) ? $params['srw_auto_paragraphs'] : $this->main->get_option( 'srw_auto_paragraphs' );
			$args['auto_new_paragraphs'] = isset( $params['srw_auto_new_paragraphs'] ) ? $params['srw_auto_new_paragraphs'] : $this->main->get_option( 'srw_auto_new_paragraphs' );
			$args['auto_sentence_trees'] = isset( $params['srw_auto_sentence_trees'] ) ? $params['srw_auto_sentence_trees'] : $this->main->get_option( 'srw_auto_sentence_trees' );
			$args['reorder_paragraphs'] = isset( $params['srw_reorder_paragraphs'] ) ? $params['srw_reorder_paragraphs'] : $this->main->get_option( 'srw_reorder_paragraphs' );

			$url = 'text=' . urlencode( stripslashes( $text ) );
			foreach ( $args as $key => $val ) {
				$url .= '&' . $key . '=' . $val;
			}
			//error_log($url);
			$ch = curl_init( $this->api_url );
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
				if ( is_object( $data ) && property_exists( $data, 'status' ) ) {
					if ( $data->status == 'ERROR' ) {
						$error_msg = $data->response;
					} else if ( $data->status == 'OK' ) {
						return $data->response;
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
			$para['protected_terms'] = intval( $_POST['post_protected_terms'] );
			$para['auto_protected_terms'] = intval( $_POST['post_auto_protected_terms'] );
			$para['confidence_level'] = intval( $_POST['post_confidence_level'] );
			$para['nested_spintax'] = intval( $_POST['post_nested_spintax'] );
			$para['auto_sentences'] = intval( $_POST['post_auto_sentences'] );
			$para['auto_paragraphs'] = intval( $_POST['post_auto_paragraphs'] );
			$para['auto_sentence_trees'] = strip_tags( $_POST['post_auto_sentence_trees'] );
			$para['reorder_paragraphs'] = strip_tags( $_POST['post_reorder_paragraphs'] );
			return $this->spin_text( $text, $para );
		}

		// do test spin
		public function do_test_spin() {

			$error_bs = false;
			$error_msg = '';
			$this->main->set_option( 'sp_message', '' );
			$this->main->set_option( 'sp_error_message', '' );
			$this->main->set_option( 'sp_error', 0 );

			if ( empty( $this->api_email ) || empty( $this->api_key ) ) {
				$error_msg = 'Make sure the Email Address and API key are not empty.';
				$error_bs = true;
			}
			if ( ! $error_bs ) {
				$ch = curl_init( $this->api_url );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
				curl_setopt( $ch, CURLOPT_POST, 1 );
				curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 60 * 20 );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, 'email_address=' . $this->api_email . '&api_key=' . $this->api_key .'&action=api_quota' );
				$result = curl_exec( $ch );
				$err = curl_error( $ch );
				curl_close( $ch );
				//error_log(print_r($result, true));
				if ( false === $result ) {
					$error_msg = "Can't connect to The Spin Rewrite. " . $err;
					$error_bs = true;
				} else {
					$data = json_decode( $result );
					//error_log(print_r($data, true));
					if ( is_object( $data ) && property_exists( $data, 'status' ) ) {
						if ( $data->status == 'ERROR' ) {
							$error_msg = $data->response;
							$error_bs = true;
						} else if ( $data->status == 'OK' ) {
							$this->main->set_option( 'sp_message', 'Remain Spin Rewrite queries: ' .  $data->response );
						}
					} else {
						$error_msg = $result;
						$error_bs = true;
					}
					if ( $error_bs ) {
						$error_msg = "Can't spin by The Spin Rewrite account. " . $error_msg; }
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

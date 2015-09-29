<?php

/**
 * The SpinChimp API class.
 * The methods on this class can be used to invoke specific Spin Chimp API methods.
 *
 * @package SpinChimp
 * @see https://www.spinchimp.com/API
 *
 * @version: 1.0.1
 *
 * Updates for 1.0.1
 * =================
 * - added new method setMaxSpinDepth
 * */
class SpinChimp {

	var $user;
	var $apikey;
	var $apiURL;
	var $aid;
	var $quality = 4;
	var $posmatch = 3;
	var $protectedterms;
	var $tagprotect;
	var $maxspindepth = 0;
	var $parameters = array();

	/**
	 * Class constructor for the SpinChimp class.
	 * @access public
	 *
	 * @param string	$user
	 * @param string	$apikey
	 */
	public function __construct( $user, $apikey, $app_id = 'wp-spinchimp', $apiURL = 'http://api.spinchimp.com/' ) {
		$this->user = $user;
		$this->apikey = $apikey;
		$this->aid = $app_id;
		$this->apiURL = $apiURL;
	}

	/**
	 * Method GlobalSpin
	 * Spins an article with various quality paramters and return it either with spintax or as a unique unspun document.
	 *
	 * @access public
	 *
	 * @param string 	$text		the text to be spun
	 * @param int 		$rewrite	whether the spintax is to be shown or not
	 *
	 * @return array	success - TRUE or FALSE, error - if unsuccessful, result - the spinned article
	 */
	public function GlobalSpin( $text, $rewrite = 0 ) {
		//Check Inputs
		if ( ! isset( $text ) || trim( $text ) === '' ) {
			return ''; }
		$this->setParam( 'rewrite', $rewrite );
		//Add parameters
		$parameters = array();
		$parameters['email'] = $this->user;
		$parameters['apiKey'] = $this->apikey;
		$parameters['aid'] = $this->aid;
		$parameters['posmatch'] = $this->posmatch;
		$parameters['quality'] = $this->quality;
		$parameters['maxspindepth'] = $this->maxspindepth;

		if ( ! empty( $this->protectedterms ) ) {
			$parameters['protectedterms'] = $this->protectedterms; }

		if ( ! empty( $this->tagprotect ) ) {
			$parameters['tagprotect'] = $this->tagprotect; }

		if ( count( $this->parameters ) > 0 ) {
			foreach ( $this->parameters as $param => $value ) {
				$parameters[ $param ] = $value;
			}
		}
		$result = $this->makeApiRequest( $this->apiURL, 'GlobalSpin', $this->buildQueryString( $parameters ), $text );

		if ( substr( $result, 0, 7 ) == 'Failed:' ) {
			$error_found = $this->extractErrors( $result );
			return array( 'success' => false, 'error' => $error_found );
		} else {
			return array( 'success' => true, 'result' => $result );
		}
	}

	/**
	 * Method PrintRequest
	 * Prints an example request from this server to the API server .
	 *
	 * @access public
	 *
	 */
	public function PrintRequest() {
		//Add parameters
		$parameters = array();
		$parameters['email'] = $this->user;
		$parameters['apiKey'] = $this->apikey;
		$parameters['aid'] = $this->aid;
		$parameters['posmatch'] = $this->posmatch;
		$parameters['quality'] = $this->quality;
		$parameters['maxspindepth'] = $this->maxspindepth;

		if ( ! empty( $this->protectedterms ) ) {
			$parameters['protectedterms'] = $this->protectedterms; }

		if ( ! empty( $this->tagprotect ) ) {
			$parameters['tagprotect'] = $this->tagprotect; }

		if ( count( $this->parameters ) > 0 ) {
			foreach ( $this->parameters as $param => $value ) {
				$parameters[ $param ] = $value;
			}
		}

		$req = curl_init();
		curl_setopt( $req, CURLOPT_URL, $this->apiURL . 'GlobalSpin' . '?' . $this->buildQueryString( $parameters ) );
		curl_setopt( $req, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $req, CURLOPT_POST, true );
		curl_setopt( $req, CURLOPT_POSTFIELDS, 'This is some sample text to sent to the server.' );
		$result = trim( curl_exec( $req ) );
		print_r( curl_getinfo( $req ) );
		curl_close( $req );
	}

	/**
	 * Method GenerateSpin
	 * Generates an unspun doc from one with spintax. Optionally reorders paragraphs and removes original word.
	 *
	 * @access public
	 *
	 * @param string 	$text		the text with spintax to be spun
	 *
	 * @return array	success - TRUE or FALSE, error - if unsuccessful, result - the spinned article
	 */
	public function GenerateSpin( $text ) {
		$params = array( 'dontincludeoriginal', 'reorderparagraphs' );
		$parameters = array();
		$parameters['email'] = $this->user;
		$parameters['apiKey'] = $this->apikey;
		$parameters['aid'] = $this->aid;

		if ( count( $this->parameters ) > 0 ) {
			foreach ( $this->parameters as $parameter => $value ) {
				if ( in_array( $parameter, $params ) ) {
					$parameters[ $parameter ] = $value;
				}
			}
		}
		$result = $this->makeApiRequest( $this->apiURL, 'GenerateSpin', $this->buildQueryString( $parameters ), $text );

		if ( substr( $result, 0, 7 ) == 'Failed:' ) {
			$error_found = $this->extractErrors( $result );
			return array( 'success' => false, 'error' => $error_found );
		} else {
			return array( 'success' => true, 'result' => $result );
		}
	}

	/**
	 * Method CalcWordDensity
	 * Calculates the word densities of words and phrases in the article.
	 *
	 * @access public
	 *
	 * @param string 	$text		Article to calculate the word densities of
	 *
	 * @return array	Associative array pairs of words and densities
	 */
	public function CalcWordDensity( $text ) {
		$params = array( 'minlength' );
		$parameters = array();
		$parameters['email'] = $this->user;
		$parameters['apiKey'] = $this->apikey;
		$parameters['aid'] = $this->aid;

		if ( count( $this->parameters ) > 0 ) {
			foreach ( $this->parameters as $parameter => $value ) {
				if ( in_array( $parameter, $params ) ) {
					$parameters[ $parameter ] = $value;
				}
			}
		}
		$result = $this->makeApiRequest( $this->apiURL, 'CalcWordDensity', $this->buildQueryString( $parameters ), $text );

		if ( substr( $result, 0, 7 ) == 'Failed:' ) {
			$error_found = $this->extractErrors( $result );
			return array( 'success' => false, 'error' => $error_found );
		} else {
			$exploded = explode( '|', $result );
			$arr = array();
			foreach ( $exploded as $res ) {
				$expres = explode( ',', $res );
				$arr[ $expres[0] ] = $expres[1];
			}
			return array( 'success' => true, 'result' => $arr );
		}
	}

	/**
	 * Method QueryStats
	 * Returns remaining query quota. Can also be used to test an account.
	 *
	 * @access public
	 *
	 * @param 	int		$simple	if 1 will return a raw number of how many queries can be made with the account.
	 * @return array	Associative array pairs of stat names and values
	 */
	public function QueryStats( $simple = 0 ) {
		$parameters = array();
		$parameters['email'] = $this->user;
		$parameters['apiKey'] = $this->apikey;
		$parameters['aid'] = $this->aid;

		$parameters['simple'] = $simple;

		$result = $this->makeApiRequest( $this->apiURL, 'QueryStats', $this->buildQueryString( $parameters ), '' );

		if ( substr( $result, 0, 7 ) == 'Failed:' ) {
			$error_found = $this->extractErrors( $result );
			return array( 'success' => false, 'error' => $error_found );
		} else {
			$exploded = explode( '|', $result );

			if ( $simple == 0 ) {
				$arr = array();
				foreach ( $exploded as $res ) {
					$expres = explode( ',', $res );
					$arr[ $expres[0] ] = $expres[1];
				}
				return array( 'success' => true, 'result' => $arr );
			} else {
				return array( 'success' => true, 'result' => $result );
			}
		}
	}

	/**
	 * Method setSpinQuality
	 * Spin quality:
	 * 		5 – Best,
	 * 		4 – Better,
	 * 		3 – Good,
	 * 		2 – Average,
	 * 		1 – All
	 *
	 * @access public
	 *
	 * @param string|integer $quality
	 */
	public function setSpinQuality( $quality = 4 ) {
		$qualities = array(
			'best' => 5,
			'better' => 4,
			'good' => 3,
			'average' => 2,
			'all' => 1,
		);
		$this->quality = 4; //set to default...
		if ( is_numeric( $quality ) && ($quality > 0 && $quality <= 5) ) {
			$this->quality = $quality; }
		if ( is_string( $quality ) && array_key_exists( strtolower( $quality ), $qualities ) ) {
			$this->quality = $qualities[ strtolower( $quality ) ]; }
	}

	/**
	 * Method setPOSMatch
	 * Required Part of Speech (POS) match for a spin:
	 * 		4 – FullSpin,
	 * 		3 – Full,
	 * 		2 – Loose,
	 * 		1 – Extremely Loose,
	 * 		0 – None.
	 * 'FullSpin' removes some common POS replacements that tend to reduce quality of spin.
	 *
	 * @access public
	 *
	 * @param string|integer $posmatch
	 */
	public function setPOSMatch( $posmatch = 3 ) {
		$posmatches = array(
			'fullspin' => 4,
			'full' => 3,
			'loose' => 2,
			'extremelyloose' => 1,
			'none' => 0,
		);
		$this->posmatch = 3; //set to default
		if ( is_numeric( $posmatch ) && ($posmatch > 0 && $posmatch <= 4) ) {
			$this->posmatch = $posmatch; }
		if ( is_string( $posmatch ) && array_key_exists( strtolower( $posmatch ), $posmatches ) ) {
			$this->posmatch = $posmatches[ strtolower( $posmatch ) ]; }
	}

	/**
	 * Method setProtectedTerms
	 * set the terms not to be touched when spinning the article
	 *
	 * @access public
	 *
	 * @param array/string	$terms	terms to protect
	 */
	public function setProtectedTerms( $terms = array() ) {
		if ( ! is_array( $terms ) ) {
			$terms = explode( ',', $terms );
		}

		$CSVterms = '';
		foreach ( $terms as $term ) {
			$CSVterms .= urlencode( trim( $term ) ) . ',';
		}
		$CSVterms = rtrim( $CSVterms, ',' );

		$this->protectedterms = $CSVterms;
	}

	/**
	 * Method setTagProtect
	 * Protects anything between any syntax you define. Separate start and end syntax with a pipe ‘|’ and separate multiple tags with a comma ‘,’. For example, you could protect anything in square brackets by setting tagprotect=[|]. You could also protect anything between “begin” and “end” by setting tagprotect=[|],begin|end
	 *
	 * @access public
	 *
	 * @param array/string	$tags	tags to protect
	 */
	public function setTagProtect( $tags = array() ) {
		if ( ! is_array( $tags ) ) {
			$tags = explode( ',', $tags );
		}

		$CSVtags = '';
		foreach ( $tags as $tags ) {
			$CSVtags .= trim( $tags ) . ',';
		}
		$CSVtags = rtrim( $CSVtags, ',' );

		$this->tagprotect = $CSVtags;
	}

	/**
	 * Method setMaxSpinDepth
	 * set the Maxspindepth
	 *
	 * @access public
	 *
	 * @param int		$value	value of the parameter set in $param
	 *
	 * @return none
	 */
	public function setMaxSpinDepth( $maxspindepth = 0 ) {
		$this->maxspindepth = $maxspindepth;
	}

	/**
	 * Method setParam
	 * set the parameters not included in the standard methods for this class
	 *
	 * @access public
	 *
	 * @param string	$param	parameter name
	 * @param int		$value	value of the parameter set in $param
	 *
	 * @return Boolean 	TRUE if parameter is set, FALSE if not
	 */
	public function setParam( $param, $value ) {
		$params = array(
			//Specific to GlobalSpin Method
			'rewrite',
			'phraseignorequality',
			'spinwithinspin',
			'spinwithinhtml',
			'applyinstantunique',
			'fullcharset',
			'spintidy',
			'tagprotect',
			//Specific to GenerateSpin Method
			'dontincludeoriginal',
			'reorderparagraphs',
			//Specific to CalcWordDensity
			'minlength',
			//Specific to QueryStats
			'simple',
		);

		if ( in_array( $param, $params ) ) {
			$this->parameters[ $param ] = $value;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Method buildQueryString
	 * set the Query string to be passed to SERVER as GET values
	 *
	 * @access private
	 *
	 * @param array		$parameters	Associative array of parameter, value pairs
	 */
	private function buildQueryString( $parameters ) {
		$data = '';
		foreach ( $parameters as $key => $value ) {
			$data .= $key . '=' . $value . '&';
		}
		$data = rtrim( $data, '&' );
		return $data;
	}

	/**
	 * Method makeApiRequest
	 * uses cURL to post to API
	 *
	 * @access private
	 *
	 * @param string		$url			URL to post to
	 * @param string		$command		API method
	 * @param string		$querystring	query string
	 * @param string		$text			article to perform operations to
	 *
	 * @return string		Response from the SpinChimp API
	 */
	private function makeApiRequest( $url, $command, $querystring, $text ) {
		$req = curl_init();
		curl_setopt( $req, CURLOPT_URL, $url . $command . '?' . $querystring );
		curl_setopt( $req, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $req, CURLOPT_POST, true );
		curl_setopt( $req, CURLOPT_POSTFIELDS, $text );
		$result = trim( curl_exec( $req ) );
		curl_close( $req );
		return $result;
	}

	/**
	 * Method extractErrors
	 * Extract the errors from the API response
	 *
	 * @access private
	 *
	 * @param string		$error			Response from the Spinchimp API
	 *
	 * @return array		Array of errors
	 */
	private function extractErrors( $error ) {
		$err_array = explode( ':', $error );
		$error_found = explode( '|', $err_array[2] );
		return $error_found;
	}
}

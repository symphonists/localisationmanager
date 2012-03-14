<?php

	/**
	 * LOCALISATION MANAGER
	 * for Symphony CMS
	 *
	 * Nils Hörrmann, http://www.nilshoerrmann.de
	 * Based on the Translation Manager class written by Marcin Konicki, http://ahwayakchih.neoni.net
	 */

	class LocalisationManager {

		private $_Sort;

		function __construct() {
			if(isset($_GET['sort'])) $this->_Sort = true;
		}
		
		public function buildDictionary($context, $lang, $name) {
			if(!$lang) $lang = 'en';
			
			// Get current translations
			$current = $this->getTranslations($context, $lang);
			if(empty($current)) {
				$current = array(
					'about' => array(
						'name' => ($name ? $name : 'New translation'),
						'author' => array(
							'name' => Administration::instance()->Author->getFullName(),
							'email' => Administration::instance()->Author->get('email'),
							'website' => ''
						),
					),
					'dictionary' => array(),
					'transliterations' => array()
				);
			}	

			// Prepare current translations
			if($this->_Sort == true) ksort($current['dictionary']);
			foreach($current['dictionary'] as $key => $value) {
				if($value === false) unset($current['dictionary'][$key]);
			}
					
			// Get needed strings currently used by Symphony
			$strings = $this->getStrings($context);
			
			// Get transliterations
			$straight = array();
			$regexp = array();
			if($context == 'symphony') {
				$transliterations = $this->getTransliterations($current['transliterations']);
				
				// Group straight transliterations by type
				$type = null;
				foreach($transliterations['straight'] as $key => $transliteration) {
					switch($type) {
						case 'lowercase':
							$straight['lowercase'][$key] = $transliteration;
							if($key == 'ŉ') $type = 'symbolic';
							break;
						case 'symbolic':
							$straight['symbolic'][$key] = $transliteration;
							if($key == '»') $type = 'special';
							break;
						case 'special':
							$straight['special'][$key] = $transliteration;
							if($key == 'º') $type = 'other';
							break;
						case 'other':
							$straight['other'][$key] = $transliteration;
							break;
						default:
							$straight['uppercase'][$key] = $transliteration;
							if($key == 'Þ') $type = 'lowercase';
							break;
					}	
				}
				
				// Regular expression based transliterations – no grouping needed so far
				$type = null;
				foreach($transliterations['regexp'] as $key => $transliteration) {
					switch($type) {
						case 'other':
							$regexp['other'][$key] = $transliteration;
							break;
						default:
							$regexp['ampersands'][$key] = $transliteration;
							if($key == '/&(?!&)/') $type = 'other';
							break;
					}	
				}
			}
			
			// Exclude core strings for extensions
			else {
				$core = $this->getStrings('symphony');
				$intersection = array_intersect_key($strings, $core);
				$strings = array_diff_key($strings, $intersection);
			}
			
			// Key compare function
			function key_compare_func($key1, $key2) {
				$key1 = stripslashes($key1);
				$key2 = stripslashes($key2);
				
				if($key1 == $key2) {
					return 0;
				}
				else if($key1 > $key2) {
					return 1;
				}
				else {
					return -1;
				}
			}
			
			// Return new dictionary
			return array(
				'about' => array(
					'name' => $current['about']['name'],
					'author' => array(
						'name' => $current['about']['author']['name'],
						'email' => $current['about']['author']['email'],
						'website' => $current['about']['author']['website']
					),
					'release-date' => date('Y-m-d')
				),
				'dictionary' => array(
					'strings' => array_intersect_key($current['dictionary'], $strings),
					'obsolete' => array_diff_ukey($current['dictionary'], $strings, "key_compare_func"),
					'missing' => array_diff_ukey($strings, $current['dictionary'], "key_compare_func"),
					'namespaces' => array()
				),
				'transliterations' => array(
					'straight' => $straight,
					'regexp' => $regexp
				)
			);
		}
		
		public function getStrings($context) {
		
			// Set context path
			if($context == 'symphony') {
				$path = DOCROOT . '/symphony';
			}
			else {
				$path = EXTENSIONS . '/' . $context;
			}
			
			// Parse files
			$strings = $this->getFiles($path, array());
			$folders = General::listDirStructure($path);
			foreach($folders as $folder) {
				$strings = $this->getFiles($folder, $strings);
			}
			if(empty($strings) && $context != 'symphony') return array();
			
			// Get navigation and date string
			if($context == 'symphony') {
				$strings = array_merge($strings, $this->__findNavigationStrings());
				$strings = array_merge($strings, $this->__getDateStrings());
			}
			
			// Remove duplicated
			$strings = array_unique($strings);
			
			// Sort array
			if($this->_Sort == true) natcasesort($strings);
			
			// Generate correct keys and values
			$strings = array_combine(
				$strings, 
				array_fill(0, count($strings), NULL)
			);
			
			// Return strings
			return $strings;
		}
		
		public function getFiles($folder, $strings) {

			// Get files
			$files = General::listStructure(preg_replace('{/$}', '', $folder), array('php', 'tpl', 'js'), false, 'asc', false);
			if(empty($files['filelist'])) {
				return $strings;
			}
			
			// Find strings
			foreach($files['filelist'] as $file) {
				if(pathinfo($file, PATHINFO_EXTENSION) == 'js') {
					$strings = array_merge($strings, $this->__findJavaScriptStrings($file));
				}
				else {
					$strings = array_merge($strings, $this->__findStrings($file));
				}
			}
			return $strings;
		}
		
		public function getTranslations($context, $lang) {
		
			// Get file
			if($context == 'symphony') {
				$file = Lang::$_languages[$lang]['path'];
			}
			else {
				$file = EXTENSIONS . '/' . $context . '/lang/lang.' . $lang . '.php';
			}
			
			// Get translations
			if(!file_exists($file)) return false;
			include($file);
			if(is_array($dictionary)) ksort($dictionary);

			// Return strings
			return array(
				'about' => (is_array($about) ? $about : array()),
				'dictionary' => (is_array($dictionary) ? $dictionary : array()),
				'transliterations' => (is_array($transliterations) ? $transliterations : array()),
			);
		}
		
		public function getTransliterations($current) {
			include(LANG . '/transliterations.php');
			
			// Return transliterations
			if(!$current['straight']) {
				return $transliterations;
			}
			else {
				return array_merge($transliterations, $current);
			}
		}
		
		private function __findStrings($path) {
			if(!file_exists($path)) return false;
			
			// Get source
			$source = file_get_contents($path);
			
			/**
			 * Find all calls to the translation function, e. g. __('Your content here'):
			 * The regular expression searches for "__('TEXT'" and makes sure that the last ' is not escaped with \
			 * as this is used for apostrophs inside some language strings, e. g. __('Symphony\'s backend').
			 * It respects any whitespace character between the opening parenthesis and single quote.
			 * The regular expression respect the usage of single and double quote.
			 */
			preg_match_all("/__\(\s*(\"|')(.*)(?<!\\\\)\\1/U", $source, $strings);
			return $strings[2];
		}
		
		private function __findNavigationStrings() {
		
			// Get source
			$doc = new DOMDocument();
			$xml = $doc->load(ASSETS . '/navigation.xml');
			
			// Find navigation elements
			$xpath = new DOMXPath($doc);
			$items = $xpath->query("//*[not(@visible='no')]");
			
			// Get strings
			$strings = array();
			foreach($items as $item) {
				$string = $item->getAttribute('name');
				if($string) $strings[] = $string;
			}
			return $strings;
		}

		private function __findJavaScriptStrings($path) {
		
			// Get source
			$source = file_get_contents($path);
			
			// Extract language object
			preg_match_all("/Symphony.Language.add\({(.*)}\);/sU", $source, $objects);
			
			// Get strings
			$strings = array();
			foreach($objects[1] as $object) {
				preg_match_all("/.*(\"|')(.*)(\"|'): ?false,?/sU", $object, $result);
				$strings = array_merge($strings, $result[2]);
			}
			return $strings;
		}
		
		private function __getDateStrings() {
			include(LANG . '/datetime.php');
			
			// Return date and time strings
			return $datetime_strings;
		}
		
	}

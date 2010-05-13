<?php

	/**
	 * LOCALISATION MANAGER
	 * for Symphony CMS
	 *
	 * Nils Hörrmann, http://www.nilshoerrmann.de
	 * Based on the Translation Manager class written by Marcin Konicki, http://ahwayakchih.neoni.net
	 */

	class LocalisationManager {

		private $_Parent;
		private $_Sort;

		function __construct(&$parent) {
			$this->_Parent = $parent;
			if(isset($_GET['sort'])) $this->_Sort = true;
		}
		
		public function buildDictionary($context, $lang, $name) {
			if(!$lang) $lang = 'en';
			
			// Get current translations
			$current = $this->getTranslations($context, $lang);
			if(empty($current)) {
				$current = array(
					'about' => array(
						'name' => $name,
						'author' => array(
							'name' => $this->_Parent->Author->getFullName(),
							'email' => $this->_Parent->Author->get('email'),
							'website' => ''
						),
					),
					'dictionary' => array(),
					'transliterations' => array()
				);
			}	

			// Prepare current translations
			$current['dictionary'] = array_flip($current['dictionary']);
			if($this->_Sort == true) natcasesort($current['dictionary']);
			$current['dictionary'] = array_unique($current['dictionary']);
			$current['dictionary'] = array_flip($current['dictionary']);
			
			// Get needed strings currently used by Symphony
			$strings = $this->getStrings($context);
			
			// Get transliterations
			$alphabetical = array();
			$symbolic = array();
			$ampersand = array();
			if($context == 'symphony') {
				$transliterations = $this->getTransliterations($current['transliterations']);
				$type = 'alphabeticalUC';
				// Group tranliterations by type
				foreach($transliterations as $key => $transliteration) {
					if($type == 'alphabeticalUC') {
						$alphabetical['uppercase'][$key] = $transliteration;
						if($key == '/Þ/') $type = 'alphabeticalLC';
						continue;
					}
					if($type == 'alphabeticalLC') {
						$alphabetical['lowercase'][$key] = $transliteration;
						if($key == '/ŉ/') $type = 'symbolic';
						continue;
					}
					elseif($type == 'symbolic') {
						$symbolic[$key] = $transliteration;
						if($key == '/¡/') $type = 'ampersand';
						continue;
					}
					elseif($type == 'ampersand') {
						$ampersand[$key] = $transliteration;
					}				
				}
			}
			
			// Exclude core strings for extensions
			else {
				$core = $this->getStrings('symphony');
				$intersection = array_intersect_key($strings, $core);
				$strings = array_diff_key($strings, $intersection);
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
					'obsolete' => array_diff_key($current['dictionary'], $strings),
					'missing' => array_diff_key($strings, $current['dictionary'])
				),
				'transliterations' => array(
					'alphabetical' => $alphabetical,
					'symbolic' => $symbolic,
					'ampersands' => $ampersand
				)
			);
		}
		
		public function getStrings($context) {
			// Set context paths
			if($context == 'symphony') {
				$paths = array(
					$context . '/assets',
					$context . '/content',
					$context . '/template',
					$context . '/lib/toolkit',
					$context . '/lib/toolkit/data-sources',
					$context . '/lib/toolkit/events',
					$context . '/lib/toolkit/fields'
				);
			}
			else {
				$paths = array(
					'extensions/'. $context .'/assets',
					'extensions/'. $context .'/content',
					'extensions/'. $context .'/data-sources',
					'extensions/'. $context .'/events',
					'extensions/'. $context .'/fields',
					'extensions/'. $context .'/lib',
					'extensions/'. $context
				);
			}
			
			// Parse files
			$strings = array();
			foreach($paths as $path) {
				// Get files
				$files = General::listStructure(DOCROOT . "/{$path}", array('php', 'tpl', 'js'), false, 'asc');
				if(empty($files['filelist'])) continue;
				// Find strings
				foreach($files['filelist'] as $file) {
					if(pathinfo($file, PATHINFO_EXTENSION) == 'js') {
						$strings = array_merge($strings, $this->__findJavaScriptStrings(DOCROOT . "/{$path}/{$file}"));
					}
					else {
						$strings = array_merge($strings, $this->__findStrings(DOCROOT . "/{$path}/{$file}"));
					}
				}
			}
			if(empty($strings) && $context != 'symphony') return array();
			
			// Get navigation and JavaScript strings
			if($context == 'symphony') {
				$strings = array_merge($strings, $this->__findNavigationStrings());
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
		
		public function getTranslations($context, $lang) {
			// Set path
			if($context == 'symphony') {
				$path = Lang::findLanguagePath($lang, new ExtensionManager($this->_Parent));
			}
			else {
				$path = EXTENSIONS . '/' . $context . '/lang';
			}
			
			// Get source
			$file = $path . '/lang.' . $lang . '.php';
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
			// Get source
			include(LANG . '/lang.en.php');
			// Return transliterations
			if(!$current) {
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
			// Find all calls to the translation function, e. g. __('Your content here'):
			// The regular expression searches for "__('TEXT'" and makes sure that the last ' is not escaped with \
			// as this is used for apostrophs inside some language strings, e. g. __('Symphony\'s backend').
			// It respects any whitespace character between the opening parenthesis and single quote.
			// The regular expression respect the usage of single and double quote.
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
		
	}

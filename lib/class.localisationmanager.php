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

		function __construct(&$parent) {
			$this->_Parent = $parent;
		}
		
		public function buildDictionary($name, $lang) {
			if(!$lang) $lang = 'en';
			
			// Get current translations
			$current = $this->getTranslations($name, $lang);
			if(empty($current)) {
				$current = array(
					'about' => array(),
					'dictionary' => array(),
					'transliterations' => array()
				);
			}	

			// Prepare current translations
			$current['dictionary'] = array_flip($current['dictionary']);
			natcasesort($current['dictionary']);
			$current['dictionary'] = array_unique($current['dictionary']);
			$current['dictionary'] = array_flip($current['dictionary']);
			
			// Get needed strings currently used by Symphony
			$strings = $this->getStrings($name);
			
			// Get transliterations
			$alphabetical = array();
			$symbolic = array();
			$ampersand = array();
			if($name == 'symphony') {
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
		
		public function getStrings($name) {
			// Set context paths
			if($name == 'symphony') {
				$paths = array(
					$name . '/content',
					$name . '/template',
					$name . '/lib/toolkit',
					$name . '/lib/toolkit/data-sources',
					$name . '/lib/toolkit/events',
					$name . '/lib/toolkit/fields'
				);
			}
			else {
				$paths = array(
					'extensions/'. $name .'/assets',
					'extensions/'. $name .'/content',
					'extensions/'. $name .'/data-sources',
					'extensions/'. $name .'/events',
					'extensions/'. $name .'/fields',
					'extensions/'. $name .'/lib',
					'extensions/'. $name
				);
			}
			
			// Parse files
			$strings = array();
			foreach($paths as $path) {
				// Get files
				$files = General::listStructure(DOCROOT . "/{$path}", array('php', 'tpl'), false, 'asc');
				if(empty($files['filelist'])) continue;
				// Find strings
				foreach($files['filelist'] as $file) {
					$strings = array_merge($strings, $this->__findStrings(DOCROOT . "/{$path}/{$file}"));
				}
			}
			
			// Get navigation and JavaScript strings
			if($name == 'symphony') {
				$strings = array_merge($strings, $this->__findNavigationStrings());
				$strings = array_merge($strings, $this->__findJavaScriptStrings());
			}
			
			// Remove duplicated
			$strings = array_unique($strings);
			// Sort array
			natcasesort($strings);
			// Generate correct keys and values
			$strings = array_combine(
				$strings, 
				array_fill(0, count($strings), NULL)
			);
			
			// Return strings
			return $strings;
		}
		
		public function getTranslations($name, $lang) {
			// Set path
			if($name == 'symphony') {
				$path = Lang::findLanguagePath($lang, new ExtensionManager($this->_Parent));
			}
			else {
				$path = EXTENSIONS . '/' . $name . '/lang';
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

		private function __findJavaScriptStrings() {
			// Get source
			$source = file_get_contents(ASSETS . '/admin.js');
			// Extract language definitions
			preg_match('/Language: {(.*)"\n\t\t}/s', $source, $language);
			// Remove line breaks and tabs
			$language = preg_replace('/\n|\r|\t/', '', $language[1]);
			// Extract language strings
			$lines = explode('",', $language);
			foreach($lines as $line) {
				// Split language strings
				$part = preg_split('/: {0,}"/', $line);
				$strings[] = $part[1];
			}
			return $strings;
		}
		
	}

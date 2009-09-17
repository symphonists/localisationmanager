<?php

	/**
	 * TRANSLATION MANAGER for Symphony CMS
	 * Written by Marcin Konicki, http://ahwayakchih.neoni.net, released under MIT licence
	 * http://github.com/ahwayakchih/translationmanager
	 */

	class TranslationManager {

		private $_Parent;

		function __construct(&$parent) {
			$this->_Parent = $parent;
		}

		public function enable($lang) {
			if (strlen($lang) < 1) return false;
			if (!file_exists(TranslationManager::filePath($lang, 'symphony'))) return false;
			$this->_Parent->Configuration->set('lang', $lang, 'symphony');
			return $this->_Parent->saveConfig();
		}

		public function listAll($name = NULL) {
			$result = array();

			if ($name === NULL) {
				$result = $this->listAll('symphony');
				foreach ($this->_Parent->ExtensionManager->listAll() as $extension => $about) {
					$result = array_merge_recursive($result, $this->listAll($extension));
				}
			}
			else if (strlen($name) > 0) {
				$path = dirname(TranslationManager::filePath('en', $name));
				foreach (glob($path.'/lang.*.php') as $file) {
					$lang = preg_replace('/^[\w\W]+\/lang.(\w+).php/', '\\1', $file);
					$result[$lang][] = $name;
				}
			}

			return $result;
		}

		public function listExtensions($lang) {
			if (strlen($lang) < 1) return array();
			$result = array();

			if (file_exists(TranslationManager::filePath($lang, 'symphony'))) $result[] = 'symphony';

			foreach ($this->_Parent->ExtensionManager->listAll() as $extension => $about) {
				if (file_exists(TranslationManager::filePath($lang, $extension))) $result[] = $extension;
			}

			return $result;
		}

		public function get($lang, $name = NULL) {
			if ($name === NULL) {
				$result = $this->get($lang, 'symphony');
				if (empty($result)) {
					$result = array(
						'about' => array(),
						'dictionary' => array(),
						'transliterations' => array(),
					);
				}
				foreach ($this->_Parent->ExtensionManager->listAll() as $extension => $about) {
					$temp = $this->get($lang, $extension);
					if (is_array($temp['about'])) {
						$result['about']['name'] = $temp['about']['name'];
						$this->__updateAuthors($result['about'], $temp['about']);
					}
					if (is_array($temp['dictionary'])) $result['dictionary'] = array_merge($result['dictionary'], $temp['dictionary']);
					if (is_array($temp['transliterations'])) $result['transliterations'] = array_merge($result['transliterations'], $temp['transliterations']);
				}

				if (empty($result['about']) && empty($result['dictionary']) && empty($result['transliterations'])) $result = array();

				return $result;
			}
			else if (strlen($name) < 1) return array();

			$file = TranslationManager::filePath($lang, $name);
			if (!file_exists($file)) return array();

			include($file);

			return array(
				'about' => (is_array($about) ? $about : array()),
				'dictionary' => (is_array($dictionary) ? $dictionary : array()),
				'transliterations' => (is_array($transliterations) ? $transliterations : array()),
			);
		}

		public function set($lang, $data, $name) {
			if (strlen($lang) < 1 || !is_array($data) || empty($name)) return false;

			$isSymphony = ($name == 'symphony');

			$data['about']['extension'] = $name;
			$this->__updateAuthors($data['about']);

			if (!is_array($data['dictionary'])) $data['dictionary'] = array();

			if (!is_array($data['transliterations']) || ($isSymphony && empty($data['transliterations'])))
				 $data['transliterations'] = ($isSymphony ? TranslationManager::defaultTransliterations() : array());

			$file = TranslationManager::filePath($lang, $name);
			if (!$isSymphony && !is_dir(dirname($file))) {
				if (!General::realiseDirectory(dirname($file), $this->_Parent->Configuration->get('write_mode', 'directory'))) return false;
			}

			return General::writeFile($file, TranslationManager::toPHP($data), $this->_Parent->Configuration->get('write_mode', 'file'));
		}

		public function remove($lang, $name = NULL) {
			if ($name === NULL) {
				if (!$this->remove($lang, 'symphony')) return false;
				foreach ($this->_Parent->ExtensionManager->listAll() as $extension => $about) {
					if (!$this->remove($lang, $extension)) return false;
				}
				return true;
			}

			$file = TranslationManager::filePath($lang, $name);
			if (file_exists($file) && !General::deleteFile($file)) return false;

			return true;
		}

		public function defaultDictionary($name = NULL) {
			$strings = array();
			$strings[] = array(); // Warnings placeholder
			foreach ($this->__dictionaryPaths($name) as $path) {
				$files = General::listStructure(DOCROOT."/{$path}", array('php', 'tpl'), false, 'asc');
				if (empty($files['filelist'])) continue;
				foreach ($files['filelist'] as $file) {
					$this->__findStrings(DOCROOT."/{$path}/{$file}", $strings);
				}
			}

			if ($name === NULL || $name == 'symphony') {
				$this->__findNavigationStrings(ASSETS.'/navigation.xml', $strings);
			}

			return $strings;
		}

		private function __updateAuthors(&$about, $author = NULL) {
			if (!is_array($author) || empty($author)) {
				$author = array(
					'name' => trim($this->_Parent->Author->getFullName()),
					'website' => URL,
					'email' => trim($this->_Parent->Author->get('email')),
					'release-date' => date('Y-m-d'),
				);
			}
			else if (!isset($author['release-date'])) $author['release-date'] = date('Y-m-d');

			if (isset($about['author']['name']) && !is_array($about['author']['name'])) {
				$about['author'] = array(trim($about['author']['name']) => $about['author']);
			}

			// Add author only only if it's not there yet, or if existing date is older than the one in new data set
			if (!isset($about['author'][$author['name']]) || strnatcmp($about['author'][$author['name']]['release-date'], $author['release-date']) < 0) {
				$about['author'][$author['name']] = $author;
			}

			uasort($about['author'], array($this, '__sortAuthorsByDate'));
		}

		private function __sortAuthorsByDate($a, $b) {
			return strnatcmp($a['release-date'], $b['release-date']);
		}

		private function __dictionaryPaths($name = NULL) {
			$paths = array();
			if ($name === NULL || $name == 'symphony') {
				$paths += array(
					'symphony/content',
					'symphony/template',
					'symphony/lib/toolkit',
					'symphony/lib/toolkit/data-sources',
					'symphony/lib/toolkit/events',
					'symphony/lib/toolkit/fields',
				);
			}

			$names = array();
			if ($name === NULL) {
				$extensions = $this->_Parent->ExtensionManager->listAll();
				if (is_array($extensions)) $names = array_keys($extensions);
			}
			else if ($name != 'symphony' && strlen($name) > 0) $names[] = $name;

			foreach ($names as $name) {
				$paths = array_merge($paths, array(
					'extensions/'.$name.'/content',
					'extensions/'.$name.'/interface',
					'extensions/'.$name.'/template',
					'extensions/'.$name.'/data-sources',
					'extensions/'.$name.'/events',
					'extensions/'.$name.'/fields',
					'extensions/'.$name.'/lib', // TODO: needs testing with extensions which use "non-symphony" code in lib directory.
					'extensions/'.$name
				));
			}

			return $paths;
		}

		private function __findStrings($path, &$result) {
			if (!file_exists($path)) return false;

			$tokens = file_get_contents($path);
			if (empty($tokens)) return false;

			$tokens = token_get_all($tokens);
			if (!is_array($tokens) || empty($tokens)) return false;

			if (!is_array($result) || !is_array($result[0])) $result[0] = array(); // Placeholder for warnings

			$i = -1;
			$on = false;
			$warn = false;
			$depth = -1;
			$line = 0;
			while ($tokens[++$i]) {
				if (!$on) {
					if (is_array($tokens[$i]) && $tokens[$i][1] == '__' && $tokens[$i][0] == T_STRING) { // __ function call starts parsable string
						$on = $i;
						$depth = -1;
						$warn = false;
						$line = $tokens[$i][2];
					}
					continue;
				}
				$isArray = is_array($tokens[$i]);

				if ($isArray) {
					$line = $tokens[$i][2];
					if ($tokens[$i][0] == T_CONSTANT_ENCAPSED_STRING && $tokens[$i-1] !== '[') { // 'some value' or "some value"
						// Constant strings are tokenized with wrapping quote/doublequote included. Text is not unescaped, so things like "foo\'s" are there too.
						$temp = trim(str_replace('\\'.$tokens[$i][1]{0}, $tokens[$i][1]{0}, $tokens[$i][1]), $tokens[$i][1]{0});
						if ($temp) $result[$temp][$path][] = $tokens[$i][2];
						else $warn = true; // Empty string?
						continue;
					}
				}

				if (($isArray && $tokens[$i][0] == T_START_HEREDOC) || // <<<END some value END
					(!$isArray && $tokens[$i] == '"')) { // "some $variable inside parsable text"
					$temp = '';
					while ($tokens[++$i]) {
						if (is_array($tokens[$i])) $temp .= $tokens[$i][1]; // Text is T_ENCAPSED_AND_WHITESPACE, but it can be also variable, function call, etc...
						else $temp .= $tokens[$i];
					}
					if ($temp) $result[$temp][$path][] = $line;
					$warn = true;
					continue;
				}

				if ($isArray) continue;

				switch ($tokens[$i]) {
					case '(': // Open inner parenthesis
						if (++$depth > 0) $warn = true;
						break;
					case ')': // Close inner parenthesis
						 if (--$depth >= 0) break;
					case ',':
						if ($depth > 0) break;
						// Comma marking end of first argument passed to __()
						if ($warn && $tokens[$on-1] !== '@') { // Warn about using parsable strings and/or variable text for translatable data
							$temp = '';
							$l = $tokens[$on][2];
							while ($on <= $i) {
								if (is_array($tokens[$on])) $temp .= $tokens[$on][1];
								else $temp .= $tokens[$on];
								$on++;
							}
							$result[0][$temp][$path][] = $l;
						}
						$on = false;
						break;
					//case '.': // Gluing strings is not "clean" - use %s tokens! ;P
					default: // Any other usage of PHP in place of simple string is not advised
						$warn = true;
						break;
				}
			}

			return true;
		}

		private function __findNavigationStrings($path, &$result) {
			$doc = new DOMDocument();
			$xml = $doc->load($path);
			if(!$xml) return false;
		
		    $xpath = new DOMXPath($doc);
		    $items = $xpath->query("//*[not(@visible='no')]");
		    
			$index = 0;
		    foreach($items as $item) {
				$index++;
				if($item->getAttribute('name')) $result[$item->getAttribute('name')][$path][] = $index;
		    }

			return true;
		}

		public static function toPHP($data) {
			$php = '<'."?php\n\n";
			$php .= '$about = '.var_export($data['about'], true).";\n\n";
			$php .= '$dictionary = '.var_export($data['dictionary'], true).";\n\n";
			$php .= '$transliterations = '.var_export($data['transliterations'], true).";\n\n";
			$php .= '?>';

			return $php;
		}

		public static function filePath($lang, $name) {
			if ($name == 'symphony') return LANG."/lang.{$lang}.php";
			else return EXTENSIONS."/{$name}/lang/lang.{$lang}.php";
		}

		public static function defaultTransliterations() {
			static $transliterations;
			if (is_array($transliterations)) return $transliterations;

			include(TOOLKIT . '/include.transliterations.php');

			return $transliterations;
		}

	}


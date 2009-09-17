<?php

	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(EXTENSIONS . '/localisationmanager/lib/class.translationmanager.php');

	Class contentExtensionLocalisationManagerIndex extends AdministrationPage{

		private $_tm;

		function __construct(&$parent) {
			parent::__construct($parent);
			$this->_tm = new TranslationManager($parent);
		}
		
		/**
		 * Display export page
		 */
		
		function view(){
			$this->setPageType();
			$this->setTitle(__('%1$s &ndash; %2$s', array(__('Symphony'), __('Localisation Manager'))));
			$this->appendSubheading(__('Language Manager'));
			$content = '<legend>' . __('Export Settings') . '</legend>
						<div class="group">
							<label>' . __('Language') . '<i>' . __('e. g. Deutsch, Français') . '</i>
								<input type="text" name="fields[language]" />
							</label>
							<label>' . __('Language Code') . '<i>' . __('e. g. de, fr') . '</i>
								<input type="text" name="fields[code]" />
							</label>
						</div>
						<p class="help">' . __('If you enter details of an already existing language, your download will contain all current strings highlighting missing ones. <br />If you leave all fields blank, a clean, untranslated language file will be returned.') . '</p>';
			$fieldset = new XMLElement('fieldset', $content, array('class' => 'settings'));
			$this->Form->appendChild($fieldset);
			$actions = new XMLElement('div', '<input name="action[export]" type="submit" value="' . __('Download Language File') . '" />', array('class' => 'actions'));
			$this->Form->appendChild($actions);
		}
		
		/**
		 * Manage form actions
		 */

		function action() {
			if (!$_POST['action']['export']) return;
			$this->export($_POST['fields']['code'], $_POST['fields']['language']);
		}

		/**
		 * Export selected language file
		 *
		 * @param string $code
		 * @param string $language
		 */

		function export($code, $language) {
			// Create content
			if(!$code) $code = 'clean';
			if(!$language) $language = 'Clean language file';
			$author = Administration::instance()->Author->_fields['first_name'] . ' ' . Administration::instance()->Author->_fields['last_name'];
			$email = Administration::instance()->Author->_fields['email'];
			$date = date('Y-m-d');
			// Create about array
			$php = '<'."?php\n\n";
			$php .= <<<END
	\$about = array(
		'name' => '$language',
		'author' => array(
			'name' => '$author',
			'email' => '$email',
		),
		'release-date' => '$date',
	);

END;
			// Get core language strings
			$php .= $this->getStrings($code, 'symphony');
			// Get extension language strings
			$extensions = $this->_Parent->ExtensionManager->listAll();
			foreach($extensions as $extension => $about) {
				$php .= $this->getStrings($code, $extension, $about['name']);			
			}
			// Setup Transliterations
			$php .= <<<END
	
	
	/*
	 * CORE: Symphony
	 * Transliterations
	 */

	\$transliterations = array(
	
		// Alphabetic
		

END;
			// Get transliterations
			$translation = $this->_tm->get($code);
			if(!is_array($translation['transliterations']) || empty($translation['transliterations'])) {
				$translation['transliterations'] = TranslationManager::defaultTransliterations();
			}
			$count = 1;
			$break = 5;
			foreach($translation['transliterations'] as $string => $translation) {
				// Create divisions
				if($string == '/à/') {
					$php .= "\n";
					$count = 1;
				}
				elseif($string == '/\(/') {
					$php .= "\n\n\t\t// Symbolic\n\n";
					$count = 1;
					$break = 3;
				}
				elseif($string == '/^&(?!&)$/') {
					$php .= "\n\t\t// Ampersands\n\n";
					$count = 1;
					$break = 1;
				}
				// Add transliteration
				if($translation == null || empty($translation)) {
					$translation = 'null';
				}
				else {
					$translation = "'" . $translation . "'";
				}
				$php .= "\t\t'" . $string . "' => " . $translation . ",";
				if(!($count % $break)) $php .= "\n";
				$count++;
			}
			$php .= "\n\t);\n\n ";
			// Send language file
			if (!empty($php)) {
				header('Content-Type: application/x-php; charset=utf-8');
				header('Content-Disposition: attachment; filename="lang.'.$code.'.php"');
				header("Content-Description: File Transfer");
				header("Cache-Control: no-cache, must-revalidate");
				header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
				echo trim($php);
				exit();
			}
		}
		
		/**
		 * Get current language strings
		 *
		 * @param string $code
		 * @param string $context
		 */

		function getStrings($code, $context, $name) {
			// Get default language strings
			$default = array(
				'about' => array(),
				'dictionary' => $this->_tm->defaultDictionary($context),
				'transliterations' => array()
			);
			// Get all translations for current language
			$translation = $this->_tm->get($code);
			if(empty($translation)) {
				$translation = array(
					'about' => array(),
					'dictionary' => array(),
					'transliterations' => array()
				);
			}
			// Set header
			$area = ($context == 'symphony') ? 'CORE' : 'EXTENSION';
			$name = ($context == 'symphony') ? 'Symphony' : $name;
			$add = ($context == 'symphony') ? '' : '+';
			$start = <<<END
	
	
	/*
	 * $area: $name
	 * Localisation strings
	 */

	\$dictionary $add= array(
	

END;
			// Add translations
			$string = '';
			$translated = array_intersect_key(array_filter($translation['dictionary'], 'trim'), $default['dictionary']);
			foreach($translated as $string => $translation) {
				//if($context != 'symphony' && array_key_exists($string, $default['dictionary'])) continue;
				if(!empty($string)) $strings .= "\t\t'" . str_replace("'", "\'", $string) . "' => \n\t\t'" . str_replace("'", "\'", $translation) . "',\n\n";
			}
			// Add missing strings
			$missing = array_diff_key($default['dictionary'], $translated);
			$missing = array_fill_keys(array_keys($missing), false);
			$index = 0;
			foreach($missing as $string => $translation) {
				if(!empty($string)) {
					if($index == 0) {
						$strings .= "\t\t// Missing translations\n\n";
						$index++;
					}
					$strings .= "\t\t'" . addslashes($string) . "' => \n\t\tfalse,\n\n";
				}
			}
			// Close array and return
			if(empty($strings)) return;
			return $start . $strings . "\t);\n";
		}
	}

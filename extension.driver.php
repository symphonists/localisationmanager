<?php

	require_once(EXTENSIONS . '/localisationmanager/lib/class.translationmanager.php');

	Class extension_localisationmanager extends Extension{
	
		/**
		 * Extension information
		 */

		public function about(){
			return array(
				'name' => 'Localisation Manager',
				'version' => '1.0.3',
				'release-date' => '2009-11-10',
				'author' => array(
					'name' => 'Nils Hörrmann',
					'website' => 'http://nilshoerrmann.de',
					'email' => 'post@nilshoerrmann.de'
				)
			);
		}

		/**
		 * Add callback functions to backend delegates
		 */

		public function getSubscribedDelegates(){
			return array(
				array(
					'page' => '/system/preferences/',
					'delegate' => 'AddCustomPreferenceFieldsets',
					'callback' => 'appendPreferences'
				),
				array(
					'page' => '/administration/',
					'delegate' => 'AdminPagePostGenerate',
					'callback' => 'customPreferences'
				),					
				array(
					'page' => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => '__setCustomLanguage'
				),					
				array(
					'page' => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => '__saveCustomPreferences'
				),					
				array(
					'page' => '/system/preferences/',
					'delegate' => 'Save',
					'callback' => '__savePreferences'
				),
				array(
					'page' => '/administration/',
					'delegate' => 'NavigationPreRender',
					'callback' => '__translateNavigation'
				),
				array(
					'page' => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => '__translateJavaScript'
				)
			);
		}
			
		/**
		 * Translate navigation
		 *
		 * @param object $context 
		 */

		public function __translateNavigation($context) {
			// navigation available?
			if(!is_array($context['navigation'])) return;
			// translate navigation items
			for($i = 1; $i < count($context['navigation']); $i++) {
				$context['navigation'][$i]['name'] = @__($context['navigation'][$i]['name']);
				if(is_array($context['navigation'][$i]['children'])) {
					for($c = 0; $c < count($context['navigation'][$i]['children']); $c++) {
						$context['navigation'][$i]['children'][$c]['name'] = @__($context['navigation'][$i]['children'][$c]['name']);
					}
				}
			}
		}
			
		/**
		 * Add file with javascript translations
		 *
		 * @param object $context 
		 */

		public function __translateJavaScript($context) {
			// get current language
			$lang = $this->__getCurrentLanguage(true);
			if($lang == 'system') {
				$lang = Administration::instance()->Configuration->get('lang', 'symphony');
			}
			// append current language file
			if($lang != 'en') $context['parent']->Page->addScriptToHead(URL . '/symphony/extension/localisationmanager/javascript', 1000, false);
		}
		
		/**
		 * Save preferences
		 *
		 * @param object $context 
		 */
		
		public function __savePreferences($context) {
			$this->_Parent->Configuration->set('lang', $_POST['settings']['language'], 'symphony');
			return $this->_Parent->saveConfig();
		}
		
		/**
		 * Save custom preferences
		 *
		 * @param object $context 
		 */
		
		public function __saveCustomPreferences($context) {
			if(Administration::instance()->Page instanceof contentSystemAuthors && !empty($_POST['fields']['custom-language'])) {
				$callback = Administration::instance()->getPageCallback();
				$id = $callback['context'][1];
				$language = Administration::instance()->Database->cleanValue($_POST['fields']['custom-language']);
				Administration::instance()->Database->query(
					"UPDATE `tbl_authors`
					SET `language` = '$language'
					WHERE `id` = '$id'"
				);
			}
		}
		
		/**
		 * Set custom preferences
		 *
		 * @param object $context 
		 */
		
		public function __setCustomLanguage($context) {
			$language = $this->__getCurrentLanguage(true);
			if($language != 'system') {
				if(file_exists(EXTENSIONS . '/localisationmanager/lang/lang.' . $language . '.php')) {
					Lang::init(EXTENSIONS . '/localisationmanager/lang/lang.%s.php', $language);
				}
				elseif(file_exists(LANG . '/lang.' . $language . '.php')) {
					Lang::init(LANG . '/lang.%s.php', $language);
				}
			}
		}
		
		/**
		 * Get current language
		 *
		 * @param boolean $current 
		 */
		
		public function __getCurrentLanguage($current) {
			if($current) {
				$id = Administration::instance()->Author->_fields['id'];		
			}
			else {
				$callback = Administration::instance()->getPageCallback();
				$id = $callback['context'][1];
			}
			$language = Administration::instance()->Database->fetchCol('language', 
				"SELECT `language`
				FROM `tbl_authors`
				WHERE `id` = '$id'"
			);
			return $language[0];
		}
		
		/**
		 * Returns an array with all languages currently available 
		 *
		 * @param object $context 
		 */

		public function __getLanguages($context) {
			// Get translation files
			$tm = new TranslationManager($context['parent']);
			$translations = $tm->listAll();
			// Get names
			foreach($translations as $lang => $extensions) {
				$language = $tm->get($lang);
				$languages[$lang] = $language['about']['name'];
			}
			if(empty($languages['en'])) $languages['en'] = 'English';
			asort($languages);
			return $languages;
		}
			
		/**
		 * Append language option to the preference pane
		 *
		 * @param object $context 
		 */

		public function appendPreferences($context) {
			// Get current language
			include(CONFIG);
			$config = new Configuration(true);
			$config->setArray($settings);
			$current = $config->get('lang', 'symphony');
			// $current = Administration::instance()->Configuration->get('lang', 'symphony');
			// Create preference pane
			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings');
			$group->appendChild(new XMLElement('legend', __('System Language')));			
			$label = Widget::Label();
			// Get languages
			$languages = $this->__getLanguages($context);
			$options = array();
			foreach($languages as $value => $desc) {
				$options[] = array($value, ($current == $value ? true : false), $desc);
			}
			// Create select list
			$select = Widget::Select('settings[language]', $options);
			$label->appendChild($select);
			$group->appendChild($label);			
			$group->appendChild(new XMLElement('p', __('Authors can set up a differing language in their profiles.'), array('class' => 'help')));
			// Append preference pane					
			$context['wrapper']->appendChild($group);
		}		
		
		/**
		 * Add custom author preference
		 *
		 * @param object $context 
		 */
		
		public function customPreferences($context) {
			if(Administration::instance()->Page instanceof contentSystemAuthors) {
				// Current language
				$current = $this->__getCurrentLanguage();
				if($current == 'system') $selected = ' selected="selected"';
			    // Load current document
			    $doc = new DOMDocument();
			    $doc->loadHTML($context['output']);
			    $xpath = new DOMXPath($doc);
			    // Create preferences
			    $content = '<fieldset class="settings">
			    				<legend>' . __('Custom Preferences') . '</legend>
			    				<label>' .  __('Language') . '
			    					<select name="fields[custom-language]">
			    						<option value="system"' . $selected . '>' . __('System Standard') . '</option>
			    					</select>
			    				</label>
			    			</fieldset>';
			    $fieldset = $doc->createDocumentFragment();
			    $fieldset->appendXML($content);
			    // Append preferences
			    $settings = $xpath->query("//fieldset[@class='settings']");
			    $actions = $xpath->query("//div[@class='actions']")->item(0);
			    if($settings->length > 0) {
			    	$actions->parentNode->insertBefore($fieldset, $actions);
					// Get languages
					$languages = $this->__getLanguages($context);
					foreach($languages as $value => $desc) {
						$selected = '';
						if($current == $value) $selected = ' selected="selected"';
						$option = '<option value="' . $value . '"' . $selected . '>' . $desc . '</option>';
						$language = $doc->createDocumentFragment();
						$language->appendXML($option);
				    	$select = $xpath->query("//select[@name='fields[custom-language]']")->item(0);
						$select->appendChild($language);
					}
				}
				// save new output
			    $context['output'] = $doc->saveHTML();
			}				
		}

		/**
		 * Function to be executed on installation.
		 *
		 * @return boolean - true on success, false otherwise
		 */
 
		public function install() {
			// Add custom language column
			return Administration::instance()->Database->query(
				"ALTER TABLE `tbl_authors` ADD `language` VARCHAR(255) DEFAULT 'system'"
			);
		}
	
		/**
		 * Function to be executed on uninstallation
		 */
	
		public function uninstall() {
			// Remove custom language settings
			Administration::instance()->Database->query(
				"ALTER TABLE `tbl_authors` DROP `language`"
			);			
			// Reset global language settings
			Administration::instance()->Configuration->set('lang', 'en', 'symphony');
			return Administration::instance()->saveConfig();		
		}
		
	}
	
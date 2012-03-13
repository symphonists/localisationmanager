<?php

	require_once(TOOLKIT . '/class.administrationpage.php');

	Class contentExtensionLocalisationManagerLocalisations extends AdministrationPage {
		
		/**
		 * Display overview page
		 */
		function view(){
			$this->setPageType('table');
			$this->setTitle(__('%1$s &ndash; %2$s', array(__('Symphony'), __('Localisation Manager'))));
			$this->appendSubheading(__('Language Manager'));
			$this->addScriptToHead(URL . '/extensions/localisationmanager/assets/sortit.js', 101);
			
			// Get extensions
			$overview = Symphony::ExtensionManager()->listAll();
			
			// Add core
			$overview['symphony'] = array(
				'name' => 'Symphony Core',
				'handle' => 'symphony'
			);
			
			// Sort by  name:
			uasort($overview, array('contentExtensionLocalisationManagerLocalisations', 'sortByName'));

			// Create table head
			$thead = array(
				array(__('Name'), 'col'),
				array(__('Available dictionaries'), 'col'),
				array(__('Action'), 'col'),
			);

			// Create table body
			$tbody = array();
			
			// Create rows
			if(is_array($overview)){
				foreach($overview as $name => $details) {
					if(strpos($about['handle'], 'lang_') !== false) continue;

					$langlinks = '';
					
					// Extensions
					if($name != 'symphony' ) {
						$path = EXTENSIONS . '/'. $name . '/lang/';
						if(file_exists($path)) {
							$directory = new DirectoryIterator($path);
							foreach($directory as $file) {
								if($file->isDot()) continue;
		
								include($file->getPathname());
		
								// Get language code
								$code = explode('.', $file);
								$code = $code[1];
		
								// Create link
								if(!empty($langlinks)) $langlinks .= ', ';
								$langlinks .= '<a href="' . URL . '/symphony/extension/localisationmanager/download/' . $details['handle'] . '/' . $code . '/">' . ($about['name'] ? $about['name'] : $code) . '</a>';
							}
						}
					}
					
					// Core
					else {
						foreach(Lang::Languages() as $code => $language) {
							if($code == 'en') continue;
						
							// Create link
							if(!empty($langlinks)) $langlinks .= ', ';
							$langlinks .= '<a href="' . URL . '/symphony/extension/localisationmanager/download/symphony/' . $code . '/">' . $language['name'] . '</a>';
						}
					}
					
					// Status
					$class = NULL;
					if(empty($langlinks)) {
						$langlinks = __('None');
						$class = 'inactive';
					}
					
					// Create cells
					$td1 = Widget::TableData($details['name']);
					$td2 = Widget::TableData(str_replace('%ext%', $about['handle'], $langlinks), $class);
					$td3 = Widget::TableData('<a href="' . URL . '/symphony/extension/localisationmanager/download/' . $details['handle'] . '">' . __('Create new dictionary') . '</a>');
					
					// Populate table body
					$tbody[] = Widget::TableRow(array($td1, $td2, $td3), NULL);
				}
			}

			// Build table
			$table = Widget::Table(
				Widget::TableHead($thead),
				NULL,
				Widget::TableBody($tbody)
			);

			// Append table
			$this->Form->appendChild($table);
		}

		public static function sortByName(array $a, array $b) {
			return strnatcasecmp($a['name'], $b['name']);
		}

	}

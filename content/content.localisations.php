<?php

	require_once(TOOLKIT . '/class.administrationpage.php');

	Class contentExtensionLocalisationManagerLocalisations extends AdministrationPage {

		function __construct(&$parent) {
			parent::__construct($parent);
		}
		
		/**
		 * Display overview page
		 */
		function view(){
			$this->setPageType('table');
			$this->setTitle(__('%1$s &ndash; %2$s', array(__('Symphony'), __('Localisation Manager'))));
			$this->appendSubheading(__('Language Manager'));
			$this->addScriptToHead(URL . '/extensions/localisationmanager/assets/sortit.js', 101);
			
			// Get extensions
			$ExtensionManager = $this->_Parent->ExtensionManager;
			$overview = $ExtensionManager->listAll();
			
			// Add core
			$overview['symphony'] = array(
				'name' => 'Symphony Core',
				'handle' => 'symphony'
			);
			
			// Sort by  name:
			uasort($overview, array('ExtensionManager', 'sortByName'));

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
				foreach($overview as $name => $about) {
					if(strpos($about['handle'], 'lang_') !== false) continue;
					
					// Get available languages
					$langlinks = '';
					foreach(Lang::$_languages as $code => $data) {
						if($code == 'en') continue;
						
						if($name == 'symphony' || array_key_exists($about['handle'], $data['extensions'])) {
							if($langlinks != '') $langlinks .= ', ';
							$langlinks .= '<a href="' . URL . '/symphony/extension/localisationmanager/download/' . $about['handle'] . '/' . $code . '/' . $data['handle'] . '">' . $data['name'] . '</a>';
						}
					}
					
					// Status
					$class = NULL;
					if(empty($langlinks)) {
						$langlinks = __('None');
						$class = 'inactive';
					}
					
					// Create cells
					$td1 = Widget::TableData($about['name']);
					$td2 = Widget::TableData(str_replace('%ext%', $about['handle'], $langlinks), $class);
					$td3 = Widget::TableData('<a href="' . URL . '/symphony/extension/localisationmanager/download/' . $about['handle'] . '">' . __('Create new dictionary') . '</a>');
					
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
	}

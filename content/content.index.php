<?php

	require_once(TOOLKIT . '/class.administrationpage.php');

	Class contentExtensionLocalisationManagerIndex extends AdministrationPage {

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
				array(__('Download installed languages'), 'col'),
				array(__('Action'), 'col'),
			);

			// Create table body
			$tbody = array();
			
			// Get available languages
			$languages = Lang::getAvailableLanguages($ExtensionManager);
			$langlinks = '';
			foreach($languages as $code => $name) {
				if($code == 'en') continue;
				if($langlinks != '') $langlinks .= ', ';
				$langlinks .= '<a href="download/%ext%/' . $code . '">' . $name . '</a>';
			}
			
			// Create rows
			if(is_array($overview)){
				foreach($overview as $name => $about) {
					if(strpos($about['handle'], 'lang_') !== false) continue;
					// Create cells
					$td1 = Widget::TableData($about['name']);
					$td2 = Widget::TableData(str_replace('%ext%', $about['handle'], $langlinks));
					$td3 = Widget::TableData('<a href="download/' . $about['handle'] . '">Add new language</a>');
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

<?php

	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(EXTENSIONS . '/localisationmanager/lib/class.translationmanager.php');

	Class contentExtensionLocalisationManagerJavaScript extends AdministrationPage {

		private $_tm;

		function __construct(&$parent) {
			parent::__construct($parent);
			$this->parent = $parent;
			$this->_tm = new TranslationManager($parent);
		}
				
		/**
		 * Display translation object for admin.js
		 */
		
		function view() {
			// Get JavaScript language strings
			$strings = $this->_tm->JavaScriptStrings();
			// Create JavaScript object
			$current = $this->__getCurrentLanguage();
			echo <<<END
/*
 * JAVASCRIPT LOCALISATION 
 * for Symphony CMS
 *
 * If you like to change the following translations, please edit /localisationmanager/lang/lang.$current.php
 * or create a new language file using Localisation Manager.
 */

Symphony.Language = {

END;
			foreach($strings as $id => $string) {
				echo "\t" . $id . ": \"" . __($string) . "\",\n";
			}
			echo '}';
			exit;
		}
		
		/**
		 * Get current language
		 */
		
		function __getCurrentLanguage() {
			$id = Administration::instance()->Author->_fields['id'];		
			$language = Administration::instance()->Database->fetchCol('language', 
				"SELECT `language`
				FROM `tbl_authors`
				WHERE `id` = '$id'"
			);
			if($language[0] == 'system') {
				return Administration::instance()->Configuration->get('lang', 'symphony');
			}
			return $language[0];
		}

	}

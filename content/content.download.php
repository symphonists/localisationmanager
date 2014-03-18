<?php

	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(EXTENSIONS . '/localisationmanager/lib/class.localisationmanager.php');

	Class contentExtensionLocalisationManagerDownload extends AdministrationPage {

		private $LocalisationManager;
		
		private $titles = array(
			'/login' => 'Namespaced translations for the login area',
			'/publish' => 'Namespace translations for the publish area',
			'/blueprints/pages' => 'Namespaced translations for pages', 
			'/blueprints/sections' => 'Namespaced translations for sections',
			'/blueprints/datasources' => 'Namespaced translations for Data Sources', 
			'/blueprints/events' => 'Namespaced translations for events',
			'/blueprints/utilities' => 'Namespaced translations for utilities',
			'/system/authors' => 'Namespaced translations for authors',
			'/system/preferences' => 'Namespaced translations for the preferences',
			'/system/extensions' => 'Namespaced translations for the extension overview'		
		);
		
		function __construct() {
			parent::__construct();
			$this->LocalisationManager = new LocalisationManager();
		}
		
		/**
		 * Download language file
		 */
		function build($context) {
		
			// Get context
			$name = $context[2];
			$lang = $context[1];
			$context = $context[0];
			
			// Get localisation strings
			$data = $this->LocalisationManager->buildDictionary($context, $lang, $name);

			// Load template
			$path = EXTENSIONS . '/localisationmanager/lib';
			if($context == 'symphony') {
				$template = file_get_contents($path . '/lang.core.tpl');
			}
			else {
				$template = file_get_contents($path . '/lang.extension.tpl');
			}
			
			// Add data
			$template = str_replace('<!-- $name -->', $data['about']['name'], $template);
			$template = str_replace('<!-- $author -->', $data['about']['author']['name'], $template);
			$template = str_replace('<!-- $email -->', $data['about']['author']['email'], $template);
			$template = str_replace('<!-- $website -->', $data['about']['author']['website'], $template);
			$template = str_replace('<!-- $date -->', $data['about']['release-date'], $template);
			
			if($context != 'symphony') {
				$ExtensionManager = new ExtensionManager($this->parent);
				$extensions = $ExtensionManager->listAll();
				$template = str_replace('<!-- $extension -->', $extensions[$context]['name'], $template);
			}

			$template = str_replace('<!-- $strings -->', $this->__layout($data['dictionary']['strings']), $template);
			$template = str_replace('<!-- $obsolete -->', $this->__layout($data['dictionary']['obsolete'], 'Obsolete'), $template);
			$template = str_replace('<!-- $missing -->', $this->__layout($data['dictionary']['missing'], 'Missing'), $template);
			$template = str_replace('<!-- $namespaces -->', $this->__layoutNamespace($data['dictionary']['namespaces']), $template);
	
			if($context == 'symphony') {
				$template = str_replace('<!-- $uppercase -->', $this->__transliterations($data['transliterations']['straight']['uppercase'], 5), $template);
				$template = str_replace('<!-- $lowercase -->', $this->__transliterations($data['transliterations']['straight']['lowercase'], 5), $template);
				$template = str_replace('<!-- $symbolic -->', $this->__transliterations($data['transliterations']['straight']['symbolic'], 3), $template);
				$template = str_replace('<!-- $special -->', $this->__transliterations($data['transliterations']['straight']['special']), $template);
				$template = str_replace('<!-- $otherstraight -->', $this->__transliterations($data['transliterations']['straight']['other']), $template);
				$template = str_replace('<!-- $ampersands -->', $this->__transliterations($data['transliterations']['regexp']['ampersands']), $template);
				$template = str_replace('<!-- $otherregexp -->', $this->__transliterations($data['transliterations']['regexp']['other']), $template);
			}
			
			// Send file
			header('Content-Type: application/x-php; charset=utf-8');
			header('Content-Disposition: attachment; filename="lang.' . ($lang ? $lang : 'new') . '.php"');
			header("Content-Description: File Transfer");
			header("Cache-Control: no-cache, must-revalidate");
			header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
			echo $template;
			exit();			
		}
		
		private function __layoutNamespace($namespaces) {
			if(empty($namespaces)) return;
		
			$namespaced = '';
			foreach($namespaces as $name => $groups) {
			
				// Get context
				if(array_key_exists($name, $this->titles)) {
					$title = $this->titles[$name];
				}
				else {
					$title = 'Namespaced translations for other areas';
				}
			
				// Create namespace
				$namespaced .= "\t\t// " . $title . "\n\n";
				$namespaced .= "\t\t'" . $this->__clean($name) . "' => array(\n\n";
				$namespaced .= $this->__layout($groups['strings'], false, $indent = "\t\t\t");
				$namespaced .= $this->__layout($groups['obsolete'], 'Obsolete', $indent = "\t\t\t");
				$namespaced .= "\t\t),\n\n";
			}
			return $namespaced;
		}
		
		private function __layout($strings, $comment=false, $indent = "\t\t") {
			if(!is_array($strings) || empty($strings)) return;
			if($comment) {
				$content = $indent . "// " . $comment . "\n\n";
			}
			foreach($strings as $key => $string) {
				$content .= $indent . "'" . $this->__clean($key) . "' => \n" . $indent . $this->__clean($string, true) . ",\n\n";
			}
			return $content;
		}
		
		private function __transliterations($strings, $break=1) {
			if(!is_array($strings)) return;
			$count = 0;
			foreach($strings as $key => $string) {
				if($count == $break) {
					$content .= "\n\t";
					$count = 0;
				}
				if(empty($string)) $string = 'null';
				else $string = "'" . $string . "'";
				$content .= " \t'" . $key . "' => " . $string . ",";
				$count++;
			}
			return $content;
		}
		
		private function __clean($string, $quote=false) {
			if(empty($string)) return 'false';
			$string = str_replace("'", "\'", stripslashes($string));
			if($quote) $string = "'" . $string . "'";
			return $string;
		}
		
	}

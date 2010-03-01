/*
 * LOCALISATION MANAGER for Symphony
 *
 * @author: Nils Hörrmann, post@nilshoerrmann.de
 * @source: http://github.com/nilshoerrmann/mediathek
 */


/*-----------------------------------------------------------------------------
	Language strings
-----------------------------------------------------------------------------*/	 

	Symphony.Language.add({
		'Sort output naturally': false
	}); 


/*-----------------------------------------------------------------------------
	Sort output
-----------------------------------------------------------------------------*/

	jQuery(document).ready(function() {
	
		// Append sort option
		var table = jQuery('table');
		var sort = jQuery('<div class="actions"><input type="checkbox" name="sortit" /> ' + Symphony.Language.get('Sort output naturally') + '</div>');
		table.after(sort);
		
		// Events
		sort.click(function(event) {
		
			// Get status
			var checked = jQuery(event.target).attr('checked');
			
			// Add or remove sort option
			table.find('a').each(function(index, link) {
				link = jQuery(link);
				var href = link.attr('href');
	
				// Is checked?
				if(checked) {
					link.attr('href', href + '?sort');
				}
				
				// Is not checked?
				else {
					link.attr('href', href.replace('?sort', ''));
				}

			});
			
		});
	});

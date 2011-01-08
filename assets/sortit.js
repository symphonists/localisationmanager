/*
 * Localisation Manager
 *
 * @author: Nils Hörrmann, post@nilshoerrmann.de
 * @source: http://github.com/nilshoerrmann/localisationmanager
 */
(function($) {
	$(document).ready(function() {
	
		// Language strings
		Symphony.Language.add({
			'Sort output naturally': false
		}); 
	
		// Append sort option
		var table = $('table');
		var sort = $('<div class="actions"><input type="checkbox" name="sortit" /> ' + Symphony.Language.get('Sort output naturally') + '</div>');
		table.after(sort);
		
		// Events
		sort.click(function(event) {
		
			// Get status
			var checked = $(event.target).attr('checked');
			
			// Add or remove sort option
			table.find('a').each(function(index, link) {
				link = $(link);
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
})(jQuery.noConflict());

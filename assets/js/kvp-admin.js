(function( $ ) {
	
	function kvp_source_types() {
	
		var selectedType = $('select#source_service option:selected').val();
		
		$('select#source_type option').each( function(){
			
			if( '' == $(this).val() ) {
				
				$(this).parent().val('');
				return;
				
			}
			
			$(this).hide();
			
			if( -1 != $.inArray( $(this).val(), services_types[selectedType]['types'] ) )
				$(this).show();
				
			
			
		});
		
	}
	
	$(document).ready( function(){
		
		kvp_source_types();
		$('select#source_service').change( kvp_source_types);
		
	});
	
})( jQuery );
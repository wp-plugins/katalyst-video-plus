var inlineEditAccount;
(function($) {
inlineEditAccount = {

	init : function(){
		var t = this, qeRow = $('#inline-edit');

		t.type = $('table.widefat').hasClass('accounts');
		t.what = '#account-';

		// prepare the edit rows
		qeRow.keyup(function(e){
			if ( e.which === 27 ) {
				return inlineEditAccount.revert();
			}
		});

		$('a.cancel', qeRow).click(function(){
			return inlineEditAccount.revert();
		});
		$('a.save', qeRow).click(function(){
			return inlineEditAccount.save(this);
		});
		$('td', qeRow).keydown(function(e){
			if ( e.which === 13 ) {
				return inlineEditAccount.save(this);
			}
		});

		// add events
		$('#the-list').on('click', 'a.editinline', function(){
			inlineEditAccount.edit(this);
			return false;
		});

		$('#doaction, #doaction2').click(function(e){
			var n = $(this).attr('id').substr(2);
			if ( 'edit' === $( 'select[name="' + n + '"]' ).val() ) {
				e.preventDefault();
			} else if ( $('form#posts-filter tr.inline-editor').length > 0 ) {
				t.revert();
			}
		});
	},

	toggle : function(el){
		var t = this;
		$( t.what + t.getId( el ) ).css( 'display' ) === 'none' ? t.revert() : t.edit( el );
	},
	
	edit : function(id) {
		var t = this, fields, editRow, rowData, status, pageOpt, pageLevel, nextPage, pageLoop = true, nextLevel, cur_format, f;
		t.revert();

		if ( typeof(id) === 'object' ) {
			id = t.getId(id);
		}

		fields = ['username', 'author', 'developer_key', 'oauth_id', 'oauth_secret'];

		// add the new blank row
		editRow = $('#inline-edit').clone(true);
		$('td', editRow).attr('colspan', $('.widefat:first thead th:visible').length);

		if ( $( t.what + id ).hasClass( 'alternate' ) ) {
			$(editRow).addClass('alternate');
		}
		$(t.what+id).hide().after(editRow);

		// populate the data
		rowData = $('#inline_'+id);
		if ( !$(':input[name="edit_account[author]"] option[value="edit_account[' + $('.author', rowData).text() + ']"]', editRow).val() ) {
			// author no longer has edit caps, so we need to add them to the list of authors
			$(':input[name="edit_account[author]"]', editRow).prepend('<option value="edit_account[' + $('.author', rowData).text() + ']">' + $('#' + t.type + '-' + id + ' .author').text() + '</option>');
		}
		if ( $( ':input[name="edit_account[author]"] option', editRow ).length === 1 ) {
			$('label.inline-edit-author', editRow).hide();
		}
		
		for ( f = 0; f < fields.length; f++ ) {
			$(':input[name="edit_account[' + fields[f] + ']"]', editRow).val( $('.'+fields[f], rowData).text() );
		}
		
		ext_status = $.parseJSON( $('.ext_status', rowData ).text() );
		
		$.each( ext_status, function( key, value ) {
			
			if( 'active' === value )
				$( ':input[name="ext_status[' + key + ']"]', editRow ).prop( 'checked', true );
			
		});

		// hierarchical taxonomies
		$('.category', rowData).each(function(){
			var taxname,
				term_ids = $(this).text();

			if ( term_ids ) {
				taxname = $(this).attr('id').replace('_'+id, '');
				$('ul.'+taxname+'-checklist :checkbox', editRow).val(term_ids.split(','));
			}
		});

		$(editRow).attr('id', 'edit-'+id).addClass('inline-editor').show();
		$('.ptitle', editRow).focus();

		return false;
	},

	save : function(id) {
		var params, fields;

		if ( typeof(id) === 'object' ) {
			id = this.getId(id);
		}

		$('table.widefat .spinner').show();

		params = {
			action: 'kvp_inline_save',
			action2: 'edit',
			ID: id,
		};

		fields = $('#edit-'+id).find(':input').serialize();
		params = fields + '&' + $.param(params);
		
		// make ajax request
		$.post( ajaxurl, params,
			function(r) {
				$('table.widefat .spinner').hide();

				if (r) {
					if ( -1 !== r.indexOf( '<tr' ) ) {
						$(inlineEditAccount.what+id).remove();
						$('#edit-'+id).before(r).remove();
						$(inlineEditAccount.what+id).hide().fadeIn();
					} else {
						r = r.replace( /<.[^<>]*?>/g, '' );
						$('#edit-'+id+' .inline-edit-save .error').html(r).show();
					}
				} else {
					$('#edit-'+id+' .inline-edit-save .error').html(inlineEditL10n.error).show();
				}

				if ( $('#post-'+id).prev().hasClass('alternate') ) {
					$('#post-'+id).removeClass('alternate');
				}
			},
		'html');
		return false;
	},

	revert : function(){
		var id = $('table.widefat tr.inline-editor').attr('id');

		if ( id ) {
			$('table.widefat .spinner').hide();

			if ( 'bulk-edit' === id ) {
				$('table.widefat #bulk-edit').removeClass('inline-editor').hide();
				$('#bulk-titles').html('');
				$('#inlineedit').append( $('#bulk-edit') );
			} else {
				$('#'+id).remove();
				id = id.substr( id.lastIndexOf('-') + 1 );
				$(this.what+id).show();
			}
		}

		return false;
	},

	getId : function(o) {
		var id = $(o).closest('tr').attr('id'),
			parts = id.split('-');
		return parts[parts.length - 1];
	}
};

$( document ).ready( function(){ inlineEditAccount.init(); } );

}(jQuery));
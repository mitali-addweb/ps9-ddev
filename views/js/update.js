var newupktse = {
	url: '',
	init: function() {
		$.ajax({
			type: 'post',
			url: newupktse.url,
			data: { type: 'init' },
			dataType: 'html',
			success: function(res) {
				if (res != '') {
					$("#updaterbodytse").html(res);
	    			$("#updatermodaltse").modal('show');
	    		} else {
	    			$("#updatermodaltse").modal('hide');
	    		}
			}
		});
	},
	update: function(e) {
		if($(e).hasClass('disabled')) {
			return false;
		} else {
			$.ajax({
				type: 'post',
				url: newupktse.url,
				data: { type: 'update' },
				dataType: 'json',
				beforeSend: function() {
	  				$(e).addClass('disabled');
				},
				success: function(json) {
					$(e).removeClass('disabled');
					$("#updatermodaltse").modal('hide');
					alert(json.msg);
				}
			});
		}
	}
};
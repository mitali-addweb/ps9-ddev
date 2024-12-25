$(document).ready(function() {
	$('#customizationForm').css('display', 'none');
	$('#customizationForm').parent().css('display', 'none');
	var customize_tab_name = $('#customizationForm').parent().attr('data-tab');
	$('*[data-tab="'+customize_tab_name+'"]').css('display', 'none');
	
});

document.getElementById("psattributes").remove();

xjQuery('.product-customization-line img').each(function() {
	var src = xjQuery(this).attr('src');
	this.onerror = function(){
		xjQuery(this).attr('src', tse_ajax_svg_url + '/tshirtecommerce/image-tool/thumbs1.php?src='+src);
		xjQuery(this).css('width', '80px');
	}

	this.src = src;
});

// fixed 56528
xjQuery(document).ready(function() {
	xjQuery('.cart_item').each(function() {
		if (xjQuery(this).find('span.found_tshirtecommerce_sizes').length) {
			xjQuery(this).find('.cart_quantity .cart_quantity_button').css('display', 'none');
			xjQuery(this).find('input.cart_quantity_input').prop('disabled', true);
		}
	});
});

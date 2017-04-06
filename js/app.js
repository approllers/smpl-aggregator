$(document).ready(function() {
	$('#content').on({
		click: function() {
			if ($(this).find('i').hasClass('fa-heart-o')) {
				$(this).find('i').removeClass('fa-heart-o');
				$(this).find('i').addClass('fa-heart');
				// $.ajax({ add bookmark });
			}
			else if ($(this).find('i').hasClass('fa-heart')) {
				$(this).find('i').removeClass('fa-heart');
				$(this).find('i').addClass('fa-heart-o');
				// $.ajax({ remove bookmark });
			}
		}
	}, '.bookmark')
});
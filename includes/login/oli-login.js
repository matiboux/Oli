var updateForm = function(setup) {
	var length = $('.form').length;
	if(length > 1) {
		$('.toggle').show();
		var index = $('.form').index($('.form:visible'));
		var nextIndex = (index+1) % length;
		if(!setup) var futureIndex = (index+2) % length;
		
		if(setup) $('.toggle').children('.fas').addClass($('.form').eq(nextIndex).attr('data-icon')); 
		else $('.toggle').children('.fas').removeClass($('.form').eq(nextIndex).attr('data-icon')).addClass($('.form').eq(futureIndex).attr('data-icon'));
		
		$('.toggle').children('.tooltip').text($('.form').eq(setup ? nextIndex : futureIndex).attr('data-text'));
		return nextIndex;
	}
};

$(document).ready(function() { updateForm(true); });
$(document).on('click', '.toggle', function() {
	var nextIndex = updateForm();
	$('.form:visible').animate({ height: 'toggle', 'padding-top': 'toggle', 'padding-bottom': 'toggle', opacity: 'toggle' }, 'slow');
	$($('.form')[nextIndex]).animate({ height: 'toggle', 'padding-top': 'toggle', 'padding-bottom': 'toggle', opacity: 'toggle' }, 'slow');
});

$(document).on('click', '.summary', function() {
	$(this).parent().find('.content').animate({ height: 'toggle', 'padding-top': 'toggle', 'padding-bottom': 'toggle', opacity: 'toggle' }, 'slow');
});

$(document).ready(() => {
	// TODO: Disable submit by enter
	// let step = 1;
	var totalSteps = $('.step-wrapper').length;
	
	const $progressBar = $('#progress-bar');
	const changeProgress = step => {
		const value = step / totalSteps * 100;
		
		$progressBar.css({ width: value + '%' });
		$progressBar.attr({ 'aria-valuenow': value });
		$progressBar.html('Step ' + step + ' / ' + totalSteps);
	}
	changeProgress(1);
	
	const $alert = $('#alert');
	const showError = alert => {
		$alert.html('<b>Error:</b> ' + alert).show();
		return false;
	}
	
	const changeStep = nextStep => {
		const $thisWrapper = $('.step-wrapper:visible');
		
		if(nextStep <= 0) return false;
		if(nextStep > totalSteps) return $('#form').submit();
		if(nextStep > 1 && $('[name="olisc"]').val() == '') return showError('Step 1 error');
		if(nextStep > 2 && $('[name="baseurl"]').val() == '') return showError('Step 2 error');
		if(nextStep > 3 && $('[name="name"]').val() == '') return showError('Step 3 error');
		
		const $nextWrapper = $('.step-wrapper[step="' + nextStep + '"]');
		if(!$nextWrapper.length) return showError('An error occurred!');
		
		if(nextStep == totalSteps) {
			const $dataSummary = $nextWrapper.find('.data-summary');
			$dataSummary.html('');
			
			for(var pair of (new FormData(document.querySelector('#form'))).entries()) {
				var node = $('<tr>');
				node.append($('<td>').html(pair[0]));
				node.append($('<td>').append($('<code>').html(pair[1])));
				$dataSummary.append(node);
			}
		}
		
		$thisWrapper.hide();
		$nextWrapper.show();
		changeProgress(nextStep);
	}

	$(document).on('keydown', '#form', event => {
		if (event.key == 'Enter') {
			event.preventDefault();
			return false;
		}
	});
	
	$(document).on('click', '.step-wrapper [type="submit"]', event => {
		event.preventDefault();
		changeStep(parseInt($(event.target).parents('.step-wrapper').attr('step')) + 1);
	});
	
	$(document).on('click', '[gotoStep]', event => {
		event.preventDefault();
		changeStep(parseInt($(event.target).attr('gotoStep')));
	});
});

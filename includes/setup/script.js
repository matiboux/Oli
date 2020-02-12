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
	
	const stepCheck = {
		1: function() {
			return $('[name="olisc"]').val() != '';
		},
		2: function() {
			return $('[name="baseurl"]').val() != '';
		},
		3: function() {
			if($('[name="use_db"]:checked').val() != 'yes') return true;
			return $('[name="use_db"]:checked').val() != 'yes' || $('[name="db_name"]').val() != '';
		},
		4: function() {
			return $('[name="name"]').val() != '';
		}
	};
	
	const changeStep = nextStep => {
		const $thisWrapper = $('.step-wrapper:visible');
		
		if(nextStep <= 0) return false;
		if(nextStep > totalSteps) return $('#form').submit();
		for (let i = 1; i < nextStep; i++)
			if (!stepCheck[i]()) return showError('Step ' + i + ' error');
		
		// if(nextStep > 1 && $('[name="olisc"]').val() == '') return showError('Step 1 error');
		// if(nextStep > 2 && $('[name="baseurl"]').val() == '') return showError('Step 2 error');
		// if(nextStep > 4 && $('[name="name"]').val() == '') return showError('Step 4 error');
		
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
		$('[gotoStep]').removeClass('btn-primary').addClass('btn-secondary');
		$nextWrapper.show();
		$('[gotoStep="' + nextStep + '"]').removeClass('btn-secondary').addClass('btn-primary');
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
	
	// Step 3
	$(document).on('change', '[name="use_db"]', event => {
		const $mysqlform = $('#mysql-form');
		if (event.target.value == 'yes') $mysqlform.show();
		else $mysqlform.hide();
	});
});

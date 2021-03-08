$(document).ready(() => {
	// Database configuration
	$(document).on('change', '[name="use_db"]', event => {
		const $mysqlform = $('#mysql-form');
		if (event.target.value == 'yes') $mysqlform.show();
		else $mysqlform.hide();
	});
});

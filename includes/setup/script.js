// TODO: Disable submit by enter
var step = 1;
var nextStep = function(next) {
	var error = null;
	var last = 4;
	next = next || step+1;
	
	if(next <= 0 || next > 4) return false;
	else if(next > 1 && document.querySelector('[name="olisc"]').value == '') error = 'Step 1 error';
	else if(next > 2 && document.querySelector('[name="baseurl"]').value == '') error = 'Step 2 error';
	else if(next > 3 && document.querySelector('[name="name"]').value == '') error = 'Step 3 error';
	
	if(error) {
		alert(error);
		return false;
	} else {
		var nextStepElem = document.querySelector('[step="' + next + '"]')
		if(nextStepElem !== null) {
			for(var elem of document.querySelectorAll('.step')) {
				elem.style.display = "none";
			}
			
			if(next == last) {
				nextStepElem.querySelector('.data-summary').innerHTML = '';
				for(var pair of (new FormData(document.querySelector('#form'))).entries()) {
					var node = document.createElement('li');
					node.appendChild(document.createTextNode(pair[0] + ' → "' + pair[1] + '"'));
					document.querySelector('.data-summary').appendChild(node);
				}
			}
			
			document.querySelector('[step="' + next + '"]').style.display = "block";
			step = next;
		} else alert('An error occurred!');
	}
}

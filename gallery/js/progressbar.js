/**
 * Gallery SVN ID:
 * $Id: progressbar.js 16551 2007-06-07 21:35:26Z jenst $
*/

function updateProgressBar(htmlId, status, percentDone) {
	var progressDescription	= document.getElementById('progressDescription_' + htmlId);
	var progressBarDone	= document.getElementById('progressBarDone_' + htmlId);

	if (status != '-1') {
		progressDescription.innerHTML = status;
	}

	if(percentDone >= 0) {
		progressBarDone.style.width = percentDone + "%";
	}
}

function addProgressBarText(htmlId, text) {
	var progressAddText = document.getElementById('progressAddText_' + htmlId);

	progressAddText.innerHTML = progressAddText.innerHTML + text;
}

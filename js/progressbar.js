
function updateProgressBar(htmlId, status, percentDone) {
    var progressBar = document.getElementById(htmlId);
    var progressBarDone = document.getElementById('progressBarDone_' + htmlId);
    var progressDescription = document.getElementById('progressDescription_' + htmlId);

    progressBarDone.style.width = percentDone + "%";
    progressDescription.innerHTML = status;
}
<script type="text/javascript">
<!--

/*
** These values define the margin between your image and the navigation and or a left block.
** Change to your suites.
*/
	var marginLeft = 100;
	var marginTop  = 275;

/* 
** Dont touch
** Here are the dimensions of the original image
*/
	var imagewidth  = <?php echo $imageWidth; ?>;
	var imageheight = <?php echo $imageHeight; ?>;
	var imageratio  = imagewidth/imageheight;

/* 
** Get the window width. NS and IE use different methods 
*/

function windowWidth()
{
 if (window.innerWidth) {
	return window.innerWidth;
 }
 else if (document.body && document.body.offsetWidth) {
	return document.body.offsetWidth;
 }
 else {
	return 0;
 }
}

/* 
** Get the window height. NS and IE use different methods 
*/

function windowHeight()
{
 if (window.innerHeight) {
	return window.innerHeight;
 }
 else if (document.body && document.body.offsetHeight) {
	return document.body.offsetHeight;
 }
 else {
	return 0;
 }
}

/*
** We load this in the header, so the page is not fully rendered.
** save the windowdimensions.
*/
function calculateNewSize(){

    windowWidth = windowWidth();
    windowHeight= windowHeight();

    newwidth = imagewidth;
    newheight = imageheight;

    if ( imagewidth > (windowWidth - marginLeft)) {
	newwidth = windowWidth - marginLeft;
	newheight = newwidth / imageratio;
    }

    if ( newheight > (windowHeight - marginTop)) {
	newheight = windowHeight - marginTop;
	newwidth = newheight * imageratio;
    }

    setReducedSize();
}

function setReducedSize() {
	document.photo_j.height = newheight;
	document.photo_j.width = newwidth;
	if (document.frameRR) {
		document.frameRR.height = newheight;
		document.frameLL.height = newheight;
	}
}

function sizeChange() {

	this.full = false;

	this.toggle = function toggle() {
		if (this.full == true) {
			this.full = false;
			setReducedSize();
		} else {
			document.photo_j.height = imageheight;
			document.photo_j.width = imagewidth;

			this.full = true;
		}
	}
}

sizeChange = new sizeChange();

// -->
</script>

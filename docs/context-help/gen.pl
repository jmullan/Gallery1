if (! -d '../dist/context-help') {
	mkdir '../dist/context-help';
}

opendir (HAND, "./sections");
@files = readdir (HAND);
closedir (HAND);

foreach $file (@files) {
	@arr = split /\./, $file;
	
	if ($arr[1] eq 'xml') {
		system "xsltproc dtds/context-help.xsl sections/" . $file . " > ../dist/context-help/" . $arr[0] . ".php";
	}
}

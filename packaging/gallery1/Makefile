BRANCH = RELEASE_1_4

TARDIR = tardir

CVS = cvs -z3
TARBALL = cat gallery/Version.php | perl -ne 'if (/\$$gallery->version.*"(.*)"/) { ($$x = $$1) =~ s/ //g; print "gallery-$$x.tgz"; }'

package:
	echo NOTE: CVSROOT needs to be set for this script to run.
	mkdir -p ${TARDIR}
	cd ${TARDIR}; ${CVS} checkout -r ${BRANCH} gallery
	cd ${TARDIR}; ${CVS} checkout -r ${BRANCH} docs
	cd ${TARDIR}/docs; ./gen g1package
	cd ${TARDIR}/gallery ; ${CVS} update -r ${BRANCH}
	cd ${TARDIR}; ${CVS} -n update gallery 2>&1 | egrep '^\?' | perl -pe 's/^..//' > /tmp/make.gallery.exclude
	mv ${TARDIR}/docs/dist/g1package ${TARDIR}/gallery/docs
	cd ${TARDIR}; tar --exclude-from /tmp/make.gallery.exclude \
	    --exclude 'CVS' \
	    --exclude '*.#*' \
	    --exclude '*~' \
	    --gzip -cvf ../`${TARBALL}`  gallery
	rm /tmp/make.gallery.exclude

export:
	echo ncftpput -u anonymous -p gallery@ upload.sourceforge.net /incoming `cd ${TARDIR};${TARBALL}`

clean:
	rm -fr ${TARDIR} *.tgz


JAVAC = javac
JAR = jar
JAVA_VERSION = $(shell java -version 2>&1)
EXPECTED_JAVA_VERSION = java version "1.1.8"

dummy: check-jdk118 ImageTools.jar

ImageTools.jar: ImageCrop.class
	${JAR} cvf ImageTools.jar *.class

ImageCrop.class: ImageCrop.java
	${JAVAC} ImageCrop.java

clean:
	rm -rf *.class
	rm -rf *.jar

install: ImageTools.jar
	cp ImageTools.jar ../../gallery/java/ImageTools.jar

check-jdk118:
	@if [ "${JAVA_VERSION}" != "${EXPECTED_JAVA_VERSION}" ]; then \
		echo "We require ${EXPECTED_JAVA_VERSION} for browser compatibility"; \
		exit 1; \
	fi
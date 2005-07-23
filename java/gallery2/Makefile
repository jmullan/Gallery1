JAVA_HOME = /usr/local/jdk1.1.8
JAVAC ?= ${JAVA_HOME}/bin/javac
JAVA ?= ${JAVA_HOME}/bin/java
JAR ?= ${JAVA_HOME}/bin/jar
JAVA_VERSION = $(shell JAVA_HOME=${JAVA_HOME} ${JAVA} -version 2>&1)
EXPECTED_JAVA_VERSION = java version "1.1.8"

all: check-jdk118 build/classes build/ImageCrop.jar

build/classes:
	mkdir -p build/classes

build/ImageCrop.jar: ImageCrop.class
	(cd build/classes && JAVA_HOME=${JAVA_HOME} ${JAR} cvf ../ImageCrop.jar *)

ImageCrop.class: ImageCrop.java
	JAVA_HOME=${JAVA_HOME} ${JAVAC} -d build/classes ImageCrop.java

clean:
	rm -rf build

# Bharat specific install path
#
install: build/ImageCrop.jar
	cp build/ImageCrop.jar /usr/www/website/menalto.com/dev/gallery/gallery/modules/core/plugins

check-jdk118:
	echo a: ${JAVA_VERSION}
	echo b: ${EXPECTED_JAVA_VERSION}
	@if [ "${JAVA_VERSION}" != "${EXPECTED_JAVA_VERSION}" ]; then \
		echo "We require ${EXPECTED_JAVA_VERSION} for browser compatibility"; \
		exit 1; \
	fi

.PHONY: check-jdk118

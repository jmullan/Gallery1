JAVA_HOME ?= /usr/local/jdk1.1.8
JAVAC ?= ${JAVA_HOME}/bin/javac
JAVA ?= ${JAVA_HOME}/bin/java
JAR ?= ${JAVA_HOME}/bin/jar
JAVA_VERSION = `JAVA_HOME=${JAVA_HOME} ${JAVA} -version 2>&1`
EXPECTED_JAVA_VERSION = java version "1.1.8"

all: check-jdk118 build/Metamorphose.jar

build/classes:
	mkdir -p build/classes/META-INF

build/Metamorphose.jar: Metamorphose.class build/classes/META-INF/Copyright
	(cd build/classes && JAVA_HOME=${JAVA_HOME} ${JAR} cvf ../Metamorphose.jar META-INF/Copyright *.class)

build/classes/META-INF/Copyright:
	cp -p Copyright build/classes/META-INF

Metamorphose.class: build/classes Metamorphose.java Panorama.java Picture.java Scroller.java
	JAVA_HOME=${JAVA_HOME} ${JAVAC} -d build/classes *.java

clean:
	rm -rf build

install: build/Metamorphose.jar
	@if [ -n "${GALLERY2_DIR}" ]; then \
		cp build/Metamorphose.jar ${GALLERY_DIR}/modules/panorama/java; \
	fi

check-jdk118:
	@echo a: ${JAVA_HOME}
	@echo b: ${JAVA_VERSION}
	@echo c: ${EXPECTED_JAVA_VERSION}
	@if [ "${JAVA_VERSION}" != "${EXPECTED_JAVA_VERSION}" ]; then \
		echo "We require ${EXPECTED_JAVA_VERSION} for browser compatibility"; \
		exit 1; \
	fi

.PHONY: check-jdk118

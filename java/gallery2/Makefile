JAVAC = javac
JAR = jar

all: build/classes build/ImageCrop.jar

build/classes:
	mkdir -p build/classes

build/ImageCrop.jar: ImageCrop.class
	(cd build/classes && ${JAR} cvf ../ImageCrop.jar *)

ImageCrop.class: ImageCrop.java
	${JAVAC} -d build/classes ImageCrop.java

clean:
	rm -rf build

# Bharat specific install path
#
install: build/ImageCrop.jar
	cp build/ImageCrop.jar /usr/www/website/menalto.com/dev/gallery/gallery/modules/core/plugins


JAVAC = javac
JAR = jar


dummy: ImageTools.jar

ImageTools.jar: ImageCrop.class
	${JAR} cvf ImageTools.jar *.class

ImageCrop.class: ImageCrop.java
	${JAVAC} ImageCrop.java

clean:
	rm -rf *.class
	rm -rf *.jar

install:
	@echo "# nothing to install"

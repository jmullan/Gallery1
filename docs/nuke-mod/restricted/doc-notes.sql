create table gallery_doc_notes (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	sect VARCHAR(80) NOT NULL,
	user VARCHAR(80) NOT NULL,
	note TEXT NOT NULL,
	ts INT NOT NULL,
	status VARCHAR(16)
);
	

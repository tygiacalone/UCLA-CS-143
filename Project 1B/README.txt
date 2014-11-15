Name: Nathan Tung
ID: 004059195
Email: nathanctung@ucla.edu
I am not working with anyone at the moment. 

Project 1B
--------------------
Primary key constraints:
	Movie ID
	Actor ID
	Director ID
Referential integrity constraints:
	MovieGenre has foreign key mid from Movie
	MovieDirector has foreign key mid from Movie, 
		did from Director
	MovieActor has foreign key mid from Movie, 
		aid from Actor
	Review has foreign key mid from Movie
CHECK constraints:
	Movie rating must be valid: G, PG, PG-13, R, or NC-17
	Movie year must be a 4-digit number (1000 to 9999)
		This should be narrowed down in the future
	Review rating must be from 0 to 10
--------------------
Actual constraint errors via TEE:
	mysql> source www/violate.sql
	ERROR 1062 (23000): Duplicate entry '2' for key 1
	ERROR 1062 (23000): Duplicate entry '1' for key 1
	ERROR 1062 (23000): Duplicate entry '16' for key 1
	ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails (`CS143/MovieGenre`, CONSTRAINT `MovieGenre_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))
	ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails (`CS143/MovieDirector`, CONSTRAINT `MovieDirector_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))
	ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails (`CS143/MovieDirector`, CONSTRAINT `MovieDirector_ibfk_2` FOREIGN KEY (`did`) REFERENCES `Director` (`id`))
	ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails (`CS143/MovieActor`, CONSTRAINT `MovieActor_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))
	ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails (`CS143/MovieActor`, CONSTRAINT `MovieActor_ibfk_2` FOREIGN KEY (`aid`) REFERENCES `Actor` (`id`))
	ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails (`CS143/Review`, CONSTRAINT `Review_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))
	mysql> notee;
--------------------
Everything else in the project should be commented or basically self-explanatory.
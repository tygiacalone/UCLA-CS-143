--Constraint: set Movie id to unique primary key
--A movie with id=2 already exists
INSERT INTO Movie VALUES (2,'Test',2014,'PG-13','None');
--ERROR 1062 (23000): Duplicate entry '2' for key 1


--Constraint: check that the rating is valid
--CHECKS ARE NOT TESTED, but rating should not be 'Blah'
INSERT INTO Movie VALUES (15000,'Test',2014,'Blah','None');


--Constraint: check that the year is a 4-digit number
--CHECKS ARE NOT TESTED, but year -150 is not within 1000-9999
INSERT INTO Movie VALUES (15001,'Test',-150,5,'None');


--Constraint: set Actor id to unique primary key
--An actor with id=1 already exists
INSERT INTO Actor VALUES (1,'Last','First','Female',1999-01-01,1999-02-02);
--ERROR 1062 (23000): Duplicate entry '1' for key 1


--Constraint: set Director id to unique primary key
--A director with id=16 already exists
INSERT INTO Director VALUES (16,'Last','First',1999-01-01,1999-02-02);
--ERROR 1062 (23000): Duplicate entry '16' for key 1


--Constraint: link MovieGenre mid to the foreign key id in Movie
--There is no id=15000 in Movie
INSERT INTO MovieGenre VALUES (15000,'Drama');
--ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails (`CS143/MovieDirector`, CONSTRAINT `MovieDirector_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))


--Constraint: link MovieDirector mid to the foreign key id in Movie
--There is no id=15000 in Movie
INSERT INTO MovieDirector VALUES (15000,112);
--ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails (`CS143/MovieDirector`, CONSTRAINT `MovieDirector_ibfk_2` FOREIGN KEY (`did`) REFERENCES `Director` (`id`))


--Constraint: link MovieDirector did to the foreign key id in Director
--There is no id=5 in Director
INSERT INTO MovieDirector VALUES (3,5);
--ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails (`CS143/MovieActor`, CONSTRAINT `MovieActor_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))


--Constraint: link MovieActor mid to the foreign key id in Movie
--There is no id=15000 in Movie
INSERT INTO MovieActor VALUES (15000,10208,'RoleTest');
--ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails (`CS143/MovieActor`, CONSTRAINT `MovieActor_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))


--Constraint: link MovieActor aid to the foreign key id in Actor
--There is no id=2 in Actor
INSERT INTO MovieActor VALUES (3,2,'RoleTest');
--ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails (`CS143/MovieActor`, CONSTRAINT `MovieActor_ibfk_2` FOREIGN KEY (`aid`) REFERENCES `Actor` (`id`))


--Constraint: link Review mid to the foreign key id in Movie
--There is no id=15000 in Movie
INSERT INTO Review VALUES ('Name','12-01-2012 00:00:00',15000,5,'No comment');
--ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails (`CS143/Review`, CONSTRAINT `Review_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))


--Constraint: check that the rating is between 0 and 10
--CHECKS ARE NOT TESTED, but the rating is negative and not within 0 and 10
INSERT INTO Review VALUES ('Name','12-01-2012 00:00:00',2,-1,'No comment');
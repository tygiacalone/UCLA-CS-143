--set Movie id to unique primary key
--check that the rating is valid
--check that the year is a 4-digit number
CREATE TABLE Movie(
	id int NOT NULL,
	title varchar(100) NOT NULL,
	year int NOT NULL,
	rating varchar(10),
	company varchar(50),
	PRIMARY KEY (id),
	CHECK(rating IS NULL OR rating='G' OR rating='PG' OR rating='PG-13' OR rating='R' OR rating='NC-17'),
	CHECK(year > 999 AND year < 10000)
) ENGINE=INNODB;

--set Actor id to unique primary key
CREATE TABLE Actor(
	id int NOT NULL,
	last varchar(20),
	first varchar(20),
	sex varchar(6),
	dob date NOT NULL,
	dod date,
	PRIMARY KEY (id)
) ENGINE=INNODB;

--set Director id to unique primary key
CREATE TABLE Director(
	id int NOT NULL,
	last varchar(20),
	first varchar(20),
	dob date NOT NULL,
	dod date,
	PRIMARY KEY (id)
) ENGINE=INNODB;

--link MovieGenre mid to the foreign key id in Movie
CREATE TABLE MovieGenre(
	mid int NOT NULL,
	genre varchar(20) NOT NULL,
	FOREIGN KEY (mid) references Movie(id)
) ENGINE=INNODB;

--link MovieDirector mid to the foreign key id in Movie
--link MovieDirector did to the foreign key id in Director
CREATE TABLE MovieDirector(
	mid int NOT NULL,
	did int NOT NULL,
	FOREIGN KEY (mid) references Movie(id),
	FOREIGN KEY (did) references Director(id)
) ENGINE=INNODB;

--link MovieActor mid to the foreign key id in Movie
--link MovieActor aid to the foreign key id in Actor
CREATE TABLE MovieActor(
	mid int NOT NULL,
	aid int NOT NULL,
	role varchar(50),
	FOREIGN KEY (mid) references Movie(id),
	FOREIGN KEY (aid) references Actor(id)
) ENGINE=INNODB;

--link Review mid to the foreign key id in Movie
--check that the rating is between 0 and 10
CREATE TABLE Review(
	name varchar(20),
	time timestamp,
	mid int NOT NULL,
	rating int NOT NULL,
	comment varchar(500),
	FOREIGN KEY (mid) references Movie(id),
	CHECK(rating >= 0 AND rating <= 10)
) ENGINE=INNODB;

CREATE TABLE MaxPersonID(
	id int NOT NULL
) ENGINE=INNODB;

CREATE TABLE MaxMovieID(
	id int NOT NULL
) ENGINE=INNODB;
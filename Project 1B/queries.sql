--display first/last concatenated names of all 'Die Another Day' movie actors
SELECT CONCAT(a.first,' ',a.last)
FROM Actor a, Movie m, MovieActor ma
WHERE m.title='Die Another Day' AND m.id=ma.mid AND ma.aid=a.id;

--count total number of actors (using distinct) who are in multiple movies
SELECT COUNT(DISTINCT ma.aid)
FROM MovieActor ma, MovieActor ma2
WHERE ma.mid<>ma2.mid AND ma.aid=ma2.aid;

--count total number of distinct actors who are in at least two movies from the same director
SELECT COUNT(DISTINCT ma.aid)
FROM MovieActor ma, MovieActor ma2, MovieDirector md, MovieDirector md2
WHERE ma.mid<>ma2.mid AND ma.aid=ma2.aid AND md.did=md2.did AND md.mid=ma.mid AND md2.mid=ma2.mid;

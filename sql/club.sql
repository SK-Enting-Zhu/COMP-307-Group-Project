-- Yvan Yin - 261238020
-- extra columns of the clubs table for the club page (About tab) and Create a Club
-- run shared.sql before this one (same database)
-- run it only once - running it again gives a "duplicate column" error

-- category = one of the interests (Coding, Music, ...) so For You can match it
-- requirement = what you need to join, like "audition" - empty means none
-- contact = email to reach the club - empty means no Contact button
-- founded = the day the club was created on McClub
ALTER TABLE clubs
	ADD category VARCHAR(50) NOT NULL DEFAULT '',
	ADD requirement VARCHAR(200) NOT NULL DEFAULT '',
	ADD contact VARCHAR(100) NOT NULL DEFAULT '',
	ADD founded DATE;

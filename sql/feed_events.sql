-- Yu Fei Dong - 261239680
-- tables for the Feed and Events tabs of a club page
-- run shared.sql before this one (same database)

CREATE TABLE IF NOT EXISTS announcements (
	id INT AUTO_INCREMENT PRIMARY KEY,
	club_id INT NOT NULL,
	user_id INT NOT NULL,
	content TEXT NOT NULL,
	created DATETIME NOT NULL,
	FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4;

-- deleting an announcement also delete its comments (CASCADE)
CREATE TABLE IF NOT EXISTS comments (
	id INT AUTO_INCREMENT PRIMARY KEY,
	announcement_id INT NOT NULL,
	user_id INT NOT NULL,
	content TEXT NOT NULL,
	created DATETIME NOT NULL,
	FOREIGN KEY (announcement_id) REFERENCES announcements(id) ON DELETE CASCADE,
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4;

-- place = the meeting point typed by the exec
-- lat and lng = its position on the map
-- they stay empty when Google didnt find the place
CREATE TABLE IF NOT EXISTS events (
	id INT AUTO_INCREMENT PRIMARY KEY,
	club_id INT NOT NULL,
	title VARCHAR(100) NOT NULL,
	event_time DATETIME NOT NULL,
	place VARCHAR(200) NOT NULL,
	description TEXT NOT NULL,
	lat DOUBLE,
	lng DOUBLE,
	FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4;

-- one row = this user is interested in going to this event
CREATE TABLE IF NOT EXISTS rsvps (
	event_id INT NOT NULL,
	user_id INT NOT NULL,
	PRIMARY KEY (event_id, user_id),
	FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4;

-- tables that more than one part of the site use
-- run this file first then the other .sql files
-- pick the database before running it
-- mcclub on XAMPP - comp-307-db on the school server

-- ticket = the random string given at login
-- its empty when logged out
CREATE TABLE IF NOT EXISTS users (
	id INT AUTO_INCREMENT PRIMARY KEY,
	full_name VARCHAR(100) NOT NULL,
	username VARCHAR(50) NOT NULL UNIQUE,
	email VARCHAR(100) NOT NULL UNIQUE,
	password VARCHAR(255) NOT NULL,
	tags VARCHAR(255),
	bio TEXT,
	photo VARCHAR(255),
	ticket VARCHAR(40)
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS clubs (
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100) NOT NULL,
	description TEXT,
	tags VARCHAR(255),
	image VARCHAR(255),
	president_id INT NOT NULL,
	FOREIGN KEY (president_id) REFERENCES users(id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4;

-- one row per user in a club
-- role is 'member' or 'exec'
CREATE TABLE IF NOT EXISTS members (
	club_id INT NOT NULL,
	user_id INT NOT NULL,
	role VARCHAR(20) NOT NULL DEFAULT 'member',
	joined DATE NOT NULL,
	PRIMARY KEY (club_id, user_id),
	FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4;

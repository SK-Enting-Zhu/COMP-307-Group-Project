# McClub

COMP 307 project, Group 12. Club discovery site for McGill students.

Site: https://fall2026-comp307-group12.cs.mcgill.ca/ (only works on the McGill VPN)

I started my part (feed and events) before anything else was pushed, so I had to make a few shared things just to get it running. I'm listing them here so we're on the same page. None of it is final, if you already did something differently tell me and I'll switch to yours.

## Folders

- html pages are at the root
- `css/` the styles, `style.css` is the common one (nav bar, tabs, buttons)
- `js/` the scripts, `common.js` is the common one
- `php/` the scripts the pages call, they only send back JSON
- `sql/` the tables

## Database

It's MySQL. Locally (XAMPP) make a database called `mcclub` in phpMyAdmin, then import `sql/shared.sql` and after that `sql/feed_events.sql`.

`php/db.php` does the connection, so in your php just put `require "db.php";` at the top and use `$conn`. It has the XAMPP login (root, no password). On the school server it picks up the real login from a file that isn't in the repo, so nobody has to edit `db.php` over there and the password stays off github.

The tables I figured we all need are in `shared.sql`. I only put the columns I saw in the wireframe, so add yours:

- `users`: id, full_name, username, email, password, bio, photo, ticket
- `clubs`: id, name, description, image, president_id
- `members`: club_id, user_id, role, joined

There are two roles, `member` and `exec`. The president is the user in `clubs.president_id` and they're also an `exec` in `members`, so whoever creates a club should get added to `members` as `exec`.

## Login

I went with the ticket method from class. Logging in saves a random ticket in `users.ticket`, the browser keeps it in localStorage under `ticket`, and it gets sent with every request.

- `php/check.php` has `getUser($conn)` which gives you the logged in user (or stops with a "login" error), and `getRole($conn, $userId, $clubId)` which gives `member`, `exec`, or an empty string if they're not in the club.
- in `js/common.js`, `send(url, data, done)` does the POST and adds the ticket for you. If the user isn't logged in it sends them to `login.html`.

## Page names

My links assume these names, so let's either use them or tell me what yours are:

`login.html`, `foryou.html`, `myclubs.html`, `createclub.html`, `profile.html`, and for a club `about.html`, `feed.html`, `events.html`, `members.html`.

The club pages take the club id in the URL, like `feed.html?club=3`.

## Feed and events (Yu Fei)

These are `feed.html` and `events.html`. For now each one has its own copy of the nav bar and the tabs, plus an empty `<div id="clubHeader">` where the club header is supposed to go.

Execs can post, edit and delete announcements and events. Everyone in the club can comment and RSVP. The Google Maps part (our external API) is on the events tab, and the key is at the top of `js/events.js`.

## School server

It runs nginx instead of Apache, but nothing really changes for us. Our files go in `~/app` and the database there is called `comp-307-db`. One thing to watch: don't start anything on port 3000, the site stops showing if something is running there.

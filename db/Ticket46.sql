ALTER TABLE trv_users ADD usertoken VARCHAR(32) NOT NULL AFTER password, ADD fortgotpassword_duration DATETIME NOT NULL AFTER usertoken;


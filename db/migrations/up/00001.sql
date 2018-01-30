-- Custom Property
ALTER TABLE users_property DROP FOREIGN KEY fk_custom_user;
ALTER TABLE users_property CHANGE `userid` `userid` binary(16) NOT NULL;

-- User Property
ALTER TABLE users CHANGE `userid` `userid` binary(16) NOT NULL;
ALTER TABLE users ADD COLUMN `uuid` varchar(36) GENERATED ALWAYS AS (insert(insert(insert(insert(hex(`userid`),9,0,'-'),14,0,'-'),19,0,'-'),24,0,'-')) VIRTUAL;
UPDATE users SET userid = (unhex(replace(uuid(),'-','')));

-- Constraint
ALTER TABLE users_property
  ADD CONSTRAINT fk_custom_user
  FOREIGN KEY (userid)
  REFERENCES users(userid);

-- Index
ALTER TABLE `users`
  ADD INDEX `ix_users_email` (`email` ASC, `password` ASC),
  ADD INDEX `ix_users_username` (`username` ASC, `password` ASC);

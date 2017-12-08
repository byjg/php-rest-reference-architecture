-- Custom Property
ALTER TABLE users_property DROP FOREIGN KEY fk_custom_user;
ALTER TABLE users_property CHANGE `userid` `userid` INTEGER NOT NULL;

-- User Property
ALTER TABLE users DROP COLUMN `uuid`;
select @i := 0;
update users set userid = (select @i := @i + 1);
ALTER TABLE users CHANGE `userid` `userid` INTEGER AUTO_INCREMENT NOT NULL;

-- Constraint
ALTER TABLE users_property
  ADD CONSTRAINT fk_custom_user
  FOREIGN KEY (userid)
  REFERENCES users(userid);

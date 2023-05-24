-- This table are using by the component byjg/authuser

create table users
(
    userid binary(16) DEFAULT (uuid_to_bin(uuid())) NOT NULL,
    `uuid` varchar(36) GENERATED ALWAYS AS (insert(insert(insert(insert(hex(`userid`),9,0,'-'),14,0,'-'),19,0,'-'),24,0,'-')) VIRTUAL,
    name varchar(50),
    email varchar(120),
    username varchar(20) not null,
    password char(40) not null,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    admin enum('yes','no'),

    constraint pk_users primary key (userid)
) ENGINE=InnoDB;


-- Index
ALTER TABLE `users`
  ADD INDEX `ix_users_email` (`email` ASC, `password` ASC),
  ADD INDEX `ix_users_username` (`username` ASC, `password` ASC);

-- Default Password is "pwd"
-- Please change it!
insert into users (name, email, username, password, admin) VALUES
  ('Administrator', 'admin@example.com', 'admin', '9800aa1b77334ff0952b203062f0fbb0c480d3de', 'yes');   -- !P4ssw0rdstr!

insert into users (userid, name, email, username, password, admin) VALUES
  (0x5f6e7fe7bd1b11ed8ca90242ac120002, 'Regular User', 'user@example.com', 'user', '9800aa1b77334ff0952b203062f0fbb0c480d3de', 'no')        -- !P4ssw0rdstr!
;

-- random binary(16) generator
-- select hex(uuid_to_bin(uuid()));


-- This table are using by the component byjg/authuser
create table users_property
(
   id integer AUTO_INCREMENT not null,
   name varchar(20),
   value varchar(100),
   userid binary(16) NOT NULL,

   constraint pk_custom primary key (id),
   constraint fk_custom_user foreign key (userid) references users (userid)
) ENGINE=InnoDB;

insert into users_property (name, value, userid) values 
  ('picture', 'https://www.gravatar.com/avatar/9f4d313491a7df705b7071c228fc79cd', 0x5f6e7fe7bd1b11ed8ca90242ac120002);

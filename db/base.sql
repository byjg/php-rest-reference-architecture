-- This table are using by the component byjg/authuser
create table users
(
    userid integer AUTO_INCREMENT not null,
    name varchar(50),
    email varchar(120),
    username varchar(15) not null,
    password char(40) not null,
    created datetime,
    admin enum('yes','no'),

    constraint pk_users primary key (userid)
) ENGINE=InnoDB;

-- Default Password is "pwd"
-- Please change it!
insert into users (name, email, username, password, admin) VALUES
  ('Administrator', 'admin@example.com', 'admin', '37fa265330ad83eaa879efb1e2db6380896cf639', 'yes'),
  ('Regular User', 'user@example.com', 'user', '37fa265330ad83eaa879efb1e2db6380896cf639', 'no')
;

-- This table are using by the component byjg/authuser
create table users_property
(
   customid integer AUTO_INCREMENT not null,
   name varchar(20),
   value varchar(100),
   userid integer not null,

   constraint pk_custom primary key (customid),
   constraint fk_custom_user foreign key (userid) references users (userid)
) ENGINE=InnoDB;

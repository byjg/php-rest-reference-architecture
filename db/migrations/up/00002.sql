-- Migration 1 --> 2 can be removed
-- Just for demo

create table dummy (
  id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
  field varchar(10)
);

insert into dummy (field) values ('fld value');
insert into dummy (field) values ('Test 1');
insert into dummy (field) values ('Test 2');



-- Migration 1 --> 2 can be removed
-- Just for demo

create table dummy (
  id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
  field varchar(10) not null
);

create index ix_field on dummy(field);

insert into dummy (field) values ('fld value');
insert into dummy (field) values ('Test 1');
insert into dummy (field) values ('Test 2');

create table dummyhex (
  id binary(16) DEFAULT (uuid_to_bin(uuid())) PRIMARY KEY NOT NULL,
  `uuid` varchar(36) GENERATED ALWAYS AS (insert(insert(insert(insert(hex(`id`),9,0,'-'),14,0,'-'),19,0,'-'),24,0,'-')) VIRTUAL,
  field varchar(10) not null
);

insert into dummyhex (field) values ('fld value');
insert into dummyhex (id, field) values (X'11111111222233334444555555555555', 'Test 1');
insert into dummyhex (field) values ('Test 2');


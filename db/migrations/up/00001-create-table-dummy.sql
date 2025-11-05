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


-- Example table using ActiveRecord pattern
create table dummy_active_record
(
    id int auto_increment not null,
    name varchar(50) not null,
    value varchar(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    constraint pk_dummy_active_record primary key (id)
) ENGINE=InnoDB;

create index ix_name on dummy_active_record(name);

-- Sample data
insert into dummy_active_record (name, value) VALUES
  ('Sample 1', 'Value 1'),
  ('Sample 2', 'Value 2');

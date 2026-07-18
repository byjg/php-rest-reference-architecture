-- Example schema: Project (1) --> Task (N) --> Note (N)
-- Demonstrates the three supported patterns. Remove with install_examples=false.

-- Repository pattern, integer auto-increment primary key
create table project (
    id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
    name varchar(100) not null,
    description varchar(255) null
) ENGINE=InnoDB;

create index ix_project_name on project(name);

insert into project (name, description) values ('Sample Project', 'A demo project');
insert into project (name, description) values ('Website Redesign', 'Q3 marketing site');

-- Repository pattern, UUID (binary) primary key with a virtual formatted uuid column
create table task (
    id binary(16) DEFAULT (uuid_to_bin(uuid())) PRIMARY KEY NOT NULL,
    `uuid` varchar(36) GENERATED ALWAYS AS (insert(insert(insert(insert(hex(`id`),9,0,'-'),14,0,'-'),19,0,'-'),24,0,'-')) VIRTUAL,
    project_id INTEGER not null,
    title varchar(150) not null,
    status varchar(20) not null default 'open',
    constraint fk_task_project foreign key (project_id) references project(id)
) ENGINE=InnoDB;

insert into task (project_id, title, status) values (1, 'Set up repository', 'done');
insert into task (project_id, title, status) values (1, 'Write first endpoint', 'open');

-- ActiveRecord pattern, attached to a task by its uuid (soft reference)
create table note (
    id int auto_increment not null,
    task_uuid varchar(36) null,
    body varchar(500) not null,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    constraint pk_note primary key (id)
) ENGINE=InnoDB;

create index ix_note_task on note(task_uuid);

insert into note (task_uuid, body) values (null, 'First note — welcome to Gluo');

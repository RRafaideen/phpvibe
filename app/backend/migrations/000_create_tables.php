<?php return new class { 
        public function up($pdo) {
            $pdo->exec(<<<SQL
                PRAGMA foreign_keys = ON;
                create table if not exists user_role (
                    name        string      primary key,
                    created_at  timestamp   default current_timestamp
                );
                insert into user_role (name) values ('ROLE_USER');
                create table if not exists users (
                    user_id     integer      primary key autoincrement,
                    first_name  varchar(512) default null,
                    last_name   varchar(512) default null,
                    email       varchar(256) not null,
                    password    text         default null,
                    role        text         default 'ROLE_USER',
                    created_at  timestamp    default current_timestamp,
                    updated_at  timestamp    default null,
                    unique (email),
                    foreign key (role) references user_role (name)
                );       
            SQL);
        }
    }
?>
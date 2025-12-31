<?php namespace Main;

    function applyMigrations($pdo): void { 
        $APP_ROOT = realpath(dirname(dirname(__FILE__))) . "/migrations";
        $files = scandir($APP_ROOT, SCANDIR_SORT_ASCENDING);
        $pdo->exec(<<<SQL
                create table if not exists migrations (
                    name         text primary key,
                    started_at   timestamp default null,
                    completed_at timestamp default null
                ); 
                SQL);
        $results = $pdo->query("select name from migrations where completed_at is not null");
        $names = array_map(fn($x) => $x["name"], $results->fetchAll());
        foreach ($files as $file) {
            if($file == "." || $file == ".." || in_array($file, $names)) continue;
            $migration = require("{$APP_ROOT}/{$file}");
            $pdo->prepare("insert into migrations (name, started_at) values (?, ?)")
                ->execute([$file, time()]);
            try {
                if(property_exists($migration, "up")) ($migration->up)($pdo);
                if(property_exists($migration, "down")) ($migration->down)($pdo);
                $pdo->prepare("update migrations set completed_at = ? where name = ?")
                    ->execute([time(), $file]);
            } catch(err) {
                throw err;
            }
        }
    }



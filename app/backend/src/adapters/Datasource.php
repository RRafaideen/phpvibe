<?php namespace Main; 

    use PDO;
    use PDOStatement;

    interface Datasource { 
        function exec(string $query, array $bindings): void;
        function query(string $query, array $bindings): array;
    }

    class SQLDS implements Datasource {
        private readonly PDO $pdo;
        public function __construct(PDO $pdo) {
            $this->pdo = $pdo;
        }

        public function exec(string $query, array $bindings = []): void {
            $stmt = $this->statement($query);
            $stmt->execute($bindings ?? []);
        }
        
        public function query(string $query, array $bindings = []): array {
            $stmt = $this->statement($query);
            $stmt->execute($bindings ?? []);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        }

        private function statement(string $query): PDOStatement {
            return $this->pdo->prepare($query);
        }
    }
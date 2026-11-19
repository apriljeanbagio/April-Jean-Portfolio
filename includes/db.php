
<?php

$websiteHost = strtolower($_SERVER["HTTP_HOST"] ?? "");

$isProduction = in_array($websiteHost, [
    "apriljeanportfolio.free.nf",
    "www.apriljeanportfolio.free.nf"
], true);

if ($isProduction) {

    require_once __DIR__ . "/db.production.php";

} else {

    $host = getenv("DB_HOST") ?: "localhost";
    $dbname = getenv("DB_NAME") ?: "april_jean_portfolio";
    $username = getenv("DB_USER") ?: "root";
    $password = getenv("DB_PASS") ?: "";

    try {

        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );

    } catch (PDOException $e) {

        error_log($e->getMessage());

        http_response_code(500);

        die("Database connection failed. Please check your configuration.");

    }

}

?>

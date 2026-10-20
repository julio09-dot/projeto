<?php
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/csrf.php";
require_once __DIR__ . "/../includes/flash.php";
require_once __DIR__ . "/../database/connect.php";

if (!isset($_SESSION["id"]) || ($_SESSION["tipo"] ?? "") !== "admin") {
    redirect("login/login.php");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect("login/listar_logins.php");
}

csrf_verify();

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

// Impede que o admin exclua a própria conta
if (!$id || $id === (int)$_SESSION["id"]) {
    redirect("login/listar_logins.php");
}

try {
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
    $stmt->execute(["id" => $id]);

    if ($stmt->rowCount() > 0) {
        flash_set("success", "Usuário excluído com sucesso.");
    } else {
        flash_set("danger", "Usuário não encontrado.");
    }
} catch (PDOException $e) {
    flash_set("danger", "Não foi possível excluir o usuário.");
}

redirect("login/listar_logins.php");

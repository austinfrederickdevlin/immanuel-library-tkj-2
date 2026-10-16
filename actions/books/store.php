<?php

require_once __DIR__ . "/../../config/database.php";

if (isset($_POST['store']) && $_SERVER['REQUEST_METHOD'] === "POST") {
try {
        $pdo->beginTransaction();

        $title = htmlspecialchars($_POST["title"]);
        $isbn = htmlspecialchars($_POST["isbn"]);
        $year = htmlspecialchars($_POST["year"]);
        $stock = htmlspecialchars($_POST["stock"]);
        $description = htmlspecialchars($_POST["description"]);
        $categoryId = htmlspecialchars($_POST["category_id"]);

        $query = "insert into books (title, isbn, year, stock, category_id, description) 
        values (:title, :isbn, :year, :stock, :category_id, :description)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':title' => $title, 
            ':isbn' => $isbn,
            ':year' => $year,
            ':stock' => $stock,
            ':category_id' => $categoryId,
            ':description' => $description
        ]);

        $bookId = $pdo->lastInsertId();

        $query = "insert into book_authors (book_id, author_id) values (:book_id, :author_id)";
        $stmt = $pdo->prepare($query);

        $authorIds = $_POST['author_ids'];
        foreach ($authorIds as $authorId) {
            $stmt->execute([
                ":book_id" => $bookId,
                ":author_id" => $authorId
            ]);
        }
        

        $pdo->commit();

        header("Location: ../../pages/books/index.php");
        exit;



    } catch (PDOException $e) {
        $pdo->rollback();
        throw $e;
    }
}
?>
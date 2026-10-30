<?php
require_once __DIR__ . "/../../config/database.php";
    session_start();
    require_once __DIR__ . "/../../config/database.php";
    require_once __DIR__ . "/../../validations/book-validation.php";
if (isset($_POST['update']) && $_SERVER['REQUEST_METHOD'] === "POST") {
    try {
        $pdo->beginTransaction();

        $id = htmlspecialchars($_POST['id']);
        $title = htmlspecialchars($_POST['title']);
        $isbn = htmlspecialchars($_POST['isbn']);
        $year = htmlspecialchars($_POST['year']);
        $stock = htmlspecialchars($_POST['stock']);
        $categoryId = htmlspecialchars($_POST['category_id']);
        $description = htmlspecialchars($_POST['description']);
        $request = getBookRequest($_POST);
        $errors = validateBook($request, $request['id']);     
        if (count($errors) > 0) {
            $_SESSION["errors"] = $errors;
            $_SESSION["old"] = $request;
            header('location: ../../pages/books/edit.php?id='. urlencode($request['id']));
            exit;
        }

        $query = "update books set title = :title, isbn = :isbn, 
                  year = :year, stock = :stock, category_id = :category_id,
                  description = :description where id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([      
            'title' => $request['title'],
            'isbn' => $request['isbn'],
            'year' => $request['year'],
            'stock' => $request['stock'],
            'category_id' => $request['category_id'],
            'description' => $request['description'],
            'id' => $request['id']            
        ]);

        $query = "delete from book_author where book_id = :book_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['book_id' => $id]);
        $stmt->execute(['book_id' => $request['id']]);

        $query = 'insert into book_author (book_id, author_id) values (:book_id, :author_id)';
        $stmt = $pdo->prepare($query);
        $authorIds = isset($_POST['author_ids']) ? $_POST['author_ids'] : [];
        $authorIds = isset($request['author_ids']) ? $request['author_ids'] : [];
        foreach ($authorIds as $authorId) {
            $stmt->execute([
                'book_id' => $id,
                'book_id' => $request['id'],
                'author_id' => htmlspecialchars($authorId)
            ]);
        }
        $pdo->commit();
        header("Location: ../../pages/books/index.php");

    } catch (PDOException $e) {
        $pdo->rollback();
        throw $e;
    }
    print_r($_POST);
}

?>
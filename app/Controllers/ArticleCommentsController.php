<?php

namespace App\Controllers;

use App\ConnectToDB;
use App\Models\Article;
use App\Redirect;
use App\View;
use PDO;

class ArticleCommentsController
{

    public function createComment(array $data): View
    {
        if ($_SESSION['id'] == '') {
            var_dump('Log in to post article');
            return new View('home');
        }

        $pdo = ConnectToDB::connect();
        $query = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
        $query->execute([$data['articleId']]);
        $data = $query->fetchAll(PDO::FETCH_ASSOC);


        return new View('Articles/show', [
            'id' => $data[0]['id']
        ]);
    }

    public function storeComment(array $data): Redirect
    {
        $pdo = ConnectToDB::connect();
        $articleQuery = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
        $articleQuery->execute([$data['articleId']]);
        $article = $articleQuery->fetchAll(PDO::FETCH_ASSOC);
        $articleId = $article[0]['id'];


        $pdo = ConnectToDB::connect();
        $query = $pdo->prepare('INSERT INTO comments(comment, user_id, author, article_id) VALUE (?, ?, ?, ?)');
        $query->execute([$_POST['comment'], $_SESSION['id'], $_SESSION['name'], $articleId]);

        return new Redirect('/articles/'.$articleId);

    }

    public function deleteComment(array $data): Redirect
    {
        $articleId = $data['articleId'];
        $commentId = $data['id'];
        $pdo = ConnectToDB::connect();
        $query = $pdo->prepare('DELETE FROM comments WHERE user_id = ? AND article_id =? AND id = ?');
        $query->execute([$_SESSION['id'], $articleId, $commentId]);

        return new Redirect('/articles/' . $articleId);
    }
}
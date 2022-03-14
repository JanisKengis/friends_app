<?php

namespace App\Controllers;

use App\ConnectToDB;
use App\Exceptions\FormValidationException;
use App\Exceptions\ResourceNotFoundException;
use App\Models\Article;
use App\Redirect;
use App\Validation\ArticleFormValidator;
use App\Validation\Errors;
use App\View;
use PDO;

class ArticleController
{

    public function index(): View
    {
        $pdo = ConnectToDB::connect();
        $query = $pdo->prepare('SELECT * FROM articles ORDER BY id DESC');
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        $articles = [];
        foreach($data as $article){
            $articles[]= new Article($article['title'], $article['description'], $article['id'], $article['user_id']);
        }


        return new View('Articles/index', [
            'data' => $articles,
            'sessionId' => $_SESSION['id'],
            'user' => $_SESSION['name']]);
    }

    public function show(array $data): View
    {
        $pdo = ConnectToDB::connect();
        $query = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
        $query->execute([$data['id']]);
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        $article = new Article($data[0]['title'], $data[0]['description'], $data[0]['id'], $data[0]['user_id']);

        $userId = $data[0]['user_id'];
        $articleId = $data[0]['id'];
        $pdo = ConnectToDB::connect();
        $userQuery = $pdo->prepare('SELECT name FROM user_profiles WHERE user_id=?');
        $userQuery->execute([$userId]);
        $user = $userQuery->fetchAll(PDO::FETCH_ASSOC);

        $pdo = ConnectToDB::connect();
        $likeQuery = $pdo->prepare('SELECT COUNT(id) as total FROM article_likes WHERE article_id = ?');
        $likeQuery->execute([$data[0]['id']]);
        $articleLikes = $likeQuery->fetchColumn(0);

        $articleLikesQuery = $pdo->prepare('SELECT user_id FROM article_likes WHERE article_id=?');
        $articleLikesQuery->execute([$articleId]);
        $userLikedArticle = $articleLikesQuery->fetchAll();
        foreach ($userLikedArticle as $value){
            if(in_array($_SESSION['id'], $value)) {
                $userLikedArticle = $_SESSION['id'];
            }
        }

        $pdo = ConnectToDB::connect();
        $commentQuery = $pdo->prepare('SELECT * FROM comments WHERE article_id = ?');
        $commentQuery->execute([$articleId]);
        $comment = $commentQuery->fetchAll();

        return new View('Articles/show', [
            'article' => $article,
            'user' => $user[0]['name'],
            'userid' => $userId,
            'sessionId' => $_SESSION['id'],
            'sessionUser' => $_SESSION['name'],
            'userLiked' => $userLikedArticle,
            'articleLikes' => $articleLikes,
            'comments' => $comment]);
    }

    public function create(): View
    {
        if ($_SESSION['id'] == '') {
            var_dump('Log in to post article');
            return new View('home');
        }
        return new View('Articles/create', [
            'errors'=> Errors::getAll(),
            'user' => $_SESSION['name'],
            'inputs'=> $_SESSION['inputs'] ?? []
        ]);
    }

    public function store(): Redirect
    {
        try {
            $validator = (new ArticleFormValidator($_POST, [
                'title'=> ['required', 'min:3'],
                'description'=> ['required']
            ]));
            $validator->passes();
        } catch (FormValidationException $exception) {
           $_SESSION['errors'] = $validator->getErrors();
           $_SESSION['inputs'] = $_POST;
           return new Redirect('articles/create');
        }



        return new Redirect('/articles');
    }

    public function delete(array $data): Redirect
    {
        $pdo = ConnectToDB::connect();
        $query = $pdo->prepare('DELETE FROM articles WHERE id = ?');
        $query->execute([$data['id']]);

        return new Redirect('/articles');
    }

    public function edit(array $data): View
    {
        try {
            $pdo = ConnectToDB::connect();
            $query = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
            $query->execute([$data['id']]);
            $data = $query->fetchAll(PDO::FETCH_ASSOC);

            if (!$data) {
                throw new ResourceNotFoundException("Article with id {$data['id']} not found.");
            }
            if ($data[0]['user_id'] != $_SESSION['id']) {
                var_dump('Only original author is allowed to edit articles');
                return new View('Articles/edit');
            }
            $article = new Article($data[0]['title'], $data[0]['description'], $data[0]['id'], $data[0]['user_id']);

            return new View('Articles/edit', [
                'article' => $article,
                'user' => $_SESSION['name']
            ]);
        } catch (ResourceNotFoundException $exception) {
            return new View('404');
        }
    }

    public function update(array $data): Redirect
    {
        $pdo = ConnectToDB::connect();
        $query = $pdo->prepare('UPDATE articles SET title = ?, description = ? WHERE id = ?');
        $query->execute([$_POST['title'], $_POST['description'], $data['id']]);

        return new Redirect('/articles/' . $data['id']);
    }

    public function like(array $data): Redirect
    {

        $pdo = ConnectToDB::connect();
        $query = $pdo->prepare('INSERT INTO article_likes(article_id, user_id) VALUE (?, ?)');
        $query->execute([$data['id'], $_SESSION['id']]);

        return new Redirect('/articles/' . $data['id']);
    }

    public function dislike(array $data): Redirect
    {

        $pdo = ConnectToDB::connect();
        $query = $pdo->prepare('DELETE FROM article_likes WHERE article_id = ? AND user_id=?');
        $query->execute([$data['id'], $_SESSION['id']]);

        return new Redirect('/articles/' . $data['id']);
    }

}
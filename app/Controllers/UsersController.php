<?php

namespace App\Controllers;

use App\ConnectToDB;
use App\Models\Article;
use App\View;
use PDO;

class UsersController
{

    public function index(): View
    {
       $pdo = (new ConnectToDB())->connect();
       $query = $pdo->prepare('SELECT * FROM articles');
       $query->execute();
       $data = $query->fetchAll(PDO::FETCH_ASSOC);
       $articles = [];
       foreach($data as $article){
           $articles[]= new Article($article['id'],$article['title'], $article['description']);
       }

       return new View('Users/index.html', ['data' => $articles]);
    }

    public function show(array $data): View
    {
        $pdo = (new ConnectToDB())->connect();
        $query = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
        $query->execute([$data['id']]);
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        $article = new Article($data[0]['id'], $data[0]['title'], $data[0]['description']);

        return new View('Users/show.html',
            ['id'=>$article->getId(),
            'title'=>$article->getTitle(),
            'description'=>$article->getDescription()]);
    }
}
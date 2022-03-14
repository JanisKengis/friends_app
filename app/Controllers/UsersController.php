<?php

namespace App\Controllers;

use App\ConnectToDB;
use App\Models\User;
use App\Redirect;
use App\View;
use PDO;

class UsersController
{

    public function index(): View
    {
        $pdo = ConnectToDB::connect();
        $query = $pdo->prepare('SELECT * FROM users INNER JOIN user_profiles ON users.id = user_profiles.user_id');
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        $users = [];

        foreach($data as $user){
                $users[] = new User($user['email'], $user['password'], $user['name'],
                    $user['surname'], $user['birthday'], $user['created_at'], $user['user_id']
                );
        }


        $friends = [];
        $invitations = [];
        $friendRequests = [];

        // Friends

        $pdo = ConnectToDB::connect()->prepare('SELECT * FROM friends WHERE user_id=?');
        $pdo->execute([$_SESSION['id']]);
        $friendId = $pdo->fetchAll(PDO::FETCH_ASSOC);

        foreach($friendId as $friend) {
            $pdo = ConnectToDB::connect()->prepare('SELECT * FROM users INNER JOIN user_profiles ON 
    users.id = user_profiles.user_id WHERE user_profiles.user_id = ?');
            $pdo->execute([$friend['friend_id']]);
            $friends[] = $pdo->fetch(PDO::FETCH_ASSOC);
        }

        // Pending invitations:
        $pdo = ConnectToDB::connect()->prepare('SELECT * FROM friend_invites  
    WHERE user_id = ?');
        $pdo->execute([$_SESSION['id']]);
        $requestedFriends = $pdo->fetchAll(PDO::FETCH_ASSOC);

        foreach ($requestedFriends as $requestedFriend) {
            $pdo = ConnectToDB::connect()->prepare('SELECT * FROM users INNER JOIN user_profiles ON 
    users.id = user_profiles.user_id WHERE user_profiles.user_id = ?');
            $pdo->execute([$requestedFriend['friend_id']]);
            $invitations[] = $pdo->fetch(PDO::FETCH_ASSOC);
        }


        // Friend requests

        $pdo = ConnectToDB::connect()->prepare('SELECT * FROM friend_invites 
    WHERE friend_id = ?');
        $pdo->execute([$_SESSION['id']]);
        $friendRequestId = $pdo->fetchAll(PDO::FETCH_ASSOC);


        foreach ($friendRequestId as $requestId){
            $pdo = ConnectToDB::connect()->prepare('SELECT * FROM users INNER JOIN user_profiles ON 
    users.id = user_profiles.user_id WHERE user_profiles.user_id = ?');
            $pdo->execute([$requestId['user_id']]);
            $friendRequests[] = $pdo->fetch(PDO::FETCH_ASSOC);
        }

        return new View('Users/index', [
            'users' => $users,
            'userName' => $_SESSION['name'],
            'sessionId' => $_SESSION['id'],
            'invitesId' => $requestedFriends,
            'requestId' => $friendRequestId,
            'friendId' => $friendId,
            'friends' => $friends,
            'invitedFriends' => $invitations,
            'friendRequests' => $friendRequests
        ]);
    }


    public function show(array $data): View
    {
        $pdo = ConnectToDB::connect();
        $query = $pdo->prepare('SELECT * FROM users INNER JOIN user_profiles ON users.id = user_profiles.user_id
WHERE users.id = ?');
        $query->execute([$data['id']]);
        $data = $query->fetchAll(PDO::FETCH_ASSOC);


        $user = new User($data[0]['email'], $data[0]['password'], $data[0]['name'],
                    $data[0]['surname'], $data[0]['birthday'], $data[0]['created_at'], $data[0]['user_id']
                );

        $pdo = ConnectToDB::connect()->prepare('SELECT friend_id FROM friends WHERE user_id=?');
        $pdo->execute([$_SESSION['id']]);
        $friends[] = $pdo->fetchAll(PDO::FETCH_ASSOC);

        $pdo = ConnectToDB::connect()->prepare('SELECT * FROM friend_invites  
    WHERE user_id = ?');
        $pdo->execute([$_SESSION['id']]);
        $invites[] = $pdo->fetchAll(PDO::FETCH_ASSOC);

        $pdo = ConnectToDB::connect()->prepare('SELECT * FROM friend_invites  
    WHERE friend_id = ?');
        $pdo->execute([$_SESSION['id']]);
        $requests[] = $pdo->fetchAll(PDO::FETCH_ASSOC);

//var_dump($user);die;
        return new View('Users/show', [
            'user' => $user,
            'name' => $_SESSION['name'],
            'friends' => $friends[0],
            'invites' => $invites[0],
            'requests' => $requests[0]
        ]);
    }

    public function signup(): View
    {
        return new View('Users/signup');
    }

    public function register(): Redirect
    {
        $hashedPwd = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $pdo = ConnectToDB::connect();
        $user = $pdo->prepare('INSERT INTO users(email, password) VALUE (?, ?)');
        $user->execute([$_POST['email'], $hashedPwd]);

        $query = $pdo->prepare('SELECT email, id FROM users WHERE email = ?');
        $query->execute([$_POST['email']]);
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        $id = $data[0]['id'];

        $profileQuery = $pdo->prepare('INSERT INTO user_profiles(user_id, name, surname, birthday) VALUE (?, ?, ?, ?)');
        $profileQuery->execute([$id, $_POST['name'], $_POST['surname'], $_POST['birthday']]);

        return new Redirect('/');
    }

    public function login(): View
    {
        return new View('Users/login');
    }

    public function signin(): Redirect
    {
        if (empty($_POST['login_email']) || empty($_POST['login_password'])) {
            return new Redirect('/users/error');
        }

        $pdo = ConnectToDB::connect();
        $query = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $query->execute([$_POST['login_email']]);
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        $passwordVerify = password_verify($_POST['login_password'], $data[0]['password']);
        if (!$passwordVerify){
            return new Redirect('/users/error');
        }

        $user = $pdo->prepare('SELECT * FROM user_profiles WHERE user_id=?');
        $user->execute([$data[0]['id']]);
        $profile = $user->fetchAll(PDO::FETCH_ASSOC);

        session_start();
        $_SESSION['id'] = $data[0]['id'];
        $_SESSION['name'] = $profile[0]['name'];

        return new Redirect('/articles');
    }

    public function error(): View
    {
        return new View('Users/error');
    }

    public function invite(array $data): Redirect
    {
        // select user from user table where id = user you want to invite
        // user id who is inviting = session id

        $pdo = ConnectToDB::connect()
        ->prepare('INSERT INTO friend_invites(user_id, friend_id) VALUE (?, ?)');
        $pdo->execute([$_SESSION['id'], $data['id']]);

        return new Redirect('/users');
    }

    public function accept(array $data): Redirect
    {
        $pdo = ConnectToDB::connect()->prepare('DELETE FROM friend_invites WHERE user_id=? AND friend_id=?');
        $pdo->execute([$data['id'], $_SESSION['id']]);

        $acceptQuery = ConnectToDB::connect()
        ->prepare('INSERT INTO friends(user_id, friend_id) VALUE (?, ?)');
        $acceptQuery->execute([$_SESSION['id'], $data['id']]);

        $pdo = ConnectToDB::connect()->prepare('INSERT INTO friends(user_id, friend_id) VALUE (?, ?)');
        $pdo->execute([$data['id'], $_SESSION['id']]);


        return new Redirect('/users');
    }

    public function decline(array $data): Redirect
    {
        $pdo = ConnectToDB::connect()->prepare('DELETE FROM friend_invites WHERE user_id=? AND friend_id=?');
        $pdo->execute([$data['id'], $_SESSION['id']]);

        return new Redirect('/users');
    }

}
<?php
$postsFile = "posts.txt";
session_start();
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = "guest_" . rand(1000, 9999); // or real username if logged in
}
$username = $_SESSION['username'];
// Handle likes
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['like_timestamp'])) {
    $likeTimestamp = $_POST['like_timestamp'];
    $lines = file($postsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $updatedPosts = [];

    foreach ($lines as $line) {
        $data = json_decode($line, true);
        if ($data["timestamp"] === $likeTimestamp) {
            if (!isset($data["LikedBy"])) {
                $data["LikedBy"] = [];
            }

            if (!in_array($username, $data["LikedBy"])) {
                $data["Likes"] = isset($data["Likes"]) ? $data["Likes"] + 1 : 1;
                $data["LikedBy"][] = $username;
            }
        }
        $updatedPosts[] = json_encode($data);
    }

    file_put_contents($postsFile, implode("\n", $updatedPosts) . "\n");
    header("Location: " . $_SERVER["PHP_SELF"]);
    exit;
}

// Handle new post
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["message"])) {
    $message = htmlspecialchars(trim($_POST["message"]));
    $timestamp = date("Y-m-d H:i:s");
    $likes = 0;

    $postData = [
        "timestamp" => $timestamp,
        "message" => $message,
        "Likes" => $likes
    ];

    $jsonLine = json_encode($postData) . "\n";
    file_put_contents($postsFile, $jsonLine, FILE_APPEND);

    header("Location: " . $_SERVER["PHP_SELF"]);
    exit;
}

// Handle delete
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_timestamp"])) {
    $deleteTimestamp = $_POST["delete_timestamp"];
    $lines = file($postsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $filtered = [];

    foreach ($lines as $line) {
        $data = json_decode($line, true);
        if (!$data || $data["timestamp"] !== $deleteTimestamp) {
            $filtered[] = $line;
        }
    }

    file_put_contents($postsFile, implode("\n", $filtered) . "\n");
    header("Location: " . $_SERVER["PHP_SELF"]);
    exit;
}

// Load posts
$posts = [];
if (file_exists($postsFile)) {
    $lines = file($postsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $data = json_decode($line, true);
        if ($data && isset($data["timestamp"], $data["message"])) {
            $posts[] = $data;
        }
    }
    $posts = array_reverse($posts); // Newest first
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chirpify</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="icon" type="image/x-icon" href="logochirp.png">
</head>
<body>
<div class="ChirpyText">
    <h1>📝 POST</h1>
    <form method="POST" class="post-form">
        <textarea name="message" placeholder="What's on your mind?" maxlength="280" required></textarea>
        <button type="submit">Post</button>
    </form>
    <div class="timeline">
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <div class="timestamp"><?= htmlspecialchars($post["timestamp"]) ?></div>
                <div class="content"><?= nl2br(htmlspecialchars($post["message"])) ?></div>
                <p>Total Likes: <?= isset($post["Likes"]) ? $post["Likes"] : 0 ?></p>
                <?php if (!isset($post["LikedBy"]) || !in_array($username, $post["LikedBy"])): ?>
                    <form method="post">
                        <input type="hidden" name="like_timestamp" value="<?= htmlspecialchars($post["timestamp"]) ?>">
                        <button type="submit" class="like_button">👍 Like</button>
                    </form>
                <?php else: ?>
                    <p><em>You liked this post 💚</em></p>
                <?php endif; ?>

                <form method="POST" class="delete-form" onsubmit="return confirm('Delete this post?');">
                    <input type="hidden" name="delete_timestamp" value="<?= htmlspecialchars($post["timestamp"]) ?>">
                    <button type="submit" class="delete-button">🗑️ Delete</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<a href="sign_up.html">Register</a>
<div class="container">
    <div class="title">Who to follow</div>

    <div class="user">
        <img src="https://via.placeholder.com/40" alt="User Profile">
        <div class="user-info">
            <div class="name">John Doe</div>
            <div class="handle">@johndoe</div>
        </div>
        <button class="follow-btn">Follow</button>
    </div>

    <div class="user">
        <img src="https://via.placeholder.com/40" alt="User Profile">
        <div class="user-info">
            <div class="name">Jane Smith</div>
            <div class="handle">@janesmith</div>
        </div>
        <button class="follow-btn">Follow</button>
    </div>

    <div class="user">
        <img src="https://via.placeholder.com/40" alt="User Profile">
        <div class="user-info">
            <div class="name">Alice Johnson</div>
            <div class="handle">@alicejohnson</div>
        </div>
        <button class="follow-btn">Follow</button>
    </div>
</div>

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: black;
        margin: 0;
        padding: 20px;
    }
    .container {
        width: 300px;
        background: black;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        position: absolute;
        right: 60px;
        top: 60px;
    }
    .title {
        color: white;
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 10px;
    }
    .user {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .user img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 10px;
    }
    .user-info {
        flex-grow: 1;
    }
    .user-info .name {
        font-size: 14px;
        font-weight: bold;
    }
    .user-info .handle {
        font-size: 12px;
        color: #657786;
    }
    .follow-btn {
        background: white;
        color: black;
        border: none;
        padding: 5px 10px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
    }
    .follow-btn:hover {
        background: gray;
    }

    .name {
        color: white;
    }

    .ChirpyText {
        max-width: 450px;
        background: limegreen;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 0 8px rgba(0,0,0,0.1);
        justify-content: center;
    }

    h1 {
        display: flex;
        text-align: center;
        color: white;
        flex-direction: column;

    }

    .post-form textarea {
        width: 100%;
        height: 80px;
        padding: 1px;
        font-size: 14px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        resize: none;
    }

    .post-form button {
        background-color: lawngreen;
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 8px;
        cursor: pointer;

    }
    .delete-button{
        background-color: red;
        color: white;
        border: none;
        padding: 1px 3px;
        border-radius: 2px;
        cursor: pointer;
    }.post-form button:hover {
        background-color: darkgreen;
    }

    .timeline {
        margin-top: 20px;
    }

    .post {
        border-bottom: 1px solid white;
        padding: 10px 0;
        color:  #f5f8fa;
        background-color: green;
    }

    .timestamp {
        font-size: 12px;
        margin-bottom: 4px;
        color: #f5f8fa;
    }

    .content {
        font-size: 16px;
        color: #f5f8fa;

    }

</style>
</body>
<h2> dit is chirpify. een sociale platform om met elkaar praten en socialeren</h2>
</html>

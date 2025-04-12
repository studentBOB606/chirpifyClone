<?php
session_start();

include("db.php");

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $username2 = $_POST['username2'];
    $password2 = $_POST['password2'];

    if(!empty($username2) && !empty($password2)){
        $query = "SELECT * FROM register WHERE email = '$username2' limit 1";
        $result = mysqli_query($con, $query);

        if($result)
        {
            if($result && mysqli_num_rows($result) > 0)
            {
                $user_data = mysqli_fetch_assoc($result);

                if($user_data['password'] == $password2)
                {
                    header("location: index.php");
                    die;
                }
            }
        }

            echo "<script type='text/javascript'> alert('Wrong email or password!')</script>";
    }
    else{
        echo "<script type='text/javascript'> alert('Logged In!')</script>";

    }
}

?>

<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


//$DB_HOST = $_ENV["$DB_HOST"];
//$DB_USER = $_ENV["$DB_USER"];
//$DB_PASSWORD = $_ENV["$DB_PASSWORD"];
//$DB_NAME = $_ENV["$DB_NAME"];
//$DB_PORT = $_ENV["$DB_PORT"];
//$con = mysqli_connect("$DB_HOST","$DB_USER","$DB_PASSWORD","$DB_NAME","$DB_PORT");
//mysqli_select_db($con,"portfolio");

$con = mysqli_connect("containers-us-west-42.railway.app","root","7RJIZEs1ZZrYHfqYGHJf","railway","5900");

$data = json_decode(file_get_contents("php://input"));

$query = $data->query;

switch($query) {
    case "LOGIN":
        $email = $data->email;
        $password = $data->password;
        
        $sql = "SELECT * FROM `users` WHERE email = '$email' AND password = '$password'";
        
        $result = mysqli_query($con,$sql);
        if ($result == false)
            $numOfRows = 0;
        else
            $numOfRows = mysqli_num_rows($result);
        
        if ($numOfRows == 0) {
            
            $response['data']=array(
                'status'=>'no user found'
            );
            echo json_encode($response);
        }
        else if ($numOfRows > 1) {
            
            $response['data']=array(
                'status'=>'unexpected error'
            );
            echo json_encode($response);
        }
        else {
            $id = mysqli_fetch_assoc($result);
            $response['data']=array(
                'status'=>'valid user',
                'id'=>$id['id']
            );
            echo json_encode($response);
        }
        break;
        
    case "REGISTER":
        $username = $data->username;
        $email = $data->email;
        $password = $data->password;
        $confirmPassword = $data->confirmPassword;
        
        $sql_email = "SELECT * FROM `users` WHERE email = '$email'";
        $result_email = mysqli_query($con,$sql_email);
        $numOfRows_email = mysqli_num_rows($result_email);
        
        $sql_username = "SELECT * FROM `users` WHERE username = '$username'";
        $result_username = mysqli_query($con,$sql_username);
        $numOfRows_username = mysqli_num_rows($result_username);
        
        if ($password != $confirmPassword) {    // if passwords don't match
            $response['data']=array(
                'status'=>'passwords do not match'
            );
            echo json_encode($response);
        }
        else if ($numOfRows_email != 0) {       // if email is already taken
            $response['data']=array(
                'status'=>'email taken'
            );
            echo json_encode($response);
        }
        else if ($numOfRows_username != 0) {    // if usernamw is already taken
            $response['data']=array(
                'status'=>'username taken'
            );
            echo json_encode($response);
        }
        else {
            if ($username && $email && $password) {
                $sql = "insert into users(
                    username,
                    email,
                    password
                )
                value(
                    '$username',
                    '$email',
                    '$password'
                )";
                $result = mysqli_query($con,$sql);
                
                $sql = "SELECT * FROM `users` WHERE email = '$email' AND password = '$password'";
                $result = mysqli_query($con,$sql);
                $id = mysqli_fetch_assoc($result);

                $response['data']=array(
                    'status'=>'valid registration',
                    'id'=>$id['id']
                );
                echo json_encode($response);
            }
        }
        break;
        
    case "ACCOUNT":
        $id = $data->id;
        $sql = "SELECT * FROM `users` WHERE id = '$id'";
        $result = mysqli_query($con,$sql);
        $userData = mysqli_fetch_assoc($result);
        
        $response['data']=array(
            'status'=>'valid account',
            'username'=>$userData['username'],
            'email'=>$userData['email'],
            'password'=>$userData['password'],
            'date'=>$userData['user_time']
        );
        echo json_encode($response);
        break;
        
    case "DELETE":
        $id = $data->id;
        $sql = "DELETE FROM `users` WHERE id = '$id'";
        $result = mysqli_query($con,$sql);
        $sql = "UPDATE comments SET user_username = 'Unknown' WHERE user_id = '$id'";
        $result = mysqli_query($con,$sql);
        break;
        
    case "DELETE_COMMENT":
        $id = $data->id;
        $sql = "UPDATE comments SET comment_status = 'hide' WHERE comment_id = '$id'";
        $result = mysqli_query($con,$sql);
        break;
        
    case "POST_COMMENT":
        $id = $data->id;
        $comment = $data->comment;
        
        $sql = "SELECT username FROM `users` WHERE id = '$id'";
        $result = mysqli_query($con,$sql);
        $user = mysqli_fetch_assoc($result);
        $user_username = $user['username'];
        
        $sql = "insert into comments(
            user_id,
            user_username,
            comment,
            comment_status
        )
        value(
            '$id',
            '$user_username',
            '$comment',
            'show'
        )";
        $result = mysqli_query($con,$sql);        
        break;
        
    case "GET_COMMENTS":        
        $sql = "SELECT * FROM `comments` WHERE comment_status = 'show'";
        $result = mysqli_query($con,$sql);
        $comments = mysqli_fetch_all($result);
        echo json_encode($comments);      
        break;
        
    case "GET_ACCOUNT_COMMENTS":
        $id = $data->id;  
        $sql = "SELECT * FROM `comments` WHERE comment_status = 'show' AND user_id = '$id'";
        $result = mysqli_query($con,$sql);
        $comments = mysqli_fetch_all($result);
        echo json_encode($comments);      
        break;
}
?>

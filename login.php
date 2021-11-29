<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>ログイン</title>
    </head>
    <body>
        <?php
        session_start();
        if (isset($_SESSION["login"])) {
            session_regenerate_id(TRUE);
            header("Location: mypage.php");
            exit();
        }
        if(isset($_POST["mail"]) && isset($_POST["pass"])){
            $dsn = dsn;
            $user = user;
            $password = pass;
            $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
            $sql = "CREATE TABLE IF NOT EXISTS user"
                ." ("
                ."id INT AUTO_INCREMENT PRIMARY KEY,"
                ."name varchar(128),"
                ."nickname varchar(128),"
                ."mail varchar(128),"
                ."createdid varchar(128),"
                ."password varchar(128),"
                ."date_c DATETIME,"
                ."date_u DATETIME"
                .");";
            $stmt = $pdo -> query($sql);
            $sql = 'SELECT * FROM user';
            $stmt = $pdo -> query($sql);
            $results = $stmt -> fetchALL();
            foreach($results as $row){
                if($row['mail'] == $_POST["mail"]){
                    $exi_address = 1;
                    if(password_verify($_POST["pass"], $row['password']) == true){
                        session_regenerate_id(TRUE); //セッションidを再発行
                        $_SESSION["login"] = $_POST['mail']; //セッションにログイン情報を登録
                        header("Location: mypage.php"); //ログイン後のページにリダイレクト
                        exit();
                    } else{
                        $m_pass = 1;
                    }
                } else{
                    $m_address = 0;
                }
            }
            $re_address = $_POST["mail"];
        }
        ?>
        <h1>ログイン</h1>
        <form action="" method="post">
            <p>
                メールアドレス<br>
                <input type="text" name="mail" size="50" required value="<?php if(isset($re_address)){echo $re_address;} ?>"><br>
            </p>
            <p>
                ID<br>
                <input type="password" name="pass" size="50" required><br>
            </p>
            <p>
                <?php
                if(isset($exi_address) && $exi_address==0 || isset($m_pass) && $m_pass==1){
                    echo "エラー：メールアドレスまたはパスワードが異なっています<br>";
                }
                ?>
                <input type="submit" value="ログイン">
            </p>
        </form>
        <p>
            <a href="https://.../misspass.php" target="_self">
                パスワードをお忘れの場合はこちら
            </a><br>
            <a href="https://.../register.php" target="_self">
                新規登録はこちら
            </a>
        </p>
    </body>
</html>
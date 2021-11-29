<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>会員登録</title>
    </head>
    <body>
        <?php
            if(isset($_POST["address"])){
                $address = $_POST["address"];
            }
            //登録ボタン
            if(isset($_POST['register'])){
                $page = 3;
                //userに登録
                $dsn = dsn;
                $user = user;
                $password = pass;
                $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
                $sql = $pdo -> prepare("INSERT INTO user (name, mail, password, date_c) VALUES (:name, :mail, :password, :date_c)");
                $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                $sql -> bindParam(':mail', $address, PDO::PARAM_STR);
                $sql -> bindParam(':password', $pass, PDO::PARAM_STR);
                $sql -> bindParam(':date_c', $date_c, PDO::PARAM_STR);
                $name = $_POST["name_2"];
                $address = $_POST["address"];
                $pass =  password_hash($_POST["pass_2"], PASSWORD_DEFAULT);
                $date_c = date("Y-m-d H:i:s");        
                $sql -> execute();
                //メール送信
                require 'src/Exception.php';
                require 'src/PHPMailer.php';
                require 'src/SMTP.php';
                require 'setting.php';
                $mail = new PHPMailer\PHPMailer\PHPMailer();
                $mail -> isSMTP(); // SMTPを使うようにメーラーを設定する
                $mail -> SMTPAuth = true;
                $mail -> Host = MAIL_HOST; // メインのSMTPサーバー（メールホスト名）を指定
                $mail -> Username = MAIL_USERNAME; // SMTPユーザー名（メールユーザー名）
                $mail -> Password = MAIL_PASSWORD; // SMTPパスワード（メールパスワード）
                $mail -> SMTPSecure = MAIL_ENCRPT; // TLS暗号化を有効にし、「SSL」も受け入れます
                $mail -> Port = SMTP_PORT; // 接続するTCPポート
                // メール内容設定
                $mail -> CharSet = "UTF-8";
                $mail -> Encoding = "base64";
                $mail -> setFrom(MAIL_FROM,MAIL_FROM_NAME);
                $mail -> addAddress($address, '受信者さん'); //受信者（送信先）を追加する
                //    $mail->addReplyTo('xxxxxxxxxx@xxxxxxxxxx','返信先');
                //    $mail->addCC('xxxxxxxxxx@xxxxxxxxxx'); // CCで追加
                //    $mail->addBcc('xxxxxxxxxx@xxxxxxxxxx'); // BCCで追加
                $mail -> Subject = MAIL_SUBJECT; // メールタイトル
                $mail -> isHTML(true);    // HTMLフォーマットの場合はコチラを設定します
                $body = 'この度はご登録いただきありがとうございます。<br>本登録致しました。';
                $mail->Body  = $body; // メール本文
                // メール送信の実行
                if($mail -> send()){
                	$message_3 = "会員登録しました。";
                } else{
                    $sql = 'delete from user where mail=:mail';
                    $stmt = $pdo -> prepare($sql);
                    $stmt -> bindParam(':mail', $address, PDO::PARAM_INT);
                    $stmt -> execute();
                	$message_3 = "メールの送信に失敗しました。";
                    echo 'Mailer Error: ' . $mail -> ErrorInfo;
                }
            //戻るボタン
            } elseif(isset($_POST["back"])){
                $page = 1;
                $name = $_POST["name_2"];
            //入力完了
            } elseif(isset($_POST["name_1"]) && isset($_POST["pass_1"]) && isset($_POST["pass_1check"])){
                $page = 1;
                if($_POST["pass_1"] != $_POST["pass_1check"]){
                    $d_pass = 1;
                    $name = $_POST["name_1"];
                }elseif(preg_match("/\A(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,20}+\z/i", $_POST["pass_1"])){
                    $page = 2;
                    $name = $_POST["name_1"];
                    $pass = $_POST["pass_1"];
                } elseif(!preg_match("/\A(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,20}+\z/i", $_POST["pass_1"])){
                    $w_pass = 1;
                    $name = $_POST["name_1"];
                }
            //初期
            } else{
                if(isset($_GET['urltoken'])){
                    $urltoken = $_GET['urltoken'];
                    $dsn = 'mysql:dbname=tb230655db;host=localhost';
                    $user = 'tb-230655';
                    $password = 'usQGpUWny6';
                    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
                    $sql = 'SELECT mail, date, urltoken FROM pre_user';
                    $stmt = $pdo -> query($sql);
                    $results = $stmt -> fetchALL();
                    foreach($results as $row){
                        if($row['urltoken'] == $urltoken){
                            $page = 1;
                            $address = $row['mail'];
                            $olddate = $row['date'];
                        }
                    }
                }
                //時間確認
                if($page == 1){
                    $limitdate = date("Y-m-d H:i:s",strtotime($olddate . "+1 day"));
                    $newdate = date("Y-m-d H:i:s");  
                    if($newdate > $limitdate){
                        $page = 0;
                        foreach($results as $row){
                            if($row['urltoken'] == $urltoken){
                                $sql = 'delete from pre_user where urltoken=:urltoken';
                                $stmt = $pdo -> prepare($sql);
                                $stmt -> bindParam(':urltoken', $urltoken, PDO::PARAM_INT);
                                $stmt -> execute();
                            }
                        }
                    }
                }
                //userを作成
                if($page == 1){
                    $dsn = 'mysql:dbname=tb230655db;host=localhost';
                    $user = 'tb-230655';
                    $password = 'usQGpUWny6';
                    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
                    $sql = "CREATE TABLE IF NOT EXISTS user"
                        ." ("
                        ."userid INT AUTO_INCREMENT PRIMARY KEY,"
                        ."name varchar(128),"
                        ."nickname varchar(128),"
                        ."mail varchar(128),"
                        ."createdid varchar(128),"
                        ."password varchar(128),"
                        ."date_c DATETIME,"
                        ."date_u DATETIME"
                        .");";
                    $stmt = $pdo -> query($sql);
                    //すでにuser登録がないか確認
                    $sql = 'SELECT mail FROM user';
                    $stmt = $pdo -> query($sql);
                    $results = $stmt -> fetchALL();
                    foreach($results as $row){
                        if($row['mail'] == $address){
                            $page = 0;
                        }
                    }
                }
            }
        ?>
        <h1>会員登録</h1>
        <?php
            if($page == 0){
                echo "このURLはご利用できません。<br>";
                echo "有効期限が過ぎたかURLが間違えている可能性がございます。<br>";
                echo "もう一度登録をやりなおして下さい。";
            } elseif($page == 1){
        ?>
                <form action="" method="post">
                    <p>
                        メールアドレス
                        <input type="hidden" name="address" value=<?php echo $address; ?>><br>
                        <?php echo $address."<br>"; ?>
                    </p>
                    <p>
                        名前<br>
                        <input type="text" name="name_1" size="50" required <?php if(isset($name)){echo "value=".$name;} else{ echo "placeholder=山田太郎";}?>><br>
                    </p>
                    <p>
                        パスワード<br>
                        <input type="password" name="pass_1" size="50" required><br>
                        半角英数字両方含めて8文字以上20文字以下<br>
                        <?php
                            if(isset($d_pass)){
                                echo "エラー：同じパスワードを入力してください。<br>";
                            } elseif(isset($w_pass)){
                                echo "エラー：条件に合ったパスワードを設定してください。<br>";
                            }
                        ?>
                    </p>
                    <p>
                        パスワード（確認用）<br>
                        <input type="password" name="pass_1check" size="50" required><br>
                    </p>
                    <p>
                        <input type="submit" name="submit" value="確認">
                    </p>
                </form>
        <?php
            } elseif($page == 2){
                echo "<p>名前：".$name."</p>";
                $pass_hide = str_repeat('*', strlen($pass));
                echo "<p>パスワード：".$pass_hide."</p>";
                echo "<p>以上の内容で登録してよろしいでしょうか。</p>";
        ?>
            <form action="" method="post">
                <input type="submit" name="back" value="戻る">
                <input type="submit" name="register" value="登録">
                <input type="hidden" name="name_2" value="<?php echo $name; ?>">
                <input type="hidden" name="pass_2" value="<?php echo $pass; ?>">
                <input type="hidden" name="address" value=<?php echo $address; ?>><br>
            </form>
        <?php
            } else{
                echo $message_3;
        ?>
            <p>
                <a href="https://.../login.php" target="_self">
                    ログイン画面へ戻る。
                </a>
            </p>
        <?php    
            }
        ?>
    </body>
</html>
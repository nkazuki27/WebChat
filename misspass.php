<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>ログイン</title>
    </head>
    <body>
        <?php
        if(isset($_POST["mail"]) && isset($_POST["createdid"])){
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
                    if($row["createdid"] == $_POST["createdid"]){
                        $sql = $pdo -> prepare("INSERT INTO pre_user (mail, date, urltoken) VALUES (:mail, :date, :urltoken)");
                        $sql -> bindParam(':mail', $address, PDO::PARAM_STR);
                        $sql -> bindParam(':date', $date, PDO::PARAM_STR);
                        $sql -> bindParam(':urltoken', $urltoken, PDO::PARAM_STR);
                        $mail = $_POST["mail"];
                        $date = date("Y-m-d H:i:s");        
                        $urltoken = hash('sha256',uniqid(rand(),1));
                        $sql -> execute();
                        $url = "https://tech-base.net/tb-230655/mission_6-2/repass.php?urltoken=".$urltoken;
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
                        $mail -> addAddress($_POST["mail"], '受信者さん'); //受信者（送信先）を追加する
                        //    $mail->addReplyTo('xxxxxxxxxx@xxxxxxxxxx','返信先');
                        //    $mail->addCC('xxxxxxxxxx@xxxxxxxxxx'); // CCで追加
                        //    $mail->addBcc('xxxxxxxxxx@xxxxxxxxxx'); // BCCで追加
                        $mail -> Subject = MAIL_SUBJECT; // メールタイトル
                        $mail -> isHTML(true);    // HTMLフォーマットの場合はコチラを設定します
                        $body = "パスワードを再設定いたします。<br>24時間以内に下記のURLからご登録下さい。<br>".$url;
                        $mail -> Body  = $body; // メール本文
                        // メール送信の実行
                        if(!$mail -> send()){
                            $sql = 'delete from pre_user where urltoken=:urltoken';
                            $stmt = $pdo -> prepare($sql);
                            $stmt -> bindParam(':urltoken', $urltoken, PDO::PARAM_INT);
                            $stmt -> execute();
                        	$message = "メッセージをお送りできませんでした。";
                            echo 'Mailer Error: ' . $mail -> ErrorInfo;
                        } else{
                        	$message = "メールをお送りしました。<br>24時間以内にメールに記載されたURLからご登録下さい。";
                            $reg = 1;
                        }
                    } else{
                        $n_createdid = 1;
                    }
                } else{
                    $exi_address = 0;
                }
            }
            $re_address = $_POST["mail"];
            $re_createdid = $_POST["createdid"];
        }
        if(!isset($reg)){
        ?>
            <h1>パスワード再設定</h1>
            <form action="" method="post">
                <p>
                    メールアドレス<br>
                    <input type="text" name="mail" size="50" required value="<?php if(isset($re_address)){echo $re_address;} ?>"><br>
                </p>
                <p>
                    ユーザーID<br>
                    <input type="text" name="createdid" size="50" required value="<?php if(isset($re_createdid)){echo $re_createdid;} ?>"><br>
                </p>
                <p>
                    <?php
                    if(isset($exi_address) && $exi_address==0 || isset($n_createdid) && $n_createdid==1){
                        echo "エラー：メールアドレスまたはユーザーIDが異なっています<br>";
                    }
                    ?>
                    <input type="submit" value="ログイン">
                </p>
            </form>
            <a href="https://.../login.php" target="_self">
                戻る
            </a><br>
        <?php
        } else{
            echo $message;
        }
        ?>
    </body>
</html>
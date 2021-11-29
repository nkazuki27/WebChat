<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>新規登録</title>
    </head>
    <body>
        <?php
            $reg = 0;
            if(isset($_POST["mail"]) && isset($_POST["mailckeck"])){
                $address = $_POST["mail"];
                if($_POST["mail"] != $_POST["mailckeck"]){
                    $message_1="同じメールアドレスを入力してください。";
                } elseif(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $address)){
                    $message_1 = "メールアドレスの形式が正しくありません。";
                } else{
                    $exi_address = 0;
                    $dsn = dsn;
                    $user = user;
                    $password = pass;
                    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
                    $sql = "CREATE TABLE IF NOT EXISTS pre_user"
                        ." ("
                        ."id INT AUTO_INCREMENT PRIMARY KEY,"
                        ."mail varchar(128),"
                        ."date DATETIME,"
                        ."urltoken varchar(255)"
                        .");";
                    $stmt = $pdo -> query($sql);
                    $sql = 'SELECT * FROM pre_user';
                    $stmt = $pdo -> query($sql);
                    $results = $stmt -> fetchALL();
                    foreach($results as $row){
                        if($row['mail'] == $address){
                            $message_1 = "このメールアドレスはすでに利用されています。";
                            $exi_address = 1;
                        }
                    }
                    if($exi_address == 0){
                        $urltoken = hash('sha256',uniqid(rand(),1));
                        $sql = $pdo -> prepare("INSERT INTO pre_user (mail, date, urltoken) VALUES (:mail, :date, :urltoken)");
                        $sql -> bindParam(':mail', $address, PDO::PARAM_STR);
                        $sql -> bindParam(':date', $date, PDO::PARAM_STR);
                        $sql -> bindParam(':urltoken', $urltoken, PDO::PARAM_STR);
                        $mail = $_POST["mail"];
                        $date = date("Y-m-d H:i:s");        
                        $sql -> execute();
                        $url = "https://tech-base.net/tb-230655/mission_6-2/signup.php?urltoken=".$urltoken;
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
                        $body = "この度はご登録いただきありがとうございます。<br>24時間以内に下記のURLからご登録下さい。<br>".$url;
                        $mail -> Body  = $body; // メール本文
                        // メール送信の実行
                        if(!$mail -> send()){
                            $sql = 'delete from pre_user where urltoken=:urltoken';
                            $stmt = $pdo -> prepare($sql);
                            $stmt -> bindParam(':urltoken', $urltoken, PDO::PARAM_INT);
                            $stmt -> execute();
                        	$message_2 = "メッセージをお送りできませんでした。";
                            echo 'Mailer Error: ' . $mail -> ErrorInfo;
                        } else{
                        	$message_2 = "メールをお送りしました。<br>24時間以内にメールに記載されたURLからご登録下さい。";
                            $reg = 1;
                        }
                    }
                }
            }
        ?>
        <h1>新規登録</h1>
        <?php
            if($reg == 0){
        ?>
            <form action="" method="post">
                <p>
                    メールアドレス<br>
                    <input type="text" name="mail" size="50" value="<?php if(isset($address)){ echo $address;} ?>" required><br>
                    <?php
                        if(isset($message_1)){
                            echo $message_1."<br>";
                        } 
                    ?>
                </p>
                <p>
                    メールアドレス（確認用）<br>
                    <input type="text" name="mailckeck" size="50" required><br>
                </p>
                <p>
                    <input type="submit" name="submit">
                </p>
            </form>
            <a href="https://.../login.php" target="_self">
                戻る
            </a><br>
        <?php
            } elseif(isset($message_2)){
                echo $message_2."<br>";
            }
        ?>
    </body>
</html>
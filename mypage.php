<!DOCTYPE html>
<html lang="ja">
    <header>
        <meta charset="UTF-8">
        <title>マイページ</title>
        <style>
            body{
                margin: 0px;
            }
            .wide{
                display: flex;
                margin: 0px;
            }
            .leftside{
                width: 300px;
                height: 100%;
                margin-left: 0px;
                margin-top: 0px;
                margin-bottom: 0px;
                background-color: skyblue;
            }
            .rightside{
                width: calc(100% - 300px);
            }
            .left{
                text-align: right;
            }
            footer{
                position: absolute;/*←絶対位置*/
                bottom: 0; /*下に固定*/
                width: calc(100% - 300px);
                margin-bottom: 10px;
            }
            input{
                margin: 5px;
                line-height: 50px;
            }
            .hidden{
                line-height: 10px;
            }
            input.comment{
                width: calc(100% - 200px);
            }
        </style>
    </header>
    <body>
        <?php
        session_start();
        if (!isset($_SESSION["login"])) {
            header("Location: login.php");
            exit();
        }
        $dsn = dsn;
        $user = user;
        $password = pass;
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        $sql = 'SELECT userid FROM user WHERE mail=:mail';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':mail', $_SESSION['login'], PDO::PARAM_STR);
        $stmt->execute();
        $results = $stmt->fetchAll(); 
        foreach ($results as $row){
            $myuserid = $row["userid"];
        }
        //ログアウト
        if(isset($_POST["logout"])){
            $_SESSION = array();
            if (ini_get("session.use_cookies")) {
                setcookie(session_name(), '', time() - 42000, '/');
            }
            session_destroy();
            header("Location: login.php");
            exit();
        }
        //chatを作成
        $sql = "CREATE TABLE IF NOT EXISTS chat"
            ." ("
            ."chatid INT AUTO_INCREMENT PRIMARY KEY,"
            ."chatname varchar(128),"
            ."date_u DATETIME"
            .");";
        $stmt = $pdo -> query($sql);
        //mychat_user1を作成
        $sql = "CREATE TABLE IF NOT EXISTS mychat_user".$myuserid
            ." ("
            ."mychatid INT AUTO_INCREMENT PRIMARY KEY,"
            ."chatid INT,"
            ."date_u DATETIME,"
            ."category INT,"
            ."FOREIGN KEY(chatid) REFERENCES chat(chatid)"
            .");";
        $stmt = $pdo -> query($sql);
        //freiend_user1を作成
        $sql = "CREATE TABLE IF NOT EXISTS friend_user".$myuserid
            ." ("
            ."friendid INT AUTO_INCREMENT PRIMARY KEY,"
            ."userid INT,"
            ."date DATETIME,"
            ."category INT,"
            ."FOREIGN KEY(userid) REFERENCES user(userid)"
            .");";
        $stmt = $pdo -> query($sql);
        $newdate = date("Y-m-d H:i:s");
        //左側
        //チャットを作る
        if(isset($_POST["chatname"])){
            if($_POST["chatname"]!="" && !isset($_POST["back_makechatname"])){
                //chatid作成
                $sql = 'SELECT chatid FROM chat';
                $stmt = $pdo -> query($sql);
                $results = $stmt -> fetchALL();
                $i = 0;
                foreach($results as $row){
                    if($row['chatid'] > $i){
                        $i = $row['chatid'];
                    }
                }
                $newchatid = $i + 1;
                //chatに記入
                $sql = $pdo -> prepare("INSERT INTO chat (chatid, chatname, date_u) VALUES (:chatid, :chatname, :date_u)");
                $sql -> bindParam(':chatid', $chatid, PDO::PARAM_STR);
                $sql -> bindParam(':chatname', $chatname, PDO::PARAM_STR);
                $sql -> bindParam(':date_u', $date, PDO::PARAM_STR);
                $chatid = $newchatid;
                $chatname = $_POST["chatname"];
                $date = $newdate; 
                $sql -> execute();
                //member_chat1を作成
                $sql = "CREATE TABLE IF NOT EXISTS member_chat".$newchatid
                    ." ("
                    ."memberid INT AUTO_INCREMENT PRIMARY KEY,"
                    ."userid INT,"
                    ."date DATETIME,"
                    ."category INT,"
                    ."FOREIGN KEY(userid) REFERENCES user(userid)"
                    .");";
                $stmt = $pdo -> query($sql);
                //member_chat1に記入
                $sql = $pdo -> prepare("INSERT INTO member_chat".$newchatid." (userid, date, category) VALUES (:userid, :date, :category)");
                $sql -> bindParam(':userid', $userid, PDO::PARAM_INT);
                $sql -> bindParam(':date', $date, PDO::PARAM_STR);
                $sql -> bindParam(':category', $category, PDO::PARAM_INT);
                $userid = $myuserid;
                $date = $newdate; 
                $category = 1;
                $sql -> execute();
                //mychat_user1に記入
                $sql = $pdo -> prepare("INSERT INTO mychat_user".$myuserid." (chatid, date_u, category) VALUES (:chatid, :date_u, :category)");
                $sql -> bindParam(':chatid', $chatid, PDO::PARAM_INT);
                $sql -> bindParam(':date_u', $date_u, PDO::PARAM_STR);
                $sql -> bindParam(':category', $category, PDO::PARAM_INT);
                $chatid = $newchatid;
                $date_u = $newdate;
                $category = 1;
                $sql -> execute();
                //comment_chat1を作成
                $sql = "CREATE TABLE IF NOT EXISTS comment_chat".$newchatid
                    ." ("
                    ."commentid INT AUTO_INCREMENT PRIMARY KEY,"
                    ."comment varchar(4000),"
                    ."userid INT,"
                    ."date_f DATETIME,"
                    ."date_u DATETIME,"
                    ."category INT,"
                    ."FOREIGN KEY(userid) REFERENCES user(userid)"
                    .");";
                $stmt = $pdo -> query($sql);
            } else{
                $n_chatname = 1;
            }
        }
        //mychatidsを作成
        $sql = 'SELECT chatid FROM mychat_user'.$myuserid;
        $stmt = $pdo -> query($sql);
        $results = $stmt -> fetchALL();
        $mychatids = array();
        foreach($results as $row){
            array_push($mychatids, $row['chatid']);
        }
        //ニックネーム変更
        if(isset($_POST["nickname"]) && !isset($_POST["back_makecreatedid"])){
            if($_POST["nickname"] != ""){
                $sql = 'UPDATE user SET nickname=:nickname WHERE userid=:userid';
                $stmt = $pdo->prepare($sql);
                $stmt -> bindParam(':nickname', $_POST["nickname"], PDO::PARAM_STR);
                $stmt -> bindParam(':userid', $myuserid, PDO::PARAM_INT);
                $stmt -> execute();
            } else{
                $n_nickname = 1;
            }
        }
        //ID変更
        if(isset($_POST["createdid"])){
            if($_POST["createdid"] != "" && !isset($_POST["back_makecreatedid"])){
                //すでに使われていないか
                $sql = 'SELECT createdid FROM user';
                $stmt = $pdo -> query($sql);
                $results = $stmt -> fetchALL();
                foreach($results as $row){
                    if($row['createdid'] == $_POST["createdid"]){
                        $exi_createdid = 0;
                    } 
                }
                if(!isset($createdid)){
                    $sql = 'UPDATE user SET createdid=:createdid WHERE userid=:userid';
                    $stmt = $pdo -> prepare($sql);
                    $stmt -> bindParam(':createdid', $_POST["createdid"], PDO::PARAM_STR);
                    $stmt -> bindParam(':userid', $myuserid, PDO::PARAM_INT);
                    $stmt -> execute();
                } else{
                    $w_createdid = $_POST["createdid"];
                }
            } else{
                $n_createdid = 1;
            }
        }
        //友達検索
        if(isset($_POST["friendid"])){
            if($_POST["friendid"] != "" && !isset($_POST["back_friendid"])){
                //自分のIDではないか
                $sql = 'SELECT createdid FROM user WHERE userid=:userid';
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(':userid', $myuserid, PDO::PARAM_INT);
                $stmt -> execute();
                $results = $stmt->fetchAll();
                foreach($results as $row){
                    if($row['createdid'] == $_POST["friendid"]){
                        $searchfriendid = 0;
                    } 
                }
                if(!isset($searchfriendid)){
                    //IDがヒットするか
                    $sql = 'SELECT userid, createdid FROM user';
                    $stmt = $pdo -> query($sql);
                    $results = $stmt -> fetchAll();
                    foreach($results as $row){
                        if($row['createdid'] == $_POST["friendid"]){
                            $makefriendid = $row["userid"];
                            $searchfriendid = 1;
                        }
                    }
                    if(isset($searchfriendid)){
                        //すでに登録済みではないか
                        $sql = 'SELECT userid FROM friend_user'.$myuserid;
                        $stmt = $pdo -> query($sql);
                        $results = $stmt -> fetchAll();
                        foreach ($results as $row){
                            if($row["userid"] == $makefriendname){
                                $searchfriendid = 0;
                            }
                        }
                        if($searchfriendid == 1){
                            $sql = $pdo -> prepare("INSERT INTO friend_user".$myuserid." (userid, date, category) VALUES (:userid, :date, :category)");
                            $sql -> bindParam(':userid', $userid, PDO::PARAM_STR);
                            $sql -> bindParam(':date', $date, PDO::PARAM_STR);
                            $sql -> bindParam(':category', $category, PDO::PARAM_INT);
                            $userid = $makefriendid;
                            $date = $newdate;
                            $category = 1;
                            $sql -> execute();
                            //freiend_user2を作成
                            $sql = "CREATE TABLE IF NOT EXISTS friend_user".$makefriendid
                                ." ("
                                ."friendid INT AUTO_INCREMENT PRIMARY KEY,"
                                ."userid INT,"
                                ."date DATETIME,"
                                ."category INT,"
                                ."FOREIGN KEY(userid) REFERENCES user(userid)"
                                .");";
                            $stmt = $pdo -> query($sql);
                            $sql = 'SELECT userid FROM friend_user'.$makefriendid;
                            $stmt = $pdo -> query($sql);
                            $results = $stmt -> fetchALL();
                            foreach($results as $row){
                                if($row["userid"] == $myuserid){
                                    $exi_friend = 1;
                                }
                            }
                            if(!isset($exi_friend)){
                                $sql = $pdo -> prepare("INSERT INTO friend_user".$makefriendid." (userid, date, category) VALUES (:userid, :date, :category)");
                                $sql -> bindParam(':userid', $userid, PDO::PARAM_STR);
                                $sql -> bindParam(':date', $date, PDO::PARAM_STR);
                                $sql -> bindParam(':category', $category, PDO::PARAM_INT);
                                $userid = $myuserid;
                                $date = $newdate;
                                $category = 0;
                                $sql -> execute();
                            }
                            //comment_user1and2を作成
                            if($myuserid < $makefriendid){
                                $id_1 = $myuserid;
                                $id_2 = $makefriendid;
                            } else{
                                $id_1 = $makefriendid;
                                $id_2 = $myuserid;
                            }
                            $sql = "CREATE TABLE IF NOT EXISTS comment_user".$id_1."and".$id_2
                                ." ("
                                ."commentid INT AUTO_INCREMENT PRIMARY KEY,"
                                ."comment varchar(4000),"
                                ."userid INT,"
                                ."date_f DATETIME,"
                                ."date_u DATETIME,"
                                ."category INT,"
                                ."FOREIGN KEY(userid) REFERENCES user(userid)"
                                .");";
                            $stmt = $pdo -> query($sql);
                            //$message_1 = "友だちを追加しました。";
                        } else{
                            $message_1 = "このIDのユーザーは登録できません。";
                            $w_friendid = $_POST["friendid"];
                        }
                    } else{
                        $message_1 = "このIDのユーザーは存在しません。";
                        $w_friendid = $_POST["friendid"];
                    }
                } else{
                    $message_1 = "このIDは不正です。";
                    $w_friendid = $_POST["friendid"];
                }
            } else{
                $n_friendid = 1;
            }
        }
        //myfriendidsを作成
        $sql = 'SELECT userid FROM friend_user'.$myuserid;
        $stmt = $pdo -> query($sql);
        $results = $stmt -> fetchALL();
        $myfriendids = array();
        foreach($results as $row){
            array_push($myfriendids, $row['userid']);
        }
        foreach($myfriendids as $myfriendid){
            //友達承認
            if(isset($_POST["ok_user".$myfriendid])){
                $category = 1;
                $userid = $myfriendid;
                $sql = 'UPDATE friend_user'.$myuserid.' SET category=:category WHERE userid=:userid';
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(':category', $category, PDO::PARAM_INT);
                $stmt -> bindParam(':userid', $userid, PDO::PARAM_INT);
                $stmt -> execute();
            //友達拒否
            } elseif(isset($_POST["ng_user".$myfriendid])){
                $userid = $myfriendid;
                $sql = 'delete from friend_user'.$myuserid.' where userid=:userid';
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(':userid', $userid, PDO::PARAM_INT);
                $stmt -> execute();
            }
            //友達チャットへ
            if($myuserid < $myfriendid){
                $id_1 = $myuserid;
                $id_2 = $myfriendid;
            } else{
                $id_1 = $myfriendid;
                $id_2 = $myuserid;
            }
            if(isset($_POST["comment_user".$id_1."and".$id_2])){
                $friendchatid = $myfriendid;
            }
            //友達をチャットに追加
            foreach($mychatids as $mychatid){
                if(isset($_POST["add_chat".$mychatid."_".$myfriendid])){
                    $sql = 'SELECT userid FROM member_chat'.$mychatid;
                    $stmt = $pdo -> query($sql);
                    $results = $stmt -> fetchAll();
                    foreach ($results as $row){
                        if($row["userid"] == $myfriendid){
                            $exi_chat_myfriendid = $mychatid;
                        }
                    }
                    if(!isset($exi_chat_myfriendid)){
                        $sql = $pdo -> prepare("INSERT INTO member_chat".$mychatid." (userid, date, category) VALUES (:userid, :date, :category)");
                        $sql -> bindParam(':userid', $userid, PDO::PARAM_STR);
                        $sql -> bindParam(':date', $date, PDO::PARAM_STR);
                        $sql -> bindParam(':category', $category, PDO::PARAM_INT);
                        $userid = $myfriendid;
                        $date = $newdate;
                        $category = 0;
                        $sql -> execute();
                        $sql = $pdo -> prepare("INSERT INTO mychat_user".$myfriendid." (chatid, date_u, category) VALUES (:chatid, :date_u, :category)");
                        $sql -> bindParam(':chatid', $chatid, PDO::PARAM_INT);
                        $sql -> bindParam(':date_u', $date, PDO::PARAM_STR);
                        $sql -> bindParam(':category', $category, PDO::PARAM_INT);
                        $chatid = $mychatid;
                        $date = $newdate;
                        $category = 0;
                        $sql -> execute();
                    }
                }
            }
        }
        //チャット追加承認
        foreach($mychatids as $mychatid){
            if(isset($_POST["ok_chat".$mychatid])){
                $category = 1;
                $sql = 'UPDATE mychat_user'.$myuserid.' SET category=:category, date_u=:date_u WHERE chatid=:chatid';
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(':category', $category, PDO::PARAM_INT);
                $stmt -> bindParam(':date_u', $newdate, PDO::PARAM_STR);
                $stmt -> bindParam(':chatid', $mychatid, PDO::PARAM_INT);
                $stmt -> execute();
                $chatid = $mychatid;
                $sql = 'UPDATE member_chat'.$mychatid.' SET category=:category, date=:date WHERE userid=:userid';
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(':category', $category, PDO::PARAM_INT);
                $stmt -> bindParam(':date', $newdate, PDO::PARAM_STR);
                $stmt -> bindParam(':userid', $myuserid, PDO::PARAM_INT);
                $stmt -> execute();
            //チャット追加拒否
            } elseif(isset($_POST["ng_chat".$mychatid])){
                $sql = 'delete from mychat_user'.$myuserid.' where chatid=:chatid';
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(':chatid', $mychatid, PDO::PARAM_INT);
                $stmt -> execute();
                $sql = 'delete from member_chat'.$mychatid.' where userid=:userid';
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(':userid', $myuserid, PDO::PARAM_INT);
                $stmt -> execute();
            }
        }
        //チャット名編集
        if(isset($_POST["edi_chatid"])){
            $chatid = $_POST["edi_chatid"];
            $chatname = $_POST["edi_chatname"];
            $sql = 'UPDATE chat SET chatname=:chatname WHERE chatid=:chatid';
            $stmt = $pdo->prepare($sql);
            $stmt -> bindParam(':chatname', $chatname, PDO::PARAM_STR);
            $stmt -> bindParam(':chatid', $chatid, PDO::PARAM_INT);
            $stmt -> execute();
        }
        //右側を開く
        foreach($mychatids as $mychatid){
            if(isset($_POST["chat".$mychatid])){
                $oldchatid = $mychatid;
            }
        }
        foreach($myfriendids as $myfriendid){
            if(isset($_POST["comment_user".$myfriendid])){
                $friendchatid = $myfriendid;
            }
        }
        //右側
        if(isset($_POST["oldchatid"])){
            $oldchatid = $_POST['oldchatid'];
            //画像投稿
            if(isset($_POST["image"])){
                
            }
            //コメントしたとき
            if(isset($_POST['comment']) && $_POST['comment']!=""){
                //新規
                if($_POST['edi_commentid'] == ""){
                    $sql = $pdo -> prepare("INSERT INTO comment_chat".$oldchatid." (comment, userid, date_f, date_u, category) VALUES (:comment, :userid, :date_f, :date_u, :category)");
                    $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                    $sql -> bindParam(':userid', $userid, PDO::PARAM_INT);
                    $sql -> bindParam(':date_f', $date_f, PDO::PARAM_STR);
                    $sql -> bindParam(':date_u', $date_u, PDO::PARAM_STR);
                    $sql -> bindParam(':category', $category, PDO::PARAM_INT);
                    $comment = $_POST['comment'];
                    $userid = $myuserid;
                    $date_f = $newdate;
                    $date_u = $newdate;
                    if(isset($_POST["important"])){
                        $category = 1;
                    } else{
                        $category = 0;
                    }
                    $sql -> execute();
                //コメント編集
                } else{
                    if(isset($_POST["important"])){
                        $important = 1;
                    } else{
                        $important = 0;
                    }
                    $comment = $_POST['comment'];
                    $commentid = $_POST['edi_commentid'];
                    $category = $important;
                    $sql = 'UPDATE comment_chat'.$oldchatid.' SET comment=:comment, date_u=:date_u, category=:category WHERE commentid=:commentid';
                    $stmt = $pdo->prepare($sql);
                    $stmt -> bindParam(':comment', $comment, PDO::PARAM_STR);
                    $stmt -> bindParam(':date_u', $newdate, PDO::PARAM_STR);
                    $stmt -> bindParam(':category', $category, PDO::PARAM_INT);
                    $stmt -> bindParam(':commentid', $commentid, PDO::PARAM_INT);
                    $stmt -> execute();
                }
                //時間更新
                $sql = 'UPDATE chat SET date_u=:date_u WHERE chatid=:chatid';
                $stmt = $pdo->prepare($sql);
                $stmt -> bindParam(':date_u', $newdate, PDO::PARAM_STR);
                $stmt -> bindParam(':chatid', $oldchatid, PDO::PARAM_INT);
                $stmt -> execute();
                $sql = 'UPDATE mychat_user'.$myuserid.' SET date_u=:date_u WHERE chatid=:chatid';
                $stmt = $pdo->prepare($sql);
                $stmt -> bindParam(':date_u', $newdate, PDO::PARAM_STR);
                $stmt -> bindParam(':chatid', $oldchatid, PDO::PARAM_INT);
                $stmt -> execute();
                $sql = 'UPDATE user SET date_u=:date_u WHERE userid=:userid';
                $stmt = $pdo->prepare($sql);
                $stmt -> bindParam(':date_u', $newdate, PDO::PARAM_STR);
                $stmt -> bindParam(':userid', $myuserid, PDO::PARAM_INT);
                $stmt -> execute();
            }
            //チャット名編集用意
            if(isset($_POST["edi_chatname".$oldchatid])){
                $sql = 'SELECT chatid, chatname FROM chat WHERE chatid=:chatid';
                $stmt = $pdo -> prepare($sql);
                $stmt-> bindParam(':chatid', $oldchatid, PDO::PARAM_INT);
                $stmt-> execute();
                $results = $stmt->fetchAll();
                foreach($results as $row){
                    $edi_chatname = $row["chatname"];
                    $edi_chatid = $row["chatid"];
                }
            }
            //コメント編集用意
            $sql = 'SELECT commentid, comment, category FROM comment_chat'.$oldchatid;
            $stmt = $pdo -> query($sql);
            $results = $stmt -> fetchALL();
            foreach($results as $row){
                if(isset($_POST["edi_chat".$oldchatid."_".$row["commentid"]])){
                    $edi_comment = $row["comment"];
                    $edi_commentid = $row["commentid"];
                    if($row["category"] == 1){
                        $edi_important = 1;
                    }
                }
            }
            //コメント削除
            foreach($results as $row){
                if(isset($_POST["del_chat".$oldchatid."_".$row["commentid"]])){
                    $commentid = $row["commentid"];
                    $sql = 'delete from comment_chat'.$oldchatid.' where commentid=:commentid';
                    $stmt = $pdo->prepare($sql);
                    $stmt -> bindParam(':commentid', $commentid, PDO::PARAM_INT);
                    $stmt -> execute();
                }
            }
            //重要のみ
            if(isset($_POST["list_important"])){
                $category = 1;
                $sql = 'SELECT commentid FROM comment_chat'.$oldchatid.' WHERE category=:category';
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(':category', $category, PDO::PARAM_INT);
                $stmt -> execute();  
                $results = $stmt -> fetchALL();
                $importants = array();
                foreach($results as $row){
                    array_push($importants, $row['commentid']);
                }
            }
            //退出
            foreach($mychatids as $mychatid){
                if(isset($_POST["exit_chat".$mychatid])){
                    $sql = 'delete from mychat_user'.$myuserid.' where chatid=:chatid';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':chatid', $oldchatid, PDO::PARAM_INT);
                    $stmt->execute();
                    $sql = 'delete from member_chat'.$oldchatid.' where userid=:userid';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':userid', $myuserid, PDO::PARAM_INT);
                    $stmt->execute();
                }
            }
            //画像
            if(isset($_POST["image"])){
                $image_name = uniqid(mt_rand(), true);//ファイル名をユニーク化
                $image_address = $image_name.".".substr(strrchr($_FILES["image"]['name'], "."), 1);//アップロードされたファイルの拡張子を取得
                $file = "image/$image_address";
                $sql = $pdo -> prepare("INSERT INTO comment_chat".$oldchatid." (userid, image, date_f, date_u, category) VALUES (:userid, :image, :date_f, :date_u, :category)");
                $sql -> bindParam(':userid', $myuserid, PDO::PARAM_INT);
                $sql -> bindParam(':image', $image_address, PDO::PARAM_STR);
                $sql -> bindParam(':date_f', $date_f, PDO::PARAM_STR);
                $sql -> bindParam(':date_u', $date_u, PDO::PARAM_STR);
                $sql -> bindParam(':category', $category, PDO::PARAM_INT);
                $sql -> execute();
                if (!empty($_FILES['image']['name'])) {//ファイルが選択されていれば$imageにファイル名を代入
                    move_uploaded_file($_FILES['image']['tmp_name'], './image/'.$image_address);//imagesディレクトリにファイル保存
                    if (exif_imagetype($file)) {//画像ファイルかのチェック
                        $message = '画像をアップロードしました';
                        $stmt->execute();
                    } else {
                        $message = '画像ファイルではありません';
                    }
                }
            }
        }
        //友達チャット
        if(isset($_POST["friendchatid"])){
            $friendchatid = $_POST["friendchatid"];
            if($myuserid < $friendchatid){
                $id_1 = $myuserid;
                $id_2 = $friendchatid;
            } else{
                $id_1 = $friendchatid;
                $id_2 = $myuserid;
            }
            //コメントしたとき
            if(isset($_POST['comment'])){
                //新規
                if($_POST['edi_commentid'] == ""){
                    $sql = $pdo -> prepare("INSERT INTO comment_user".$id_1."and".$id_2." (comment, userid, date_f, date_u, category) VALUES (:comment, :userid, :date_f, :date_u, :category)");
                    $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                    $sql -> bindParam(':userid', $userid, PDO::PARAM_INT);
                    $sql -> bindParam(':date_f', $date_f, PDO::PARAM_STR);
                    $sql -> bindParam(':date_u', $date_u, PDO::PARAM_STR);
                    $sql -> bindParam(':category', $category, PDO::PARAM_INT);
                    $comment = $_POST['comment'];
                    $userid = $myuserid;
                    $date_f = $newdate;
                    $date_u = $newdate;
                    if(isset($_POST["important"])){
                        $category = 1;
                    } else{
                        $category = 0;
                    }
                    $sql -> execute();
                //コメント編集
                } else{
                    if(isset($_POST["important"])){
                        $important = 1;
                    } else{
                        $important = 0;
                    }
                    $comment = $_POST['comment'];
                    $commentid = $_POST['edi_commentid'];
                    $category = $important;
                    $sql = 'UPDATE comment_user'.$id_1.'and'.$id_2.' SET comment=:comment, date_u=:date_u, category=:category WHERE commentid=:commentid';
                    $stmt = $pdo->prepare($sql);
                    $stmt -> bindParam(':comment', $comment, PDO::PARAM_STR);
                    $stmt -> bindParam(':date_u', $newdate, PDO::PARAM_STR);
                    $stmt -> bindParam(':category', $category, PDO::PARAM_INT);
                    $stmt -> bindParam(':commentid', $commentid, PDO::PARAM_INT);
                    $stmt -> execute();
                }
                //時間変更
                $sql = 'UPDATE user SET date_u=:date_u WHERE userid=:userid';
                $stmt = $pdo->prepare($sql);
                $stmt -> bindParam(':date_u', $newdate, PDO::PARAM_STR);
                $stmt -> bindParam(':userid', $myuserid, PDO::PARAM_INT);
                $stmt -> execute();
            }
            //コメント編集用意
            $sql = 'SELECT commentid, comment, category FROM comment_user'.$id_1.'and'.$id_2;
            $stmt = $pdo -> query($sql);
            $results = $stmt -> fetchALL();
            foreach($results as $row){
                if(isset($_POST["edi_chat".$friendchatid."_".$row["commentid"]])){
                    $edi_comment = $row["comment"];
                    $edi_commentid = $row["commentid"];
                    if($row["category"] == 1){
                        $edi_important = 1;
                    }
                }
            }
            //重要のみ
            if(isset($_POST["list_important"])){
                $category = 1;
                $sql = 'SELECT commentid FROM comment_user'.$id_1.'and'.$id_2.' WHERE category=:category ';
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(':category', $category, PDO::PARAM_INT);
                $stmt -> execute();  
                $results = $stmt -> fetchALL();
                $importants = array();
                foreach($results as $row){
                    array_push($importants, $row['commentid']);
                }
            }
        }
        ?>
        <div class="wide">
            <div class="leftside">
                <h1>
                    <?php
                    //ニックネーム
                    $sql = 'SELECT userid, name, nickname FROM user';
                    $stmt = $pdo -> query($sql);
                    $results = $stmt -> fetchALL();
                    foreach($results as $row){
                        if($row['userid'] == $myuserid){
                            if($row['nickname'] != NULL){
                                echo $row['nickname'];
                                $nickname = $row['nickname'];
                            } else{
                                echo $row['name'];
                            }
                        }
                    }
                    ?>
                </h1>
                <!--ログアウト-->
                <form action="" method="post">
                    <button type="submit" name="logout">ログアウト</button>
                </form>
                <?php
                //ニックネーム編集
                if(!isset($_POST["makenickname"]) && !isset($n_nickname) || isset($_POST["back_nickname"])){
                ?>
                    <form action="" method="post">
                        <button type="submit" name="makenickname">
                            ユーザー名を変更する
                        </button>
                    </form>
                <?php    
                } else{
                ?>
                    <form action="" method="post">
                        <input type="text" name="nickname" <?php //if(isset($nickname)){ echo "value='".$nickname."'" ;} ?> placeholder="ユーザー名" ><br>
                        <?php
                        if(isset($n_nickname)){
                            echo "入力してください。<br>";
                        }
                        ?>
                        <input type="submit" name="back_nickname" value="戻る">
                        <input type="submit" name="submit" value="登録">
                    </form>
                <?php
                }
                //ID表示
                $sql = 'SELECT createdid FROM user WHERE userid=:userid';
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(':userid', $myuserid, PDO::PARAM_INT);
                $stmt -> execute();
                $results = $stmt->fetchAll();
                foreach($results as $row){
                    if($row["createdid"] != NULL){
                        echo "<h3>";
                        echo "ID：".$row["createdid"];
                        echo "</h3>";
                        $exi_createdid = $row["createdid"];
                    }
                }
                //ID編集
                if(!isset($_POST["makecreatedid"]) && !isset($w_createdid) && !isset($n_createdid) || isset($_POST["back_makecreatedid"])){
                ?>
                    <form action="" method="post">
                        <button type="submit" name="makecreatedid">
                            ユーザーIDを<?php if(isset($exi_createdid)){ echo "変更" ;} else { echo "設定" ;} ?>する
                        </button>
                    </form>
                <?php    
                } else{
                ?>
                    <form action="" method="post">
                        <input type="text" name="createdid" <?php if(isset($w_createdid)){ echo "value='".$w_createdid."'" ;} ?> placeholder="ユーザーID" ><br>
                        <?php
                        if(isset($n_createdid)){
                            echo "入力してください。<br>";
                        }
                        ?>
                        <input type="submit" name="back_makecreatedid" value="戻る">
                        <input type="submit" name="submit" value="登録">
                    </form>
                <?php
                }
                ?>
                <hr>
                <h2>友だち</h2>
                <?php
                //友達追加
                if(!isset($_POST["searchfriendid"]) && !isset($w_friendid) && !isset($n_friendid) || isset($_POST["back_friendid"])){
                ?>
                    <form action="" method="post">
                        <button type="submit" name="searchfriendid">
                            友だち追加
                        </button>
                    </form>
                <?php
                } else{
                ?>
                    <form action="" method="post">
                        <input type="text" name="friendid" <?php if(isset($w_friendid)){ echo "value='".$w_friendid."'" ;} ?> placeholder="ユーザーID"  ><br> 
                        <?php
                        if(isset($n_friendid)){
                            echo "入力してください。<br>";
                        } elseif(isset($message_1)){
                            echo $message_1."<br>";
                        }
                        ?>
                        <input type="submit" name="back_friendid" value="戻る">
                        <input type="submit" name="submit" value="追加">
                    </form>
                <?php
                }
                ?>
                <?php
                    //友達一覧
                    $sql = 'SELECT userid, category FROM friend_user'.$myuserid;
                    $stmt = $pdo -> query($sql);
                    $results = $stmt -> fetchALL();
                    $sql = 'SELECT userid, name, nickname FROM user ORDER BY name';
                    $stmt = $pdo -> query($sql);
                    $lines = $stmt -> fetchALL();
                    echo "<ul>";
                    foreach($lines as $line)
                        foreach($results as $row){
                            if($row["userid"]==$line["userid"] && $row["category"]!=2){
                                echo "<li>";
                                echo "<form action='' method='post'>";
                                if($row["category"] == 0){
                                    echo "[友だち申請]";
                                }
                                if($line["nickname"] != NULL){
                                    echo $line["nickname"];
                                } else{
                                    echo $line["name"];
                                }
                                if($row["category"] == 1){
                                    echo "<input type='submit' name='comment_user".$line['userid']."' value='チャット'>";
                                } elseif($row["category"] == 0){
                                    echo "<input type='submit' name='ok_user".$line['userid']."' value='承諾'>";
                                    echo "<input type='submit' name='ng_user".$line['userid']."' value='拒否'>";
                                }
                                echo "</form>";
                                echo "</li>";
                            }
                    }
                    echo "</ul>";
                    ?>
                <hr>
                <h2>チャット</h2>
                <?php
                if(!isset($_POST["makechat"]) && !isset($n_chatname) || isset($_POST["back_makechatname"])){
                ?>        
                    <form action="" method="post">
                        <button type="submit" name="makechat">
                            チャットを作る
                        </button>
                    </form>
                <?php
                } else{
                ?>
                    <form action="" method="post">
                        <input type="text" name="chatname" placeholder="チャット名"><br>
                        <?php
                        if(isset($n_chatname)){
                            echo "入力してください。<br>";
                        }
                        ?>
                        <input type="submit" name="back_makechatname" value="戻る">
                        <input type="submit" name="submit" value="登録">
                    </form>
                <?php
                }
                ?>
                <ul>
                    <?php
                    //チャット一覧
                    $sql = 'SELECT chatid, chatname FROM chat';
                    $stmt = $pdo -> query($sql);
                    $results = $stmt -> fetchALL();
                    $sql = 'SELECT chatid, category FROM mychat_user'.$myuserid.' ORDER BY date_u DESC';
                    $stmt = $pdo -> query($sql);
                    $lists = $stmt -> fetchALL();
                    foreach($lists as $list)
                        foreach($results as $row){
                        if($row['chatid'] == $list["chatid"]){
                            echo "<li>";
                            echo "<form action='' method='post'>";
                            if(isset($edi_chatid)){
                                if($row['chatid'] == $edi_chatid){
                                    echo "[編集中]";
                                }
                            } elseif($list["category"] == 0){
                                echo "[追加申請]";
                            }
                            echo $row['chatname'];
                            if($list["category"] == 1){
                                echo "<input type='submit' name='chat".$row['chatid']."' value='チャット'>";
                                echo "<input type='submit' name='listfriend_chat".$row['chatid']."' value='友達追加'>";
                                if(isset($_POST["listfriend_chat".$row['chatid']]) || isset($exi_chat_myfriendid)){
                                    echo "<ul>";
                                    foreach($myfriendids as $myfriendid){
                                        foreach($lines as $line){
                                            if($line["userid"] == $myfriendid){
                                                echo "<li>";
                                                if($line["nickname"] != NULL){
                                                    echo $line["nickname"];
                                                } else{
                                                    echo $line["name"];
                                                }
                                                echo "<input type='submit' name='add_chat".$row['chatid']."_".$myfriendid."' value='追加'><br>";
                                                if(isset($exi_chat_myfriendid) && $exi_chat_myfriendid==$row['chatid']){
                                                    echo "この友だちはすでにチャットのメンバーです。<br>";
                                                }
                                                echo "</li>";
                                            }
                                        }
                                    }
                                    echo "</ul>";
                                }
                            } else{
                                echo "<input type='submit' name='ok_chat".$list['chatid']."' value='承諾'>";
                                echo "<input type='submit' name='ng_chat".$list['chatid']."' value='拒否'>";
                            }
                            echo "</form>";
                            echo "</li>";
                        }
                    }
                    ?>
                </ul>
                <?php
                //チャット名編集
                if(isset($edi_chatid)){
                ?>
                <footer>
                    <form action="" method="post">
                        <input type="hidden" name="edi_chatid" class="hidden" <?php if(isset($edi_chatid)){ echo "value=".$edi_chatid ;} ?>><br>
                        <input type="text" name="edi_chatname" <?php if(isset($edi_chatname)){ echo "value=".$edi_chatname; } ?> placeholder="チャット名">
                        <input type="submit" name="submit" value="送信">
                    </form>
                </footer>
                <?php
                }
                ?>
            </div>
            <div class="rightside">
                <?php
                //チャットタイトル
                if(isset($oldchatid)){
                    $sql = 'SELECT chatname FROM chat WHERE chatid=:chatid';
                    $stmt = $pdo->prepare($sql);
                    $stmt-> bindParam(':chatid', $oldchatid, PDO::PARAM_INT);
                    $stmt-> execute();   
                    $results = $stmt->fetchAll();
                    foreach ($results as $row){
                ?>
                        <h1>
                            <?php        
                            echo $row['chatname'];
                            ?>
                        </h1>
                        <form action="" method="post">
                        <input type="checkbox" name="list_important" <?php if(isset($_POST["list_important"])){ echo "checked";} ?>>
                        <label for="list_important" >重要のみ</label>
                        <input type="submit" value="実行">
                        /
                        <input type="submit" <?php echo "name='list_member_chat".$oldchatid."'" ?> value="メンバー一覧">
                        /
                        <input type="submit" <?php echo "name='edi_chatname".$oldchatid."'" ?> value="チャット名変更">
                        /
                        <input type="submit" <?php echo "name='exit_chat".$oldchatid."'" ?> value="チャットから退出">
                        <input type="hidden" name="oldchatid" class='hidden' <?php echo "value='".$oldchatid."'" ?>>
                        </form>
                <?php
                    }
                    echo "<hr>";
                    //チャットメンバー一覧
                    if(isset($_POST["list_member_chat".$oldchatid])){
                        $sql = 'SELECT userid, category FROM member_chat'.$oldchatid;
                        $stmt = $pdo->query($sql);
                        $results = $stmt->fetchAll();
                        foreach ($results as $row){
                            if($row["category"] == 0){
                                echo "[未承諾]";
                            }
                            $sql = 'SELECT name, nickname FROM user WHERE userid=:userid ';
                            $stmt = $pdo->prepare($sql); 
                            $stmt->bindParam(':userid', $row['userid'], PDO::PARAM_INT); 
                            $stmt->execute();
                            $lines = $stmt->fetchAll();
                            foreach ($lines as $line){
                                if($line["nickname"] != NULL){
                                    echo $line["nickname"];
                                } else{
                                    echo $line["name"];
                                }
                            }
                            echo " / ";
                        }
                        echo "<hr>";
                    }
                    //チャットコメント一覧
                    $sql = 'SELECT commentid, userid, comment, date_u, category FROM comment_chat'.$oldchatid;
                    $stmt = $pdo -> query($sql);
                    $results = $stmt -> fetchALL();
                    $sql = 'SELECT userid, name, nickname FROM user';
                    $stmt = $pdo -> query($sql);
                    $lines = $stmt -> fetchALL();
                    echo "<form action='' method='post'>";
                    if(isset($_POST["list_important"])){
                        foreach($importants as $important){
                            foreach($results as $row){
                                if($row["commentid"] == $important){    
                                    if($row['userid'] == $myuserid){
                                        echo "<div class='left'>";
                                    } else{
                                        echo "<div>";
                                    }
                                    //if(isset($edi_commentid)){
                                    //    if($edi_commentid == $row['commentid']){
                                    //        echo "[編集中]";
                                    //    }
                                    //}
                                    if($row['userid'] != $myuserid){
                                        foreach($lines as $line){
                                            if($row['userid'] == $line['userid']){
                                                if($line['nickname'] != NULL){
                                                    echo $line['nickname'];
                                                } else{
                                                    echo $line['name'];
                                                }
                                            }
                                        }
                                    }
                                    echo "　「";
                                    echo $row['comment'];
                                    echo "」 ";
                                    echo $row['date_u'];
                                    //if($row['userid'] == $myuserid){
                                    //    echo "<input type='submit' name='edi_chat".$oldchatid."_".$row['commentid']."' value='編集'>";
                                    //    echo "<input type='submit' name='del_chat".$oldchatid."_".$row['commentid']."' value='削除'>";
                                    //}
                                    echo "</div>";
                                }
                            }
                        }
                    } else{
                        foreach($results as $row){
                            if($row['userid'] == $myuserid){
                                echo "<div class='left'>";
                            } else{
                                echo "<div>";
                            }
                            if(isset($edi_commentid)){
                                if($edi_commentid == $row['commentid']){
                                    echo "[編集中]";
                                }
                            }
                            if($row['userid'] != $myuserid){
                                foreach($lines as $line){
                                    if($row['userid'] == $line['userid']){
                                        if($line['nickname'] != NULL){
                                            echo $line['nickname'];
                                        } else{
                                            echo $line['name'];
                                        }
                                    }
                                }
                            }
                            echo "　「";
                            if($row["category"] == 1){
                                echo "<b>";
                                echo $row['comment'];
                                echo "</b>";
                            } else{
                                echo $row['comment'];
                            }
                            echo "」　";
                            echo $row['date_u'];
                            if($row['userid'] == $myuserid){
                                echo "<input type='submit' name='edi_chat".$oldchatid."_".$row['commentid']."' value='編集'>";
                                echo "<input type='submit' name='del_chat".$oldchatid."_".$row['commentid']."' value='削除'>";
                            }
                            echo "</div>";
                        }
                    }
                    echo "<input type='hidden' name='oldchatid' value=".$oldchatid.">";
                    echo "</form>";
                ?>
                    <footer>
                        <form action="" method="post" enctype="multipart/form-data">
                            <input type="file" name="image">
                            <input type="hidden" name="oldchatid" class="hidden" <?php if(isset($oldchatid)){ echo "value=".$oldchatid;} ?>>
                            <input type="hidden" name="edi_commentid" class="hidden" <?php if(isset($edi_commentid)){ echo "value=".$edi_commentid;} ?>>
                            <input type="text" class="comment" name="comment" <?php if(isset($edi_commentid)){ echo "value=".$edi_comment ;} ?> placeholder="コメント">
                            重要
                            <input type="checkbox" name="important" value="important" <?php if(isset($edi_important)){ echo "checked" ;} ?>>
                            <input type="submit" name="upload" value="送信">
                        </form>
                    </footer>
                <?php
                }
                ?>
                <?php
                //友達ニックネーム名
                if(isset($friendchatid)){
                    $sql = 'SELECT name, nickname FROM user WHERE userid=:userid';
                    $stmt = $pdo->prepare($sql);
                    $stmt-> bindParam(':userid', $friendchatid, PDO::PARAM_INT);
                    $stmt-> execute();   
                    $lines = $stmt->fetchAll();
                    foreach ($lines as $line){
                ?>
                        <h1>
                            <?php        
                            if($line['nickname'] != NULL){
                                echo $line['nickname'];
                            } else{
                                echo $line['name'];
                            }
                            ?>
                        </h1>
                        <form action="" method="post">
                            <input type="checkbox" name="list_important" <?php if(isset($_POST["list_important"])){ echo "checked";} ?>>
                            <label for="list_important" >重要のみ</label>
                            <input type="submit" value="実行">
                            <input type="hidden" name="friendchatid" class='hidden' <?php echo "value='".$friendchatid."'" ?>>
                        </form>
                <?php
                    }
                    echo "<hr>";
                    //コメント一覧
                    if($myuserid < $friendchatid){
                        $id_1 = $myuserid;
                        $id_2 = $friendchatid;
                    } else{
                        $id_1 = $friendchatid;
                        $id_2 = $myuserid;
                    }
                    $sql = 'SELECT commentid, comment, userid, date_u, category FROM comment_user'.$id_1.'and'.$id_2;
                    $stmt = $pdo -> query($sql);
                    $results = $stmt -> fetchALL();
                    $sql = 'SELECT userid, name, nickname FROM user';
                    $stmt = $pdo -> query($sql);
                    $lines = $stmt -> fetchALL();
                    echo "<form action='' method='post'>";
                    if(isset($_POST["list_important"])){
                        foreach($importants as $important){
                            foreach($results as $row){
                                if($row["commentid"] == $important){
                                    if($row['userid'] == $myuserid){
                                        echo "<div class='left'>";
                                    } else{
                                        echo "<div>";
                                    }
                                    //if(isset($edi_commentid)){
                                    //    if($edi_commentid == $row['commentid']){
                                    //        echo "[編集中]";
                                    //    }
                                    //}
                                    foreach($lines as $line){
                                        if($row['userid'] == $line['userid']){
                                            if($line['nickname'] != NULL){
                                                echo $line['nickname'];
                                            } else{
                                                echo $line['name'];
                                            }
                                        }
                                    }
                                    echo "　「";
                                    echo $row['comment'];
                                    echo "」　";
                                    echo $row['date_u'];
                                    //if($row['userid'] == $myuserid){
                                    //    echo "<input type='submit' name='edi_chat".$oldchatid."_".$row['commentid']."' value='編集'>";
                                    //    echo "<input type='submit' name='del_chat".$oldchatid."_".$row['commentid']."' value='削除'>";
                                    //}
                                    echo "</div>";
                                }
                            }
                        }
                    } else{
                        foreach($results as $row){
                            if($row['userid'] == $myuserid){
                                echo "<div class='left'>";
                            } else{
                                echo "<div>";
                            }
                            if(isset($edi_commentid)){
                                if($edi_commentid == $row['commentid']){
                                    echo "[編集中]";
                                }
                            }
                            if($row['userid'] != $myuserid){
                                foreach($lines as $line){
                                    if($row['userid'] == $line['userid']){
                                        if($line['nickname'] != NULL){
                                            echo $line['nickname'];
                                        } else{
                                            echo $line['name'];
                                        }
                                    }
                                }
                            }
                            echo "　「";
                            if($row["category"] == 1){
                                echo "<b>";
                                echo $row['comment'];
                                echo "</b>";
                            } else{
                                echo $row['comment'];
                            }
                            echo "」　";
                            echo $row['date_u'];
                            if($row['userid'] == $myuserid){
                                echo "<input type='submit' name='edi_chat".$friendchatid."_".$row['commentid']."' value='編集'>";
                                echo "<input type='submit' name='del_chat".$friendchatid."_".$row['commentid']."' value='削除'>";
                            }
                            echo "</div>";
                        }
                    }
                    echo "<input type='hidden' name='friendchatid' value=".$friendchatid.">";
                    echo "</form>";
                ?>
                    <footer>
                        <form action="" method="post">
                            <input type="file" name="image">
                            <input type="hidden" name="friendchatid" class="hidden" <?php if(isset($friendchatid)){ echo "value=".$friendchatid;} ?>>
                            <input type="hidden" name="edi_commentid" class="hidden" <?php if(isset($edi_commentid)){ echo "value=".$edi_commentid;} ?>>
                            <input type="text" class="comment" name="comment" <?php if(isset($edi_commentid)){ echo "value=".$edi_comment ;} ?> placeholder="コメント">
                            重要
                            <input type="checkbox" name="important" value="important" <?php if(isset($edi_important)){ echo "checked" ;} ?>>
                            <input type="submit" name="submit" value="送信"><br>
                        </form>
                    </footer>
                <?php
                }
                ?>
            </div>
        </div>
    </body>
</html>
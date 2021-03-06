<?php
    class User extends DB{
        public function checkValidUser($email, $pass = false ){
            $qr = "SELECT * FROM user where email = '$email'";
            $result = false;
            if ($pass == false)
            {
                $rows = mysqli_query($this->con, $qr);
                if ($rows->fetch_assoc() > 0) $result = true;
            }
            else {
                $rows = mysqli_query($this->con, $qr." and password = '$pass'");
                if ($rows->fetch_assoc() > 0) $result = true;
            }
            return $result;
        }
        public function getUserName($email){
            $qr = "SELECT name FROM user where email = '$email'";
            $rows = mysqli_query($this->con, $qr);
            if ($row = $rows->fetch_assoc()) 
               return $row['name'];
            return 'Cannot find name';
        }
        public function insertUser($email, $password, $gender){
            //generate id
            $rows= mysqli_query($this->con,"SELECT max(userId) as MaxId from user");
            $row = $rows->fetch_assoc();
            $newId = $row['MaxId']+1;
            $imgDefault = $gender == 'Male' ? 'public/image/male.jpg' : 'public/image/female.jpg';
            //set time
            date_default_timezone_set("Asia/Bangkok");
            $datetime = date('Y-m-d H:i:s');
            //insert
            $qr = "INSERT INTO user (userId,userName,email,gender,password,date,image) VALUES ('$newId','User name','$email','$gender','$password','$datetime','$imgDefault')";
            $result = false;
            if (mysqli_query($this->con, $qr)){
                $result = true;
            }
            return ($result);
        }
        public function getUserInfor($email){
            $qr = "SELECT * FROM user where email = '$email'";
            $rows = mysqli_query($this->con, $qr);
            $data = array();
            if ($row = mysqli_fetch_array($rows))
            {
                $data = $row;
            }
            //Gui mang cung duoc nhung gui Json sau nay nen tang khac lay du lieu ok hon
            return json_encode($data);
        }
        public function updateUser(){
            //Bi???n l??u ???????ng d???n ???nh
            $target_file = '';
            //N???u c?? g???i ???nh
            if ($_FILES['setting-image']['name'] != NULL && $_FILES['setting-image']['name'] != '')
            {
                // Ki???m tra d??? li???u c?? b??? l???i kh??ng
                if ($_FILES["setting-image"]['error'] != 0)
                {
                    return json_encode(['status'=>'error', 'message'=>'H??nh ???nh upload b??? l???i']);
                }
                //Th?? m???c b???n s??? l??u file upload
                $target_dir    = "public/image/";
                
                //V??? tr?? file l??u t???m trong server (file s??? l??u trong uploads, v???i t??n gi???ng t??n ban ?????u)
                $target_file   = $target_dir . basename($_FILES["setting-image"]["name"]);

                //L???y ph???n m??? r???ng c???a file (jpg, png, ...)
                $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);

                // C??? l???n nh???t ???????c upload (bytes)
                $maxfilesize   = 5242880;

                ////Nh???ng lo???i file ???????c ph??p upload
                $allowtypes    = array('jpg', 'png', 'jpeg', 'gif','JPG', 'PNG', 'JPEG', 'GIF');

                $allowUpload   = true;
                //Ki???m tra xem c?? ph???i l?? ???nh b???ng h??m getimagesize
                $check = getimagesize($_FILES["setting-image"]["tmp_name"]);
                if ($check == false)
                {
                    //Kh??ng ph???i file ???nh
                    $allowUpload = false;
                    return json_encode(["status"=>"error","message"=>"File upload kh??ng ph???i l?? file ???nh !!!"]);
                }
                // Ki???m tra n???u file ???? t???n t???i 
                if (file_exists($target_file))
                {
                    $target_file = $target_file . "-1";
                }
                // Ki???m tra k??ch th?????c file upload cho v?????t qu?? gi???i h???n cho ph??p
                if ($_FILES["setting-image"]["size"] > $maxfilesize)
                {
                    $allowUpload = false;
                    return json_encode(["status"=>"error","message"=>"File b???n upload V?????t qu?? 5MB !!!"]);
                }
                // Ki???m tra ki???u ???nh
                if (!in_array($imageFileType,$allowtypes))
                {
                    $allowUpload = false;
                    return json_encode(["status"=>"error","message"=>"File ???nh c???a b???n kh??ng ph?? h???p, ch??? cho ph??p 'jpg', 'png', 'jpeg', 'gif' !!!"]);
                }

                if ($allowUpload)
                {
                    // X??? l?? di chuy???n file t???m ra th?? m???c c???n l??u tr???, d??ng h??m move_uploaded_file
                    if (!move_uploaded_file($_FILES["setting-image"]["tmp_name"], $target_file))
                    {
                        return json_encode(["status"=>"error","message"=>"Kh??ng th??? di chuy???n ra th?? m???c c???n l??u tr???"]);
                    }
                }
            }

            $userEmail = $_SESSION['userEmail'];
            $name = $_POST['setting-name'];
            $gender =  $_POST['gender'];
            $pass = $_POST['setting-pass'];
            $qr = "UPDATE user SET userName = '$name',gender = '$gender'";
            if ($pass != '')
            {
                $qr = $qr." ,password = "."'$pass'";
            }
            if ($target_file != ''){
                $qr = $qr." ,image = "."'$target_file'";
            }

            $qr = $qr." WHERE email='$userEmail'";
            $rows = mysqli_query($this->con, $qr);
            $data = array (['status'=>'error','message'=>'C???p nh???t kh??ng th??nh c??ng, l???i update CSDL']);
            if ($rows)
            {
                $data = ['status'=>'success','message'=>'C???p nh???t th??nh c??ng'];
            }
            return json_encode($data) ;
            }
        
    }
?>  
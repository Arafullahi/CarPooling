<?php

require_once 'dbconfig.php';

class USER
{
	public $userID;
	public $userName;
	public $userEmail;
	public $userPass;
	public $phoneNo;
	public $paypalAccount;
	public $userStatus;
	public $tokenCode;
	public $accountLocked = "N";

	private $conn;
	public $database;

	public function __construct()
	{
		$this->database = Database::getInstance();
		$this->conn = $this->database->conn;
  }

	private function fillData($row)
	{
		$this->userID = $row['userID'];
		$this->userName = $row['userName'];
		$this->userEmail = $row['userEmail'];
		$this->userPass = $row['userPass'];
		$this->phoneNo = $row['phoneNo'];
		$this->paypalAccount = $row['paypalAccount'];
		$this->userStatus = $row['userStatus'];
		$this->tokenCode = $row['tokenCode'];
		$this->accountLocked = $row['accountLocked'];
	}

	public function update()
	{
		$stmt = $this->database->prepare("UPDATE users SET
				userPass=:userPass,
				phoneNo=:phoneNo,
				paypalAccount=:paypalAccount,
				userStatus=:userStatus,
				tokenCode=:tokenCode,
				accountLocked=:accountLocked
				WHERE userID=:uID");
		$stmt->bindparam(":userPass",$this->userPass);
		$stmt->bindparam(":phoneNo",$this->phoneNo);
		$stmt->bindparam(":paypalAccount",$this->paypalAccount);
		$stmt->bindparam(":userStatus",$this->userStatus);
		$stmt->bindparam(":tokenCode",$this->tokenCode);
		$stmt->bindparam(":accountLocked",$this->accountLocked);
		$stmt->bindparam(":uID",$this->userID);
		$stmt->execute();
	}

	public function getUserById($userId)
	{
		$rowCount = $this->database->fetch("SELECT * FROM users WHERE userID=:uid", array(":uid"=>$userId), $rows);
		$this->fillData($rows);
		return $rowCount;
	}

	public function getUserByEmail($email)
	{
		$rowCount = $this->database->fetch("SELECT * FROM users WHERE userEmail=:email_id",array(":email_id"=>$email), $rows);
		$this->fillData($rows);
		return $rowCount;
	}

	public function register($uname,$email,$upass,$code)
	{
		try
		{
			$password = md5($upass);
			$stmt = $this->database->conn->prepare("INSERT INTO users(userName,userEmail,userPass,tokenCode)
			                                             VALUES(:user_name, :user_mail, :user_pass, :active_code)");
			$stmt->bindparam(":user_name",$uname);
			$stmt->bindparam(":user_mail",$email);
			$stmt->bindparam(":user_pass",$password);
			$stmt->bindparam(":active_code",$code);
			$stmt->execute();
			//
			$this->userID=$this->database->lasdID();
			return $stmt;
		}
		catch(PDOException $ex)
		{
			echo $ex->getMessage();
		}
	}

	public function login($email,$upass)
	{
		try
		{
			$count=$this->getUserByEmail($email);
			if($count == 1)
			{
				if($this->userStatus=="Y")
				{
					if($this->userPass==md5($upass))
					{
						$_SESSION['userSession'] = $this->userID;
						return true;
					}
					else
					{
						header("Location: index.php?error");
						exit;
					}
				}
				else
				{
					header("Location: index.php?inactive");
					exit;
				}
			}
			else
			{
				header("Location: index.php?error");
				exit;
			}
		}
		catch(PDOException $ex)
		{
			echo $ex->getMessage();
		}
	}


	public function is_logged_in()
	{
		if(isset($_SESSION['userSession']))
		{
			return true;
		}
	}

	public function redirect($url)
	{
		header("Location: $url");
	}

	public function logout()
	{
		session_destroy();
		$_SESSION['userSession'] = false;
	}

	function send_mail($email,$message,$subject)
	{
		require_once('mailer/class.phpmailer.php');
		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->SMTPDebug  = 1;
<<<<<<< HEAD
		$mail->SMTPAuth   = true;
		//$mail->SMTPSecure = "";
		$mail->Host       = "smtp.Fastwebnet.it";
		$mail->Port       = 587;
		$mail->AddAddress("facebook@libraro.it");
		$mail->Username="mario.libraro";
		$mail->Password="vagxWd6ywb";
		$mail->SetFrom('facebook@libraro.it','CarPooling System');
		$mail->AddReplyTo("facebook@libraro.it","CarPooling System");
=======
		//$mail->SMTPAuth   = true;
		//$mail->SMTPSecure = "";
		$mail->Host       = "mail.libraro.it";
		$mail->Port       = 25;
		$mail->AddAddress("...");
		$mail->Username="...";
		$mail->Password="...";
		$mail->SetFrom('...','CarPooling System');
		$mail->AddReplyTo("...","CarPooling System");
>>>>>>> origin/master
		$mail->Subject    = $subject;
		$mail->MsgHTML($message);
		$mail->Send();
	}
}

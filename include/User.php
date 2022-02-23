<?php
include_once("DB.php");

class UserException extends RuntimeException
{
}

class User
{
    # MySQL Data Storage Version

    private $_id;
    private $_name;
    private $_pwdcrypt;
    private $_email;
    private $_firstlogin;
    private $_lastlogin;

    public $security;
    public $valcode;
    public $validated;
    public $lastpage;


    #######################################
    # PRIVATE Class Functions

    private function __construct($data)
    {
        $this->_id = $data['id'];
        $this->_email = $data['email'];
        $this->_name = $data['name'];
        $this->_firstlogin = $data['firstlogin'];
        $this->_pwdcrypt = $data['password'];
        $this->security = $data['security'];
        $this->_lastlogin = $data['lastlogin'];
        $this->lastpage = $data['lastpage'];
        $this->valcode = $data['valcode'];
        $this->validated = $data['validated'];
        $this->lastpage = $data['lastpage'];
    }

    #############################################
    # PUBLIC Class Functions

    public function __get($varname): string
    {
        switch ($varname) {
            case 'id':
                return $this->_id;
                break;
            case 'name':
                return $this->_name;
                break;
            case 'email':
                return $this->_email;
                break;
            case 'pwdcrypt':
                return $this->_pwdcrypt;
                break;
            case 'firstlogin':
                return $this->_firstlogin;
                break;
            case 'lastlogin':
                return $this->_lastlogin;
                break;
        }
        throw new ErrorException("No such public property [" . $varname . "]");
    }

    public function updateLastlogin(): void
    {
        $this->_lastlogin = (new DateTime())->format("Y-m-d H:i:s");
    }

    public function generateValidationCode(): string
    {
        if ($this->isGuest()) return false;
        return $this->valcode = password_hash($this->_name . $this->_email . (new DateTime())->format(DATE_RSS), PASSWORD_DEFAULT);
    }

    public function isGuest(): bool
    {
        return ($this->_id == -1);
    }

    public function matchPassword($plaintext): bool
    {
        if (!$plaintext) return false;
        return (password_verify($plaintext, $this->_pwdcrypt));
    }

    public function setPassword($plaintext): void
    {
        $this->_pwdcrypt = password_hash($plaintext, PASSWORD_BCRYPT);
    }


    #################################################
    # Database Accessor and Class builder functions 

    public static function getAllNames(): array
    {
        $stmt = DB_Factory::getDatabase()->query("SELECT name FROM users");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function getByID($id): User|false
    {
        $pstmt = DB_Factory::getDatabase()
            ->prepare("SELECT * FROM users WHERE id = :id");
        $pstmt->execute(array('id' => $id));
        if ($row = $pstmt->fetch()) return new User($row);
        return false;
    }

    public static function getByName($name): User|false
    {
        $pstmt = DB_Factory::getDatabase()
            ->prepare("SELECT * FROM users WHERE name = :name");
        $pstmt->execute(array('name' => $name));

        if ($row = $pstmt->fetch()) return new User($row);
        return false;
    }

    public static function getByEmail($email): User|false
    {
        $pstmt = DB_Factory::getDatabase()
            ->prepare("SELECT * FROM users WHERE email = :email");
        $pstmt->execute(array('email' => $email));

        if ($row = $pstmt->fetch()) return new User($row);
        return false;
    }

    public static function createGuest(): User
    {
        return new User(
            array(
                'id' => -1,
                'name' => 'Guest',
                'firstlogin' => (new DateTime())->format("Y-m-d H:i:s"),
                'security' => 0,
                'email' => null,
                'password' => null,
                'lastlogin' => null,
                'lastpage' => null,
                'valcode' => null,
                'validated' => null
            )
        );
    }

    public static function createNew($username, $email): User
    {
        $dbc = DB_Factory::getDatabase();
        $pstmt = $dbc->prepare("INSERT INTO users (name, email) VALUES (:name, :email);");

        try {
            $pstmt->execute(array('name' => $username, 'email' => $email));
        } catch (PDOException $pe) {
            throw new UserException("Duplicate entry :: " . $dbc->errorInfo()[2]);
        }

        $pstmt = $dbc->prepare("SELECT * FROM users WHERE email = :email");
        $pstmt->execute(array("email" => $email));

        return new User($pstmt->fetch());
    }

    public function save(): bool
    {
        if ($this->isGuest()) return false;

        $pstmt = DB_Factory::getDatabase()
            ->prepare(
                "UPDATE users SET
                                password = :password,
                                security = :security,
                                lastlogin = :lastlogin,
                                valcode = :valcode,
                                validated = :validated,
                                lastpage = :lastpage
                                WHERE id = :id"
            );

        return $pstmt->execute(
            array(
                'password' => $this->_pwdcrypt,
                'security' => $this->security,
                'lastlogin' => $this->_lastlogin,
                'valcode' => $this->valcode,
                'validated' => $this->validated,
                'lastpage' => $this->lastpage,
                'id' => $this->_id
            )
        );
    }

    public function sendValidationEmail(): bool
    {
        if ($this->isGuest()) return false;

        // needs more work
        return false;
    }
}

<?php
namespace Invoice\Library;

include('Password.php');

class User extends Password
{
    /**
     * @var \PDO Database object
     */
    private $_db;

    /**
     * @var int random key length
     */
    private $_length = 11;


    /**
     * User constructor.
     */
    function __construct()
    {
        parent::__construct();

        $c = new Config();

        $this->_db = $c->database();
    }

    /**
     * Authenticates the user
     *
     * @TODO Add error logging for attempts
     *
     * @param $user string The username
     * @param $pass string The password
     *
     * @return bool The result either yes or no
     */
    public function authenticate($user, $pass)
    {
        //first, lets check the format is right
        if(!is_string($user) || !is_string($pass))
        {
            //need to log an error here
            return false;
        }
        
        try {
            $tmp = $this->_db->prepare("SELECT password, userID FROM users WHERE username = :user");
            $tmp->execute(array(':user', $user));
            
            $row = $tmp->fetch();
            
            if($this->password_verify($pass, $row['password']))
            {
                $_SESSION['loggedin'] = true;
                $_SESSION['uid'] = $row['userID'];

                return true;
            }    
        } catch(\PDOException $e) {
            echo '<p class="error">'.$e->getMessage().'</p>';
        }
    }

    /**
     * Basic logout function by destroying session
     */
    public function logout()
    {
        session_destroy();
    }

    /**
     * Checks to see if the user is the client
     *
     * @return bool The result
     */
    public function is_client()
    {
        if($this->is_authenticated())
        {
            try {
                $tmp = $this->_db->prepare("SELECT username FROM users WHERE userid = :id AND flag = 1");
                $tmp->execute(array('id' => $_SESSION['uid']));

                if($tmp->rowCount() > 0)
                {
                    return true;
                } else {
                    return false;
                }
            } catch (\PDOException $e) {
                echo("<p>Error: " . $e->errorInfo . "</p>");
            }
        } else {
            //add something about user not being logged in?
            return false;
        }
    }

    /**
     * Checks to see if the user is staff
     *
     * @return bool The result
     */
    public function is_staff()
    {
        if($this->is_authenticated())
        {
            try {
                $tmp = $this->_db->prepare("SELECT username FROM users WHERE userid = :id AND flag = 2");
                $tmp->execute(array('id' => $_SESSION['uid']));

                if($tmp->rowCount() > 0)
                {
                    return true;
                } else {
                    return false;
                }
            } catch (\PDOException $e) {
                echo("<p>Error: " . $e->getMessage() . "</p>");
            }
        } else {
            //add something about user not being logged in?
            return false;
        }
    }

    /**
     * Checks to see if the user is already authenticated by checking sessions
     *
     * @return bool
     */
    public function is_authenticated()
    {
        if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)
        {
            if(isset($_SESSION['uid']) && is_numeric($_SESSION['uid']))
            {
                return true;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * Password reset function
     *
     * @TODO Add the email call
     *
     * @param array $user The form details (username or password)
     *
     * @return bool Returns true or false depending on the result of finding the user
     */
    public function password_reset(array $user)
    {
        //Check to see if it an email address or username
        if(strpos($user['user'], '@'))
        {
            try {
                $tmp = $this->_db->prepare("SELECT userid FROM users WHERE email = :email");
                $tmp->execute(array('email' => $user['user']));

                if($tmp->rowCount() > 0)
                {
                    $row = $tmp->fetch();
                    $stmt = $this->_db->prepare("INSERT INTO reset_key(userid, tmp_key) VALUES (:userid, :tmpkey)");
                    $stmt->execute(array(':userid' => $row['userid'], ':tmpkey' => $this->_create_key()));

                    return true;
                    //email
                }
            } catch(\PDOException $e) {
                echo '<p class="error">Email address is not in the database</p>';

                return false;
            }
        } else {
            try
            {
                $tmp = $this->_db->prepare("SELECT userid FROM users WHERE username = :user");
                $tmp->execute(array('user' => $user['user']));

                if($tmp->rowCount() > 0)
                {
                    $row = $tmp->fetch();
                    $stmt = $this->_db->prepare("INSERT INTO reset_key(userid, tmp_key) VALUES (:userid, :tmpkey)");
                    $stmt->execute(array(':userid' => $row['userid'], ':tmpkey' => $this->_create_key()));

                    return true;
                    //email
                }
            } catch(\PDOException $e)
            {
                echo '<p class="error">Username is not in the database</p>';

                return false;
            }
        }
    }

    /**
     * Checks the password reset key to see if it matches
     *
     * @param string $key The reset key
     *
     * @return bool|\PDOStatement Either FALSE on failure or the PDO object
     */
    public function check_key($key)
    {
        if(strlen($key) == $this->_length)
        {
            try {
                $stmt = $this->_db->prepare("SELECT userid FROM reset_key WHERE tmp_key = :tmpkey");
                $stmt->execute(array('tmpkey' => $key));

                $row = $stmt->fetch();

                return $row;
            } catch(\PDOException $e) {
                echo '<p class="error">Invalid/Expired key  -   '.$e->getMessage() . '</p>';

                return false;
            }
        } else {
            echo '<p class="error">Malformed key</p>';

            return false;
        }
    }

    /**
     * Creates a random token key
     *
     * @return string The token key
     */
    private function _create_key()
    {
        if(phpversion() >= '7.0.0')
        {
            $token = substr(bin2hex(random_bytes($this->_length)), 0, $this->_length);
        } else {
            $token = substr(bin2hex(openssl_random_pseudo_bytes($this->_length)), 0, $this->_length);
        }

        return $token;
    }

    /**
     * The login function
     *
     * @param string $user The username
     * @param string $pass The password
     *
     * @return bool The login result
     */
    public function login($user, $pass)
    {
        $hashed = $this->_get_user_hash($user);

        if($this->password_verify($pass,$hashed) == 1)
        {
            $_SESSION['loggedin'] = true;

            return true;
        } else {
            return false;
        }
    }

    public function register(array $details)
    {
        try {
            $details['password'] = $this->password_hash($details['password'], PASSWORD_BCRYPT);

            $stmt = $this->_db->prepare('INSERT INTO users(username, password, email, name) VALUES (:user, :pass, :email, :name)');
            $stmt->execute(array(
                ':user' => $details['user'],
                ':pass' => $details['password'],
                ':email' => $details['email'],
                ':name' => $details['name']
            ));

            header('Location: index.php');
        } catch(\PDOException $e) {
            echo('<p class="error">Sorry there was an issue with the registering, try again later</p>');
        }
    }
    /**
     * Gets the user hashed password
     *
     * @param string $username The username
     *
     * @return string The password hash
     */
    private function _get_user_hash($username)
    {
        try {
            $stmt = $this->_db->prepare("SELECT password, userid FROM users WHERE username = :name");
            $stmt->execute(array('name' => $username));

            $row = $stmt->fetch();
            $_SESSION['uid'] = $row['userid'];

            return $row['password'];
        } catch(\PDOException $e) {
            echo '<p class="error">'.$e->getMessage().'</p>';
        }
    }
}
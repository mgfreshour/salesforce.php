<?php
if (!class_exists('SforcePartnerClient')) {
require_once (FS_APP_ROOT.'/lib/third_party/salesforce/soapclient/SforcePartnerClient.php');
}

/**
 * The base class for all classes that need to access salesforce API.  It handles
 *  connecting.
 */
abstract class salesforce_Base {
    /**
     * The connection to salesforce for the session
     * @var SforcePartnerClient
     */
    static $_conn;

    /**
     * Container for session data (will normally be empty, just using the $_SESSION super global)
     * @var array
     */
    static $_session;
	
	/**
	 * @var string
	 */
	static $_username;
	/**
	 * @var string
	 */
	static $_password;
	/**
	 * @var string
	 */
	static $_token;

    //--------------------------------------------------------------------------
    // Object Methods
    //--------------------------------------------------------------------------

    /**
     * C'Tor.  Will attempt to connect to salesforce
     */
    public function __construct($params = array()) {
        // Dependency Injection
        if (isset($params['conn'])) {
            $this->setConn($params['conn']);
        }
        if (isset($params['session'])) {
            $this->setSession($params['session']);
        }

        $this->useTheForce();
    }

    //--------------------------------------------------------------------------
    // Static Methods
    //--------------------------------------------------------------------------
	public static function setCredentials($username, $password, $token) {
		self::$_username = $username;
		self::$_password = $password;
		self::$_token = $token;
	}
    /**
     * Attempts to connect to salesforce
     * @todo move the credentials out
     */
    protected static function login() {
        $wsdl = FS_APP_ROOT.'/lib/third_party/salesforce/soapclient/partner.wsdl.xml';

        if (self::getSessionData('salesforce_sessionId') == NULL) {
            self::getConn()->createConnection($wsdl);
            self::getConn()->login(self::$_username, self::$_password . self::$_token);

            // Now we can save the connection info for the next page
            self::setSessionData('salesforce_location', self::getConn()->getLocation());
            self::setSessionData('salesforce_sessionId', self::getConn()->getSessionId());
            self::setSessionData('salesforce_wsdl', $wsdl);
        } else {
            // Use the saved info
            self::getConn()->createConnection(self::getSessionData('salesforce_wsdl'));
            self::getConn()->setEndpoint(self::getSessionData('salesforce_location'));
            self::getConn()->setSessionHeader(self::getSessionData('salesforce_sessionId'));
        }
    }

    /**
     * Attempts to connect to salesforce
     */
    public static function useTheForce() {
        try {
            // Attempt to connect
            self::login();
        } catch (Exception $e) {
            // Did we fail because of an old session?
            if (isset($e->faultcode) && $e->faultcode == 'sf:INVALID_SESSION_ID') {
                // Clear the session and try again
                self::unsetSessionData();
                self::login();
            } else {
                throw $e;
            }
        }
    }

    /**
     * Dependency Injection for session data
     * @param array $session
     */
    public static function setSession($session) {
        self::$_session = $session;
    }

    /**
     * Returns the singleton of the salesforce connection
     * @return SforcePartnerClient
     */
    protected static function getConn() {
        if (!isset(self::$_conn)) {
            self::setConn(new SforcePartnerClient());
            self::useTheForce();
        }
        return self::$_conn;
    }

    /**
     * Dependency Injection for Base::$_conn
     * @param SforcePartnerClient $conn
     */
    public static function setConn(/*SforcePartnerClient*/ $conn) {
        self::$_conn = $conn;
    }

    /**
     * Gets a variable from the session (wether internal or global)
     * @param string $name
     * @return mixed
     */
    public static function getSessionData($name) {
        if (isset(self::$_session)) {
            return isset(self::$_session[$name]) ? self::$_session[$name] : NULL;
        } else {
            return $_SESSION[$name];
        }
    }

    /**
     * Sets a variable in the session (wether internal or global)
     * @param string $name
     */
    public static function setSessionData($name, $value) {
        if (isset(self::$_session)) {
            self::$_session[$name] = $value;
        } else {
            $_SESSION[$name] = $value;
        }
    }

    /**
     * Unsets any saved session data
     */
    protected static function unsetSessionData() {
        if (isset(self::$_session)) {
            self::$_session = array();
        } else {
            unset($_SESSION['salesforce_wsdl']);
            unset($_SESSION['salesforce_location']);
            unset($_SESSION['salesforce_sessionId']);
        }
    }
}

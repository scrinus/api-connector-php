<?php
namespace Scrinus\Connector;

/**
 * Rest client for scrinus api calls.
 *
 * This component is a port of the scrinus library,
 * which is copyright scrinus GmbH, @see https://scrinus.com.
 *
 * @author Günther Jedenastik <guenther@scrinus.com>
 * @author David Spiola <david@scrinus.com>
 * @author Markus Pfeifenberger <markus@scrinus.com>
 *
 */
class HttpClient {

    /**
     * @var string
     */
    static protected $methodGet = 'GET';

    /**
     * @var string
     */
    static protected $methodPost = 'POST';

    /**
     * @var string
     */
    static protected $methodPut = 'PUT';

    /**
     * @var string
     */
    static protected $methodDelete = 'DELETE';

    /**
     * @var string
     */
    protected $plaintextsignature = null;

    /**
     * @var bool
     */
    protected $decodeResponse = true;

    /**
     * @var string
     */
    protected $username = null;

    /**
     * @var string
     */
    protected $password = null;

    /**
     * @var string
     */
    protected $salt = null;

    /**
     * @var array
     */
    protected $responseheaders = [];

    /**
     * @var int
     */
    protected $responsestatus = 0;

    /**
     * @var string
     */
    protected $apiUrl;

    /**
     * HttpClient constructor
     *
     * @param string $apiUrl scrinus rest endpoint url
     * @param string $username scrinus username
     * @param string $password scrinus password
     * @param string $salt scrinus api salt
     */
    public function __construct($apiUrl, $username=null, $password=null, $salt=null) {
        $this->apiUrl = $apiUrl;
        $this->setCredentials($username, $password, $salt);
    }

    /**
     * Set scrinus api credentials
     *
     * @param string $username
     * @param string $password
     * @param string $salt
     * @return HttpClient $this
     * @throws \Exception
     */
    private function setCredentials($username, $password, $salt) {
        $this->username = $username;
        $this->password = $password;
        $this->salt = $salt;

        if ($this->username != null && $this->salt == null) {
            $this->fetchSalt();
        }

        return $this;
    }

    /**
     * Get scrinus api salt
     *
     * @return void
     * @throws \Exception
     */
    private function fetchSalt() {
        // Create a client with a base URI
        $response = $this->sendRequest("GET", [], "/login/getSalt", "id={$this->username}");

        if ($response['info']['http_code'] != 200) {
            throw new \Exception("querying api for salt failed [".$response['info']['http_code']."]: ".$response['body']);
        }

        $body = json_decode($response['body']);
        if ($body->success) {
            $this->salt = $body->data;
        } else{
            throw new \Exception("Invalid username given: [{$body->message}]");
        }
    }

    /**
     * Set decode response
     *
     * @param boolean $decode
     */
    protected function setDecodeResponse($decode) {
        $this->decodeResponse = $decode;
    }

    /**
     * Generate salted password
     *
     * @return string
     */
    private function generateSaltedPassword() {
        return md5(md5($this->password) . $this->salt);
    }

    /**
     * Generate signature
     *
     * @param string $identifier
     * @param string $date
     * @param string $method
     * @param string $scheme
     * @param string $host
     * @param string $path
     * @param string $query
     * @param string $md5payload
     *
     * @return string
     */
    protected function generateSignature($identifier, $date, $method, $scheme, $host, $path, $query, $md5payload) {
        $this->plaintextsignature  = $identifier . '|' . $date . '|' . $method . '|' . $scheme . '|' . $host . '|' . $path . '|' . $query . '|' . $md5payload;

        return md5(md5($this->plaintextsignature) . $this->generateSaltedPassword());
    }

    /**
     * @param string $method
     * @param string $path
     * @param string $query
     * @param string $payload
     *
     * @return array
     */
    private function getAuthentication($method, $path, $query = "", $payload = "") {
        $username = $this->username;

        $headers = array();
        $date = gmdate('D, d M Y H:i:s', time());
        $headers['X-Date'] = $date;

        if (!is_null($username)) {
            $method     = strtoupper($method);
            $uri        = rtrim($this->apiUrl, '/') . '/' . ltrim($path, '/');
            $scheme     = strtolower(parse_url($uri, PHP_URL_SCHEME));
            $host       = parse_url($uri, PHP_URL_HOST);
            $md5payload = md5($payload);

            if ($path[0] != '/') {
                $path = parse_url($uri, PHP_URL_PATH);
            }

            $signature = $this->generateSignature($username, $date, $method, $scheme, $host, $path, $query, $md5payload);
            $headers['Scr-Authorization'] = "$signature;$username";
        }

        return $headers;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt() {
        return $this->salt;
    }

    /**
     * Send rest GET call
     *
     * @param string $path  rest url
     * @param array $data get parameters
     *
     * @return mixed
     * @throws \Exception
     */
    public function get($path, array $data = null) {
        if (is_array($data)) {
            $data = $this->buildQuery($data);
        }

        $authentication = $this->getAuthentication(self::$methodGet, $path, $data); //TODO: why does the get call has not payload parameter
        $response = $this->sendRequest(self::$methodGet, $authentication, $path, $data);

        return $this->handleResponse($response, array($authentication, self::$methodGet, $path, $data));
    }

    /**
     * Send rest POST call
     *
     * @param string $path rest url
     * @param mixed $data post parameters
     *
     * @return mixed
     */
    public function post($path, $data = null) {
        if (is_array($data)) {
            $data = $this->buildQuery($data);
        }

        $authentication = $this->getAuthentication(self::$methodPost, $path, "", $data);
        $response = $this->sendRequest(self::$methodPost, $authentication, $path, $data);

        return $this->handleResponse($response, array($authentication, self::$methodPost, $path, $data));
    }

    /**
     * Send rest PUT call
     *
     * @param string $path rest url
     * @param array $data put parameters
     * @return mixed
     */
    public function put($path, array $data = null)
    {
        if (is_array($data)){
            $data = $this->buildQuery($data);
        }

        $authentication = $this->getAuthentication(self::$methodPut, $path, "", $data);
        $response = $this->sendRequest(self::$methodPut, $authentication, $path, $data);

        return $this->handleResponse($response, array($authentication, self::$methodPut, $path, $data));
    }

    /**
     * Send rest DELETE call
     *
     * @param string $path rest url
     * @param array $data delete parameters
     *
     * @return mixed
     */
    public function delete($path, array $data = null) {
        if (is_array($data)){
            $data = $this->buildQuery($data);
        }

        $authentication = $this->getAuthentication(self::$methodDelete, $path, "", $data);
        $response = $this->sendRequest(self::$methodDelete, $authentication, $path, $data);

        return $this->handleResponse($response, array($authentication, self::$methodDelete, $path, $data));
    }

    /**
     * @param string $method rest api method
     * @param string  $headers request header
     * @param string $path rest url
     * @param mixed $data
     *
     * @return array
     */
    private function sendRequest($method, $headers, $path, $data = null) {
        // Set the Request Url (without Parameters) here
        $uri = $this->apiUrl . $path;

        // Let's set all Request Parameters (api_key, token, user_id, etc)
        if (is_array($data)) {
            $data = $this->buildQuery($data);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        switch(strtoupper($method)) {
            case self::$methodDelete:
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, self::$methodDelete);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case self::$methodGet:
                if (strlen($data) > 0) {
                    $uri .= '?' . $data;
                }
                break;
            case self::$methodPost:
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case self::$methodPut:
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, self::$methodPut);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
        }

        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120); //timeout in seconds

        if (is_array($headers) && count($headers) > 0) {
            foreach ($headers as $key => $value) {
                $headers[$key] = $key . ': ' . $value;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //TODO: has to be true in the future

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        $header = trim(substr($response, 0, $info['header_size']));
        $body = substr($response, $info['header_size']);

        return array('info' => $info, 'header' => $header, 'body' => $body);
    }

    /**
     * Format response
     *
     * @param array $response response
     * @param array $info response information
     *
     * @return mixed
     * @throws \Exception
     */
    private function handleResponse(array $response, array $info = null){
        $body = null;
        if (is_array($response)) {
            $body = $response['body'];
        } else {
            $body = $response->getBody(true);
        }

        if ($this->decodeResponse === true) {
            $json = json_decode($body);
            if (json_last_error()==JSON_ERROR_NONE) {
                return $json;
            } else {
                // json encoding failed, handle exceptions
                $statusCode = 0;
                $reasonPhrase = "";
                if (is_array($response)) {
                    $statusCode = $response['info']['http_code'];
                    $reasonPhrase = explode("\r\n", $response['header']);
                    $reasonPhrase = $reasonPhrase[0];
                    $reasonPhrase = explode(" ", $reasonPhrase, 3);
                    $reasonPhrase = $reasonPhrase[2];
                } else {
                    $statusCode = $response->getStatusCode();
                    $reasonPhrase = $response->getReasonPhrase();
                }

                switch ($statusCode) {
                    case 401:
                    case 403:
                        throw new \Exception($reasonPhrase . ": RestClient.php generated Signature is invalid '{$this->plaintextsignature}'", $statusCode);
                        break;
                    default:
                        throw new \Exception($reasonPhrase, $statusCode);
                }
            }
        } else {
            return $body;
        }
    }

    /**
     * Build request query
     *
     * @param mixed $input
     * @param string $numericPrefix
     * @param string $argSeparator
     * @param int $encodingType
     * @param string $keyValueSeparator
     * @param string $prefix
     * @return string
     */
    private function buildQuery($input, $numericPrefix = '', $argSeparator = '&', $encodingType = 2, $keyValueSeparator = '=', $prefix = '') {
        if (is_array($input) || is_object($input)) {
            $arr = array();
            foreach ($input as $key => $value) {
                $name = $prefix;
                if (strlen($prefix)) {
                    $name .= '[';
                    if (!is_numeric($key)) {
                        $name .= $key;
                    }
                    $name .= ']';
                } else {
                    if (is_numeric($key)) {
                        $name .= $numericPrefix;
                    }
                    $name .= $key;
                }
                if ((is_array($value) || is_object($value)) && count($value)) {
                    $arr[] = $this->buildQuery($value,$numericPrefix, $argSeparator,$encodingType, $keyValueSeparator,$name);
                } else {
                    if ($encodingType === 2) {
                        $arr[] = rawurlencode($name) . $keyValueSeparator . rawurlencode($value ? $value : '');
                    } else {
                        $arr[] = urlencode($name) . $keyValueSeparator . urlencode($value ? $value : '');
                    }
                }
            }
            return implode($argSeparator, $arr);
        } else {
            if ($encodingType === 2) {
                return rawurlencode($input);
            } else {
                return urlencode($input);
            }
        }
    }
}

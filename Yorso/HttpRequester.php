class HttpRequester
{
    /**
     * @var resource
     */
    protected $curl;

    /**
     * @var CurlBuilderDto
     */
    protected $curlParams;

    protected $countRetries = 3;
    protected $counter = 0;

    /**
     * @param CurlBuilderDto $curlParams
     *
     * @return ServerResponseDto
     * @throws \Exception
     */
    protected function sendPostRequest(CurlBuilderDto $curlParams): ServerResponseDto
    {
        $this->initBaseParams($curlParams);
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $curlParams->parameters);

        $this->curlParams = $curlParams;

        return $this->processCurlExecution();
    }

    /**
     * @param CurlBuilderDto $curlParams
     *
     * @return ServerResponseDto
     * @throws \Exception
     */
    protected function sendDeleteRequest(CurlBuilderDto $curlParams): ServerResponseDto
    {
        $this->initBaseParams($curlParams);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $curlParams->parameters);

        $this->curlParams = $curlParams;

        return $this->processCurlExecution();
    }

    /**
     * @param CurlBuilderDto $curlParams
     *
     * @return ServerResponseDto
     * @throws \Exception
     */
    protected function sendAjaxRequest(CurlBuilderDto $curlParams): ServerResponseDto
    {
        $curlParams->headers[] = 'Content-Type: application/json';

        return $this->sendPostRequest($curlParams);
    }

    /**
     * @param CurlBuilderDto $curlParams
     *
     * @return ServerResponseDto
     */
    protected function sendGetRequest(CurlBuilderDto $curlParams): ServerResponseDto
    {
        $this->initBaseParams($curlParams);
        curl_setopt($this->curl, CURLOPT_POST, false);

        $this->curlParams = $curlParams;

        return $this->processCurlExecution();
    }

    /**
     * @return ServerResponseDto
     */
    private function processCurlExecution(): ServerResponseDto
    {
        $response = curl_exec($this->curl);

        $result = new ServerResponseDto();
        if ($this->curlParams->file) {
            $result->body = $response;
            $result->headers = 'See binary file';

            return $result;
        }

        if (!$response) {

            if ($this->counter < $this->countRetries) {
                $this->counter++;
                sleep(3);
                echo 'Retries ' . json_encode($this->curlParams) . ' with counter ' . $this->counter . PHP_EOL;
                return $this->processCurlExecution();
            }

            echo 'Limit exceeded for request ' . json_encode($this->curlParams) . PHP_EOL;

            $result = new ServerResponseDto();
            $result->code = 500;

            return $result;
        }

        //reset counter
        $this->counter = 0;

        if ($this->curlParams->returnHeaders) {
            list($header, $body) = explode("\r\n\r\n", $response, 2);
        } else {
            $header = null;
            $body = $response;
        }

        $result->code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        $result->body = $body;
        $result->headers = $header;

        if ($this->curlParams->closeAfterExecute) {
            $this->close();
        }

        return $result;
    }

    /**
     * @param CurlBuilderDto $curlParams
     */
    private function initBaseParams(CurlBuilderDto $curlParams)
    {
        $this->curl = curl_init();

        curl_setopt($this->curl, CURLOPT_USERAGENT, $curlParams->userAgent);
        curl_setopt($this->curl, CURLOPT_URL, $curlParams->url);

        curl_setopt($this->curl, CURLOPT_FAILONERROR, $curlParams->failOnError);

        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        /** @noinspection CurlSslServerSpoofingInspection */
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);

        if ($curlParams->timeout) {
            curl_setopt($this->curl, CURLOPT_TIMEOUT, $curlParams->timeout);
        }

        if ($curlParams->cookie) {
            curl_setopt($this->curl, CURLOPT_COOKIEFILE, $curlParams->cookie);
            curl_setopt($this->curl, CURLOPT_COOKIEJAR, $curlParams->cookie);
        }

        if ($curlParams->file) {
            curl_setopt($this->curl, CURLOPT_FILE, $curlParams->file);
        }

        if ($curlParams->headers) {
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $curlParams->headers);
        }

        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_MAXREDIRS, 1);
        curl_setopt($this->curl, CURLOPT_HEADER, $curlParams->returnHeaders);
        curl_setopt($this->curl, CURLOPT_VERBOSE, false);
    }

    /**
     * Close something
     */
    public function __destruct()
    {
        if ($this->curl) {
            $this->close();
        }
    }

    public function close()
    {
        curl_close($this->curl);
        $this->curl = null;
    }
}

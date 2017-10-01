class CurlBuilderDto
{
    public $url;
    public $parameters;
    public $headers = [];
    public $cookie;
    public $timeout = 30;
    public $userAgent = 'Yorso System';
    public $failOnError = false;
    public $file;
    public $returnHeaders = true;
    public $returnTransfers = true;

    public $closeAfterExecute = true;
}

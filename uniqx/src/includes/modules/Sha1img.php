<?php

class Sha1img {

    protected string $_url;
    protected string $_directory;
    protected string $_file;

    public function __construct(string $url) {
        $this->_url = $url;
        $this->_directory = Utils::createRandomDirectory();
        $this->_file = '';
    }

    protected function _downloadFile() : void {

        $url_info = parse_url($this->_url);

        if (!in_array(@$url_info['scheme'], ['http', 'https']))
            throw new Exception("Only 'http://' and 'https://' schemes are allowed in the URL.");

        $filename = @basename($url_info['path']);

        if (empty($filename))
            throw new Exception("Could not determine the filename from the URL.");

        $curl_handler = curl_init();
        $curl_options = [
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FOLLOWLOCATION  => false,
            CURLOPT_SSL_VERIFYHOST  => false,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_URL             => $this->_url
        ];
        
        @curl_setopt_array($curl_handler, $curl_options);
        
        $curl_response = @curl_exec($curl_handler);
        $curl_info = curl_getinfo($curl_handler);
        if (empty($curl_response) || @$curl_info['http_code'] !== 200)
            throw new Exception("Could not get content from the URL ressource.");

        $this->_file = "{$this->_directory}/$filename";
        
        if (file_put_contents($this->_file, $curl_response) === false)
            throw new Exception("Could not save downloaded content into a file.");
    }

    protected function _verifyExtension() : void {
        $file_parts = pathinfo($this->_file);

        $allowed_extensions = [ 'jpg', 'gif', 'png' ];

        if (!in_array($file_parts['extension'], $allowed_extensions))
            throw new Exception("File extension '{$file_parts['extension']}' not allowed.");
    }
    
    public function getSignature() : string {

        $this->_downloadFile();
        $this->_verifyExtension();

        if (!file_exists($this->_file))
            throw new Exception('File does not exist locally.');
        
        $sig = sha1_file($this->_file);

        if ($sig === false)
            throw new Exception('Could not compute sha1 hash.');

        return $sig;
    }

    public function __destruct() {
        if (is_dir($this->_directory)) { // Delete our random folder when the job is done
            @unlink($this->_file);
            @rmdir($this->_directory);
        }
    }
}
<?php

namespace FroshTinyPngMediaOptimizer\Components;

/**
 * Class OptimusService
 */
class TinyPngService
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var integer
     */
    private $limit;

    /**
     * @var string
     */
    private $endpoint = 'https://api.tinify.com/shrink';

    /**
     * @param string $apiKey
     * @param $limit
     */
    public function __construct($apiKey, $limit)
    {
        $this->apiKey = $apiKey;
        $this->limit = $limit;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     *
     * @return $this
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = (string) $apiKey;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return bool
     */
    public function verifyApiKey()
    {
        $endpoint = $this->endpoint;

        $headers = [
            'User-Agent: API',
            'Accept: */*',
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_USERNAME => 'user',
            CURLOPT_PASSWORD => $this->getApiKey(),
            CURLOPT_URL => $endpoint,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => '',
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        $body = substr($response, $header_size);

        $header = [];
        foreach (explode("\r\n", trim(substr($response, 0, $header_size))) as $row) {
            if (preg_match('/(.*?): (.*)/', $row, $matches)) {
                $header[$matches[1]] = $matches[2];
            }
        }

        if (array_key_exists('Compression-Count', $header) && (int) $header['Compression-Count'] < $this->getLimit()) {
            return true;
        }

        return false;
    }

    /**
     * @param string $image
     * @param string $target
     *
     * @throws TinyPngApiException
     *
     * @return void
     */
    public function optimize($image, $target = '')
    {
        $endpoint = $this->endpoint;

        $headers = [
            'User-Agent: API',
            'Accept: */*',
        ];

        if ($target === '') {
            $target = $image;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_USERNAME => 'user',
            CURLOPT_PASSWORD => $this->apiKey,
            CURLOPT_URL => $endpoint,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => file_get_contents($image),
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        $body = substr($response, $header_size);

        $header = [];
        foreach (explode("\r\n", trim(substr($response, 0, $header_size))) as $row) {
            if (preg_match('/(.*?): (.*)/', $row, $matches)) {
                $header[$matches[1]] = $matches[2];
            }
        }

        //Compression-Count

        //Compression-Count

        // error catching
        if (!empty($curlError) || empty($header['Location']) || $httpcode != 201) {
            throw new TinyPngApiException($body);
        }

        file_put_contents($target, file_get_contents($header['Location']));
    }
}

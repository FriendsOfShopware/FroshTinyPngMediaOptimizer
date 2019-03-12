<?php

namespace FroshTinyPngMediaOptimizer\Components;

/**
 * Class TinyPngService
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
     * @var null|integer
     */
    private $compressionCount = null;

    /**
     * @var string
     */
    private $endpoint = 'https://api.tinify.com/shrink';

    /**
     * @var array
     */
    private $requestHeaders = [
        'User-Agent: API',
        'Accept: */*',
    ];

    /**
     * @var \Zend_Cache_Core
     */
    private $cache;

    /**
     * @param string $apiKey
     * @param $limit
     * @param \Zend_Cache_Core $cache
     */
    public function __construct($apiKey, $limit, \Zend_Cache_Core $cache)
    {
        $this->apiKey = $apiKey;
        $this->limit = $limit;
        $this->cache = $cache;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return int|null
     */
    public function getCompressionCount()
    {
        return $this->cache->load($this->getCompressionCountKey());
    }

    /**
     * @param int $count
     * @return bool
     * @throws \Zend_Cache_Exception
     */
    public function setCompressionCount(int $count)
    {
        return $this->cache->save($count, $this->getCompressionCountKey(), [], 60*60);
    }

    /**
     * @return string
     */
    private function getCompressionCountKey()
    {
        return md5($this->getApiKey() . 'tinypngCounter');
    }

    private function raiseCompressionCount(int $step = 1)
    {
        $this->compressionCount += $step;
    }

    /**
     * @param bool $allowCaching
     * @return bool
     * @throws \Zend_Cache_Exception
     */
    public function verifyApiKey($allowCaching = false)
    {
        // use verification cache
        if($allowCaching && $this->getCompressionCount() !== false) {
            return $this->getCompressionCount() < $this->getLimit();
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_USERNAME => 'user',
            CURLOPT_PASSWORD => $this->getApiKey(),
            CURLOPT_URL => $this->endpoint,
            CURLOPT_HTTPHEADER => $this->requestHeaders,
            CURLOPT_POSTFIELDS => '',
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $header = self::parseHeadersFromCurlResponse($ch, $response);

        if (array_key_exists('Compression-Count', $header) && (int) $header['Compression-Count'] < $this->getLimit()) {
            if($allowCaching) {
                $this->setCompressionCount(intval($header['Compression-Count']));
            }
            return true;
        }

        return true;
    }

    /**
     * @param string $image
     * @param string $target
     *
     * @return void
     * @throws TinyPngApiException
     * @throws TinyPngPersistanceException
     */
    public function optimize($image, $target = '')
    {
        if ($target === '') {
            $target = $image;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_USERNAME => 'user',
            CURLOPT_PASSWORD => $this->apiKey,
            CURLOPT_URL => $this->endpoint,
            CURLOPT_HTTPHEADER => $this->requestHeaders,
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
        $header = self::parseHeadersFromCurlResponse($ch, $response);

        // error catching
        if (!empty($curlError) || empty($header['Location']) || $httpcode != 201) {
            throw new TinyPngApiException($body);
        }

        if(($compressedImage = file_get_contents($header['Location'])) === false) {
            throw new TinyPngApiException("Couldn't retrieve {$header['Location']}.");
        }

        if(file_put_contents($target, $compressedImage) === false) {
            throw new TinyPngPersistanceException("Couldn't write compressed image to {$target}.");
        }

        $this->raiseCompressionCount();
    }

    /**
     * @param resource|false $curlResource
     * @param bool|string $response
     *
     * @return array
     */
    private static function parseHeadersFromCurlResponse($curlResource, $response)
    {
        $header_size = curl_getinfo($curlResource, CURLINFO_HEADER_SIZE);

        $header = [];
        foreach (explode("\r\n", trim(substr($response, 0, $header_size))) as $row) {
            if (preg_match('/(.*?): (.*)/', $row, $matches)) {
                $header[$matches[1]] = $matches[2];
            }
        }
        return $header;
    }
}

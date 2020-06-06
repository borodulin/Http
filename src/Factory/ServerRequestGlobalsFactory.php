<?php

declare(strict_types=1);

namespace Borodulin\Http\Factory;

use Borodulin\Http\Message\ServerRequest;
use Borodulin\Http\Message\UploadedFile;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class ServerRequestGlobalsFactory
{
    /**
     * @var UriFactoryInterface
     */
    private $uriFactory;

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    /**
     * @var UploadedFileFactoryInterface|null
     */
    private $uploadedFileFactory;

    public function __construct(
        UriFactoryInterface $uriFactory = null,
        StreamFactoryInterface $streamFactory = null,
        UploadedFileFactoryInterface $uploadedFileFactory = null
    ) {
        $this->uriFactory = $uriFactory ?? new UriFactory();
        $this->streamFactory = $streamFactory ?? new StreamFactory();
        $this->uploadedFileFactory = $uploadedFileFactory ?? new UploadedFileFactory();
    }

    public function createServerRequest(
        array $server = null,
        array $cookie = null,
        array $get = null,
        array $post = null,
        array $files = null,
        StreamInterface $body = null
    ): ServerRequestInterface {
        $server = $server ?? $_SERVER;
        $cookie = $cookie ?? $_COOKIE;
        $get = $get ?? $_GET;
        $post = $post ?? $_POST;
        $files = $files ?? $_FILES;
        $body = $body ?? $this->streamFactory->createStreamFromFile('php://input');

        $headers = $this->headers($server);

        return new ServerRequest(
            $this->getUri($server, $headers, $get),
            $body,
            $this->getMethod($headers, $server),
            $headers,
            $server,
            $cookie,
            $get,
            $this->getUploadedFiles($files),
            [],
            $post,
            $this->getProtocolVersion($server)
        );
    }

    private function getMethod(array $headers, array $server): string
    {
        if (isset($headers['X-Http-Method-Override'])) {
            return strtoupper($headers['X-Http-Method-Override']);
        } elseif (isset($server['REQUEST_METHOD'])) {
            return strtoupper($server['REQUEST_METHOD']);
        }

        return 'GET';
    }

    private function headers(array $server): array
    {
        $headers = [];
        foreach ($server as $key => $value) {
            if ('HTTP_' != substr($key, 0, 5)) {
                continue;
            }
            $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $headers[$header] = $value;
        }

        return $headers;
    }

    private function getUri(array $server, array $headers, array $get): UriInterface
    {
        $hostInfo = $this->getHostInfo($server, $headers);
        $query = http_build_query($get);
        $pathInfo = $this->getPathInfo($server);
        $url = "$hostInfo/$pathInfo?$query";

        return $this->uriFactory->createUri($url);
    }

    private function getPathInfo(array $server)
    {
        if (!isset($server['REQUEST_URI'])) {
            throw new \RuntimeException('Server REQUEST_URI is not defined.');
        }
        $pathInfo = $server['REQUEST_URI'];
        $pathInfo = strtok($pathInfo, '?');

        $pathInfo = urldecode($pathInfo);

        // try to encode in UTF8 if not so
        // http://w3.org/International/questions/qa-forms-utf-8.html
        if (!preg_match('%^(?:
            [\x09\x0A\x0D\x20-\x7E]              # ASCII
            | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
            | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
            | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
            | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
            )*$%xs', $pathInfo)
        ) {
            $pathInfo = $this->utf8Encode($pathInfo);
        }

        $scriptUrl = $this->getScriptUrl($server);
        $baseUrl = rtrim(\dirname($scriptUrl), '.\\/');
        if (0 === strpos($pathInfo, $scriptUrl)) {
            $pathInfo = substr($pathInfo, \strlen($scriptUrl));
        } elseif ('' === $baseUrl || 0 === strpos($pathInfo, $baseUrl)) {
            $pathInfo = substr($pathInfo, \strlen($baseUrl));
        } elseif (isset($server['PHP_SELF']) && 0 === strpos($server['PHP_SELF'], $scriptUrl)) {
            $pathInfo = substr($_SERVER['PHP_SELF'], \strlen($scriptUrl));
        } else {
            throw new \RuntimeException('Unable to determine the path info of the current request.');
        }

        if (0 === strncmp($pathInfo, '/', 1)) {
            $pathInfo = substr($pathInfo, 1);
        }

        return (string) $pathInfo;
    }

    /**
     * Returns the relative URL of the entry script.
     * The implementation of this method referenced Zend_Controller_Request_Http in Zend Framework.
     *
     * @return string the relative URL of the entry script
     *
     * @throws \RuntimeException if unable to determine the entry script URL
     */
    public function getScriptUrl(array $server)
    {
        if (!isset($server['SCRIPT_FILENAME'])) {
            throw new \RuntimeException('Server SCRIPT_FILENAME is not defined.');
        }
        $scriptFile = $server['SCRIPT_FILENAME'];
        $scriptName = basename($scriptFile);
        foreach (['SCRIPT_NAME', 'PHP_SELF', 'ORIG_SCRIPT_NAME'] as $key) {
            if (isset($server[$key]) && basename($server[$key]) === $scriptName) {
                return $server[$key];
            }
        }
        if (isset($server['PHP_SELF']) && false !== ($pos = strpos($server['PHP_SELF'], '/'.$scriptName))) {
            return substr($server['SCRIPT_NAME'], 0, $pos).'/'.$scriptName;
        } elseif (!empty($server['DOCUMENT_ROOT']) && 0 === strpos($scriptFile, $server['DOCUMENT_ROOT'])) {
            return str_replace([$server['DOCUMENT_ROOT'], '\\'], ['', '/'], $scriptFile);
        } else {
            throw new \RuntimeException('Unable to determine the entry script URL.');
        }
    }

    /**
     * Encodes an ISO-8859-1 string to UTF-8.
     *
     * @see https://github.com/symfony/polyfill-php72/blob/master/Php72.php#L24
     */
    private function utf8Encode(string $s): string
    {
        $s .= $s;
        $len = \strlen($s);
        for ($i = $len >> 1, $j = 0; $i < $len; ++$i, ++$j) {
            switch (true) {
                case $s[$i] < "\x80": $s[$j] = $s[$i]; break;
                case $s[$i] < "\xC0": $s[$j] = "\xC2"; $s[++$j] = $s[$i]; break;
                default: $s[$j] = "\xC3"; $s[++$j] = \chr(\ord($s[$i]) - 64); break;
            }
        }

        return substr($s, 0, $j);
    }

    private function getHostInfo($server, $headers): string
    {
        $secure = $this->getIsSecureConnection($server);
        $http = $secure ? 'https' : 'http';

        if (isset($headers['X-Forwarded-Host'])) {
            $hostInfo = $http.'://'.trim(explode(',', $headers['X-Forwarded-Host'])[0]);
        } elseif (isset($headers['Host'])) {
            $hostInfo = $http.'://'.$headers['Host'];
        } elseif (isset($server['SERVER_NAME'])) {
            $hostInfo = $http.'://'.$server['SERVER_NAME'];
            $expectedPort = ($secure ? 443 : 80);
            $port = $server['SERVER_PORT'] ?? $expectedPort;
            if (($port !== $expectedPort)) {
                $hostInfo .= ':'.$port;
            }
        } else {
            $hostInfo = 'http://localhost';
        }

        return trim($hostInfo, '/');
    }

    private function getIsSecureConnection(array $server)
    {
        return isset($server['HTTPS']) && (0 === strcasecmp($server['HTTPS'], 'on') || 1 == $server['HTTPS']);
    }

    /**
     * @param string|array $path
     * @param int|array    $size
     * @param int|array    $error
     * @param string|array $name
     * @param string|array $type
     *
     * @return array|\Psr\Http\Message\UploadedFileInterface
     */
    private function createUploadedRecursive($path, $size, $error, $name, $type)
    {
        if (!\is_array($path)) {
            $stream = is_uploaded_file($path) ? $this->streamFactory->createStreamFromFile($path, 'rb') : null;

            return new UploadedFile(
                $stream,
                (int) $size,
                (int) $error,
                \is_string($name) ? $name : null,
                \is_string($type) ? $type : null
            );
        }
        $result = [];

        foreach ($path as $key => $value) {
            $result[$key] = $this->createUploadedRecursive(
                $path[$key],
                $size[$key],
                $error[$key],
                $name[$key],
                $type[$key]
            );
        }

        return $result;
    }

    private function getUploadedFiles(array $files): array
    {
        $result = [];

        foreach ($files as $key => $file) {
            $result[$key] = $this->createUploadedRecursive(
                $file['tmp_name'],
                $file['size'],
                $file['error'],
                $file['name'],
                $file['type']
            );
        }

        return $result;
    }

    private function getProtocolVersion($server): string
    {
        if (isset($server['SERVER_PROTOCOL'])) {
            if (preg_match('/^HTTP\/(\d\.\d)$/', $server['SERVER_PROTOCOL'], $matches)) {
                return $matches[1];
            }
        }

        return '1.1';
    }
}

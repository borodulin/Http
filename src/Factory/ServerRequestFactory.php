<?php

declare(strict_types=1);

namespace Borodulin\Http\Factory;

use Borodulin\Http\Message\ServerRequest;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * @var UriFactoryInterface
     */
    private $uriFactory;

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    public function __construct(
        UriFactoryInterface $uriFactory = null,
        StreamFactoryInterface $streamFactory = null
    ) {
        $this->uriFactory = $uriFactory ?? new UriFactory();
        $this->streamFactory = $streamFactory ?? new StreamFactory();
    }

    /**
     * Create a new server request.
     *
     * Note that server-params are taken precisely as given - no parsing/processing
     * of the given values is performed, and, in particular, no attempt is made to
     * determine the HTTP method or URI, which must be provided explicitly.
     *
     * @param string              $method       the HTTP method associated with the request
     * @param UriInterface|string $uri          The URI associated with the request. If
     *                                          the value is a string, the factory MUST create a UriInterface
     *                                          instance based on it.
     * @param array               $serverParams array of SAPI parameters with which to seed
     *                                          the generated request instance
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        if (\is_string($uri)) {
            $uri = $this->uriFactory->createUri($uri);
        }

        if (!$uri instanceof UriInterface) {
            throw new \InvalidArgumentException('Uri should implement UriInterface.');
        }
        $body = $this->streamFactory->createStream();

        return new ServerRequest($uri, $body, $method);
    }
}

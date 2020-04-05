<?php

namespace App\Manager;

use App\Exception\CallException;
use App\Model\CallModel;
use Symfony\Component\HttpClient\HttpClient;

class CallManager
{
    /**
     * @required
     */
    public function init(string $elasticsearchUrl, string $elasticsearchUsername, string $elasticsearchPassword, bool $sslVerifyPeer)
    {
        $this->elasticsearchUrl = $elasticsearchUrl;
        $this->elasticsearchUsername = $elasticsearchUsername;
        $this->elasticsearchPassword = $elasticsearchPassword;
        $this->sslVerifyPeer = $sslVerifyPeer;

        $this->client = HttpClient::create();
    }

    public function call(CallModel $call)
    {
        $options = $call->getOptions();

        $headers = [];

        if (false == $options['body']) {
            unset($options['body']);
        } else {
            $headers['Content-Type'] = 'application/json; charset=UTF-8';
        }

        if (0 == count($options['json'])) {
            unset($options['json']);
        }

        if ('GET' == $call->getMethod() && false == isset($options['query']['format'])) {
            $options['query']['format'] = 'json';
        }

        if ($this->elasticsearchUsername && $this->elasticsearchPassword) {
            $headers['Authorization'] = 'Basic '.base64_encode($this->elasticsearchUsername.':'.$this->elasticsearchPassword);
        }

        if (0 < count($headers)) {
            $options['headers'] = $headers;
        }

        $options['verify_peer'] = $this->sslVerifyPeer;

        $response = $this->client->request($call->getMethod(), $this->elasticsearchUrl.$call->getPath(), $options);

        if ($response && in_array($response->getStatusCode(), [400, 401, 404, 405, 500])) {
            $json = json_decode($response->getContent(false), true);

            if (true == isset($json['error'])) {
                if (true == isset($json['error']['caused_by']) && true == isset($json['error']['caused_by']['reason'])) {
                    throw new CallException($json['error']['caused_by']['reason']);

                } else if (true == isset($json['error']['reason'])) {
                    throw new CallException($json['error']['reason']);
                }
            }
            throw new CallException('Not found or method not allowed for '.$call->getPath().' ('.$call->getMethod().')');
        }

        if (true == isset($options['query']['format']) && 'text' == $options['query']['format']) {
            return $response->getContent();
        } else {
            return $response->toArray();
        }
    }
}

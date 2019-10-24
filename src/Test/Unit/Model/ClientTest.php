<?php

declare(strict_types=1);

namespace Omikron\FactfinderNG\Model;

use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Omikron\Factfinder\Api\Config\AuthConfigInterface;
use Omikron\Factfinder\Exception\ResponseException;
use Omikron\FactfinderNG\Model\Api\Credentials;
use Omikron\FactfinderNG\Model\Api\CredentialsFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /** @var MockObject|SerializerInterface */
    private $serializerMock;

    /** @var MockObject|ClientInterface */
    private $httpClientMock;

    /** @var MockObject|CredentialsFactory */
    private $credentialsFactoryMock;

    /** @var Client */
    private $client;

    /**
     * @testdox ResponseException should be thrown if response body is not serializable
     */
    public function test_send_request_should_thrown_exception_when_response_is_not_serializable()
    {
        $this->httpClientMock->method('getStatus')->willReturn(200);
        $this->httpClientMock->method('getBody')->willReturn('unserializable string');
        $this->serializerMock->expects($this->once())->method('unserialize')->willThrowException(new ResponseException());

        $this->expectException(ResponseException::class);
        $this->client->sendRequest('http://fake-ff-server.com/Search.ff', []);
    }

    /**
     * @testdox ResponseException should be thrown if response code is not equal to 200
     */
    public function test_send_request_should_throw_exception_if_status_is_not_200()
    {
        $this->httpClientMock->method('getStatus')->willReturn(500);
        $this->httpClientMock->method('getBody')->willReturn('{}');

        $this->expectException(ResponseException::class);
        $this->client->sendRequest('http://fake-ff-server.com/Search.ff', []);
    }

    /**
     * @testdox Correct response should be an associative array with 'searchResult' key
     */
    public function test_send_correct_request()
    {
        $response = '{"searchResult":{"breadCrumbTrailItems":[],"campaigns":[],"channel":"channel","fieldRoles":[]}}';
        $this->httpClientMock->method('getStatus')->willReturn(200);
        $this->httpClientMock->method('getBody')->willReturn($response);
        $this->httpClientMock->expects($this->once())->method('setOption')->with(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->serializerMock->expects($this->once())->method('unserialize')->willReturn(json_decode($response, true));

        $response = $this->client->sendRequest('http://fake-ff-server.com/rest/v3/search/channel', []);

        $this->assertArrayHasKey('searchResult', $response, 'Correct response should contains searchResult key');
    }

    /**
     * @testdox Response header should be returned if response has no body
     */
    public function test_return_response_headers_if_response_has_no_body()
    {
        $response = '';
        $responseHeaders = json_decode('{"Access-Control-Allow-Credentials":"true","Access-Control-Allow-Headers":"Content-Type, Depth, User-Agent, X-File-Size, X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control, x-gwt-module-base, x-gwt-permutation, origin, content-type, sid, Authorization","Access-Control-Allow-Methods":"OPTIONS, GET, POST","Access-Control-Allow-Origin":"*","Access-Control-Max-Age":"3600","Cache-Control":"no-cache, no-store, max-age=0, must-revalidate","Content-Length":"0","Date":"Wed, 23 Oct 2019 06:20:35 GMT","Expires":"0","Pragma":"no-cache","Vary":"Accept-Encoding","X-Content-Type-Options":"nosniff","X-Frame-Options":"DENY","X-Xss-Protection":"1; mode=block"}', true);
        $this->httpClientMock->method('getStatus')->willReturn(200);
        $this->httpClientMock->method('getBody')->willReturn($response);
        $this->httpClientMock->method('getHeaders')->willReturn($responseHeaders);
        $this->serializerMock->expects($this->never())->method('unserialize');
        $this->httpClientMock->expects($this->once())->method('getHeaders');

        $response = $this->client->sendRequest('http://fake-ff-server.com/rest/v3/search/channelName', [] );

        $this->assertArrayHasKey('Access-Control-Allow-Credentials', $response, 'Correct response in this case should contains contains response headers');
    }

    /**
     * @testdox The API credentials can be overwritten using credentials passed as an argument constructor
     */
    public function test_override_params()
    {
        $newUserName = 'OverrideUser';
        $newPassword = 'OverridePassword';
        $credentials = new Credentials($newUserName, $newPassword);
        $client = new Client(
            $this->createConfiguredMock(ClientFactory::class, ['create' => $this->httpClientMock]),
            $this->serializerMock,
            $this->createMock(AuthConfigInterface::class),
            $this->credentialsFactoryMock,
            $credentials
        );

        $this->httpClientMock->method('getStatus')->willReturn(200);
        $this->httpClientMock->expects($this->at(2))->method('addHeader')->with('Authorization', $credentials);
        $this->httpClientMock->method('getBody')->willReturn('{}');
        $this->serializerMock->expects($this->once())->method('unserialize')->willReturn([]);

        $client->sendRequest('http://fake-ff-server.com/rest/v3/search/channelName', []);
    }

    protected function setUp()
    {
        $this->serializerMock         = $this->createMock(SerializerInterface::class);
        $this->httpClientMock         = $this->createMock(ClientInterface::class);
        $this->credentialsFactoryMock = $this->getMockBuilder(CredentialsFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->credentialsFactoryMock->method('create')->willReturn(new Credentials('apiUser', 'apiPassword', 'FF', 'FF'));

        $this->client = new Client(
            $this->createConfiguredMock(ClientFactory::class, ['create' => $this->httpClientMock]),
            $this->serializerMock,
            $this->createMock(AuthConfigInterface::class),
            $this->credentialsFactoryMock
        );
    }
}

<?php

namespace Tests\Functional;

class UpdateTaskTest extends BaseTest
{
    public function testUpdateTaskOk()
    {
        $client = self::createClient();
        $client->request('PATCH', '/task/6', [
            'json' => '{"title":"PHPUnit Test", "description":"Do not remove this task ;-)", "status":"finished"}',
        ], [], ['HTTP_authorization' => $this->getAuthToken()]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertContains('success', $client->getResponse()->getContent());
        $this->assertNotContains('error', $client->getResponse()->getContent());
        $this->assertNotContains('not authorized', $client->getResponse()->getContent());
    }

    public function testUpdateTaskError()
    {
        $client = self::createClient();
        $client->request('PATCH', '/task/845');

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        $this->assertContains('error', $client->getResponse()->getContent());
        $this->assertContains('not authorized', $client->getResponse()->getContent());
        $this->assertNotContains('success', $client->getResponse()->getContent());
    }

    public function testUpdateTaskNotFound()
    {
        $client = self::createClient();
        $client->request('PATCH', '/task/1234567890', [
            'json' => '{"name":"Mau","surname":"B","email": "m@b.com.ar", "password": "123", "title":"test.", "description":"Mi test 1...", "status":"todo"}',
        ], [], ['HTTP_authorization' => $this->getAuthToken()]);

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertContains('error', $client->getResponse()->getContent());
        $this->assertContains('Task not found', $client->getResponse()->getContent());
        $this->assertNotContains('success', $client->getResponse()->getContent());
    }

    public function testUpdateTaskOwnerError()
    {
        $client = self::createClient();
        $client->request('PATCH', '/task/1', [
            'json' => '{"title":"Abc", "description":"123..."}',
        ], [], ['HTTP_authorization' => $this->getAuthToken()]);

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertContains('error', $client->getResponse()->getContent());
        $this->assertNotContains('success', $client->getResponse()->getContent());
    }
}

<?php

namespace Tests\Functional;

class GetOneTaskTest extends BaseTest
{
    public function testGetOneTaskOk()
    {
        $client = self::createClient();
        $client->request('GET', '/task/6', [], [], [
            'HTTP_authorization' => $this->getAuthToken(),
        ]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertContains('success', $client->getResponse()->getContent());
        $this->assertNotContains('error', $client->getResponse()->getContent());
        $this->assertNotContains('not authorized', $client->getResponse()->getContent());
    }

    public function testGetOneTaskNotFound()
    {
        $client = self::createClient();
        $client->request('GET', '/task/1', [], [], [
            'HTTP_authorization' => $this->getAuthToken(),
        ]);

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertContains('error', $client->getResponse()->getContent());
        $this->assertContains('Task not found', $client->getResponse()->getContent());
        $this->assertNotContains('success', $client->getResponse()->getContent());
    }

    public function testGetOneTaskError()
    {
        $client = self::createClient();
        $client->request('GET', '/task/1');

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        $this->assertContains('error', $client->getResponse()->getContent());
        $this->assertContains('not authorized', $client->getResponse()->getContent());
        $this->assertNotContains('success', $client->getResponse()->getContent());
    }
}

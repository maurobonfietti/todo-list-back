<?php

namespace Tests\Functional;

class UpdateStatusTaskTest extends BaseTest
{
    public function testUpdateTaskOk()
    {
        $client = self::createClient();
        $client->request(
            'PATCH', '/task/status/845', 
            [], 
            [], 
            ['HTTP_authorization' => $this->getAuthToken()]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertContains('success', $client->getResponse()->getContent());
        $this->assertNotContains('error', $client->getResponse()->getContent());
        $this->assertNotContains('not authorized', $client->getResponse()->getContent());
    }
}

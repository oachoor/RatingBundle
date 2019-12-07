<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RatingControllerTest extends WebTestCase
{
    public function testNotFoundContent()
    {
        $client = static::createClient();

        $client->request('GET', '/rating/result/999999');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}

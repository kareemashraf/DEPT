<?php
namespace App\Tests;

use App\Controller\IndexController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class IndexTest extends WebTestCase
{	
	
    public function testIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }


    public function testInvalidSearchRequest()
    {
        $client = static::createClient();

        $client->request('GET', '/search');

        $this->assertEquals(405, $client->getResponse()->getStatusCode());
    }


    public function testValidSearchRequest()
    {
        $client = static::createClient();

        $client->request('post', '/search');

        $this->assertEquals(500, $client->getResponse()->getStatusCode());
    }

    public function testValidAPIRequest()
    {
        $client = static::createClient();
        $test_movie = 'titanic'; //test movie title
        $client->request('GET', '/api/trailer/'.$test_movie);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent());

        $this->assertContains('id', $client->getResponse()->getContent());
        $this->assertContains('title', $client->getResponse()->getContent());
        $this->assertContains('vote_average', $client->getResponse()->getContent());
        $this->assertContains('video', $client->getResponse()->getContent());
        $this->assertContains('vimeo', $client->getResponse()->getContent());
        $this->assertContains('description', $client->getResponse()->getContent());
        $this->assertContains('overview', $client->getResponse()->getContent());
        $items = json_decode($client->getResponse()->getContent());
        $this->assertCount(20, $items );

    }

    

}
<?php

use Silex\WebTestCase;

class ApplicationTest extends WebTestCase
{
   public function createApplication()
   {
      $app = require __DIR__ . '/../../src/app.php';
      return $app;
   }

   public function testInitialPage()
   {
      $client  = $this->createClient();
      $crawler = $client->request('GET', '/');
      $this->assertTrue($client->getResponse()->isOk());
   }

   public function test404()
   {
      $client = $this->createClient();
      $client->request('GET', '/give-me-a-404');
      $this->assertEquals(404, $client->getResponse()->getStatusCode());
   }
}
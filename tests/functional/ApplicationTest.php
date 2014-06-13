<?php

use Silex\WebTestCase;

class ApplicationTest extends WebTestCase
{
   public function createApplication()
   {
      require __DIR__ . '/../../vendor/autoload.php';

      $app = new Silex\Application();
      require __DIR__ . '/../../src/Config/dev.php';
      require __DIR__ . '/../../src/app.php';
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

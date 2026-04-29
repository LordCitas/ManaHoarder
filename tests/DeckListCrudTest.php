<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DeckListCrudTest extends WebTestCase
{
    public function testAnonymousIsRedirectedFromLists(): void
    {
        $client = static::createClient();
        $client->request('GET', '/lists');

        // Debe redirigir a login (por denyAccessUnlessGranted)
        $this->assertResponseStatusCodeSame(302);
        $this->assertTrue($client->getResponse()->headers->has('Location'));
        $this->assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'));
    }
}


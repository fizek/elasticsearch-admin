<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class ElasticsearchCatControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/cat", name="cat")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/cat');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Compact and aligned text (CAT) APIs');
        $this->assertSelectorTextSame('h1', 'Compact and aligned text (CAT) APIs');
    }
}

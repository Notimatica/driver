<?php

namespace Notimatica\Driver\Tests\Support;

use Notimatica\Driver\Support\HasHttpsImage;
use Notimatica\Driver\Tests\TestCase;

class HasHttpsImageTest extends TestCase
{
    /** @test */
    public function it_makes_https_images_from_http()
    {
        $trait = $this->getMockForTrait(HasHttpsImage::class);

        $this->assertEquals('', $trait->ensureHttps(''));
        $this->assertEquals('https://foo.bar/image.png', $trait->ensureHttps('https://foo.bar/image.png'));
        $this->assertEquals('ssl://foo.bar/image.png', $trait->ensureHttps('ssl://foo.bar/image.png'));
        $this->assertEquals('http://localhost/image.png', $trait->ensureHttps('http://localhost/image.png'));
        $this->assertEquals('http://images.weserv.nl/image.png', $trait->ensureHttps('http://images.weserv.nl/image.png'));
        $this->assertEquals('https://images.weserv.nl/?url=foo.bar/image.png', $trait->ensureHttps('http://foo.bar/image.png'));
    }
}

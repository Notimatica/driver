<?php

namespace Notimatica\Driver\Tests\Support;

use Mockery as m;
use Notimatica\Driver\Support\MakesUrls;
use Notimatica\Driver\Tests\TestCase;

class MakesUrlsTest extends TestCase
{
    /** @test */
    public function it_makes_click_url_from_notification()
    {
        $notification = $this->makeNotification();
        $project = $this->makeProject();
        $trait = $this->getMockForTrait(MakesUrls::class);

        $trait->setProject($project);
        $this->assertEquals('https://localhost/click', $trait->makeClickUrl($notification));
    }

    /** @test */
    public function it_makes_click_url_from_project()
    {
        $project = $this->makeProject();
        $trait = $this->getMockForTrait(MakesUrls::class);

        $trait->setProject($project);
        $this->assertEquals('https://localhost', $trait->makeClickUrl());
    }

    /** @test */
    public function it_makes_icon_url()
    {
        $project = $this->makeProject();
        $trait = $this->getMockForTrait(MakesUrls::class);

        $trait->setProject($project);
        $this->assertEquals('https://localhost/icon.png', $trait->makeIconUrl());
    }
}
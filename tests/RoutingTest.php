<?php

namespace ManeOlawale\Superban\Tests;

use Illuminate\Foundation\Auth\User;
use Spatie\TestTime\TestTime;

class RoutingTest extends TestBench
{
    public function testRoutingCanPassThrough(): void
    {
        $now = TestTime::freeze();
        $this->get('/index')->assertContent('ok')->assertStatus(200);
        $this->get('/superban')->assertContent('ok')->assertStatus(200);
        $this->get('/superban_route')->assertContent('ok')->assertStatus(200);
        $this->get('/superban_custom')->assertContent('ok')->assertStatus(200);
        $this->get('/superban_route_custom')->assertContent('ok')->assertStatus(200);
    }

    public function testRoutingRateLimitTest(): void
    {
        $now = TestTime::freeze();

        for ($i=0; $i < 200; $i++) {
            $this->get('/superban')->assertContent('ok')->assertStatus(200);
        }

        $this->get('/superban')
            ->assertContent(
                'Sorry, you\'re temporarily banned. Please return after ' . $now->addMinutes(1440)->format('M d, Y, g:i a') . '.'
            )->assertStatus(403);
        
        TestTime::addDays(2);
        $this->get('/superban')->assertContent('ok')->assertStatus(200);
    }

    public function testRoutingRateLimitRouteTest(): void
    {
        $now = TestTime::freeze();

        for ($i=0; $i < 200; $i++) {
            $this->get('/superban_route')->assertContent('ok')->assertStatus(200);
        }

        $this->get('/superban_route')
            ->assertContent(
                'Sorry, you\'re temporarily banned. Please return after ' . $now->addMinutes(1440)->format('M d, Y, g:i a') . '.'
            )->assertStatus(403);
        
        TestTime::addDays(2);
        $this->get('/superban_route')->assertContent('ok')->assertStatus(200);
    }

    public function testRoutingRateLimitCustomTest(): void
    {
        $now = TestTime::freeze();

        for ($i=0; $i < 150; $i++) {
            $this->get('/superban_custom')->assertContent('ok')->assertStatus(200);
        }

        $this->get('/superban_custom')
            ->assertContent(
                'Sorry, you\'re temporarily banned. Please return after ' . $now->addMinutes(2880)->format('M d, Y, g:i a') . '.'
            )->assertStatus(403);
        
        TestTime::addDays(3);
        $this->get('/superban_custom')->assertContent('ok')->assertStatus(200);
    }

    public function testRoutingRateLimitRouteCustomTest(): void
    {
        $now = TestTime::freeze();

        for ($i=0; $i < 150; $i++) {
            $this->get('/superban_custom')->assertContent('ok')->assertStatus(200);
        }

        $this->get('/superban_custom')
            ->assertContent(
                'Sorry, you\'re temporarily banned. Please return after ' . $now->addMinutes(2880)->format('M d, Y, g:i a') . '.'
            )->assertStatus(403);
        
        TestTime::addDays(3);
        $this->get('/superban_custom')->assertContent('ok')->assertStatus(200);
    }

    public function testRoutingRateLimitUserTest(): void
    {
        $now = TestTime::freeze();
        $this->actingAs((new User())->forceFill(['id' => 1,'email' => 'horlawaley001gmail.com']));

        for ($i=0; $i < 200; $i++) {
            $this->get('/superban')->assertContent('ok')->assertStatus(200);
        }

        $this->get('/superban')
            ->assertContent(
                'Sorry, you\'re temporarily banned. Please return after ' . $now->addMinutes(1440)->format('M d, Y, g:i a') . '.'
            )->assertStatus(403);
        
        TestTime::addDays(2);
        $this->get('/superban')->assertContent('ok')->assertStatus(200);
    }

    public function testRoutingRateLimitRouteUserTest(): void
    {
        $now = TestTime::freeze();
        $this->actingAs((new User())->forceFill(['id' => 1,'email' => 'horlawaley001gmail.com']));

        for ($i=0; $i < 200; $i++) {
            $this->get('/superban_route')->assertContent('ok')->assertStatus(200);
        }

        $this->get('/superban_route')
            ->assertContent(
                'Sorry, you\'re temporarily banned. Please return after ' . $now->addMinutes(1440)->format('M d, Y, g:i a') . '.'
            )->assertStatus(403);
        
        TestTime::addDays(2);
        $this->get('/superban_route')->assertContent('ok')->assertStatus(200);
    }

    public function testRoutingRateLimitCustomUserTest(): void
    {
        $now = TestTime::freeze();
        $this->actingAs((new User())->forceFill(['id' => 1,'email' => 'horlawaley001gmail.com']));

        for ($i=0; $i < 150; $i++) {
            $this->get('/superban_custom')->assertContent('ok')->assertStatus(200);
        }

        $this->get('/superban_custom')
            ->assertContent(
                'Sorry, you\'re temporarily banned. Please return after ' . $now->addMinutes(2880)->format('M d, Y, g:i a') . '.'
            )->assertStatus(403);
        
        TestTime::addDays(3);
        $this->get('/superban_custom')->assertContent('ok')->assertStatus(200);
    }

    public function testRoutingRateLimitRouteCustomUserTest(): void
    {
        $now = TestTime::freeze();
        $this->actingAs((new User())->forceFill(['id' => 1,'email' => 'horlawaley001gmail.com']));

        for ($i=0; $i < 150; $i++) {
            $this->get('/superban_custom')->assertContent('ok')->assertStatus(200);
        }

        $this->get('/superban_custom')
            ->assertContent(
                'Sorry, you\'re temporarily banned. Please return after ' . $now->addMinutes(2880)->format('M d, Y, g:i a') . '.'
            )->assertStatus(403);
        
        TestTime::addDays(3);
        $this->get('/superban_custom')->assertContent('ok')->assertStatus(200);
    }
}

<?php

namespace ManeOlawale\Superban\Tests;

use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use ManeOlawale\Superban\Superban;
use Spatie\TestTime\TestTime;

class SuperbanTest extends TestBench
{
    public function testSuperbanInstance()
    {
        $now = TestTime::freeze();
        $superban = new Superban(
            $this->makeRequest(),
            1440
        );

        $this->assertSame('superban-20.10.2020', $superban->key());
        $this->assertFalse($superban->banned());
        $this->assertTrue($superban->untill()->equalTo($now->addMinutes(1440)));
        $this->assertInstanceOf(Response::class, $response = $superban->banResponse());
        $this->assertSame(
            'Sorry, you\'re temporarily banned. Please return after ' . $now->addMinutes(1440)->format('M d, Y, g:i a') . '.',
            $response->getContent()
        );
        $this->assertSame(
            403,
            $response->getStatusCode()
        );
    }

    public function testSuperbanInstanceWithUser()
    {
        $user = (new User())->forceFill(['id' => 1,'email' => 'horlawaley001gmail.com']);

        $now = TestTime::freeze();
        $superban = new Superban(
            $this->makeRequest($user),
            1440
        );

        $this->assertSame('superban-20.10.2020-1-horlawaley001gmail.com', $superban->key());
        $this->assertFalse($superban->banned());
        $this->assertTrue($superban->untill()->equalTo($now->addMinutes(1440)));
    }

    public function testSuperbanInstanceAppendedKey()
    {
        $superban = new Superban(
            $this->makeRequest(),
            1440,
            ':appended'
        );

        $this->assertSame('superban-20.10.2020:appended', $superban->key());
    }

    public function testSuperbanInstanceBan()
    {
        $now = TestTime::freeze();
        $superban = new Superban(
            $this->makeRequest(),
            1440
        );

        $this->assertSame('superban-20.10.2020', $superban->key());
        $this->assertTrue($superban->ban());
        $this->assertTrue($superban->banned());
        $this->assertTrue($superban->untill()->equalTo(Carbon::createFromTimestamp($now->addMinutes(1440)->getTimestamp())));
    }

    public function testSuperbanInstanceResponseUsing()
    {
        Superban::banResponseUsing(function (Request $request, Carbon $untill) {
            return response('Hands up!', 401);
        });

        $now = TestTime::freeze();
        $superban = new Superban(
            $this->makeRequest(),
            1440
        );
        $this->assertInstanceOf(Response::class, $response = $superban->banResponse());
        $this->assertSame('Hands up!', $response->getContent());
        $this->assertSame(401, $response->getStatusCode());
        Superban::banResponseUsing();
        $this->assertInstanceOf(Response::class, $response = $superban->banResponse());
        $this->assertSame(
            'Sorry, you\'re temporarily banned. Please return after ' . $now->addMinutes(1440)->format('M d, Y, g:i a') . '.',
            $response->getContent()
        );
        $this->assertSame(
            403,
            $response->getStatusCode()
        );
    }
}

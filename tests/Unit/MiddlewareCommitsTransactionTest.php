<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\TestCase;

class MiddlewareCommitsTransactionTest extends TestCase
{
    public function testMiddlewareCommitsTransactionOnSuccess()
    {
        $response = Mockery::mock(\Illuminate\Http\Response::class);
        $response->shouldReceive('status')->andReturn(200);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldNotReceive('rollBack');

        $request = Request::create('/test', 'GET');
        $next = fn($req) => $response;

        $middleware = new \App\Http\Middleware\DbTransactionMiddleware();
        $result = $middleware->handle($request, $next);

        $this->assertEquals(200, $result->status());
    }

    public function testMiddlewareRollsBackTransactionOnError()
    {
        $response = Mockery::mock(\Illuminate\Http\Response::class);
        $response->shouldReceive('status')->andReturn(500);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();
        DB::shouldNotReceive('commit');

        $request = Request::create('/test', 'GET');
        $next = fn($req) => $response;

        $middleware = new \App\Http\Middleware\DbTransactionMiddleware();
        $result = $middleware->handle($request, $next);

        $this->assertEquals(500, $result->status());
    }

    public function testMiddlewareRollsBackTransactionOnException()
    {
        $response = Mockery::mock(\Illuminate\Http\Response::class);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();
        DB::shouldNotReceive('commit');

        $request = Request::create('/test', 'GET');

        $next = fn($req) => throw new \Exception('Something went wrong');

        $middleware = new \App\Http\Middleware\DbTransactionMiddleware();

        try {
            $middleware->handle($request, $next);
        } catch (\Exception $exception) {
            $this->assertEquals('Something went wrong', $exception->getMessage());
        }
    }
}

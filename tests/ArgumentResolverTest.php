<?php

use PHPUnit\Framework\TestCase;
use Venta\Container\ArgumentResolver;
use Venta\Contracts\Container\Container;

class ArgumentResolverTest extends TestCase
{

    public function tearDown()
    {
        Mockery::close();
    }

    /**
     * @test
     */
    public function canResolveOptionalArguments()
    {
        $resolver = new ArgumentResolver(Mockery::mock(Container::class));
        $function = function (string $scalar = 'reso') {
            return $scalar . 'lved';
        };
        $closure = $resolver->createCallback(new ReflectionFunction($function));
        $arguments = $closure();

        $this->assertSame(['reso'], $arguments);
        $this->assertSame('resolved', $function(...$arguments));
    }

    /**
     * @test
     */
    public function canResolveWithClassArguments()
    {
        $mock = Mockery::mock(TestClassContract::class);
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('has')->with(TestClassContract::class)->andReturn(true)->once();
        $container->shouldReceive('get')->with(TestClassContract::class)->andReturn($mock)->once();

        $function = function (TestClassContract $test) {
            return $test;
        };

        $resolver = new ArgumentResolver($container);
        $closure = $resolver->createCallback(new ReflectionFunction($function));

        $arguments = $closure();

        $this->assertSame([$mock], $arguments);
        $this->assertSame($mock, $function(...$arguments));
    }

    /**
     * @test
     */
    public function canResolveWithPassedArguments()
    {
        $resolver = new ArgumentResolver(Mockery::mock(Container::class));
        $function = function (string $scalar) {
            return $scalar . 'd';
        };
        $closure = $resolver->createCallback(new ReflectionFunction($function));
        $arguments = $closure(['resolve']);

        $this->assertSame(['resolve'], $arguments);
        $this->assertSame('resolved', $function(...$arguments));
    }

    /**
     * @test
     */
    public function canResolveWithoutArguments()
    {
        $resolver = new ArgumentResolver(Mockery::mock(Container::class));
        $function = function () {
            return 'resolved';
        };
        $closure = $resolver->createCallback(new ReflectionFunction($function));
        $arguments = $closure();

        $this->assertSame([], $arguments);
        $this->assertSame('resolved', $function(...$arguments));
    }

    /**
     * @test
     * @expectedException \Venta\Container\Exception\ArgumentResolverException
     * @expectedExceptionMessage test
     */
    public function failsToResolveMandatoryClassArgumentsContainerMisses()
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('has')->with(TestClassContract::class)->andReturn(false)->once();
        $container->shouldNotReceive('get');

        $function = function (TestClassContract $test) {
            return $test;
        };

        $resolver = new ArgumentResolver($container);
        $closure = $resolver->createCallback(new ReflectionFunction($function));

        $closure();
    }

    /**
     * @test
     *
     * @expectedException \Venta\Container\Exception\ArgumentResolverException
     * @expectedExceptionMessage scalar
     */
    public function failsToResolveMandatoryScalarArguments()
    {
        $resolver = new ArgumentResolver(Mockery::mock(Container::class));
        $function = function (string $scalar) {
            return $scalar . 'd';
        };
        $closure = $resolver->createCallback(new ReflectionFunction($function));
        $closure();
    }

}

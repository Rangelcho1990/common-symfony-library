<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Module\Endpoint;

use CSL\Endpoints\Examples\ExampleList\Controller\Transformer\Response\ExampleTransformer;
use CSL\Module\Endpoint\Transformer\Response\Provider\ResponseTransformerProvider;
use CSL\Module\Endpoint\Transformer\Response\ResponseTransformerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ResponseTransformerProviderTest extends TestCase
{
    public function testRetrievesTransformerWithConstructorDependencyFromCompiledLocator(): void
    {
        $container = new ContainerBuilder();
        $container->registerForAutoconfiguration(ResponseTransformerInterface::class)->addTag(ResponseTransformerInterface::class);
        $container->register('prefix', \stdClass::class);
        $container->register(DependencyTransformer::class)->setAutowired(true)->setAutoconfigured(true)
            ->setArgument('$dependency', new Reference('prefix'));
        $container->register(ResponseTransformerProvider::class)->setAutowired(true)->setPublic(true);
        $container->compile();
        $provider = $container->get(ResponseTransformerProvider::class);
        self::assertInstanceOf(ResponseTransformerProvider::class, $provider);
        $transformer = $provider->get(DependencyTransformer::class);
        self::assertInstanceOf(DependencyTransformer::class, $transformer);
        self::assertSame('{"injected":true}', $transformer->transformContent());
        self::assertSame($transformer, $provider->get(DependencyTransformer::class));
    }

    public function testMissingServiceIsReported(): void
    {
        $locator = $this->createMock(ContainerInterface::class);
        $locator->expects(self::once())->method('has')->with(ExampleTransformer::class)->willReturn(false);
        $locator->expects(self::never())->method('get');
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('must be registered');
        (new ResponseTransformerProvider($locator))->get(ExampleTransformer::class);
    }

    public function testWrongServiceTypeIsReported(): void
    {
        $locator = $this->createStub(ContainerInterface::class);
        $locator->method('has')->willReturn(true);
        $locator->method('get')->willReturn(new \stdClass());
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('must implement');
        (new ResponseTransformerProvider($locator))->get(ExampleTransformer::class);
    }
}

class DependencyTransformer implements ResponseTransformerInterface
{
    public function __construct(private readonly \stdClass $dependency)
    {
    }

    public function transformContent(): string
    {
        return json_encode(['injected' => [] === get_object_vars($this->dependency)], JSON_THROW_ON_ERROR);
    }

    public function getStatusCode(): int
    {
        return 202;
    }

    public function getContentType(): string
    {
        return 'application/json';
    }
}

<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Module\Endpoint {
    use CSL\Endpoints\Examples\ExampleList\Controller\ExampleController;
    use CSL\Endpoints\Examples\ExampleList\Controller\Transformer\Response\ExampleTransformer;
    use CSL\Module\Endpoint\Resolver\ControllerClassResolverInterface;
    use CSL\Module\Endpoint\Transformer\Response\Validation\ResponseTransformerValidator;
    use PHPUnit\Framework\Attributes\DataProvider;
    use PHPUnit\Framework\TestCase;

    final class ResponseTransformerValidatorTest extends TestCase
    {
        public function testResolvesExistingResponseTransformerWithoutExecutingIt(): void
        {
            $resolver = $this->createMock(ControllerClassResolverInterface::class);
            $resolver->expects(self::once())->method('resolve')->with('arbitrary_route_name')->willReturn(ExampleController::class);
            self::assertSame(ExampleTransformer::class, (new ResponseTransformerValidator($resolver))->validate('arbitrary_route_name'));
        }

        /** @return iterable<string, array{class-string, string}> */
        public static function invalidControllers(): iterable
        {
            yield 'missing response despite request transformer' => [\CSL\Endpoints\ValidatorFixture\Controller\MissingController::class, 'Transformer\\Response\\MissingTransformer'];
            yield 'abstract transformer' => [\CSL\Endpoints\ValidatorFixture\Controller\AbstractController::class, 'must be a concrete class'];
            yield 'external controller' => [\stdClass::class, 'must follow CSL\\Endpoints'];
            yield 'invalid controller name' => [\CSL\Endpoints\ValidatorFixture\Controller\Invalid::class, 'must follow CSL\\Endpoints'];
        }

        /** @param class-string $controller */
        #[DataProvider('invalidControllers')]
        public function testRejectsInvalidEndpoint(string $controller, string $message): void
        {
            $resolver = $this->createStub(ControllerClassResolverInterface::class);
            $resolver->method('resolve')->willReturn($controller);
            $this->expectException(\LogicException::class);
            $this->expectExceptionMessage($message);
            (new ResponseTransformerValidator($resolver))->validate('fixture_route');
        }

        public function testDoesNotConstructTransformerOrRequireOtherEndpointClasses(): void
        {
            $resolver = $this->createStub(ControllerClassResolverInterface::class);
            $resolver->method('resolve')->willReturn(\CSL\Endpoints\ValidatorFixture\Controller\ValidController::class);
            self::assertSame(\CSL\Endpoints\ValidatorFixture\Controller\Transformer\Response\ValidTransformer::class, (new ResponseTransformerValidator($resolver))->validate('fixture_route'));
        }
    }
}

namespace CSL\Endpoints\ValidatorFixture\Controller {
    class MissingController
    {
    }
    class AbstractController
    {
    }
    class Invalid
    {
    }
    class ValidController
    {
    }
}

namespace CSL\Endpoints\ValidatorFixture\Controller\Transformer\Request {
    class MissingTransformer
    {
    }
}

namespace CSL\Endpoints\ValidatorFixture\Controller\Transformer\Response {
    abstract class AbstractTransformer
    {
    }
    class ValidTransformer
    {
        public function __construct()
        {
            throw new \LogicException('Validation must not instantiate the transformer.');
        }
    }
}

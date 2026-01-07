<?php

declare(strict_types=1);

namespace CSL\Endpoints\Examples\ExampleList\Controller\Transformer\Response;

use Symfony\Component\HttpFoundation\Response;

class ExampleTransformer
{
    public function getStatusCode(): int
    {
        // TODO: move to abstract base class.
        return Response::HTTP_OK;
    }

    public function getContentType(): string
    {
        // TODO: move to abstract base class.
        return 'application/json';
    }

    public function transformContent(): string
    {
        // 1. Prepare response => use abstract base class.
        // 2. check for error and transform the error if need it.
        // 3. Transform data if need it.

        $data = json_encode([
            'success' => true,
            'data' => [
                'id' => 1,
                'name' => 'Example',
            ],
        ]);

        if (!$data) {
            return '';
        }

        return $data;
    }
}

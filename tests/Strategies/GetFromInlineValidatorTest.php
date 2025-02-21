<?php

namespace Knuckles\Scribe\Tests\Strategies;

use Closure;
use Knuckles\Camel\Extraction\ExtractedEndpointData;
use Knuckles\Scribe\Extracting\Strategies\BodyParameters;
use Knuckles\Scribe\Extracting\Strategies\QueryParameters;
use Knuckles\Scribe\Tests\BaseLaravelTest;
use Knuckles\Scribe\Tests\Fixtures\TestController;
use Knuckles\Scribe\Tools\DocumentationConfig;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

class GetFromInlineValidatorTest extends BaseLaravelTest
{
    use ArraySubsetAsserts;

    private static $expected = [
        'user_id' => [
            'type' => 'integer',
            'required' => true,
            'description' => 'The id of the user.',
            'example' => 9,
        ],
        'room_id' => [
            'type' => 'string',
            'required' => false,
            'description' => 'The id of the room. Must be one of <code>3</code>, <code>5</code>, or <code>6</code>.',
        ],
        'forever' => [
            'type' => 'boolean',
            'required' => false,
            'description' => 'Whether to ban the user forever.',
            'example' => false,
        ],
        'another_one' => [
            'type' => 'number',
            'required' => false,
            'description' => 'Just need something here.',
        ],
        'even_more_param' => [
            'type' => 'string[]',
            'required' => false,
            'description' => '',
        ],
        'book' => [
            'type' => 'object',
            'description' => '',
            'required' => false,
            'example' => [],
        ],
        'book.name' => [
            'type' => 'string',
            'description' => '',
            'required' => false,
        ],
        'book.author_id' => [
            'type' => 'integer',
            'description' => '',
            'required' => false,
        ],
        'book.pages_count' => [
            'type' => 'integer',
            'description' => '',
            'required' => false,
        ],
        'ids' => [
            'type' => 'integer[]',
            'description' => '',
            'required' => false,
        ],
        'users' => [
            'type' => 'object[]',
            'description' => '',
            'required' => false,
            'example' => [[]],
        ],
        'users[].first_name' => [
            'type' => 'string',
            'description' => 'The first name of the user.',
            'required' => false,
            'example' => 'John',
        ],
        'users[].last_name' => [
            'type' => 'string',
            'description' => 'The last name of the user.',
            'required' => false,
            'example' => 'Doe',
        ],
    ];

    /** @test */
    public function can_fetch_from_request_validate_assignment()
    {
        $endpoint = $this->endpoint(function (ExtractedEndpointData $e) {
            $e->method = new \ReflectionMethod(TestController::class, 'withInlineRequestValidate');
        });

        $results = $this->fetchViaBodyParams($endpoint);

        $this->assertArraySubset(self::$expected, $results);
        $this->assertIsArray($results['ids']['example']);
    }

    /** @test */
    public function can_fetch_from_request_validate_expression()
    {
        $endpoint = $this->endpoint(function (ExtractedEndpointData $e) {
            $e->method = new \ReflectionMethod(TestController::class, 'withInlineRequestValidateNoAssignment');
        });

        $results = $this->fetchViaBodyParams($endpoint);

        $this->assertArraySubset(self::$expected, $results);
        $this->assertIsArray($results['ids']['example']);
    }

    /** @test */
    public function can_fetch_from_request_validatewithbag()
    {
        $endpoint = $this->endpoint(function (ExtractedEndpointData $e) {
            $e->method = new \ReflectionMethod(TestController::class, 'withInlineRequestValidateWithBag');
        });

        $results = $this->fetchViaBodyParams($endpoint);

        $this->assertArraySubset(self::$expected, $results);
        $this->assertIsArray($results['ids']['example']);
    }

    /** @test */
    public function can_fetch_from_this_validate()
    {
        $endpoint = $this->endpoint(function (ExtractedEndpointData $e) {
            $e->method = new \ReflectionMethod(TestController::class, 'withInlineThisValidate');
        });

        $results = $this->fetchViaBodyParams($endpoint);

        $this->assertArraySubset(self::$expected, $results);
        $this->assertIsArray($results['ids']['example']);
    }

    /** @test */
    public function can_fetch_from_validator_make()
    {
        $endpoint = $this->endpoint(function (ExtractedEndpointData $e) {
            $e->method = new \ReflectionMethod(TestController::class, 'withInlineValidatorMake');
        });

        $results = $this->fetchViaBodyParams($endpoint);

        $this->assertArraySubset(self::$expected, $results);
        $this->assertIsArray($results['ids']['example']);
    }

    /** @test */
    public function respects_query_params_comment()
    {
        $queryParamsEndpoint = $this->endpoint(function (ExtractedEndpointData $e) {
            $e->method = new \ReflectionMethod(TestController::class, 'withInlineRequestValidateQueryParams');
        });

        $results = $this->fetchViaBodyParams($queryParamsEndpoint);
        $this->assertEquals([], $results);

        $results = $this->fetchViaQueryParams($queryParamsEndpoint);
        $this->assertArraySubset(self::$expected, $results);
        $this->assertIsArray($results['ids']['example']);

        $bodyParamsEndpoint = $this->endpoint(function (ExtractedEndpointData $e) {
            $e->method = new \ReflectionMethod(TestController::class, 'withInlineRequestValidate');
        });
        $results = $this->fetchViaQueryParams($bodyParamsEndpoint);
        $this->assertEquals([], $results);
    }

    protected function endpoint(Closure $configure): ExtractedEndpointData
    {
        $endpoint = new class extends ExtractedEndpointData {
            public function __construct(array $parameters = [])
            {
            }
        };
        $configure($endpoint);
        return $endpoint;
    }

    protected function fetchViaBodyParams(ExtractedEndpointData $endpoint): ?array
    {
        $strategy = new BodyParameters\GetFromInlineValidator(new DocumentationConfig([]));
        return $strategy($endpoint, []);
    }

    protected function fetchViaQueryParams(ExtractedEndpointData $endpoint): ?array
    {
        $strategy = new QueryParameters\GetFromInlineValidator(new DocumentationConfig([]));
        return $strategy($endpoint, []);
    }
}

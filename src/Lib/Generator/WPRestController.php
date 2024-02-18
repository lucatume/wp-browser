<?php

namespace lucatume\WPBrowser\Lib\Generator;

class WPRestController extends AbstractGenerator
{
    protected string $template = <<<EOF
<?php
{{namespace}}
class {{name}}Test extends \lucatume\WPBrowser\TestCase\WPRestControllerTestCase
{
    {{tester}}
    private \WP_REST_Controller|null \$controller = null;

    public function setUp(): void
    {
        // Before...
        parent::setUp();

        // Your set up methods here.
        if (\$this->controller !== null) {
            // Do not register the controller more than once.
            return;
        }
        
        \$controller = new class extends \WP_REST_Controller {
            private array \$data = [
                'minion' => [
                    'label' => 'Minion',
                    'count' => 89,
                ],
                'villain' => [
                    'label' => 'Villain',
                    'count' => 23,
                ],
                'bbeg' => [
                    'label' => 'Big Bad Evil Guy',
                    'count' => 1,
                ],
            ];

            public function anyone(): bool
            {
                return true;
            }

            public function adminsOnly(): bool
            {
                return current_user_can('manage_options');
            }

            public function greet(\WP_REST_Request \$request): \WP_REST_Response
            {
                \$response = new \WP_REST_Response();
                \$name = \$request->get_param('name');
                \$greet = sprintf('Hello %s!', \$name);
                \$response->set_data(\$greet);

                return \$response;
            }

            public function get_item_schema(): array
            {
                return [
                    '\$schema' => 'http://json-schema.org/draft-04/schema#',
                    'title' => 'adversary',
                    'type' => 'object',
                    'properties' => [
                        'label' => [
                            'description' => 'The adversary display name',
                            'type' => 'string',
                            'context' => ['view', 'edit'],
                        ],
                        'count' => [
                            'description' => 'The adversary current count',
                            'type' => 'integer',
                            'context' => ['edit'],
                        ]
                    ]
                ];
            }

            public function prepare_item_for_response(\$item, \$request): array
            {
                \$context = \$request->get_param('context');
                \$item['count'] = (int)\$item['count'];

                if (\$context === 'edit') {
                    return \$item;
                }

                return array_diff_key(\$item, ['count' => true]);
            }

            public function getAdversary(\WP_REST_Request \$request): \WP_REST_Response
            {
                \$name = \$request->get_param('name');
                \$item = \$this->data[\$name] ?? null;

                if (\$item === null) {
                    return new \WP_REST_Response([], 404);
                }

                \$item = \$this->prepare_item_for_response(\$item, \$request);

                return new \WP_REST_Response(\$item);
            }

            public function getAdversaries(\WP_REST_Request \$request): \WP_REST_Response
            {
                \$data = \$this->data;

                foreach (\$data as &\$item) {
                    \$item = \$this->prepare_item_for_response(\$item, \$request);
                }

                return new \WP_REST_Response(\$data);
            }

            public function upsertAdversary(\WP_REST_Request \$request): \WP_REST_Response
            {
                \$name = (string)\$request->get_param('name');
                \$update = isset(\$this->data[\$name]);
                \$label = (string)\$request->get_param('label') ?: \$this->data[\$name]['label'];
                \$count = (string)\$request->get_param('count') ?: \$this->data[\$name]['count'];

                \$item = compact('label', 'count');
                \$this->data[\$name] = \$item;

                return new \WP_REST_Response(\$item, \$update ? 200 : 201);
            }

            public function deleteAdversary(\WP_REST_Request \$request): \WP_REST_Response
            {
                \$name = \$request->get_param('name');

                \$item = \$this->data[\$name] ?? null;

                if (\$item === null) {
                    return new \WP_REST_Response('NOT FOUND', 404);
                }

                \$this->data = array_diff_key(\$this->data, [\$name => true]);

                return new \WP_REST_Response(\$item, 200);
            }
        };

        \$this->controller = \$controller;

        register_rest_route(
            'example',
            'greet/(?P<name>[\w-]+)',
            [
                'methods' => 'GET',
                'callback' => [\$controller, 'greet'],
                'permission_callback' => [\$controller, 'anyone'],
            ]
        );

        register_rest_route(
            'example',
            'adversaries',
            [
                'methods' => 'GET',
                'callback' => [\$controller, 'getAdversaries'],
                'permission_callback' => [\$controller, 'anyone'],
                'schema' => [\$controller, 'get_item_schema'],
            ],
        );

        register_rest_route(
            'example',
            'adversaries/(?<name>[\w-]*)',
            [
                'methods' => 'GET',
                'callback' => [\$controller, 'getAdversary'],
                'permission_callback' => [\$controller, 'anyone'],
                'schema' => [\$controller, 'get_item_schema'],
            ],
        );

        register_rest_route(
            'example',
            'adversary',
            [
                'methods' => ['POST', 'PUT'],
                'callback' => [\$controller, 'upsertAdversary'],
                'permission_callback' => [\$controller, 'adminsOnly'],
                'schema' => [\$controller, 'get_item_schema'],
            ],
        );

        register_rest_route(
            'example',
            'adversary',
            [
                'methods' => 'DELETE',
                'callback' => [\$controller, 'deleteAdversary'],
                'permission_callback' => [\$controller, 'adminsOnly'],
                'schema' => [\$controller, 'get_item_schema'],
            ],
        );
    }

    public function tearDown(): void
    {
        // Your tear down methods here.

        // Then...
        parent::tearDown();
    }

    public function test_register_routes()
    {
        \$routes = rest_get_server()->get_routes();
        \$this->assertArrayHasKey('/example', \$routes);
        \$this->assertCount(1, \$routes['/example']);
        \$this->assertArrayHasKey('/example/greet/(?P<name>[\w-]+)', \$routes);
        \$this->assertCount(1, \$routes['/example/greet/(?P<name>[\w-]+)']);
        \$this->assertArrayHasKey('/example/adversaries', \$routes);
        \$this->assertCount(1, \$routes['/example/adversaries']);
    }

    public function test_context_param()
    {
        \$request = new \WP_REST_Request('GET', '/example/adversaries');

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(
            [
                'minion' => ['label' => 'Minion'],
                'villain' => ['label' => 'Villain'],
                'bbeg' => ['label' => 'Big Bad Evil Guy'],
            ],
            \$response->data
        );

        \$request = new \WP_REST_Request('GET', '/example/adversaries');
        \$request->set_param('context', 'view');

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(
            [
                'minion' => ['label' => 'Minion'],
                'villain' => ['label' => 'Villain'],
                'bbeg' => ['label' => 'Big Bad Evil Guy'],
            ],
            \$response->data
        );

        \$request = new \WP_REST_Request('GET', '/example/adversaries');
        \$request->set_param('context', 'edit');

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(
            [
                'minion' => [
                    'label' => 'Minion',
                    'count' => 89,
                ],
                'villain' => [
                    'label' => 'Villain',
                    'count' => 23,
                ],
                'bbeg' => [
                    'label' => 'Big Bad Evil Guy',
                    'count' => 1,
                ],
            ],
            \$response->data
        );
    }

    public function test_get_items()
    {
        \$request = new \WP_REST_Request('GET', '/example/adversaries');

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(
            [
                'minion' => ['label' => 'Minion'],
                'villain' => ['label' => 'Villain'],
                'bbeg' => ['label' => 'Big Bad Evil Guy'],
            ],
            \$response->data
        );
    }

    public function test_get_item()
    {
        \$request = new \WP_REST_Request('GET', '/example/adversaries/bbeg');

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(['label' => 'Big Bad Evil Guy'], \$response->data);

        \$request = new \WP_REST_Request('GET', '/example/adversaries/bbeg');
        \$request->set_param('context', 'edit');

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(['label' => 'Big Bad Evil Guy', 'count' => 1], \$response->data);

        \$request = new \WP_REST_Request('GET', '/example/adversaries/troll');

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals([], \$response->data);
        \$this->assertEquals(404, \$response->status);
    }

    public function test_create_item()
    {
        \$request = new \WP_REST_Request('POST', '/example/adversary');
        \$request->set_param('name', 'goblin');
        \$request->set_param('label', 'Goblin');
        \$request->set_param('count', 1000);

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(401, \$response->status);

        // Become admin.
        wp_set_current_user(static::factory()->user->create(['role' => 'administrator']));

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(201, \$response->status);
        \$this->assertEquals([
            'label' => 'Goblin',
            'count' => 1000,
        ], \$response->data);
    }

    public function test_update_item()
    {
        // Become admin.
        wp_set_current_user(static::factory()->user->create(['role' => 'administrator']));

        // Create a new item to operate on.
        \$request = new \WP_REST_Request('POST', '/example/adversary');
        \$request->set_param('name', 'troll');
        \$request->set_param('label', 'Troll');
        \$request->set_param('count', 3);

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(201, \$response->status);
        \$this->assertEquals([
            'label' => 'Troll',
            'count' => 3,
        ], \$response->data);

        // Update the item.
        \$request = new \WP_REST_Request('PUT', '/example/adversary');
        \$request->set_param('name', 'troll');
        \$request->set_param('count', 2);

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(200, \$response->status);
        \$this->assertEquals([
            'label' => 'Troll',
            'count' => 2,
        ], \$response->data);
    }

    public function test_delete_item()
    {
        // Become admin.
        wp_set_current_user(static::factory()->user->create(['role' => 'administrator']));

        // Create a new item to operate on.
        \$request = new \WP_REST_Request('POST', '/example/adversary');
        \$request->set_param('name', 'ghast');
        \$request->set_param('label', 'Ghast');
        \$request->set_param('count', 5);

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(201, \$response->status);

        // Delete a non-existing adversary.
        \$request = new \WP_REST_Request('DELETE', '/example/adversary');
        \$request->set_param('name', 'beholder');

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(404, \$response->status);

        // Delete the item.
        \$request = new \WP_REST_Request('DELETE', '/example/adversary');
        \$request->set_param('name', 'ghast');

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(200, \$response->status);
        \$this->assertEquals([
            'label' => 'Ghast',
            'count' => 5,
        ], \$response->data);
    }

    public function test_prepare_item()
    {
        \$item = [
            'label' => 'Ghoul',
            'count' => '123',
        ];

        \$request = new \WP_REST_Request('GET', '/example/adversaries/ghoul');

        \$this->assertEquals(
            [
                'label' => 'Ghoul',
            ],
            \$this->controller->prepare_item_for_response(\$item, \$request)
        );

        \$request->set_param('context', 'edit');

        \$this->assertEquals(
            [
                'label' => 'Ghoul',
                'count' => 123
            ],
            \$this->controller->prepare_item_for_response(\$item, \$request)
        );
    }

    public function test_get_item_schema()
    {
        \$controller = \$this->controller;

        \$this->assertEquals([
            '\$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'adversary',
            'type' => 'object',
            'properties' => [
                'label' => [
                    'description' => 'The adversary display name',
                    'type' => 'string',
                    'context' => ['view', 'edit'],
                ],
                'count' => [
                    'description' => 'The adversary current count',
                    'type' => 'integer',
                    'context' => ['edit'],
                ]
            ]
        ], \$controller->get_item_schema());
    }
}

EOF;
}

<?php

namespace lucatume\WPBrowser\Lib\Generator;

class WPRestPostTypeController extends AbstractGenerator
{
    protected string $template = <<<EOF
<?php
{{namespace}}
class {{name}}Test extends \lucatume\WPBrowser\TestCase\WPRestPostTypeControllerTestCase
{
    {{tester}}
    /**
     * @var \WP_REST_Posts_Controller|null
     */
    private \$controller = null;

    public function setUp(): void
    {
        // Before...
        parent::setUp();

        // Your set up methods here.
        if (\$this->controller !== null) {
            // Do not register the controller more than once.
            return;
        }

        register_post_type('book');

        \$controller = new class('book') extends WP_REST_Posts_Controller {
            public function anyone(): bool
            {
                return true;
            }

            public function adminsOnly(): bool
            {
                return current_user_can('manage_options');
            }

            public function get_item_schema()
            {
                return [
                    '\$schema' => 'http://json-schema.org/draft-04/schema#',
                    'title' => 'book',
                    'type' => 'object',
                    'properties' => [
                        'title' => [
                            'description' => 'The book title',
                            'type' => 'string',
                            'context' => ['view', 'edit'],
                        ],
                        'year' => [
                            'description' => 'The year the book was published',
                            'type' => 'string',
                            'context' => ['view', 'edit'],
                        ],
                        'copies' => [
                            'description' => 'The number of book copies available',
                            'type' => 'integer',
                            'context' => ['edit'],
                        ]
                    ]
                ];
            }

            public function prepare_item_for_response(\$item, \$request)
            {
                \$post = get_post(\$item);
                \$data = [
                    'title' => \$post->post_title,
                    'year' => (int)get_post_meta(\$post->ID, 'year', true),
                ];

                if (\$request->get_param('context') === 'edit') {
                    \$data['copies'] = get_post_meta(\$post->ID, 'copies', true);
                }

                return \$data;
            }


            public function getBook(\WP_REST_Request \$request): \WP_REST_Response
            {
                \$book = get_post(\$request->get_param('id'));

                if (!\$book instanceof \WP_Post || get_post_type(\$book->ID) !== 'book') {
                    return new \WP_REST_Response(null, 404);
                }

                return new \WP_REST_Response(\$this->prepare_item_for_response(\$book, \$request), 200);
            }

            public function createBook(\WP_REST_Request \$request): \WP_REST_Response
            {
                \$prepareRequest = clone \$request;
                \$prepareRequest->set_param('context', 'edit');
                \$postarr = [
                    'post_type' => 'book',
                    'post_title' => \$request->get_param('title'),
                    'meta_input' => [
                        'year' => \$request->get_param('year'),
                        'copies' => \$request->get_param('copies')
                    ]
                ];

                \$inserted = wp_insert_post(\$postarr);

                if (\$inserted instanceof \WP_Error) {
                    return new \WP_REST_Response(\$inserted->get_error_message(), 500);
                }

                return new \WP_REST_Response(\$this->prepare_item_for_response(\$inserted, \$prepareRequest), 201);
            }

            public function updateBook(\WP_REST_Request \$request): \WP_REST_Response
            {
                \$book = get_post(\$request->get_param('id'));
                \$prepareRequest = clone \$request;
                \$prepareRequest->set_param('context', 'edit');

                if (!\$book instanceof \WP_Post || get_post_type(\$book->ID) !== 'book') {
                    return new \WP_REST_Response(null, 404);
                }

                \$postarr = [
                    'ID' => \$book->ID,
                    'post_title' => \$request->get_param('title') ?: \$book->post_title,
                    'meta_input' => [
                        'year' => \$request->get_param('year') ?: get_post_meta(\$book->ID, 'year', true),
                        'copies' => \$request->get_param('copies') ?: get_post_meta(\$book->ID, 'copies', true)
                    ]
                ];

                if ((\$update = wp_update_post(\$postarr)) instanceof \WP_Error) {
                    return new \WP_REST_Response(\$update->get_error_message(), 500);
                }

                return new \WP_REST_Response(\$this->prepare_item_for_response(\$book, \$prepareRequest), 200);
            }

            public function deleteBook(\WP_REST_Request \$request): \WP_REST_Response
            {
                \$book = get_post(\$request->get_param('id'));
                \$prepareRequest = clone \$request;
                \$prepareRequest->set_param('context', 'edit');

                if (!\$book instanceof \WP_Post || get_post_type(\$book->ID) !== 'book') {
                    return new \WP_REST_Response(null, 404);
                }

                \$prepared = \$this->prepare_item_for_response(\$book, \$prepareRequest);

                if (!wp_delete_post(\$book->ID)) {
                    return new \WP_REST_Response(null, 500);
                }

                return new \WP_REST_Response(\$prepared, 200);
            }

            public function getBooks(\WP_REST_Request \$request): \WP_REST_Response
            {
                \$posts = get_posts(['post_type' => 'book']);

                \$books = array_map(
                    function (\$post) use (\$request) {
                        return \$this->prepare_item_for_response(\$post, \$request);
                    },
                    \$posts
                );

                return new \WP_REST_Response(\$books, 200);
            }
        };

        register_rest_route('example', '/books', [
            'methods' => 'GET',
            'callback' => [\$controller, 'getBooks'],
            'permission_callback' => [\$controller, 'anyone'],
            'schema' => \$controller->get_item_schema()
        ]);

        register_rest_route('example', '/book/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [\$controller, 'getBook'],
            'permission_callback' => [\$controller, 'anyone'],
            'schema' => \$controller->get_item_schema()
        ]);

        register_rest_route('example', '/book', [
            'methods' => 'POST',
            'callback' => [\$controller, 'createBook'],
            'permission_callback' => [\$controller, 'adminsOnly'],
            'schema' => \$controller->get_item_schema()
        ]);

        register_rest_route('example', '/book/(?P<id>\d+)', [
            'methods' => 'PUT',
            'callback' => [\$controller, 'updateBook'],
            'permission_callback' => [\$controller, 'adminsOnly'],
            'schema' => \$controller->get_item_schema()
        ]);

        register_rest_route('example', '/book/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [\$controller, 'deleteBook'],
            'permission_callback' => [\$controller, 'adminsOnly'],
            'schema' => \$controller->get_item_schema()
        ]);

        \$this->controller = \$controller;
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
        \$this->assertArrayHasKey('/example/books', \$routes);
        \$this->assertCount(1, \$routes['/example/books']);
        \$this->assertArrayHasKey('/example/book', \$routes);
        \$this->assertCount(1, \$routes['/example/book']);
        \$this->assertArrayHasKey('/example/book/(?P<id>\d+)', \$routes);
        \$this->assertCount(3, \$routes['/example/book/(?P<id>\d+)']);
    }

    public function test_context_param()
    {
        \$book = static::factory()->post->create_and_get([
            'post_type' => 'book',
            'post_title' => 'Alice in Wonderland',
            'meta_input' => [
                'year' => 1865,
                'copies' => 13
            ]
        ]);

        \$request = new \WP_REST_Request('GET', "/example/book/{\$book->ID}");

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(200, \$response->status);
        \$this->assertEquals([
            'title' => 'Alice in Wonderland',
            'year' => 1865
        ], \$response->data);

        \$request->set_param('context', 'edit');

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(200, \$response->status);
        \$this->assertEquals([
            'title' => 'Alice in Wonderland',
            'year' => 1865,
            'copies' => 13
        ], \$response->data);
    }

    public function test_get_items()
    {
        \$request = new \WP_REST_Request('GET', '/example/books');

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(200, \$response->status);
        \$this->assertEquals([], \$response->data);

        \$book1 = static::factory()->post->create_and_get([
            'post_type' => 'book',
            'post_title' => 'Alice in Wonderland',
            'meta_input' => [
                'year' => 1865,
                'copies' => 13
            ]
        ]);
        \$book2 = static::factory()->post->create_and_get([
            'post_type' => 'book',
            'post_title' => 'Through the Looking-Glass',
            'meta_input' => [
                'year' => 1871,
                'copies' => 10
            ]
        ]);

        \$request = new \WP_REST_Request('GET', "/example/books");

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(200, \$response->status);
        \$this->assertEqualSets([
            [
                'title' => 'Alice in Wonderland',
                'year' => 1865
            ],
            [
                'title' => 'Through the Looking-Glass',
                'year' => 1871
            ]
        ], \$response->data);
    }

    public function test_get_item()
    {
        \$book = static::factory()->post->create_and_get([
            'post_type' => 'book',
            'post_title' => 'Alice in Wonderland',
            'meta_input' => [
                'year' => 1865,
                'copies' => 13
            ]
        ]);

        \$request = new \WP_REST_Request('GET', "/example/book/2389");

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(404, \$response->status);

        \$request = new \WP_REST_Request('GET', "/example/book/{\$book->ID}");

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(200, \$response->status);
        \$this->assertEquals([
            'title' => 'Alice in Wonderland',
            'year' => 1865
        ], \$response->data);
    }

    public function test_create_item()
    {
        \$request = new \WP_REST_Request('POST', "/example/book");
        \$request->set_body_params([
            'title' => 'Alice in Wonderland',
            'year' => 1865,
            'copies' => 13
        ]);

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(401, \$response->status);

        // Become administrator.
        wp_set_current_user(static::factory()->user->create(['role' => 'administrator']));

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(201, \$response->status);
        \$this->assertEquals([
            'title' => 'Alice in Wonderland',
            'year' => 1865,
            'copies' => 13
        ], \$response->data);
    }

    public function test_update_item()
    {
        \$book = static::factory()->post->create_and_get([
            'post_type' => 'book',
            'post_title' => 'Alice in Wonderland',
            'meta_input' => [
                'year' => 1865,
                'copies' => 13
            ]
        ]);

        \$request = new \WP_REST_Request('PUT', "/example/book/{\$book->ID}");
        \$request->set_param('copies', 10);
        \$request->set_param('year', 1867);

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(401, \$response->status);

        // Become administrator.
        wp_set_current_user(static::factory()->user->create(['role' => 'administrator']));

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(200, \$response->status);
        \$this->assertEquals([
            'title' => 'Alice in Wonderland',
            'year' => 1867,
            'copies' => 10
        ], \$response->data);
    }

    public function test_delete_item()
    {
        \$book = static::factory()->post->create_and_get([
            'post_type' => 'book',
            'post_title' => 'Alice in Wonderland',
            'meta_input' => [
                'year' => 1865,
                'copies' => 13
            ]
        ]);

        \$request = new \WP_REST_Request('DELETE', "/example/book/{\$book->ID}");

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(401, \$response->status);

        // Become administrator.
        wp_set_current_user(static::factory()->user->create(['role' => 'administrator']));

        \$response = rest_get_server()->dispatch(\$request);

        \$this->assertEquals(200, \$response->status);
        \$this->assertEquals([
            'title' => 'Alice in Wonderland',
            'year' => 1865,
            'copies' => 13
        ], \$response->data);
    }

    public function test_prepare_item()
    {
        \$book = static::factory()->post->create_and_get([
            'post_type' => 'book',
            'post_title' => 'Alice in Wonderland',
            'meta_input' => [
                'year' => 1865,
                'copies' => 13
            ]
        ]);

        \$request = new \WP_REST_Request('GET', "/example/book/{\$book->ID}");

        \$this->assertEquals([
            'title' => 'Alice in Wonderland',
            'year' => 1865
        ], \$this->controller->prepare_item_for_response(\$book, \$request));

        \$request->set_param('context', 'edit');

        \$this->assertEquals([
            'title' => 'Alice in Wonderland',
            'year' => 1865,
            'copies' => 13
        ], \$this->controller->prepare_item_for_response(\$book, \$request));

        // Become administrator.
        wp_set_current_user(static::factory()->user->create(['role' => 'administrator']));

        \$this->assertEquals([
            'title' => 'Alice in Wonderland',
            'year' => 1865,
            'copies' => 13
        ], \$this->controller->prepare_item_for_response(\$book, \$request));
    }

    public function test_get_item_schema()
    {
        \$this->assertEquals([
            '\$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'book',
            'type' => 'object',
            'properties' => [
                'title' => [
                    'description' => 'The book title',
                    'type' => 'string',
                    'context' => ['view', 'edit'],
                ],
                'year' => [
                    'description' => 'The year the book was published',
                    'type' => 'string',
                    'context' => ['view', 'edit'],
                ],
                'copies' => [
                    'description' => 'The number of book copies available',
                    'type' => 'integer',
                    'context' => ['edit'],
                ]
            ]
        ], \$this->controller->get_item_schema());
    }
}

EOF;
}

<?php
/**
 * Provides informations about WordPress tables.
 *
 * @package lucatume\WPBrowser\Generators
 */

namespace lucatume\WPBrowser\Generators;

use InvalidArgumentException;
use lucatume\WPBrowser\Utils\Strings;
use RuntimeException;

/**
 * Class Tables
 *
 * @package lucatume\WPBrowser\Generators
 */
class Tables
{
    /**
     * The absolute path to the the templates directory.
     */
    protected string $templatesDir;

    /**
     * Tables constructor.
     */
    public function __construct()
    {
        $this->templatesDir = __DIR__ . '/templates';
    }

    /**
     * Returns a list of default table names for a single site WordPress installation.
     *
     * @param string $table_prefix The table prefix to prepend to each blog table name.
     * @param int $blog_id The ID of the blog to return the table names for.
     *
     * @return array<string> The list of tables, not prefixed with the table prefix.
     */
    public static function blogTables(string $table_prefix = '', int $blog_id = 1): array
    {
        $blog_id = (int)$blog_id < 2 ? '' : $blog_id . '_';
        return array_map(static function ($table) use ($table_prefix, $blog_id): string {
            return sprintf('%s%s%s', $table_prefix, $blog_id, $table);
        }, [
            'commentmeta',
            'comments',
            'links',
            'options',
            'postmeta',
            'posts',
            'term_relationships',
            'term_taxonomy',
            'termmeta',
            'terms'
        ]);
    }

    /**
     * Returns the SQL to alter a table.
     *
     * @param string $table The table to alter.
     * @param string $prefix The prefix of the table to alter.
     *
     * @return string The SQL.
     */
    public function getAlterTableQuery(string $table, string $prefix): string
    {
        $data = ['operation' => 'ALTER TABLE', 'prefix' => $prefix];
        return in_array($table, $this->alterableTables(), true) ? $this->renderQuery($table, $data) : '';
    }

    /**
     * Returns  a list of alterable tables.
     *
     * @return array<string> The list of alterable tables in the context of multisite installations.
     */
    private function alterableTables(): array
    {
        return [
            'users'
        ];
    }

    /**
     * Renders a SQL query for a WordPress table operation.
     *
     * @param string $table The name of the table to render the query for, e.g. `blogs` or `posts`.
     * @param array<string,mixed> $data The data that should be used to render the query.
     *
     * @return string The rendered SQL query.
     *
     * @throws InvalidArgumentException If the table name is not a valid table name.
     */
    protected function renderQuery(string $table, array $data): string
    {
        if (!in_array($table, $this->tables(), true)) {
            throw new InvalidArgumentException('Table ' . $table . ' is not a valid table name');
        }

        $template = $this->templates($table);

        return Strings::renderString($template, $data);
    }

    /**
     * Returns a list of all the site tables.
     *
     * @return array<string> A list of all the site tables.
     */
    protected function tables(): array
    {
        return array_merge([], self::multisiteTables());
    }

    /**
     * Returns a list of additional table names that will be created in default multi-site installation.
     *
     * This list does not include single site installation tables.
     *
     * @param string $table_prefix The table prefix to prepend to each table name.
     *
     * @return array<string> The list of tables, not prefixed with the table prefix.
     *
     * @see Tables::blogTables()
     */
    public static function multisiteTables(string $table_prefix = ''): array
    {
        return array_map(static function ($table) use ($table_prefix): string {
            return $table_prefix . $table;
        }, [
            'blogs',
            'sitemeta',
            'site',
            'signups',
            'registration_log'
        ]);
    }

    /**
     * Returns the SQL query to perform a table operation.
     *
     * @param string $table The table name or table operation slug.
     *
     * @return string The SQL query.
     *
     * @throws RuntimeException If the SQL query cannot be fetched.
     */
    protected function templates(string $table): string
    {
        $templateFile = $this->templatesDir . DIRECTORY_SEPARATOR . "{$table}.handlebars";

        if (!is_file($templateFile)) {
            throw new RuntimeException("Template file {$templateFile} not found.");
        }

        $queryTemplate = file_get_contents($templateFile);

        if (false === $queryTemplate) {
            throw new RuntimeException("Template file {$templateFile} could not be read.");
        }

        return $queryTemplate;
    }

    /**
     * Returns the SQL code to create a blog table.
     *
     * @param string $table The table name.
     * @param string $prefix The table prefix.
     *
     * @return string The SQL.
     */
    public function getCreateTableQuery(string $table, string $prefix): string
    {
        $data = ['operation' => 'CREATE TABLE IF NOT EXISTS', 'prefix' => $prefix];
        return $this->renderQuery($table, $data);
    }

    /**
     * Returns the SQL code required to scaffold a blog tables.
     *
     * @param string $prefix The blog prefix.
     * @param int $blogId The blog ID.
     * @param array<string,mixed> $data The blog data.
     *
     * @return string The SQL query.
     */
    public function getBlogScaffoldQuery(string $prefix, int $blogId, array $data): string
    {
        $template = $this->templates('new-blog');
        $data = array_merge([
            'subdomain' => '',
            'domain' => '',
            'subfolder' => '',
            'stylesheet' => '',
            'prefix' => $prefix,
            'blog_id' => $blogId,
            'scheme' => 'http'
        ], $data);

        $data['siteurl'] = $data['siteurl'] ?? sprintf(
            '%s://%s%s%s',
            isset($data['scheme']) && is_string($data['scheme']) ? $data['scheme'] : 'http',
            $data['subdomain'] ? $data['subdomain'] . '.' : '',
            isset($data['domain']) && is_string($data['domain']) ? $data['domain'] : '',
            $data['subfolder'] ? '/' . $data['subfolder'] : ''
        );
        $data['home'] = $data['home'] ?? $data['siteurl'];
        $data['template'] = $data['template'] ?? $data['stylesheet'];

        return Strings::renderString($template, $data);
    }

    /**
     * Returns the SQL code to drop a blog tables.
     *
     * @param string $tablePrefix The database table prefix.
     * @param int $blogId The blog ID.
     *
     * @return string SQL code.
     */
    public function getBlogDropQuery(string $tablePrefix, int $blogId): string
    {
        $template = $this->templates('drop-blog-tables');
        $data = [
            'prefix' => $tablePrefix,
            'blog_id' => $blogId
        ];

        return Strings::renderString($template, $data);
    }
}

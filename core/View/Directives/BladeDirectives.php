<?php

namespace Core\View\Directives;

use Core\Container\Container;

class BladeDirectives
{
    /**
     * @var Container
     */
    protected Container $container;

    /**
     * @var array
     */
    protected array $directives = [];

    /**
     * Constructor
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->registerDefaultDirectives();
    }

    /**
     * Register default directives
     */
    protected function registerDefaultDirectives(): void
    {
        // ===== AUTHENTICATION DIRECTIVES =====
        $this->register('auth', function() {
            return '<?php if (auth()->check()): ?>';
        });

        $this->register('endauth', function() {
            return '<?php endif; ?>';
        });

        $this->register('guest', function() {
            return '<?php if (!auth()->check()): ?>';
        });

        $this->register('endguest', function() {
            return '<?php endif; ?>';
        });

        $this->register('can', function($expression) {
            return '<?php if (auth()->can(' . $expression . ')): ?>';
        });

        $this->register('endcan', function() {
            return '<?php endif; ?>';
        });

        $this->register('cannot', function($expression) {
            return '<?php if (!auth()->can(' . $expression . ')): ?>';
        });

        $this->register('endcannot', function() {
            return '<?php endif; ?>';
        });

        $this->register('role', function($expression) {
            return '<?php if (auth()->hasRole(' . $expression . ')): ?>';
        });

        $this->register('endrole', function() {
            return '<?php endif; ?>';
        });

        // ===== FORM & SECURITY DIRECTIVES =====
        $this->register('csrf', function() {
            return '<?php echo csrf_field(); ?>';
        });

        $this->register('method', function($expression) {
            return '<?php echo method_field(' . $expression . '); ?>';
        });

        $this->register('honeypot', function($expression = '') {
            return '<?php echo honeypot_field(' . $expression . '); ?>';
        });

        $this->register('recaptcha', function($expression = '') {
            return '<?php echo recaptcha_field(' . $expression . '); ?>';
        });

        // ===== URL & ASSET DIRECTIVES =====
        $this->register('asset', function($expression) {
            return '<?php echo asset(' . $expression . '); ?>';
        });

        $this->register('url', function($expression) {
            return '<?php echo url(' . $expression . '); ?>';
        });

        $this->register('route', function($expression) {
            return '<?php echo route(' . $expression . '); ?>';
        });

        $this->register('secure_asset', function($expression) {
            return '<?php echo secure_asset(' . $expression . '); ?>';
        });

        $this->register('secure_url', function($expression) {
            return '<?php echo secure_url(' . $expression . '); ?>';
        });

        // ===== DATA & FLASH DIRECTIVES =====
        $this->register('old', function($expression) {
            return '<?php echo old(' . $expression . '); ?>';
        });

        $this->register('flash', function($expression) {
            return '<?php echo flash(' . $expression . '); ?>';
        });

        $this->register('has_flash', function($expression) {
            return '<?php echo has_flash(' . $expression . ') ? "is-invalid" : ""; ?>';
        });

        $this->register('session', function($expression) {
            return '<?php echo session(' . $expression . '); ?>';
        });

        $this->register('cookie', function($expression) {
            return '<?php echo cookie(' . $expression . '); ?>';
        });

        // ===== CONFIG & ENVIRONMENT DIRECTIVES =====
        $this->register('config', function($expression) {
            return '<?php echo config(' . $expression . '); ?>';
        });

        $this->register('env', function($expression) {
            return '<?php echo env_custom(' . $expression . '); ?>';
        });

        $this->register('app_name', function() {
            return '<?php echo config("app.name"); ?>';
        });

        $this->register('app_version', function() {
            return '<?php echo config("app.version"); ?>';
        });

        // ===== DATE & TIME DIRECTIVES =====
        $this->register('date', function($expression) {
            return '<?php echo date(' . $expression . '); ?>';
        });

        $this->register('now', function($expression = '') {
            return '<?php echo now(' . $expression . '); ?>';
        });

        $this->register('time_ago', function($expression) {
            return '<?php echo time_ago(' . $expression . '); ?>';
        });

        $this->register('format_date', function($expression) {
            return '<?php echo format_date(' . $expression . '); ?>';
        });

        // ===== CONDITIONAL DIRECTIVES =====
        $this->register('if', function($expression) {
            return '<?php if(' . $expression . '): ?>';
        });

        $this->register('elseif', function($expression) {
            return '<?php elseif(' . $expression . '): ?>';
        });

        $this->register('else', function() {
            return '<?php else: ?>';
        });

        $this->register('endif', function() {
            return '<?php endif; ?>';
        });

        $this->register('unless', function($expression) {
            return '<?php if(!(' . $expression . ')): ?>';
        });

        $this->register('endunless', function() {
            return '<?php endif; ?>';
        });

        $this->register('isset', function($expression) {
            return '<?php if(isset(' . $expression . ')): ?>';
        });

        $this->register('endisset', function() {
            return '<?php endif; ?>';
        });

        $this->register('empty', function($expression) {
            return '<?php if(empty(' . $expression . ')): ?>';
        });

        $this->register('endempty', function() {
            return '<?php endif; ?>';
        });

        // ===== LOOP DIRECTIVES =====
        $this->register('foreach', function($expression) {
            return '<?php foreach(' . $expression . '): ?>';
        });

        $this->register('endforeach', function() {
            return '<?php endforeach; ?>';
        });

        $this->register('forelse', function($expression) {
            return '<?php if(count(' . $expression . ') > 0): foreach(' . $expression . '): ?>';
        });

        $this->register('empty', function() {
            return '<?php endforeach; else: ?>';
        });

        $this->register('endforelse', function() {
            return '<?php endif; ?>';
        });

        $this->register('for', function($expression) {
            return '<?php for(' . $expression . '): ?>';
        });

        $this->register('endfor', function() {
            return '<?php endfor; ?>';
        });

        $this->register('while', function($expression) {
            return '<?php while(' . $expression . '): ?>';
        });

        $this->register('endwhile', function() {
            return '<?php endwhile; ?>';
        });

        // ===== COMPONENT DIRECTIVES =====
        $this->register('component', function($expression) {
            return '<?php echo $this->renderComponent(' . $expression . '); ?>';
        });

        $this->register('endcomponent', function() {
            return '<?php echo $this->endComponent(); ?>';
        });

        $this->register('slot', function($expression = '') {
            return '<?php echo $this->slot(' . $expression . '); ?>';
        });

        $this->register('endslot', function() {
            return '<?php echo $this->endSlot(); ?>';
        });

        // ===== INCLUDE & STACK DIRECTIVES =====
        $this->register('include', function($expression) {
            return '<?php echo $this->includeView(' . $expression . '); ?>';
        });

        $this->register('includeIf', function($expression) {
            return '<?php echo $this->includeViewIf(' . $expression . '); ?>';
        });

        $this->register('includeWhen', function($expression) {
            return '<?php echo $this->includeViewWhen(' . $expression . '); ?>';
        });

        $this->register('push', function($expression) {
            return '<?php $this->push(' . $expression . '); ?>';
        });

        $this->register('endpush', function() {
            return '<?php $this->endPush(); ?>';
        });

        $this->register('stack', function($expression) {
            return '<?php echo $this->stack(' . $expression . '); ?>';
        });

        $this->register('prepend', function($expression) {
            return '<?php $this->prepend(' . $expression . '); ?>';
        });

        $this->register('endprepend', function() {
            return '<?php $this->endPrepend(); ?>';
        });

        // ===== ADVANCED PRISM DIRECTIVES =====
        $this->register('preview', function($expression) {
            return '<?php echo $this->renderBladePreview(' . $expression . '); ?>';
        });

        $this->register('live', function($expression) {
            return '<?php echo $this->renderLiveComponent(' . $expression . '); ?>';
        });

        $this->register('cache', function($expression) {
            return '<?php if($this->shouldCache(' . $expression . ')): ?>';
        });

        $this->register('endcache', function() {
            return '<?php $this->endCache(); endif; ?>';
        });

        $this->register('once', function($expression) {
            return '<?php if($this->once(' . $expression . ')): ?>';
        });

        $this->register('endonce', function() {
            return '<?php endif; ?>';
        });

        $this->register('canonical', function($expression = '') {
            return '<?php echo $this->canonicalUrl(' . $expression . '); ?>';
        });

        $this->register('meta', function($expression) {
            return '<?php echo $this->metaTag(' . $expression . '); ?>';
        });

        $this->register('og', function($expression) {
            return '<?php echo $this->openGraphTag(' . $expression . '); ?>';
        });

        $this->register('twitter', function($expression) {
            return '<?php echo $this->twitterCardTag(' . $expression . '); ?>';
        });

        // ===== PERFORMANCE DIRECTIVES =====
        $this->register('lazy', function($expression) {
            return '<?php echo $this->lazyLoad(' . $expression . '); ?>';
        });

        $this->register('defer', function($expression) {
            return '<?php echo $this->deferLoad(' . $expression . '); ?>';
        });

        $this->register('async', function($expression) {
            return '<?php echo $this->asyncLoad(' . $expression . '); ?>';
        });

        // ===== SECURITY DIRECTIVES =====
        $this->register('sanitize', function($expression) {
            return '<?php echo $this->sanitize(' . $expression . '); ?>';
        });

        $this->register('escape', function($expression) {
            return '<?php echo $this->escape(' . $expression . '); ?>';
        });

        $this->register('raw', function($expression) {
            return '<?php echo ' . $expression . '; ?>';
        });

        // ===== UTILITY DIRECTIVES =====
        $this->register('dump', function($expression) {
            return '<?php dump(' . $expression . '); ?>';
        });

        $this->register('dd', function($expression) {
            return '<?php dd(' . $expression . '); ?>';
        });

        $this->register('json', function($expression) {
            return '<?php echo json_encode(' . $expression . '); ?>';
        });

        $this->register('base64', function($expression) {
            return '<?php echo base64_encode(' . $expression . '); ?>';
        });

        $this->register('md5', function($expression) {
            return '<?php echo md5(' . $expression . '); ?>';
        });

        $this->register('sha1', function($expression) {
            return '<?php echo sha1(' . $expression . '); ?>';
        });

        // ===== CUSTOM PRISM DIRECTIVES =====
        $this->register('prism', function($expression) {
            return '<?php echo $this->prismDirective(' . $expression . '); ?>';
        });

        $this->register('framework', function($expression = '') {
            return '<?php echo $this->frameworkInfo(' . $expression . '); ?>';
        });

        $this->register('version', function() {
            return '<?php echo $this->frameworkVersion(); ?>';
        });

        $this->register('debug', function($expression = '') {
            return '<?php echo $this->debugInfo(' . $expression . '); ?>';
        });
    }

    /**
     * Register a directive
     */
    public function register(string $name, callable $callback): void
    {
        $this->directives[$name] = $callback;
    }

    /**
     * Get a directive
     */
    public function get(string $name): ?callable
    {
        return $this->directives[$name] ?? null;
    }

    /**
     * Get all directives
     */
    public function all(): array
    {
        return $this->directives;
    }

    /**
     * Check if directive exists
     */
    public function has(string $name): bool
    {
        return isset($this->directives[$name]);
    }

    /**
     * Remove a directive
     */
    public function remove(string $name): void
    {
        unset($this->directives[$name]);
    }

    /**
     * Clear all directives
     */
    public function clear(): void
    {
        $this->directives = [];
    }
}

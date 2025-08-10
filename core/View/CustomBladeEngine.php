<?php

namespace Core\View;

use Core\View\Interfaces\ViewEngineInterface;
use Core\View\Directives\BladeDirectives;
use Core\Container\Container;

class CustomBladeEngine implements ViewEngineInterface
{
    protected array $viewPaths;
    protected string $cachePath;
    protected array $data = [];
    protected BladeDirectives $directives;
    protected Container $container;
    
    // Component system
    protected array $components = [];
    protected array $componentData = [];
    protected ?string $currentComponent = null;
    protected ?string $currentSlotName = null;
    
    // Stack system
    protected array $stacks = [];
    protected ?string $currentStack = null;
    
    // Cache system
    protected array $cacheKeys = [];
    protected array $onceKeys = [];
    
    // Performance tracking
    protected array $performanceMetrics = [];
    protected float $startTime;

    public function __construct(array $viewPaths, string $cachePath, Container $container)
    {
        $this->viewPaths = $viewPaths;
        $this->cachePath = $cachePath;
        $this->container = $container;
        $this->directives = new BladeDirectives($container);
        
        // Cache klasörünü oluştur
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }
        
        $this->startTime = microtime(true);
    }

    public function render(string $view, array $data = []): string
    {
        $this->data = $data;
        
        // View dosyasını bul
        $viewFile = $this->findView($view);
        if (!$viewFile) {
            throw new \Exception("View not found: {$view}");
        }

        // Cache dosyası yolu
        $cacheFile = $this->getCacheFile($view);
        
        // Cache geçerli mi kontrol et
        if (!$this->isCacheValid($viewFile, $cacheFile)) {
            $this->compileView($viewFile, $cacheFile);
        }

        // Cache dosyasını render et
        return $this->renderCacheFile($cacheFile, $data);
    }

    public function exists(string $view): bool
    {
        return $this->findView($view) !== null;
    }

    public function getViewPath(string $view): string
    {
        return $this->findView($view) ?? '';
    }

    public function getViewPaths(): array
    {
        return $this->viewPaths;
    }

    protected function findView(string $view): ?string
    {
        $viewPath = str_replace('.', '/', $view) . '.blade.php';
        
        foreach ($this->viewPaths as $path) {
            $fullPath = $path . '/' . $viewPath;
            if (file_exists($fullPath)) {
                return $fullPath;
            }
        }
        
        return null;
    }

    protected function getCacheFile(string $view): string
    {
        $hash = md5($view);
        return $this->cachePath . '/' . $hash . '.php';
    }

    protected function isCacheValid(string $viewFile, string $cacheFile): bool
    {
        if (!file_exists($cacheFile)) {
            return false;
        }
        
        return filemtime($viewFile) <= filemtime($cacheFile);
    }

    protected function compileView(string $viewFile, string $cacheFile): void
    {
        $content = file_get_contents($viewFile);
        $compiled = $this->compileBladeTemplate($content);
        file_put_contents($cacheFile, $compiled);
    }

    protected function compileBladeTemplate(string $content): string
    {
        // Debug için log
        error_log("Compiling Blade template...");
        
        try {
            // ===== TEMEL BLADE SYNTAX =====
            
            // @extends
            $content = preg_replace("/@extends\('([^']+)'\)/", '<?php $this->extend("$1"); ?>', $content);
            
            // @section
            $content = preg_replace("/@section\('([^']+)',\s*'([^']*)'\)/", '<?php $this->section("$1", "$2"); ?>', $content);
            $content = preg_replace("/@section\('([^']+)'\)/", '<?php $this->startSection("$1"); ?>', $content);
            $content = str_replace('@endsection', '<?php $this->endSection(); ?>', $content);
            
            // @yield
            $content = preg_replace("/@yield\('([^']+)'\)/", '<?php echo $this->yieldContent("$1"); ?>', $content);
            $content = preg_replace("/@yield\('([^']+)',\s*'([^']*)'\)/", '<?php echo $this->yieldContent("$1", "$2"); ?>', $content);
            
            // ===== CONDITIONAL DIRECTIVES =====
            $content = preg_replace("/@if\s*\(([^)]+)\)/", '<?php if($1): ?>', $content);
            $content = preg_replace("/@elseif\s*\(([^)]+)\)/", '<?php elseif($1): ?>', $content);
            $content = str_replace('@else', '<?php else: ?>', $content);
            $content = str_replace('@endif', '<?php endif; ?>', $content);
            
            $content = preg_replace("/@unless\s*\(([^)]+)\)/", '<?php if(!($1)): ?>', $content);
            $content = str_replace('@endunless', '<?php endif; ?>', $content);
            
            $content = preg_replace("/@isset\s*\(([^)]+)\)/", '<?php if(isset($1)): ?>', $content);
            $content = str_replace('@endisset', '<?php endif; ?>', $content);
            
            $content = preg_replace("/@empty\s*\(([^)]+)\)/", '<?php if(empty($1)): ?>', $content);
            $content = str_replace('@endempty', '<?php endif; ?>', $content);
            
            // ===== LOOP DIRECTIVES =====
            $content = preg_replace("/@foreach\s*\(([^)]+)\)/", '<?php foreach($1): ?>', $content);
            $content = str_replace('@endforeach', '<?php endforeach; ?>', $content);
            
            $content = preg_replace("/@for\s*\(([^)]+)\)/", '<?php for($1): ?>', $content);
            $content = str_replace('@endfor', '<?php endfor; ?>', $content);
            
            $content = preg_replace("/@while\s*\(([^)]+)\)/", '<?php while($1): ?>', $content);
            $content = str_replace('@endwhile', '<?php endwhile; ?>', $content);
            
            // ===== COMPONENT DIRECTIVES =====
            $content = preg_replace("/@component\s*\(([^)]+)\)/", '<?php $this->startComponent("$1"); ?>', $content);
            $content = str_replace('@endcomponent', '<?php $this->endComponent(); ?>', $content);
            
            // Handle @slot with and without arguments
            $content = preg_replace("/@slot\s*\(([^)]*)\)/", '<?php $this->startSlot("$1"); ?>', $content);
            $content = str_replace('@slot', '<?php $this->startSlot("default"); ?>', $content);
            $content = str_replace('@endslot', '<?php $this->endSlot(); ?>', $content);
            
            // ===== INCLUDE DIRECTIVES =====
            $content = preg_replace("/@include\s*\(([^)]+)\)/", '<?php echo $this->includeView("$1"); ?>', $content);
            $content = preg_replace("/@includeIf\s*\(([^)]+)\)/", '<?php echo $this->includeViewIf("$1"); ?>', $content);
            $content = preg_replace("/@includeWhen\s*\(([^)]+)\)/", '<?php echo $this->includeViewWhen("$1"); ?>', $content);
            
            // ===== STACK DIRECTIVES =====
            $content = preg_replace("/@push\s*\(([^)]+)\)/", '<?php $this->push("$1"); ?>', $content);
            $content = str_replace('@endpush', '<?php $this->endPush(); ?>', $content);
            
            $content = preg_replace("/@prepend\s*\(([^)]+)\)/", '<?php $this->prepend("$1"); ?>', $content);
            $content = str_replace('@endprepend', '<?php $this->endPrepend(); ?>', $content);
            
            $content = preg_replace("/@stack\s*\(([^)]+)\)/", '<?php echo $this->stack("$1"); ?>', $content);
            
            // ===== ADVANCED PRISM DIRECTIVES =====
            $content = preg_replace("/@preview\s*\(([^)]+)\)/", '<?php echo $this->renderBladePreview("$1"); ?>', $content);
            $content = preg_replace("/@live\s*\(([^)]+)\)/", '<?php echo $this->renderLiveComponent("$1"); ?>', $content);
            
            $content = preg_replace("/@cache\s*\(([^)]*)\)/", '<?php if($this->shouldCache("$1")): ?>', $content);
            $content = str_replace('@endcache', '<?php $this->endCache(); endif; ?>', $content);
            
            $content = preg_replace("/@once\s*\(([^)]*)\)/", '<?php if($this->once("$1")): ?>', $content);
            $content = str_replace('@endonce', '<?php endif; ?>', $content);
            
            // ===== PERFORMANCE DIRECTIVES =====
            $content = preg_replace("/@lazy\s*\(([^)]+)\)/", '<?php echo $this->lazyLoad("$1"); ?>', $content);
            $content = preg_replace("/@lazy\s*$/", '<?php echo $this->lazyLoad("', $content);
            $content = str_replace('@endlazy', '"); ?>', $content);
            $content = preg_replace("/@defer\s*\(([^)]+)\)/", '<?php echo $this->deferLoad("$1"); ?>', $content);
            $content = preg_replace("/@defer\s*$/", '<?php echo $this->deferLoad("', $content);
            $content = str_replace('@enddefer', '"); ?>', $content);
            $content = preg_replace("/@async\s*\(([^)]+)\)/", '<?php echo $this->asyncLoad("$1"); ?>', $content);
            $content = preg_replace("/@async\s*$/", '<?php echo $this->asyncLoad("', $content);
            $content = str_replace('@endasync', '"); ?>', $content);
            
            // ===== SECURITY DIRECTIVES =====
            $content = preg_replace("/@sanitize\s*\(([^)]+)\)/", '<?php echo $this->sanitize("$1"); ?>', $content);
            $content = preg_replace("/@escape\s*\(([^)]+)\)/", '<?php echo $this->escape("$1"); ?>', $content);
            $content = preg_replace("/@raw\s*\(([^)]+)\)/", '<?php echo $1; ?>', $content);
            
            // ===== UTILITY DIRECTIVES =====
            $content = preg_replace("/@dump\s*\(([^)]*)\)/", '<?php dump("$1"); ?>', $content);
            $content = preg_replace("/@dd\s*\(([^)]*)\)/", '<?php dd("$1"); ?>', $content);
            $content = preg_replace("/@json\s*\(([^)]+)\)/", '<?php echo json_encode("$1"); ?>', $content);
            
            // ===== CUSTOM PRISM DIRECTIVES =====
            $content = preg_replace("/@prism\s*\(([^)]+)\)/", '<?php echo $this->prismDirective("$1"); ?>', $content);
            $content = preg_replace("/@framework\s*\(([^)]*)\)/", '<?php echo $this->frameworkInfo("$1"); ?>', $content);
            $content = str_replace('@version', '<?php echo $this->frameworkVersion(); ?>', $content);
            $content = preg_replace("/@debug\s*\(([^)]*)\)/", '<?php echo $this->debugInfo("$1"); ?>', $content);
            
            // ===== AUTHENTICATION DIRECTIVES =====
            $content = str_replace('@auth', '<?php if (auth()->check()): ?>', $content);
            $content = str_replace('@endauth', '<?php endif; ?>', $content);
            $content = str_replace('@guest', '<?php if (!auth()->check()): ?>', $content);
            $content = str_replace('@endguest', '<?php endif; ?>', $content);
            
            $content = preg_replace("/@can\s*\(([^)]+)\)/", '<?php if (auth()->can("$1")): ?>', $content);
            $content = str_replace('@endcan', '<?php endif; ?>', $content);
            $content = preg_replace("/@cannot\s*\(([^)]+)\)/", '<?php if (!auth()->can("$1")): ?>', $content);
            $content = str_replace('@endcannot', '<?php endif; ?>', $content);
            
            $content = preg_replace("/@role\s*\(([^)]+)\)/", '<?php if (auth()->hasRole("$1")): ?>', $content);
            $content = str_replace('@endrole', '<?php endif; ?>', $content);
            
            // ===== FORM & SECURITY DIRECTIVES =====
            $content = str_replace('@csrf', '<?php echo csrf_field(); ?>', $content);
            $content = preg_replace("/@method\s*\(([^)]+)\)/", '<?php echo method_field("$1"); ?>', $content);
            $content = preg_replace("/@honeypot\s*\(([^)]*)\)/", '<?php echo honeypot_field("$1"); ?>', $content);
            $content = preg_replace("/@recaptcha\s*\(([^)]*)\)/", '<?php echo recaptcha_field("$1"); ?>', $content);
            
            // ===== URL & ASSET DIRECTIVES =====
            $content = preg_replace("/@asset\s*\(([^)]+)\)/", '<?php echo asset("$1"); ?>', $content);
            $content = preg_replace("/@url\s*\(([^)]+)\)/", '<?php echo url("$1"); ?>', $content);
            $content = preg_replace("/@route\s*\(([^)]+)\)/", '<?php echo route("$1"); ?>', $content);
            $content = preg_replace("/@secure_asset\s*\(([^)]+)\)/", '<?php echo secure_asset("$1"); ?>', $content);
            $content = preg_replace("/@secure_url\s*\(([^)]+)\)/", '<?php echo secure_url("$1"); ?>', $content);
            
            // ===== DATA & FLASH DIRECTIVES =====
            $content = preg_replace("/@old\s*\(([^)]+)\)/", '<?php echo old("$1"); ?>', $content);
            $content = preg_replace("/@flash\s*\(([^)]+)\)/", '<?php echo flash("$1"); ?>', $content);
            $content = preg_replace("/@has_flash\s*\(([^)]+)\)/", '<?php echo has_flash("$1") ? "is-invalid" : ""; ?>', $content);
            $content = preg_replace("/@session\s*\(([^)]+)\)/", '<?php echo session("$1"); ?>', $content);
            $content = preg_replace("/@cookie\s*\(([^)]+)\)/", '<?php echo cookie("$1"); ?>', $content);
            
            // ===== CONFIG & ENVIRONMENT DIRECTIVES =====
            $content = preg_replace("/@config\s*\(([^)]+)\)/", '<?php echo config("$1"); ?>', $content);
            $content = preg_replace("/@env\s*\(([^)]+)\)/", '<?php echo env_custom("$1"); ?>', $content);
            $content = str_replace('@app_name', '<?php echo config("app.name"); ?>', $content);
            $content = str_replace('@app_version', '<?php echo config("app.version"); ?>', $content);
            
            // ===== DATE & TIME DIRECTIVES =====
            $content = preg_replace("/@date\s*\(([^)]+)\)/", '<?php echo date("$1"); ?>', $content);
            $content = preg_replace("/@now\s*\(([^)]*)\)/", '<?php echo now("$1"); ?>', $content);
            $content = preg_replace("/@time_ago\s*\(([^)]+)\)/", '<?php echo time_ago("$1"); ?>', $content);
            $content = preg_replace("/@format_date\s*\(([^)]+)\)/", '<?php echo format_date("$1"); ?>', $content);
            
            // ===== META & SEO DIRECTIVES =====
            $content = preg_replace("/@canonical\s*\(([^)]*)\)/", '<?php echo $this->canonicalUrl("$1"); ?>', $content);
            $content = preg_replace("/@meta\s*\(([^)]+)\)/", '<?php echo $this->metaTag("$1"); ?>', $content);
            $content = preg_replace("/@og\s*\(([^)]+)\)/", '<?php echo $this->openGraphTag("$1"); ?>', $content);
            $content = preg_replace("/@twitter\s*\(([^)]+)\)/", '<?php echo $this->twitterCardTag("$1"); ?>', $content);
            
            // ===== EXPRESSION SYNTAX =====
            // {{ }} syntax - daha güvenli hale getirildi
            $content = preg_replace('/\{\{\s*([^}]+)\s*\}\}/', '<?php echo htmlspecialchars($1, ENT_QUOTES, "UTF-8"); ?>', $content);
            
            // {!! !!} syntax - raw output
            $content = preg_replace('/\{\!\!\s*([^}]+)\s*\!\!\}/', '<?php echo $1; ?>', $content);
            
            // Debug için log
            error_log("Blade template compiled successfully");
            
        } catch (\Exception $e) {
            error_log("Error compiling Blade template: " . $e->getMessage());
            throw $e;
        }
        
        return $content;
    }

    protected function renderCacheFile(string $cacheFile, array $data): string
    {
        // Variables'ları extract et
        extract($data);
        
        // Layout sistem için gerekli variables
        $this->sections = [];
        $this->currentSection = null;
        $this->layout = null;
        
        // Cache dosyasını include et
        ob_start();
        include $cacheFile;
        $content = ob_get_clean();
        
        // Layout varsa render et
        if ($this->layout) {
            $content = $this->renderLayout($this->layout, $data);
        }
        
        return $content;
    }

    // ===== LAYOUT SYSTEM =====
    protected array $sections = [];
    protected ?string $currentSection = null;
    protected ?string $layout = null;

    protected function extend(string $layout): void
    {
        $this->layout = $layout;
    }

    protected function section(string $name, $content): void
    {
        $this->sections[$name] = $content;
    }

    protected function startSection(string $name): void
    {
        $this->currentSection = $name;
        ob_start();
    }

    protected function endSection(): void
    {
        if ($this->currentSection) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }

    protected function yieldContent(string $section, $default = ''): string
    {
        return $this->sections[$section] ?? $default;
    }

    protected function renderLayout(string $layout, array $data): string
    {
        $layoutFile = $this->findView($layout);
        if (!$layoutFile) {
            throw new \Exception("Layout not found: {$layout}");
        }

        $layoutContent = file_get_contents($layoutFile);
        $compiledLayout = $this->compileBladeTemplate($layoutContent);
        
        // Debug için log
        error_log("Layout compiled successfully");
        error_log("Compiled layout preview: " . substr($compiledLayout, 0, 500));
        
        // Layout'u render et
        $this->data = $data;
        extract($data);
        
        ob_start();
        
        // eval yerine include kullanmayı deneyelim
        try {
            // Geçici dosya oluştur
            $tempFile = tempnam(sys_get_temp_dir(), 'blade_');
            file_put_contents($tempFile, $compiledLayout);
            
            include $tempFile;
            
            // Geçici dosyayı sil
            unlink($tempFile);
        } catch (\ParseError $e) {
            error_log("Parse error in compiled layout: " . $e->getMessage());
            error_log("Compiled content: " . $compiledLayout);
            throw $e;
        } catch (\Exception $e) {
            error_log("Error in layout rendering: " . $e->getMessage());
            throw $e;
        }
        
        return ob_get_clean();
    }

    // ===== COMPONENT SYSTEM =====
    protected function startComponent(string $component): void
    {
        $this->currentComponent = $component;
        $this->componentData[$component] = [];
        ob_start();
    }

    protected function endComponent(): string
    {
        if ($this->currentComponent) {
            $content = ob_get_clean();
            $component = $this->currentComponent;
            $this->currentComponent = null;
            
            return $this->renderComponent($component, $this->componentData[$component], $content);
        }
        return '';
    }

    protected function startSlot(string $name = 'default'): void
    {
        if ($this->currentComponent) {
            $this->componentData[$this->currentComponent][$name] = '';
            $this->currentSlotName = $name; // Store the current slot name
            ob_start();
        }
    }

    protected function endSlot(): void
    {
        if ($this->currentComponent && $this->currentSlotName !== null) {
            $slotContent = ob_get_clean();
            $this->componentData[$this->currentComponent][$this->currentSlotName] = $slotContent;
            $this->currentSlotName = null; // Reset for next slot
        }
    }

    protected function slot(string $name = 'default'): string
    {
        if ($this->currentComponent && isset($this->componentData[$this->currentComponent][$name])) {
            return $this->componentData[$this->currentComponent][$name];
        }
        return '';
    }

    protected function renderComponent(string $component, array $data, string $content): string
    {
        try {
            $componentFile = $this->findView($component);
            if ($componentFile) {
                $componentContent = file_get_contents($componentFile);
                $compiledComponent = $this->compileBladeTemplate($componentContent);
                
                // Component'i render et
                extract($data);
                $slot = $content;
                
                ob_start();
                eval('?>' . $compiledComponent);
                return ob_get_clean();
            }
        } catch (\Exception $e) {
            return '<div class="component-error">Component Error: ' . $e->getMessage() . '</div>';
        }
        
        return $content;
    }

    // ===== STACK SYSTEM =====
    protected function push(string $name): void
    {
        $this->currentStack = $name;
        if (!isset($this->stacks[$name])) {
            $this->stacks[$name] = [];
        }
        ob_start();
    }

    protected function endPush(): void
    {
        if ($this->currentStack) {
            $content = ob_get_clean();
            $this->stacks[$this->currentStack][] = $content;
            $this->currentStack = null;
        }
    }

    protected function prepend(string $name): void
    {
        $this->currentStack = $name;
        if (!isset($this->stacks[$name])) {
            $this->stacks[$name] = [];
        }
        ob_start();
    }

    protected function endPrepend(): void
    {
        if ($this->currentStack) {
            $content = ob_get_clean();
            array_unshift($this->stacks[$this->currentStack], $content);
            $this->currentStack = null;
        }
    }

    protected function stack(string $name): string
    {
        if (isset($this->stacks[$name])) {
            return implode('', $this->stacks[$name]);
        }
        return '';
    }

    // ===== INCLUDE SYSTEM =====
    protected function includeView(string $view): string
    {
        return $this->render($view, $this->data);
    }

    protected function includeViewIf(string $view): string
    {
        if ($this->exists($view)) {
            return $this->render($view, $this->data);
        }
        return '';
    }

    protected function includeViewWhen(string $condition): string
    {
        // @includeWhen('condition', 'view')
        $parts = explode(',', $condition);
        if (count($parts) === 2) {
            $condition = trim($parts[0]);
            $view = trim($parts[1], " \t\n\r\0\x0B\"'");
            
            if (eval("return {$condition};")) {
                return $this->render($view, $this->data);
            }
        }
        return '';
    }

    // ===== ADVANCED PRISM FEATURES =====
    
    // Blade Preview System (HTML iframe mantığında ama performanslı)
    protected function renderBladePreview(string $view): string
    {
        try {
            $previewContent = $this->render($view, $this->data);
            $previewId = 'preview_' . md5($view . time());
            
            return '<div class="blade-preview" id="' . $previewId . '">
                <div class="preview-header">
                    <span class="preview-title">Blade Preview: ' . $view . '</span>
                    <button class="preview-toggle" onclick="togglePreview(\'' . $previewId . '\')">Toggle</button>
                </div>
                <div class="preview-content" style="display: none;">
                    ' . $previewContent . '
                </div>
            </div>';
        } catch (\Exception $e) {
            return '<div class="preview-error">Preview Error: ' . $e->getMessage() . '</div>';
        }
    }

    // Live Component System
    protected function renderLiveComponent(string $component): string
    {
        try {
            $componentContent = $this->render($component, $this->data);
            $componentId = 'live_' . md5($component . time());
            
            return '<div class="live-component" id="' . $componentId . '" data-component="' . $component . '">
                <div class="live-header">
                    <span class="live-title">Live Component: ' . $component . '</span>
                    <button class="live-refresh" onclick="refreshLiveComponent(\'' . $componentId . '\')">Refresh</button>
                </div>
                <div class="live-content">
                    ' . $componentContent . '
                </div>
            </div>';
        } catch (\Exception $e) {
            return '<div class="live-error">Live Component Error: ' . $e->getMessage() . '</div>';
        }
    }

    // Cache System
    protected function shouldCache(string $key = ''): bool
    {
        $cacheKey = $key ?: md5(serialize($this->data));
        return !isset($this->cacheKeys[$cacheKey]);
    }

    protected function endCache(): void
    {
        // Cache logic burada implement edilecek
    }

    // Once System
    protected function once(string $key = ''): bool
    {
        $onceKey = $key ?: md5(serialize($this->data));
        if (isset($this->onceKeys[$onceKey])) {
            return false;
        }
        $this->onceKeys[$onceKey] = true;
        return true;
    }

    // ===== PERFORMANCE FEATURES =====
    protected function lazyLoad(string $content): string
    {
        $id = 'lazy_' . md5($content);
        return '<div class="lazy-content" id="' . $id . '" data-content="' . base64_encode($content) . '">
            <div class="lazy-placeholder">Loading...</div>
        </div>
        <script>
            loadLazyContent("' . $id . '");
        </script>';
    }

    protected function deferLoad(string $content): string
    {
        $id = 'defer_' . md5($content);
        return '<div class="defer-content" id="' . $id . '" data-content="' . base64_encode($content) . '"></div>
        <script>
            loadDeferContent("' . $id . '");
        </script>';
    }

    protected function asyncLoad(string $content): string
    {
        $id = 'async_' . md5($content);
        return '<div class="async-content" id="' . $id . '" data-content="' . base64_encode($content) . '"></div>
        <script>
            loadAsyncContent("' . $id . '");
        </script>';
    }

    // ===== SECURITY FEATURES =====
    protected function sanitize(string $content): string
    {
        return htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
    }

    protected function escape(string $content): string
    {
        return htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
    }

    // ===== META & SEO FEATURES =====
    protected function canonicalUrl(string $url = ''): string
    {
        $canonical = $url ?: ($_SERVER['REQUEST_URI'] ?? '/');
        return '<link rel="canonical" href="' . $canonical . '">';
    }

    protected function metaTag(string $content): string
    {
        return '<meta name="description" content="' . $content . '">';
    }

    protected function openGraphTag(string $content): string
    {
        return '<meta property="og:description" content="' . $content . '">';
    }

    protected function twitterCardTag(string $content): string
    {
        return '<meta name="twitter:description" content="' . $content . '">';
    }

    // ===== CUSTOM PRISM DIRECTIVES =====
    protected function prismDirective(string $expression): string
    {
        return '<div class="prism-directive">PRISM: ' . $expression . '</div>';
    }

    protected function frameworkInfo(string $info = ''): string
    {
        if ($info === 'version') {
            return '1.0.0';
        }
        return 'PRISM Framework - Advanced PHP Framework';
    }

    protected function frameworkVersion(): string
    {
        return '1.0.0';
    }

    protected function debugInfo(string $expression = ''): string
    {
        $debug = defined('APP_DEBUG') ? constant('APP_DEBUG') : false;
        if ($debug) {
            return '<div class="debug-info">' . $expression . '</div>';
        }
        return '';
    }

    // ===== PERFORMANCE METRICS =====
    public function getPerformanceMetrics(): array
    {
        $endTime = microtime(true);
        $executionTime = ($endTime - $this->startTime) * 1000;
        
        return [
            'execution_time_ms' => round($executionTime, 2),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'cache_hits' => count($this->cacheKeys),
            'components_rendered' => count($this->components)
        ];
    }
}

<?php
declare(strict_types=1);

function config(string $key, mixed $default = null): mixed {
    $value = $GLOBALS['config'] ?? [];
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) return $default;
        $value = $value[$segment];
    }
    return $value;
}

function base_url(string $path = ''): string {
    $base = config('app.base_url', '');
    return rtrim($base, '/') . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function asset(string $path): string { return base_url('public/assets/' . ltrim($path, '/')); }
function upload_url(string $path): string { return base_url('public/uploads/' . ltrim($path, '/')); }
function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function old(string $key, string $default = ''): string { return e($_SESSION['_old'][$key] ?? $default); }
function money(float|int|string $amount, string $currency = ''): string {
    $n = (float)$amount;
    if ($currency === '') $currency = (string)(company_settings()['primary_currency'] ?? 'CDF');
    $currency = strtoupper($currency);
    if ($currency === 'CDF' || $currency === 'FC') return number_format($n, 0, ',', ' ') . ' FC';
    return number_format($n, 2, ',', ' ') . ' USD';
}
function redirect(string $path): never { header('Location: ' . base_url($path)); exit; }
function is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'; }
function selected(mixed $a, mixed $b): string { return (string)$a === (string)$b ? 'selected' : ''; }
function checked(bool $state): string { return $state ? 'checked' : ''; }
function role_in(array $roles): bool {
    $role = $_SESSION['user']['role'] ?? null;
    return $role !== null && in_array($role, $roles, true);
}
function auth_user(): ?array { return $_SESSION['user'] ?? null; }
function flash(string $key, ?string $value = null): ?string {
    if ($value !== null) { $_SESSION['_flash'][$key] = $value; return null; }
    $v = $_SESSION['_flash'][$key] ?? null; unset($_SESSION['_flash'][$key]); return $v;
}
function csrf_field(): string {
    return '<input type="hidden" name="_token" value="' . e(App\Core\Csrf::token()) . '">';
}
function route_is(string $prefix): bool {
    $uri = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', '/');
    $base = trim(config('app.base_url', ''), '/');
    if ($base && str_starts_with($uri, $base)) $uri = trim(substr($uri, strlen($base)), '/');
    return str_starts_with($uri, trim($prefix, '/'));
}

function company_settings(): array {
    static $cache = null;
    if (is_array($cache)) return $cache;
    try { $cache = (new App\Models\Setting())->allSettings(); }
    catch (Throwable $e) { $cache = ['company_name'=>'KIN-MART','company_subtitle'=>'Super Marché','company_logo'=>'public/assets/images/logo-kinmart.jpg','primary_currency'=>'CDF','secondary_currency'=>'USD','usd_cdf_rate'=>'2850']; }
    return $cache;
}
function company_name(): string { return (string)(company_settings()['company_name'] ?? 'KIN-MART'); }
function company_subtitle(): string { return (string)(company_settings()['company_subtitle'] ?? 'Super Marché'); }
function company_logo_url(): string {
    $p=(string)(company_settings()['company_logo'] ?? 'public/assets/images/logo-kinmart.jpg');
    if (str_starts_with($p,'public/')) return base_url($p);
    return asset('images/logo-kinmart.jpg');
}

function km_category_slug(string $category): string {
    $key = mb_strtolower(trim($category), 'UTF-8');
    $map = [
        'alimentation'=>'alimentation','boissons'=>'boissons','fruits et légumes'=>'fruits-et-legumes','fruits et legumes'=>'fruits-et-legumes',
        'boulangerie'=>'boulangerie','produits laitiers'=>'produits-laitiers','viandes'=>'viandes','surgelés'=>'surgeles','surgeles'=>'surgeles',
        'hygiène'=>'hygiene','hygiene'=>'hygiene','beauté'=>'beaute','beaute'=>'beaute','entretien'=>'entretien','bébé'=>'bebe','bebe'=>'bebe',
        'maison'=>'maison','papeterie'=>'papeterie','électroménager'=>'electromenager','electroménager'=>'electromenager','electromenager'=>'electromenager'
    ];
    return $map[$key] ?? 'default';
}
function category_art_url(string $category): string {
    $slug = km_category_slug($category);
    $photoMap = [
        'alimentation' => 'products/epicerie-rdc.png',
        'boissons' => 'products/boissons-rdc.png',
        'fruits-et-legumes' => 'products/fruits-legumes-rdc.png',
        'boulangerie' => 'products/boulangerie-laitiers-rdc.png',
        'produits-laitiers' => 'products/boulangerie-laitiers-rdc.png',
        'viandes' => 'products/viandes-surgeles-rdc.png',
        'surgeles' => 'products/viandes-surgeles-rdc.png',
        'hygiene' => 'products/hygiene-rdc.png',
        'beaute' => 'products/beaute-rdc.png',
        'entretien' => 'products/entretien-rdc.png',
        'bebe' => 'products/bebe-rdc.png',
        'maison' => 'products/maison-bureau-electro-rdc.png',
        'papeterie' => 'products/maison-bureau-electro-rdc.png',
        'electromenager' => 'products/maison-bureau-electro-rdc.png',
    ];
    if (isset($photoMap[$slug])) {
        return upload_url($photoMap[$slug]);
    }
    return asset('images/categories/'.$slug.'.svg');
}
function product_image_url(array $product): string {
    if (!empty($product['image'])) return upload_url((string)$product['image']);
    return category_art_url((string)($product['category'] ?? ''));
}
function discount_percent(array $product): int {
    $regular=(float)($product['sale_price']??0);$effective=(float)($product['effective_price']??$regular);
    if($regular<=0 || $effective >= $regular) return 0;
    return (int)round((1-($effective/$regular))*100);
}
function km_greeting(): string {
    $h=(int)date('G');
    if($h<12) return 'Bonjour';
    if($h<18) return 'Bon après-midi';
    return 'Bonsoir';
}

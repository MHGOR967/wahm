<?php
/**
 * WA7M BOOST - Configuration & Core Engine
 * Social Media Marketing Platform
 * 
 * @package WA7M
 * @version 1.0.0
 */

// ─── Error Reporting ───
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ─── Bot Configuration ───
// Load from environment or .env file
if (file_exists(__DIR__ . '/.env')) {
    $envLines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            [$key, $val] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($val);
            putenv(trim($key) . '=' . trim($val));
        }
    }
}

define('BOT_TOKEN', getenv('BOT_TOKEN') ?: ($_ENV['BOT_TOKEN'] ?? ''));
define('ADMIN_ID', (int)(getenv('ADMIN_ID') ?: ($_ENV['ADMIN_ID'] ?? 0)));
define('BOT_USERNAME', getenv('BOT_USERNAME') ?: ($_ENV['BOT_USERNAME'] ?? 'g5wbot'));
define('WEBAPP_URL', getenv('WEBAPP_URL') ?: ($_ENV['WEBAPP_URL'] ?? 'https://wa7m.com'));

// ─── Telegram API ───
define('TELEGRAM_API', 'https://api.telegram.org/bot' . BOT_TOKEN);

// ─── Paths ───
define('DATA_DIR', __DIR__ . '/data');
define('USERS_DIR', DATA_DIR . '/users');
define('CACHE_DIR', DATA_DIR . '/cache');
define('LANG_DIR', __DIR__ . '/lang');

// ─── Default Settings ───
define('DEFAULT_SETTINGS', [
    'referral_points'       => 500,
    'points_per_star'       => 20,
    'min_stars_purchase'    => 50,
    'max_stars_purchase'    => 10000,
    'currency_label'        => 'WA7M Points',
    'mandatory_channels'    => [],
    'news_ticker'           => '',
    'news_ticker_en'        => '',
    'vip_tiers'             => [
        'bronze' => ['min_spend' => 0,     'discount' => 0,  'label_ar' => 'برونزي', 'label_en' => 'Bronze'],
        'silver' => ['min_spend' => 5000,  'discount' => 5,  'label_ar' => 'فضي',   'label_en' => 'Silver'],
        'gold'   => ['min_spend' => 20000, 'discount' => 10, 'label_ar' => 'ذهبي',  'label_en' => 'Gold'],
    ],
    'provider_api_url'      => '',
    'provider_api_key'      => '',
    'default_profit_margin' => 20,
    'support_enabled'       => true,
    'tasks_enabled'         => true,
    'referral_enabled'      => true,
    'maintenance_mode'      => false,
]);

// ─── Ensure directories exist ───
foreach ([DATA_DIR, USERS_DIR, CACHE_DIR, LANG_DIR] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// ═══════════════════════════════════════════════════════════════
//  JSON Storage Engine
// ═══════════════════════════════════════════════════════════════

class JsonStore
{
    private string $file;

    public function __construct(string $file)
    {
        $this->file = $file;
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Read entire file contents as array
     */
    public function readAll(): array
    {
        if (!file_exists($this->file)) {
            return [];
        }
        $fp = fopen($this->file, 'r');
        if (!$fp) return [];
        flock($fp, LOCK_SH);
        $data = file_get_contents($this->file);
        flock($fp, LOCK_UN);
        fclose($fp);
        return json_decode($data, true) ?: [];
    }

    /**
     * Write entire array to file
     */
    public function writeAll(array $data): bool
    {
        $fp = fopen($this->file, 'c');
        if (!$fp) return false;
        flock($fp, LOCK_EX);
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }

    /**
     * Find record by key=value
     */
    public function find(string $key, mixed $value): ?array
    {
        $data = $this->readAll();
        foreach ($data as $record) {
            if (isset($record[$key]) && $record[$key] == $value) {
                return $record;
            }
        }
        return null;
    }

    /**
     * Insert a record
     */
    public function insert(array $record): bool
    {
        $data = $this->readAll();
        $data[] = $record;
        return $this->writeAll($data);
    }

    /**
     * Update record(s) matching key=value
     */
    public function update(string $key, mixed $value, array $updates): bool
    {
        $data = $this->readAll();
        $found = false;
        foreach ($data as &$record) {
            if (isset($record[$key]) && $record[$key] == $value) {
                $record = array_merge($record, $updates);
                $found = true;
                break;
            }
        }
        if ($found) {
            return $this->writeAll($data);
        }
        return false;
    }

    /**
     * Delete record(s) matching key=value
     */
    public function delete(string $key, mixed $value): bool
    {
        $data = $this->readAll();
        $data = array_values(array_filter($data, function ($r) use ($key, $value) {
            return !isset($r[$key]) || $r[$key] != $value;
        }));
        return $this->writeAll($data);
    }

    /**
     * Filter records by callback
     */
    public function filter(callable $callback): array
    {
        return array_values(array_filter($this->readAll(), $callback));
    }

    /**
     * Count records
     */
    public function count(): int
    {
        return count($this->readAll());
    }
}

// ═══════════════════════════════════════════════════════════════
//  User Storage (Per-user JSON files)
// ═══════════════════════════════════════════════════════════════

class UserStore
{
    /**
     * Get user data by Telegram user ID
     */
    public static function get(int $userId): ?array
    {
        $file = USERS_DIR . "/{$userId}.json";
        if (!file_exists($file)) {
            return null;
        }
        $data = file_get_contents($file);
        return json_decode($data, true);
    }

    /**
     * Save user data
     */
    public static function save(int $userId, array $data): bool
    {
        $file = USERS_DIR . "/{$userId}.json";
        $fp = fopen($file, 'c');
        if (!$fp) return false;
        flock($fp, LOCK_EX);
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }

    /**
     * Create new user with defaults
     */
    public static function create(array $telegramUser, ?int $referrerId = null): array
    {
        $userId = $telegramUser['id'];
        $now = time();
        $user = [
            'id'              => $userId,
            'first_name'      => $telegramUser['first_name'] ?? '',
            'last_name'       => $telegramUser['last_name'] ?? '',
            'username'        => $telegramUser['username'] ?? '',
            'language'        => $telegramUser['language_code'] ?? 'ar',
            'balance'         => 0,
            'total_spent'     => 0,
            'total_earned'    => 0,
            'vip_tier'        => 'bronze',
            'referrer_id'     => $referrerId,
            'referral_count'  => 0,
            'referral_earnings' => 0,
            'webapp_opened'   => false,
            'joined_at'       => $now,
            'last_active'     => $now,
            'is_banned'       => false,
            'orders_count'    => 0,
        ];
        self::save($userId, $user);
        return $user;
    }

    /**
     * Update specific fields for a user
     */
    public static function update(int $userId, array $updates): bool
    {
        $user = self::get($userId);
        if (!$user) return false;
        $user = array_merge($user, $updates);
        return self::save($userId, $user);
    }

    /**
     * Add points to user balance
     */
    public static function addBalance(int $userId, int $amount, string $reason = ''): bool
    {
        $user = self::get($userId);
        if (!$user) return false;
        $user['balance'] += $amount;
        $user['total_earned'] += $amount;
        $user['last_active'] = time();
        // Update VIP tier
        $user['vip_tier'] = self::calculateVipTier($user['total_spent']);
        return self::save($userId, $user);
    }

    /**
     * Deduct points from user balance
     */
    public static function deductBalance(int $userId, int $amount): bool
    {
        $user = self::get($userId);
        if (!$user || $user['balance'] < $amount) return false;
        $user['balance'] -= $amount;
        $user['total_spent'] += $amount;
        $user['last_active'] = time();
        $user['vip_tier'] = self::calculateVipTier($user['total_spent']);
        return self::save($userId, $user);
    }

    /**
     * Calculate VIP tier based on total spending
     */
    public static function calculateVipTier(int $totalSpent): string
    {
        $settings = Settings::get();
        $tiers = $settings['vip_tiers'];
        $currentTier = 'bronze';
        foreach ($tiers as $tier => $config) {
            if ($totalSpent >= $config['min_spend']) {
                $currentTier = $tier;
            }
        }
        return $currentTier;
    }

    /**
     * Get all user IDs
     */
    public static function getAllIds(): array
    {
        $ids = [];
        $files = glob(USERS_DIR . '/*.json');
        foreach ($files as $file) {
            $ids[] = (int) basename($file, '.json');
        }
        return $ids;
    }

    /**
     * Get total user count
     */
    public static function count(): int
    {
        return count(glob(USERS_DIR . '/*.json'));
    }

    /**
     * Get VIP discount for a user
     */
    public static function getDiscount(int $userId): int
    {
        $user = self::get($userId);
        if (!$user) return 0;
        $settings = Settings::get();
        $tiers = $settings['vip_tiers'];
        return $tiers[$user['vip_tier']]['discount'] ?? 0;
    }
}

// ═══════════════════════════════════════════════════════════════
//  Settings Manager
// ═══════════════════════════════════════════════════════════════

class Settings
{
    private static string $file = DATA_DIR . '/settings.json';

    public static function get(): array
    {
        if (!file_exists(self::$file)) {
            self::save(DEFAULT_SETTINGS);
            return DEFAULT_SETTINGS;
        }
        $data = json_decode(file_get_contents(self::$file), true);
        return array_merge(DEFAULT_SETTINGS, $data ?: []);
    }

    public static function save(array $settings): bool
    {
        $fp = fopen(self::$file, 'c');
        if (!$fp) return false;
        flock($fp, LOCK_EX);
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }

    public static function set(string $key, mixed $value): bool
    {
        $settings = self::get();
        $settings[$key] = $value;
        return self::save($settings);
    }
}

// ═══════════════════════════════════════════════════════════════
//  Telegram Helpers
// ═══════════════════════════════════════════════════════════════

class Telegram
{
    /**
     * Send API request to Telegram
     */
    public static function api(string $method, array $params = []): array
    {
        $ch = curl_init(TELEGRAM_API . '/' . $method);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($params),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true) ?: [];
    }

    /**
     * Send text message
     */
    public static function sendMessage(int $chatId, string $text, array $extra = []): array
    {
        return self::api('sendMessage', array_merge([
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ], $extra));
    }

    /**
     * Answer callback query
     */
    public static function answerCallback(string $callbackId, string $text = '', bool $alert = false): array
    {
        return self::api('answerCallbackQuery', [
            'callback_query_id' => $callbackId,
            'text'              => $text,
            'show_alert'        => $alert,
        ]);
    }

    /**
     * Create invoice link for Telegram Stars
     */
    public static function createInvoiceLink(int $userId, int $stars, int $points): array
    {
        return self::api('createInvoiceLink', [
            'title'         => "WA7M BOOST - {$points} Points",
            'description'   => "Purchase {$points} points for WA7M BOOST platform",
            'payload'       => json_encode([
                'user_id' => $userId,
                'stars'   => $stars,
                'points'  => $points,
                'time'    => time(),
            ]),
            'currency'      => 'XTR',
            'prices'        => [['label' => "{$points} Points", 'amount' => $stars]],
        ]);
    }

    /**
     * Answer pre-checkout query
     */
    public static function answerPreCheckout(string $queryId, bool $ok, string $error = ''): array
    {
        $params = [
            'pre_checkout_query_id' => $queryId,
            'ok'                    => $ok,
        ];
        if (!$ok && $error) {
            $params['error_message'] = $error;
        }
        return self::api('answerPreCheckoutQuery', $params);
    }

    /**
     * Check if user is a member of a channel
     */
    public static function checkMembership(int $userId, string $channelUsername): bool
    {
        $result = self::api('getChatMember', [
            'chat_id' => '@' . ltrim($channelUsername, '@'),
            'user_id' => $userId,
        ]);
        if (isset($result['result']['status'])) {
            return in_array($result['result']['status'], ['member', 'administrator', 'creator']);
        }
        return false;
    }

    /**
     * Validate Telegram Web App initData
     */
    public static function validateInitData(string $initData): ?array
    {
        $data = [];
        parse_str($initData, $data);

        if (empty($data['hash'])) {
            return null;
        }

        $hash = $data['hash'];
        unset($data['hash']);

        ksort($data);
        $checkString = '';
        foreach ($data as $k => $v) {
            $checkString .= "{$k}={$v}\n";
        }
        $checkString = rtrim($checkString, "\n");

        $secretKey = hash_hmac('sha256', BOT_TOKEN, 'WebAppData', true);
        $calculatedHash = bin2hex(hash_hmac('sha256', $checkString, $secretKey, true));

        if (hash_equals($calculatedHash, $hash)) {
            if (isset($data['user'])) {
                $data['user'] = json_decode($data['user'], true);
            }
            return $data;
        }
        return null;
    }
}

// ═══════════════════════════════════════════════════════════════
//  SMM Provider API Client
// ═══════════════════════════════════════════════════════════════

class ProviderAPI
{
    /**
     * Send request to SMM provider
     */
    public static function request(array $params): ?array
    {
        $settings = Settings::get();
        if (empty($settings['provider_api_url']) || empty($settings['provider_api_key'])) {
            return null;
        }
        $params['key'] = $settings['provider_api_key'];
        $ch = curl_init($settings['provider_api_url']);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error) return null;
        return json_decode($response, true);
    }

    /**
     * Get services from provider
     */
    public static function getServices(): ?array
    {
        return self::request(['action' => 'services']);
    }

    /**
     * Place order with provider
     */
    public static function placeOrder(int $serviceId, string $link, int $quantity): ?array
    {
        return self::request([
            'action'   => 'add',
            'service'  => $serviceId,
            'link'     => $link,
            'quantity' => $quantity,
        ]);
    }

    /**
     * Check order status
     */
    public static function checkOrder(int $orderId): ?array
    {
        return self::request([
            'action' => 'status',
            'order'  => $orderId,
        ]);
    }

    /**
     * Check multiple order statuses
     */
    public static function checkOrders(array $orderIds): ?array
    {
        return self::request([
            'action' => 'status',
            'orders' => implode(',', $orderIds),
        ]);
    }

    /**
     * Get provider balance
     */
    public static function getBalance(): ?array
    {
        return self::request(['action' => 'balance']);
    }
}

// ═══════════════════════════════════════════════════════════════
//  Cache Helper
// ═══════════════════════════════════════════════════════════════

class Cache
{
    /**
     * Get cached data
     */
    public static function get(string $key, int $ttl = 300): ?array
    {
        $file = CACHE_DIR . '/' . md5($key) . '.json';
        if (!file_exists($file)) return null;
        if (time() - filemtime($file) > $ttl) {
            unlink($file);
            return null;
        }
        return json_decode(file_get_contents($file), true);
    }

    /**
     * Set cache data
     */
    public static function set(string $key, array $data): void
    {
        $file = CACHE_DIR . '/' . md5($key) . '.json';
        file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Clear all cache
     */
    public static function clear(): void
    {
        $files = glob(CACHE_DIR . '/*.json');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}

// ═══════════════════════════════════════════════════════════════
//  Language Helper
// ═══════════════════════════════════════════════════════════════

class Lang
{
    private static array $strings = [];

    public static function load(string $lang = 'ar'): array
    {
        if (isset(self::$strings[$lang])) {
            return self::$strings[$lang];
        }
        $file = LANG_DIR . "/{$lang}.json";
        if (!file_exists($file)) {
            $file = LANG_DIR . '/ar.json';
        }
        self::$strings[$lang] = json_decode(file_get_contents($file), true) ?: [];
        return self::$strings[$lang];
    }

    public static function t(string $key, string $lang = 'ar'): string
    {
        $strings = self::load($lang);
        return $strings[$key] ?? $key;
    }
}

// ═══════════════════════════════════════════════════════════════
//  Response Helper
// ═══════════════════════════════════════════════════════════════

function jsonResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function generateId(): string
{
    return bin2hex(random_bytes(8));
}

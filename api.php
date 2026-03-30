<?php
/**
 * WA7M BOOST - REST API
 * Handles all Web App API requests
 * 
 * @package WA7M
 */

require_once __DIR__ . '/config.php';

// ─── CORS & Headers ───
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Init-Data');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ─── Parse Request ───
$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['action'] ?? '';
$body = json_decode(file_get_contents('php://input'), true) ?? [];

// ─── Auth Middleware ───
$currentUser = null;
$initData = $_SERVER['HTTP_X_INIT_DATA'] ?? $body['init_data'] ?? '';

if ($initData && $path !== 'init') {
    $validated = Telegram::validateInitData($initData);
    if ($validated && isset($validated['user'])) {
        $currentUser = UserStore::get($validated['user']['id']);
    }
}

// ─── Route API ───
switch ($path) {
    // ─── Authentication ───
    case 'init':
        handleInit($body);
        break;

    // ─── User Profile ───
    case 'profile':
        requireAuth();
        handleProfile();
        break;
    case 'profile.update':
        requireAuth();
        handleProfileUpdate($body);
        break;

    // ─── Services ───
    case 'services':
        requireAuth();
        handleServices();
        break;
    case 'services.categories':
        requireAuth();
        handleServiceCategories();
        break;

    // ─── Orders ───
    case 'order.create':
        requireAuth();
        handleCreateOrder($body);
        break;
    case 'orders':
        requireAuth();
        handleOrders();
        break;
    case 'order.status':
        requireAuth();
        handleOrderStatus($body);
        break;

    // ─── Payment ───
    case 'payment.invoice':
        requireAuth();
        handleCreateInvoice($body);
        break;
    case 'payment.verify':
        requireAuth();
        handleVerifyPayment($body);
        break;

    // ─── Referral ───
    case 'referral':
        requireAuth();
        handleReferral();
        break;

    // ─── Tasks ───
    case 'tasks':
        requireAuth();
        handleTasks();
        break;
    case 'task.complete':
        requireAuth();
        handleCompleteTask($body);
        break;

    // ─── Promo Codes ───
    case 'promo.redeem':
        requireAuth();
        handleRedeemPromo($body);
        break;

    // ─── Support ───
    case 'ticket.create':
        requireAuth();
        handleCreateTicket($body);
        break;
    case 'tickets':
        requireAuth();
        handleTickets();
        break;
    case 'ticket.reply':
        requireAuth();
        handleTicketReply($body);
        break;

    // ─── Settings (public) ───
    case 'settings.public':
        handlePublicSettings();
        break;

    // ─── Language ───
    case 'lang':
        handleLang();
        break;

    // ═══ Admin Endpoints ═══
    case 'admin.stats':
        requireAdmin();
        handleAdminStats();
        break;
    case 'admin.users':
        requireAdmin();
        handleAdminUsers();
        break;
    case 'admin.user':
        requireAdmin();
        handleAdminUser($body);
        break;
    case 'admin.settings':
        requireAdmin();
        handleAdminSettings($body);
        break;
    case 'admin.settings.get':
        requireAdmin();
        handleAdminSettingsGet();
        break;
    case 'admin.services.import':
        requireAdmin();
        handleAdminImportServices($body);
        break;
    case 'admin.services.update':
        requireAdmin();
        handleAdminUpdateService($body);
        break;
    case 'admin.promo.create':
        requireAdmin();
        handleAdminCreatePromo($body);
        break;
    case 'admin.promos':
        requireAdmin();
        handleAdminPromos();
        break;
    case 'admin.promo.delete':
        requireAdmin();
        handleAdminDeletePromo($body);
        break;
    case 'admin.tasks.create':
        requireAdmin();
        handleAdminCreateTask($body);
        break;
    case 'admin.tasks':
        requireAdmin();
        handleAdminTasks();
        break;
    case 'admin.task.delete':
        requireAdmin();
        handleAdminDeleteTask($body);
        break;
    case 'admin.broadcast':
        requireAdmin();
        handleAdminBroadcast($body);
        break;
    case 'admin.tickets':
        requireAdmin();
        handleAdminTickets();
        break;
    case 'admin.ticket.reply':
        requireAdmin();
        handleAdminTicketReply($body);
        break;
    case 'admin.provider.test':
        requireAdmin();
        handleAdminProviderTest();
        break;
    case 'admin.orders.sync':
        requireAdmin();
        handleAdminSyncOrders();
        break;

    default:
        jsonResponse(['error' => 'Unknown action'], 404);
}

// ═══════════════════════════════════════════════════════════════
//  Auth Helpers
// ═══════════════════════════════════════════════════════════════

function requireAuth(): void
{
    global $currentUser;
    if (!$currentUser) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
    if ($currentUser['is_banned']) {
        jsonResponse(['error' => 'Account suspended'], 403);
    }
}

function requireAdmin(): void
{
    global $currentUser;
    if (!$currentUser || $currentUser['id'] != ADMIN_ID) {
        jsonResponse(['error' => 'Admin access required'], 403);
    }
}

// ═══════════════════════════════════════════════════════════════
//  Init / Auth Endpoint
// ═══════════════════════════════════════════════════════════════

function handleInit(array $body): void
{
    $initData = $body['init_data'] ?? '';
    if (empty($initData)) {
        jsonResponse(['error' => 'Missing init_data'], 400);
    }

    $validated = Telegram::validateInitData($initData);
    if (!$validated || !isset($validated['user'])) {
        jsonResponse(['error' => 'Invalid init_data'], 401);
    }

    $telegramUser = $validated['user'];
    $userId = $telegramUser['id'];
    $startParam = $validated['start_param'] ?? '';

    // Get or create user
    $user = UserStore::get($userId);
    $isNew = false;

    if (!$user) {
        $referrerId = null;
        if (str_starts_with($startParam, 'ref_')) {
            $referrerId = (int) substr($startParam, 4);
            if ($referrerId === $userId) $referrerId = null;
        }
        $user = UserStore::create($telegramUser, $referrerId);
        $isNew = true;
    }

    // Mark Web App as opened (for referral verification)
    if (!$user['webapp_opened']) {
        UserStore::update($userId, ['webapp_opened' => true, 'last_active' => time()]);
        $user['webapp_opened'] = true;

        // Process referral reward NOW (referee has opened the app)
        if ($user['referrer_id']) {
            processReferralReward($userId, $user['referrer_id']);
        }
    } else {
        UserStore::update($userId, ['last_active' => time()]);
    }

    // Refresh user data
    $user = UserStore::get($userId);

    $settings = Settings::get();

    jsonResponse([
        'ok'   => true,
        'user' => [
            'id'              => $user['id'],
            'first_name'      => $user['first_name'],
            'last_name'       => $user['last_name'],
            'username'        => $user['username'],
            'language'        => $user['language'],
            'balance'         => $user['balance'],
            'vip_tier'        => $user['vip_tier'],
            'vip_discount'    => $settings['vip_tiers'][$user['vip_tier']]['discount'] ?? 0,
            'referral_count'  => $user['referral_count'],
            'referral_earnings' => $user['referral_earnings'],
            'total_spent'     => $user['total_spent'],
            'orders_count'    => $user['orders_count'],
            'is_admin'        => ($user['id'] == ADMIN_ID),
            'joined_at'       => $user['joined_at'],
        ],
        'settings' => [
            'news_ticker'      => $settings['news_ticker'] ?? '',
            'news_ticker_en'   => $settings['news_ticker_en'] ?? '',
            'referral_points'  => $settings['referral_points'],
            'points_per_star'  => $settings['points_per_star'],
            'vip_tiers'        => $settings['vip_tiers'],
            'support_enabled'  => $settings['support_enabled'],
            'tasks_enabled'    => $settings['tasks_enabled'],
            'referral_enabled' => $settings['referral_enabled'],
            'maintenance_mode' => $settings['maintenance_mode'],
        ],
        'is_new' => $isNew,
    ]);
}

// ═══════════════════════════════════════════════════════════════
//  Referral Processing
// ═══════════════════════════════════════════════════════════════

function processReferralReward(int $refereeId, int $referrerId): void
{
    $referrer = UserStore::get($referrerId);
    if (!$referrer) return;

    $settings = Settings::get();
    $points = $settings['referral_points'];

    // Add points to referrer
    UserStore::addBalance($referrerId, $points, "Referral reward for user {$refereeId}");
    UserStore::update($referrerId, [
        'referral_count'    => $referrer['referral_count'] + 1,
        'referral_earnings' => $referrer['referral_earnings'] + $points,
    ]);

    // Notify referrer via Telegram
    $referrerMsg = "🎉 <b>مكافأة إحالة!</b>\n\n";
    $referrerMsg .= "👤 صديقك فتح التطبيق\n";
    $referrerMsg .= "💎 حصلت على: <b>{$points}</b> نقطة\n";
    $referrerMsg .= "💰 رصيدك الجديد: <b>" . UserStore::get($referrerId)['balance'] . "</b>";
    Telegram::sendMessage($referrerId, $referrerMsg);

    // Notify referee via Telegram
    $refereeMsg = "🎁 <b>مرحباً بك!</b>\n\n";
    $refereeMsg .= "👥 تمت دعوتك بنجاح\n";
    $refereeMsg .= "🎊 تم تسجيلك عبر رابط إحالة";
    Telegram::sendMessage($refereeId, $refereeMsg);

    // Log referral
    $referrals = new JsonStore(DATA_DIR . '/referrals.json');
    $referrals->insert([
        'id'          => generateId(),
        'referrer_id' => $referrerId,
        'referee_id'  => $refereeId,
        'points'      => $points,
        'created_at'  => time(),
    ]);
}

// ═══════════════════════════════════════════════════════════════
//  Profile Endpoints
// ═══════════════════════════════════════════════════════════════

function handleProfile(): void
{
    global $currentUser;
    $settings = Settings::get();
    jsonResponse([
        'ok'   => true,
        'user' => [
            'id'              => $currentUser['id'],
            'first_name'      => $currentUser['first_name'],
            'last_name'       => $currentUser['last_name'],
            'username'        => $currentUser['username'],
            'language'        => $currentUser['language'],
            'balance'         => $currentUser['balance'],
            'vip_tier'        => $currentUser['vip_tier'],
            'vip_discount'    => $settings['vip_tiers'][$currentUser['vip_tier']]['discount'] ?? 0,
            'referral_count'  => $currentUser['referral_count'],
            'total_spent'     => $currentUser['total_spent'],
            'orders_count'    => $currentUser['orders_count'],
            'is_admin'        => ($currentUser['id'] == ADMIN_ID),
        ],
    ]);
}

function handleProfileUpdate(array $body): void
{
    global $currentUser;
    $lang = $body['language'] ?? $currentUser['language'];
    if (!in_array($lang, ['ar', 'en'])) $lang = 'ar';
    UserStore::update($currentUser['id'], ['language' => $lang]);
    jsonResponse(['ok' => true]);
}

// ═══════════════════════════════════════════════════════════════
//  Services Endpoints
// ═══════════════════════════════════════════════════════════════

function handleServices(): void
{
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';
    $platform = $_GET['platform'] ?? '';

    $store = new JsonStore(DATA_DIR . '/services.json');
    $services = $store->readAll();

    // Filter
    if ($category) {
        $services = array_filter($services, fn($s) => ($s['category'] ?? '') === $category);
    }
    if ($platform) {
        $services = array_filter($services, fn($s) => ($s['platform'] ?? '') === $platform);
    }
    if ($search) {
        $searchLower = mb_strtolower($search);
        $services = array_filter($services, function ($s) use ($searchLower) {
            return mb_strpos(mb_strtolower($s['name'] ?? ''), $searchLower) !== false
                || mb_strpos(mb_strtolower($s['name_en'] ?? ''), $searchLower) !== false
                || mb_strpos(mb_strtolower($s['category'] ?? ''), $searchLower) !== false;
        });
    }

    // Apply user's VIP discount
    global $currentUser;
    $discount = UserStore::getDiscount($currentUser['id']);

    $result = [];
    foreach (array_values($services) as $s) {
        if (isset($s['enabled']) && !$s['enabled']) continue;
        $price = $s['price'] ?? 0;
        if ($discount > 0) {
            $price = (int) round($price * (1 - $discount / 100));
        }
        $result[] = [
            'id'          => $s['id'],
            'name'        => $s['name'] ?? '',
            'name_en'     => $s['name_en'] ?? '',
            'category'    => $s['category'] ?? '',
            'platform'    => $s['platform'] ?? '',
            'price'       => $price,
            'original_price' => $s['price'] ?? 0,
            'min'         => $s['min'] ?? 1,
            'max'         => $s['max'] ?? 10000,
            'description' => $s['description'] ?? '',
            'description_en' => $s['description_en'] ?? '',
        ];
    }

    jsonResponse(['ok' => true, 'services' => $result, 'discount' => $discount]);
}

function handleServiceCategories(): void
{
    $store = new JsonStore(DATA_DIR . '/services.json');
    $services = $store->readAll();

    $categories = [];
    $platforms = [];
    foreach ($services as $s) {
        if (isset($s['enabled']) && !$s['enabled']) continue;
        if (!empty($s['category']) && !in_array($s['category'], $categories)) {
            $categories[] = $s['category'];
        }
        if (!empty($s['platform']) && !in_array($s['platform'], $platforms)) {
            $platforms[] = $s['platform'];
        }
    }

    jsonResponse(['ok' => true, 'categories' => $categories, 'platforms' => $platforms]);
}

// ═══════════════════════════════════════════════════════════════
//  Order Endpoints
// ═══════════════════════════════════════════════════════════════

function handleCreateOrder(array $body): void
{
    global $currentUser;

    $serviceId = $body['service_id'] ?? '';
    $link = trim($body['link'] ?? '');
    $quantity = (int) ($body['quantity'] ?? 0);

    if (empty($serviceId) || empty($link) || $quantity <= 0) {
        jsonResponse(['error' => 'Missing required fields'], 400);
    }

    // Get service
    $store = new JsonStore(DATA_DIR . '/services.json');
    $service = $store->find('id', $serviceId);
    if (!$service) {
        jsonResponse(['error' => 'Service not found'], 404);
    }

    // Validate quantity
    $min = $service['min'] ?? 1;
    $max = $service['max'] ?? 10000;
    if ($quantity < $min || $quantity > $max) {
        jsonResponse(['error' => "Quantity must be between {$min} and {$max}"], 400);
    }

    // Calculate price with VIP discount
    $discount = UserStore::getDiscount($currentUser['id']);
    $pricePerUnit = $service['price'] / 1000; // Price per 1000 units
    $totalPrice = (int) ceil(($pricePerUnit * $quantity));
    if ($discount > 0) {
        $totalPrice = (int) round($totalPrice * (1 - $discount / 100));
    }

    // Check balance
    if ($currentUser['balance'] < $totalPrice) {
        jsonResponse(['error' => 'Insufficient balance', 'required' => $totalPrice, 'balance' => $currentUser['balance']], 400);
    }

    // Deduct balance
    if (!UserStore::deductBalance($currentUser['id'], $totalPrice)) {
        jsonResponse(['error' => 'Payment failed'], 500);
    }

    // Place order with provider (if API is configured)
    $providerOrderId = null;
    $orderStatus = 'pending';
    $settings = Settings::get();

    if (!empty($settings['provider_api_url']) && !empty($service['provider_service_id'])) {
        $providerResult = ProviderAPI::placeOrder($service['provider_service_id'], $link, $quantity);
        if ($providerResult && isset($providerResult['order'])) {
            $providerOrderId = $providerResult['order'];
            $orderStatus = 'processing';
        } else {
            // Auto-refund on provider failure
            UserStore::addBalance($currentUser['id'], $totalPrice, 'Auto-refund: provider order failed');
            Telegram::sendMessage($currentUser['id'],
                "⚠️ <b>إعادة رصيد تلقائية</b>\n\n" .
                "❌ فشل الطلب لدى المزود\n" .
                "💰 تم إعادة <b>{$totalPrice}</b> نقطة إلى رصيدك"
            );
            jsonResponse(['error' => 'Provider order failed. Points refunded.'], 500);
        }
    }

    // Save order
    $orderId = generateId();
    $orders = new JsonStore(DATA_DIR . '/orders.json');
    $orders->insert([
        'id'                  => $orderId,
        'user_id'             => $currentUser['id'],
        'service_id'          => $serviceId,
        'service_name'        => $service['name'],
        'platform'            => $service['platform'] ?? '',
        'link'                => $link,
        'quantity'            => $quantity,
        'price'               => $totalPrice,
        'discount'            => $discount,
        'status'              => $orderStatus,
        'provider_order_id'   => $providerOrderId,
        'created_at'          => time(),
        'updated_at'          => time(),
    ]);

    // Update user order count
    UserStore::update($currentUser['id'], [
        'orders_count' => $currentUser['orders_count'] + 1,
    ]);

    $user = UserStore::get($currentUser['id']);

    jsonResponse([
        'ok'      => true,
        'order'   => ['id' => $orderId, 'status' => $orderStatus],
        'balance' => $user['balance'],
    ]);
}

function handleOrders(): void
{
    global $currentUser;
    $orders = new JsonStore(DATA_DIR . '/orders.json');
    $userOrders = $orders->filter(fn($o) => $o['user_id'] == $currentUser['id']);

    // Sort by created_at desc
    usort($userOrders, fn($a, $b) => ($b['created_at'] ?? 0) - ($a['created_at'] ?? 0));

    jsonResponse(['ok' => true, 'orders' => $userOrders]);
}

function handleOrderStatus(array $body): void
{
    global $currentUser;
    $orderId = $body['order_id'] ?? '';
    $orders = new JsonStore(DATA_DIR . '/orders.json');
    $order = $orders->find('id', $orderId);

    if (!$order || $order['user_id'] != $currentUser['id']) {
        jsonResponse(['error' => 'Order not found'], 404);
    }

    // If provider order, check live status
    if ($order['provider_order_id']) {
        $providerStatus = ProviderAPI::checkOrder($order['provider_order_id']);
        if ($providerStatus && isset($providerStatus['status'])) {
            $statusMap = [
                'Pending'    => 'pending',
                'In progress' => 'processing',
                'Processing' => 'processing',
                'Completed'  => 'completed',
                'Canceled'   => 'canceled',
                'Refunded'   => 'refunded',
                'Partial'    => 'partial',
            ];
            $newStatus = $statusMap[$providerStatus['status']] ?? $order['status'];

            if ($newStatus !== $order['status']) {
                $orders->update('id', $orderId, ['status' => $newStatus, 'updated_at' => time()]);
                $order['status'] = $newStatus;

                // Auto-refund for canceled orders
                if ($newStatus === 'canceled' || $newStatus === 'refunded') {
                    UserStore::addBalance($currentUser['id'], $order['price'], "Refund for order {$orderId}");
                    Telegram::sendMessage($currentUser['id'],
                        "🔄 <b>إعادة رصيد</b>\n\n" .
                        "📦 الطلب: {$orderId}\n" .
                        "💰 تم إعادة <b>{$order['price']}</b> نقطة"
                    );
                }
            }
        }
    }

    jsonResponse(['ok' => true, 'order' => $order]);
}

// ═══════════════════════════════════════════════════════════════
//  Payment Endpoints (Telegram Stars)
// ═══════════════════════════════════════════════════════════════

function handleCreateInvoice(array $body): void
{
    global $currentUser;

    $stars = (int) ($body['stars'] ?? 0);
    $settings = Settings::get();

    if ($stars < $settings['min_stars_purchase'] || $stars > $settings['max_stars_purchase']) {
        jsonResponse(['error' => "Stars must be between {$settings['min_stars_purchase']} and {$settings['max_stars_purchase']}"], 400);
    }

    $points = $stars * $settings['points_per_star'];

    $result = Telegram::createInvoiceLink($currentUser['id'], $stars, $points);

    if (isset($result['ok']) && $result['ok'] && isset($result['result'])) {
        jsonResponse([
            'ok'     => true,
            'url'    => $result['result'],
            'stars'  => $stars,
            'points' => $points,
        ]);
    } else {
        jsonResponse(['error' => 'Failed to create invoice'], 500);
    }
}

function handleVerifyPayment(array $body): void
{
    global $currentUser;
    // Refresh user data to get updated balance
    $user = UserStore::get($currentUser['id']);
    jsonResponse([
        'ok'      => true,
        'balance' => $user['balance'],
    ]);
}

// ═══════════════════════════════════════════════════════════════
//  Referral Endpoints
// ═══════════════════════════════════════════════════════════════

function handleReferral(): void
{
    global $currentUser;
    $settings = Settings::get();

    $referralLink = "https://t.me/" . BOT_USERNAME . "?start=ref_" . $currentUser['id'];

    // Get referral list
    $referrals = new JsonStore(DATA_DIR . '/referrals.json');
    $myReferrals = $referrals->filter(fn($r) => $r['referrer_id'] == $currentUser['id']);

    jsonResponse([
        'ok'   => true,
        'link' => $referralLink,
        'stats' => [
            'count'    => $currentUser['referral_count'],
            'earnings' => $currentUser['referral_earnings'],
            'points_per_referral' => $settings['referral_points'],
        ],
        'referrals' => array_map(fn($r) => [
            'referee_id' => $r['referee_id'],
            'points'     => $r['points'],
            'date'       => $r['created_at'],
        ], $myReferrals),
    ]);
}

// ═══════════════════════════════════════════════════════════════
//  Tasks Endpoints
// ═══════════════════════════════════════════════════════════════

function handleTasks(): void
{
    global $currentUser;

    $store = new JsonStore(DATA_DIR . '/tasks.json');
    $tasks = $store->readAll();

    // Get user's completed tasks
    $completedStore = new JsonStore(DATA_DIR . '/task_completions.json');
    $completions = $completedStore->filter(fn($c) => $c['user_id'] == $currentUser['id']);
    $completedIds = array_column($completions, 'task_id');

    $result = [];
    foreach ($tasks as $task) {
        if (isset($task['enabled']) && !$task['enabled']) continue;
        $result[] = [
            'id'          => $task['id'],
            'title'       => $task['title'] ?? '',
            'title_en'    => $task['title_en'] ?? '',
            'description' => $task['description'] ?? '',
            'description_en' => $task['description_en'] ?? '',
            'type'        => $task['type'] ?? 'join_channel',
            'target'      => $task['target'] ?? '',
            'points'      => $task['points'] ?? 0,
            'completed'   => in_array($task['id'], $completedIds),
        ];
    }

    jsonResponse(['ok' => true, 'tasks' => $result]);
}

function handleCompleteTask(array $body): void
{
    global $currentUser;

    $taskId = $body['task_id'] ?? '';
    $store = new JsonStore(DATA_DIR . '/tasks.json');
    $task = $store->find('id', $taskId);

    if (!$task) {
        jsonResponse(['error' => 'Task not found'], 404);
    }

    // Check if already completed
    $completedStore = new JsonStore(DATA_DIR . '/task_completions.json');
    $existing = $completedStore->filter(
        fn($c) => $c['user_id'] == $currentUser['id'] && $c['task_id'] == $taskId
    );
    if (!empty($existing)) {
        jsonResponse(['error' => 'Task already completed'], 400);
    }

    // Verify task completion based on type
    $verified = false;
    switch ($task['type']) {
        case 'join_channel':
            $verified = Telegram::checkMembership($currentUser['id'], $task['target']);
            break;
        case 'visit_link':
            $verified = true; // Trust client-side verification
            break;
        default:
            $verified = true;
    }

    if (!$verified) {
        jsonResponse(['error' => 'Task verification failed. Please complete the task first.'], 400);
    }

    // Record completion
    $completedStore->insert([
        'id'         => generateId(),
        'user_id'    => $currentUser['id'],
        'task_id'    => $taskId,
        'points'     => $task['points'],
        'created_at' => time(),
    ]);

    // Award points
    UserStore::addBalance($currentUser['id'], $task['points'], "Task completed: {$taskId}");
    $user = UserStore::get($currentUser['id']);

    jsonResponse([
        'ok'      => true,
        'points'  => $task['points'],
        'balance' => $user['balance'],
    ]);
}

// ═══════════════════════════════════════════════════════════════
//  Promo Code Endpoints
// ═══════════════════════════════════════════════════════════════

function handleRedeemPromo(array $body): void
{
    global $currentUser;

    $code = strtoupper(trim($body['code'] ?? ''));
    if (empty($code)) {
        jsonResponse(['error' => 'Missing promo code'], 400);
    }

    $store = new JsonStore(DATA_DIR . '/promo_codes.json');
    $promo = $store->find('code', $code);

    if (!$promo) {
        jsonResponse(['error' => 'Invalid promo code'], 404);
    }

    // Check expiry
    if (isset($promo['expires_at']) && $promo['expires_at'] < time()) {
        jsonResponse(['error' => 'Promo code expired'], 400);
    }

    // Check usage limit
    if (isset($promo['max_uses']) && $promo['uses'] >= $promo['max_uses']) {
        jsonResponse(['error' => 'Promo code fully redeemed'], 400);
    }

    // Check if user already used this code
    $usageStore = new JsonStore(DATA_DIR . '/promo_usage.json');
    $used = $usageStore->filter(
        fn($u) => $u['user_id'] == $currentUser['id'] && $u['promo_code'] == $code
    );
    if (!empty($used)) {
        jsonResponse(['error' => 'You already used this code'], 400);
    }

    // Apply promo
    $points = $promo['points'] ?? 0;
    if ($points > 0) {
        UserStore::addBalance($currentUser['id'], $points, "Promo code: {$code}");
    }

    // Record usage
    $usageStore->insert([
        'user_id'    => $currentUser['id'],
        'promo_code' => $code,
        'points'     => $points,
        'created_at' => time(),
    ]);

    // Increment usage count
    $store->update('code', $code, ['uses' => ($promo['uses'] ?? 0) + 1]);

    $user = UserStore::get($currentUser['id']);

    jsonResponse([
        'ok'      => true,
        'points'  => $points,
        'balance' => $user['balance'],
    ]);
}

// ═══════════════════════════════════════════════════════════════
//  Support / Ticket Endpoints
// ═══════════════════════════════════════════════════════════════

function handleCreateTicket(array $body): void
{
    global $currentUser;

    $subject = trim($body['subject'] ?? '');
    $message = trim($body['message'] ?? '');

    if (empty($subject) || empty($message)) {
        jsonResponse(['error' => 'Subject and message are required'], 400);
    }

    $ticketId = generateId();
    $store = new JsonStore(DATA_DIR . '/tickets.json');
    $store->insert([
        'id'         => $ticketId,
        'user_id'    => $currentUser['id'],
        'username'   => $currentUser['username'] ?: $currentUser['first_name'],
        'subject'    => $subject,
        'status'     => 'open',
        'messages'   => [
            [
                'from'       => 'user',
                'user_id'    => $currentUser['id'],
                'text'       => $message,
                'created_at' => time(),
            ],
        ],
        'created_at' => time(),
        'updated_at' => time(),
    ]);

    // Notify admin
    Telegram::sendMessage(ADMIN_ID,
        "🎫 <b>تذكرة دعم جديدة</b>\n\n" .
        "🆔 #{$ticketId}\n" .
        "👤 {$currentUser['first_name']} (@{$currentUser['username']})\n" .
        "📌 {$subject}\n\n" .
        "💬 {$message}"
    );

    jsonResponse(['ok' => true, 'ticket_id' => $ticketId]);
}

function handleTickets(): void
{
    global $currentUser;
    $store = new JsonStore(DATA_DIR . '/tickets.json');
    $tickets = $store->filter(fn($t) => $t['user_id'] == $currentUser['id']);
    usort($tickets, fn($a, $b) => ($b['updated_at'] ?? 0) - ($a['updated_at'] ?? 0));

    jsonResponse(['ok' => true, 'tickets' => $tickets]);
}

function handleTicketReply(array $body): void
{
    global $currentUser;

    $ticketId = $body['ticket_id'] ?? '';
    $message = trim($body['message'] ?? '');

    if (empty($ticketId) || empty($message)) {
        jsonResponse(['error' => 'Missing fields'], 400);
    }

    $store = new JsonStore(DATA_DIR . '/tickets.json');
    $ticket = $store->find('id', $ticketId);

    if (!$ticket || $ticket['user_id'] != $currentUser['id']) {
        jsonResponse(['error' => 'Ticket not found'], 404);
    }

    $ticket['messages'][] = [
        'from'       => 'user',
        'user_id'    => $currentUser['id'],
        'text'       => $message,
        'created_at' => time(),
    ];
    $ticket['updated_at'] = time();
    $ticket['status'] = 'open';

    $store->update('id', $ticketId, $ticket);

    // Notify admin
    Telegram::sendMessage(ADMIN_ID,
        "💬 <b>رد على تذكرة</b> #{$ticketId}\n\n" .
        "👤 {$currentUser['first_name']}\n" .
        "💬 {$message}"
    );

    jsonResponse(['ok' => true]);
}

// ═══════════════════════════════════════════════════════════════
//  Public Settings
// ═══════════════════════════════════════════════════════════════

function handlePublicSettings(): void
{
    $settings = Settings::get();
    jsonResponse([
        'ok'       => true,
        'settings' => [
            'news_ticker'      => $settings['news_ticker'] ?? '',
            'news_ticker_en'   => $settings['news_ticker_en'] ?? '',
            'vip_tiers'        => $settings['vip_tiers'],
            'points_per_star'  => $settings['points_per_star'],
            'maintenance_mode' => $settings['maintenance_mode'],
        ],
    ]);
}

// ═══════════════════════════════════════════════════════════════
//  Language Endpoint
// ═══════════════════════════════════════════════════════════════

function handleLang(): void
{
    $lang = $_GET['lang'] ?? 'ar';
    if (!in_array($lang, ['ar', 'en'])) $lang = 'ar';
    $strings = Lang::load($lang);
    jsonResponse(['ok' => true, 'strings' => $strings, 'lang' => $lang]);
}

// ═══════════════════════════════════════════════════════════════
//  Admin Endpoints
// ═══════════════════════════════════════════════════════════════

function handleAdminStats(): void
{
    $totalUsers = UserStore::count();
    $orders = new JsonStore(DATA_DIR . '/orders.json');
    $allOrders = $orders->readAll();
    $transactions = new JsonStore(DATA_DIR . '/transactions.json');
    $allTransactions = $transactions->readAll();

    $totalRevenue = array_sum(array_column($allTransactions, 'amount'));
    $pendingOrders = count(array_filter($allOrders, fn($o) => $o['status'] === 'pending' || $o['status'] === 'processing'));
    $completedOrders = count(array_filter($allOrders, fn($o) => $o['status'] === 'completed'));

    $tickets = new JsonStore(DATA_DIR . '/tickets.json');
    $openTickets = count($tickets->filter(fn($t) => $t['status'] === 'open'));

    jsonResponse([
        'ok'    => true,
        'stats' => [
            'total_users'      => $totalUsers,
            'total_orders'     => count($allOrders),
            'pending_orders'   => $pendingOrders,
            'completed_orders' => $completedOrders,
            'total_revenue'    => $totalRevenue,
            'total_transactions' => count($allTransactions),
            'open_tickets'     => $openTickets,
        ],
    ]);
}

function handleAdminUsers(): void
{
    $page = (int) ($_GET['page'] ?? 1);
    $perPage = 20;
    $search = $_GET['search'] ?? '';

    $allIds = UserStore::getAllIds();
    $users = [];

    foreach ($allIds as $uid) {
        $user = UserStore::get($uid);
        if (!$user) continue;
        if ($search) {
            $s = mb_strtolower($search);
            if (mb_strpos(mb_strtolower($user['first_name'] ?? ''), $s) === false
                && mb_strpos(mb_strtolower($user['username'] ?? ''), $s) === false
                && strpos((string)$user['id'], $search) === false) {
                continue;
            }
        }
        $users[] = [
            'id'         => $user['id'],
            'first_name' => $user['first_name'],
            'username'   => $user['username'],
            'balance'    => $user['balance'],
            'vip_tier'   => $user['vip_tier'],
            'total_spent' => $user['total_spent'],
            'orders_count' => $user['orders_count'],
            'is_banned'  => $user['is_banned'],
            'joined_at'  => $user['joined_at'],
        ];
    }

    // Sort by joined_at desc
    usort($users, fn($a, $b) => ($b['joined_at'] ?? 0) - ($a['joined_at'] ?? 0));

    $total = count($users);
    $offset = ($page - 1) * $perPage;
    $users = array_slice($users, $offset, $perPage);

    jsonResponse([
        'ok'    => true,
        'users' => $users,
        'total' => $total,
        'page'  => $page,
        'pages' => ceil($total / $perPage),
    ]);
}

function handleAdminUser(array $body): void
{
    $userId = (int) ($body['user_id'] ?? 0);
    $action = $body['action'] ?? 'get';

    $user = UserStore::get($userId);
    if (!$user) {
        jsonResponse(['error' => 'User not found'], 404);
    }

    switch ($action) {
        case 'ban':
            UserStore::update($userId, ['is_banned' => true]);
            jsonResponse(['ok' => true]);
            break;
        case 'unban':
            UserStore::update($userId, ['is_banned' => false]);
            jsonResponse(['ok' => true]);
            break;
        case 'add_balance':
            $amount = (int) ($body['amount'] ?? 0);
            if ($amount > 0) {
                UserStore::addBalance($userId, $amount, 'Admin added');
                Telegram::sendMessage($userId, "🎁 تم إضافة <b>{$amount}</b> نقطة بواسطة الإدارة!");
            }
            jsonResponse(['ok' => true, 'balance' => UserStore::get($userId)['balance']]);
            break;
        default:
            jsonResponse(['ok' => true, 'user' => $user]);
    }
}

function handleAdminSettings(array $body): void
{
    $settings = Settings::get();
    $allowed = [
        'referral_points', 'points_per_star', 'min_stars_purchase', 'max_stars_purchase',
        'mandatory_channels', 'news_ticker', 'news_ticker_en', 'vip_tiers',
        'provider_api_url', 'provider_api_key', 'default_profit_margin',
        'support_enabled', 'tasks_enabled', 'referral_enabled', 'maintenance_mode',
    ];

    foreach ($allowed as $key) {
        if (array_key_exists($key, $body)) {
            $settings[$key] = $body[$key];
        }
    }

    Settings::save($settings);
    jsonResponse(['ok' => true]);
}

function handleAdminSettingsGet(): void
{
    $settings = Settings::get();
    jsonResponse(['ok' => true, 'settings' => $settings]);
}

function handleAdminImportServices(array $body): void
{
    $providerServices = ProviderAPI::getServices();
    if (!$providerServices || !is_array($providerServices)) {
        jsonResponse(['error' => 'Failed to fetch provider services'], 500);
    }

    $settings = Settings::get();
    $profitMargin = $settings['default_profit_margin'];
    $store = new JsonStore(DATA_DIR . '/services.json');
    $existing = $store->readAll();
    $existingProviderIds = array_column($existing, 'provider_service_id');

    $imported = 0;
    foreach ($providerServices as $ps) {
        $providerId = $ps['service'] ?? null;
        if (!$providerId) continue;

        // Skip if already imported
        if (in_array($providerId, $existingProviderIds)) continue;

        // Determine platform from category
        $category = $ps['category'] ?? '';
        $platform = 'other';
        $platformKeywords = [
            'instagram' => 'instagram', 'telegram' => 'telegram', 'twitter' => 'twitter',
            'tiktok' => 'tiktok', 'youtube' => 'youtube', 'facebook' => 'facebook',
            'snapchat' => 'snapchat', 'spotify' => 'spotify',
        ];
        foreach ($platformKeywords as $kw => $pf) {
            if (stripos($category, $kw) !== false || stripos($ps['name'] ?? '', $kw) !== false) {
                $platform = $pf;
                break;
            }
        }

        // Calculate price with profit margin
        $originalPrice = (float) ($ps['rate'] ?? 0);
        $price = (int) ceil($originalPrice * (1 + $profitMargin / 100));

        $existing[] = [
            'id'                  => generateId(),
            'provider_service_id' => $providerId,
            'name'                => $ps['name'] ?? "Service #{$providerId}",
            'name_en'             => $ps['name'] ?? "Service #{$providerId}",
            'category'            => $category,
            'platform'            => $platform,
            'price'               => $price,
            'provider_price'      => $originalPrice,
            'profit_margin'       => $profitMargin,
            'min'                 => (int) ($ps['min'] ?? 1),
            'max'                 => (int) ($ps['max'] ?? 10000),
            'description'         => $ps['name'] ?? '',
            'description_en'      => $ps['name'] ?? '',
            'enabled'             => true,
        ];
        $imported++;
    }

    $store->writeAll($existing);
    jsonResponse(['ok' => true, 'imported' => $imported, 'total' => count($existing)]);
}

function handleAdminUpdateService(array $body): void
{
    $serviceId = $body['service_id'] ?? '';
    if (empty($serviceId)) {
        jsonResponse(['error' => 'Missing service_id'], 400);
    }

    $store = new JsonStore(DATA_DIR . '/services.json');
    $allowed = ['name', 'name_en', 'price', 'profit_margin', 'enabled', 'category', 'platform', 'min', 'max', 'description', 'description_en'];
    $updates = array_intersect_key($body, array_flip($allowed));

    // Recalculate price if profit_margin changed
    if (isset($updates['profit_margin'])) {
        $service = $store->find('id', $serviceId);
        if ($service && isset($service['provider_price'])) {
            $updates['price'] = (int) ceil($service['provider_price'] * (1 + $updates['profit_margin'] / 100));
        }
    }

    if ($store->update('id', $serviceId, $updates)) {
        jsonResponse(['ok' => true]);
    } else {
        jsonResponse(['error' => 'Service not found'], 404);
    }
}

function handleAdminCreatePromo(array $body): void
{
    $code = strtoupper(trim($body['code'] ?? ''));
    $points = (int) ($body['points'] ?? 0);
    $maxUses = (int) ($body['max_uses'] ?? 0);
    $expiresIn = (int) ($body['expires_in_days'] ?? 0);

    if (empty($code) || $points <= 0) {
        jsonResponse(['error' => 'Code and points are required'], 400);
    }

    $store = new JsonStore(DATA_DIR . '/promo_codes.json');

    // Check duplicate
    if ($store->find('code', $code)) {
        jsonResponse(['error' => 'Code already exists'], 400);
    }

    $store->insert([
        'id'         => generateId(),
        'code'       => $code,
        'points'     => $points,
        'max_uses'   => $maxUses ?: null,
        'uses'       => 0,
        'expires_at' => $expiresIn > 0 ? time() + ($expiresIn * 86400) : null,
        'created_at' => time(),
    ]);

    jsonResponse(['ok' => true]);
}

function handleAdminPromos(): void
{
    $store = new JsonStore(DATA_DIR . '/promo_codes.json');
    jsonResponse(['ok' => true, 'promos' => $store->readAll()]);
}

function handleAdminDeletePromo(array $body): void
{
    $code = $body['code'] ?? '';
    $store = new JsonStore(DATA_DIR . '/promo_codes.json');
    $store->delete('code', $code);
    jsonResponse(['ok' => true]);
}

function handleAdminCreateTask(array $body): void
{
    $title = trim($body['title'] ?? '');
    $titleEn = trim($body['title_en'] ?? $title);
    $type = $body['type'] ?? 'join_channel';
    $target = trim($body['target'] ?? '');
    $points = (int) ($body['points'] ?? 0);

    if (empty($title) || $points <= 0) {
        jsonResponse(['error' => 'Title and points are required'], 400);
    }

    $store = new JsonStore(DATA_DIR . '/tasks.json');
    $store->insert([
        'id'             => generateId(),
        'title'          => $title,
        'title_en'       => $titleEn,
        'description'    => $body['description'] ?? '',
        'description_en' => $body['description_en'] ?? '',
        'type'           => $type,
        'target'         => $target,
        'points'         => $points,
        'enabled'        => true,
        'created_at'     => time(),
    ]);

    jsonResponse(['ok' => true]);
}

function handleAdminTasks(): void
{
    $store = new JsonStore(DATA_DIR . '/tasks.json');
    jsonResponse(['ok' => true, 'tasks' => $store->readAll()]);
}

function handleAdminDeleteTask(array $body): void
{
    $taskId = $body['task_id'] ?? '';
    $store = new JsonStore(DATA_DIR . '/tasks.json');
    $store->delete('id', $taskId);
    jsonResponse(['ok' => true]);
}

function handleAdminBroadcast(array $body): void
{
    $message = trim($body['message'] ?? '');
    if (empty($message)) {
        jsonResponse(['error' => 'Message is required'], 400);
    }

    $userIds = UserStore::getAllIds();
    $success = 0;
    $failed = 0;

    foreach ($userIds as $uid) {
        $user = UserStore::get($uid);
        if (!$user || $user['is_banned']) {
            $failed++;
            continue;
        }
        $result = Telegram::sendMessage($uid, $message);
        if (isset($result['ok']) && $result['ok']) {
            $success++;
        } else {
            $failed++;
        }
        usleep(50000);
    }

    jsonResponse(['ok' => true, 'success' => $success, 'failed' => $failed, 'total' => count($userIds)]);
}

function handleAdminTickets(): void
{
    $store = new JsonStore(DATA_DIR . '/tickets.json');
    $tickets = $store->readAll();
    usort($tickets, fn($a, $b) => ($b['updated_at'] ?? 0) - ($a['updated_at'] ?? 0));
    jsonResponse(['ok' => true, 'tickets' => $tickets]);
}

function handleAdminTicketReply(array $body): void
{
    $ticketId = $body['ticket_id'] ?? '';
    $message = trim($body['message'] ?? '');
    $closeTicket = $body['close'] ?? false;

    if (empty($ticketId) || empty($message)) {
        jsonResponse(['error' => 'Missing fields'], 400);
    }

    $store = new JsonStore(DATA_DIR . '/tickets.json');
    $ticket = $store->find('id', $ticketId);
    if (!$ticket) {
        jsonResponse(['error' => 'Ticket not found'], 404);
    }

    $ticket['messages'][] = [
        'from'       => 'admin',
        'text'       => $message,
        'created_at' => time(),
    ];
    $ticket['status'] = $closeTicket ? 'closed' : 'answered';
    $ticket['updated_at'] = time();

    $store->update('id', $ticketId, $ticket);

    // Notify user
    Telegram::sendMessage($ticket['user_id'],
        "💬 <b>رد من الدعم الفني</b>\n\n" .
        "🎫 تذكرة: #{$ticketId}\n" .
        "💬 {$message}"
    );

    jsonResponse(['ok' => true]);
}

function handleAdminProviderTest(): void
{
    $balance = ProviderAPI::getBalance();
    $services = ProviderAPI::getServices();

    jsonResponse([
        'ok'       => true,
        'balance'  => $balance,
        'services_count' => is_array($services) ? count($services) : 0,
    ]);
}

function handleAdminSyncOrders(): void
{
    $orders = new JsonStore(DATA_DIR . '/orders.json');
    $allOrders = $orders->readAll();

    $pendingOrders = array_filter($allOrders, function ($o) {
        return in_array($o['status'], ['pending', 'processing']) && !empty($o['provider_order_id']);
    });

    if (empty($pendingOrders)) {
        jsonResponse(['ok' => true, 'synced' => 0]);
    }

    $providerIds = array_column($pendingOrders, 'provider_order_id');
    $statuses = ProviderAPI::checkOrders($providerIds);

    $synced = 0;
    if ($statuses && is_array($statuses)) {
        $statusMap = [
            'Pending'     => 'pending',
            'In progress' => 'processing',
            'Processing'  => 'processing',
            'Completed'   => 'completed',
            'Canceled'    => 'canceled',
            'Refunded'    => 'refunded',
            'Partial'     => 'partial',
        ];

        foreach ($allOrders as &$order) {
            if (empty($order['provider_order_id'])) continue;
            $pid = (string) $order['provider_order_id'];
            if (isset($statuses[$pid])) {
                $newStatus = $statusMap[$statuses[$pid]['status']] ?? $order['status'];
                if ($newStatus !== $order['status']) {
                    $order['status'] = $newStatus;
                    $order['updated_at'] = time();
                    $synced++;

                    // Auto-refund
                    if (in_array($newStatus, ['canceled', 'refunded'])) {
                        UserStore::addBalance($order['user_id'], $order['price'], "Refund: order {$order['id']}");
                        Telegram::sendMessage($order['user_id'],
                            "🔄 <b>إعادة رصيد تلقائية</b>\n💰 تم إعادة <b>{$order['price']}</b> نقطة\n📦 الطلب: {$order['id']}"
                        );
                    }

                    if ($newStatus === 'completed') {
                        Telegram::sendMessage($order['user_id'],
                            "✅ <b>تم إكمال طلبك!</b>\n📦 الطلب: {$order['id']}\n🎯 {$order['service_name']}"
                        );
                    }
                }
            }
        }
        $orders->writeAll($allOrders);
    }

    jsonResponse(['ok' => true, 'synced' => $synced]);
}

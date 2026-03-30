<?php
/**
 * WA7M BOOST - Telegram Bot Webhook Handler
 * Handles /start, referrals, payments, mandatory channels, admin commands, broadcasting
 * 
 * @package WA7M
 */

require_once __DIR__ . '/config.php';

// ─── Receive Webhook Update ───
$input = file_get_contents('php://input');
$update = json_decode($input, true);

if (!$update) {
    http_response_code(200);
    exit('OK');
}

// ─── Route Update ───
if (isset($update['message'])) {
    handleMessage($update['message']);
} elseif (isset($update['callback_query'])) {
    handleCallbackQuery($update['callback_query']);
} elseif (isset($update['pre_checkout_query'])) {
    handlePreCheckout($update['pre_checkout_query']);
} elseif (isset($update['successful_payment']) || isset($update['message']['successful_payment'])) {
    $msg = $update['message'] ?? $update;
    handleSuccessfulPayment($msg);
}

http_response_code(200);
exit('OK');

// ═══════════════════════════════════════════════════════════════
//  Message Handler
// ═══════════════════════════════════════════════════════════════

function handleMessage(array $message): void
{
    $chatId = $message['chat']['id'];
    $text = $message['text'] ?? '';
    $user = $message['from'] ?? [];
    $userId = $user['id'] ?? 0;

    // Handle successful payment within message
    if (isset($message['successful_payment'])) {
        processPayment($message);
        return;
    }

    // ─── /start command ───
    if (str_starts_with($text, '/start')) {
        handleStart($chatId, $userId, $user, $text);
        return;
    }

    // ─── Admin commands ───
    if ($chatId == ADMIN_ID) {
        handleAdminCommand($chatId, $text, $message);
        return;
    }

    // ─── Default response ───
    sendWebAppButton($chatId);
}

// ═══════════════════════════════════════════════════════════════
//  /start Handler with Referral System
// ═══════════════════════════════════════════════════════════════

function handleStart(int $chatId, int $userId, array $telegramUser, string $text): void
{
    $settings = Settings::get();

    // Check mandatory channels first
    if (!checkMandatoryChannels($chatId, $userId)) {
        return;
    }

    // Parse referral code from /start payload
    $referrerId = null;
    $parts = explode(' ', $text);
    if (count($parts) > 1) {
        $payload = $parts[1];
        if (str_starts_with($payload, 'ref_')) {
            $referrerId = (int) substr($payload, 4);
            // Don't allow self-referral
            if ($referrerId === $userId) {
                $referrerId = null;
            }
        }
    }

    // Get or create user
    $existingUser = UserStore::get($userId);

    if (!$existingUser) {
        // New user - create account
        $newUser = UserStore::create($telegramUser, $referrerId);

        // Note: referral reward is NOT given here.
        // It's given when the referee actually opens the Web App (see api.php init)

        $lang = $telegramUser['language_code'] ?? 'ar';
        if ($lang === 'ar') {
            $welcome = "🎉 <b>مرحباً بك في WA7M BOOST!</b>\n\n";
            $welcome .= "🚀 منصة التسويق الاجتماعي الأقوى\n\n";
            $welcome .= "💰 رصيدك الحالي: <b>0</b> نقطة\n";
            if ($referrerId) {
                $welcome .= "👥 تمت دعوتك بواسطة مستخدم آخر!\n";
                $welcome .= "🎁 افتح التطبيق للحصول على نقاط الإحالة\n\n";
            }
            $welcome .= "\n📱 اضغط الزر أدناه لفتح التطبيق";
        } else {
            $welcome = "🎉 <b>Welcome to WA7M BOOST!</b>\n\n";
            $welcome .= "🚀 The most powerful social media marketing platform\n\n";
            $welcome .= "💰 Your balance: <b>0</b> points\n";
            if ($referrerId) {
                $welcome .= "👥 You were invited by another user!\n";
                $welcome .= "🎁 Open the app to claim your referral bonus\n\n";
            }
            $welcome .= "\n📱 Press the button below to open the app";
        }

        Telegram::sendMessage($chatId, $welcome, [
            'reply_markup' => json_encode(getMainKeyboard($lang)),
        ]);
    } else {
        // Existing user - update info
        UserStore::update($userId, [
            'first_name'  => $telegramUser['first_name'] ?? $existingUser['first_name'],
            'last_name'   => $telegramUser['last_name'] ?? $existingUser['last_name'],
            'username'    => $telegramUser['username'] ?? $existingUser['username'],
            'last_active' => time(),
        ]);

        $lang = $existingUser['language'] ?? 'ar';
        $balance = $existingUser['balance'];
        $tier = $existingUser['vip_tier'];

        if ($lang === 'ar') {
            $msg = "👋 <b>مرحباً مجدداً!</b>\n\n";
            $msg .= "💰 رصيدك: <b>{$balance}</b> نقطة\n";
            $msg .= "⭐ مستواك: <b>" . ($settings['vip_tiers'][$tier]['label_ar'] ?? $tier) . "</b>\n\n";
            $msg .= "📱 اضغط الزر أدناه لفتح التطبيق";
        } else {
            $msg = "👋 <b>Welcome back!</b>\n\n";
            $msg .= "💰 Balance: <b>{$balance}</b> points\n";
            $msg .= "⭐ Tier: <b>" . ($settings['vip_tiers'][$tier]['label_en'] ?? $tier) . "</b>\n\n";
            $msg .= "📱 Press the button below to open the app";
        }

        Telegram::sendMessage($chatId, $msg, [
            'reply_markup' => json_encode(getMainKeyboard($lang)),
        ]);
    }
}

// ═══════════════════════════════════════════════════════════════
//  Mandatory Channel Subscription Check
// ═══════════════════════════════════════════════════════════════

function checkMandatoryChannels(int $chatId, int $userId): bool
{
    $settings = Settings::get();
    $channels = $settings['mandatory_channels'] ?? [];

    if (empty($channels)) {
        return true;
    }

    $notJoined = [];
    foreach ($channels as $channel) {
        if (!Telegram::checkMembership($userId, $channel)) {
            $notJoined[] = $channel;
        }
    }

    if (empty($notJoined)) {
        return true;
    }

    $buttons = [];
    foreach ($notJoined as $ch) {
        $buttons[] = [['text' => "📢 @{$ch}", 'url' => "https://t.me/{$ch}"]];
    }
    $buttons[] = [['text' => '✅ تحقق / Verify', 'callback_data' => 'check_channels']];

    $msg = "⚠️ <b>يجب عليك الاشتراك في القنوات التالية أولاً:</b>\n";
    $msg .= "<b>You must subscribe to the following channels first:</b>\n\n";
    foreach ($notJoined as $ch) {
        $msg .= "📢 @{$ch}\n";
    }
    $msg .= "\n✅ بعد الاشتراك، اضغط \"تحقق\" / After subscribing, press \"Verify\"";

    Telegram::sendMessage($chatId, $msg, [
        'reply_markup' => json_encode(['inline_keyboard' => $buttons]),
    ]);

    return false;
}

// ═══════════════════════════════════════════════════════════════
//  Payment Handlers (Telegram Stars)
// ═══════════════════════════════════════════════════════════════

function handlePreCheckout(array $query): void
{
    $queryId = $query['id'];
    $payload = json_decode($query['invoice_payload'], true);

    if (!$payload || !isset($payload['user_id'], $payload['stars'], $payload['points'])) {
        Telegram::answerPreCheckout($queryId, false, 'Invalid payment data');
        return;
    }

    // Verify user exists
    $user = UserStore::get($payload['user_id']);
    if (!$user) {
        Telegram::answerPreCheckout($queryId, false, 'User not found');
        return;
    }

    if ($user['is_banned']) {
        Telegram::answerPreCheckout($queryId, false, 'Account suspended');
        return;
    }

    Telegram::answerPreCheckout($queryId, true);
}

function handleSuccessfulPayment(array $message): void
{
    processPayment($message);
}

function processPayment(array $message): void
{
    $chatId = $message['chat']['id'];
    $payment = $message['successful_payment'];
    $payload = json_decode($payment['invoice_payload'], true);

    if (!$payload) return;

    $userId = $payload['user_id'];
    $points = $payload['points'];
    $stars = $payload['stars'];

    // Add points to user balance
    UserStore::addBalance($userId, $points, "Purchased {$points} points for {$stars} stars");

    // Log transaction
    $transactions = new JsonStore(DATA_DIR . '/transactions.json');
    $transactions->insert([
        'id'         => generateId(),
        'user_id'    => $userId,
        'type'       => 'purchase',
        'amount'     => $points,
        'stars'      => $stars,
        'status'     => 'completed',
        'telegram_payment_id' => $payment['telegram_payment_charge_id'] ?? '',
        'created_at' => time(),
    ]);

    $user = UserStore::get($userId);
    $balance = $user ? $user['balance'] : $points;

    // Send confirmation
    $msgAr = "✅ <b>تم الدفع بنجاح!</b>\n\n";
    $msgAr .= "💎 النقاط المضافة: <b>{$points}</b>\n";
    $msgAr .= "💰 رصيدك الجديد: <b>{$balance}</b>\n";
    $msgAr .= "⭐ تم خصم: <b>{$stars}</b> نجمة\n\n";
    $msgAr .= "شكراً لك! 🙏";

    Telegram::sendMessage($chatId, $msgAr);

    // Notify admin
    $adminMsg = "💳 <b>عملية شراء جديدة</b>\n\n";
    $adminMsg .= "👤 المستخدم: {$userId}\n";
    $adminMsg .= "💎 النقاط: {$points}\n";
    $adminMsg .= "⭐ النجوم: {$stars}\n";
    $adminMsg .= "💰 الرصيد الجديد: {$balance}";
    Telegram::sendMessage(ADMIN_ID, $adminMsg);
}

// ═══════════════════════════════════════════════════════════════
//  Callback Query Handler
// ═══════════════════════════════════════════════════════════════

function handleCallbackQuery(array $query): void
{
    $callbackId = $query['id'];
    $data = $query['data'] ?? '';
    $userId = $query['from']['id'];
    $chatId = $query['message']['chat']['id'] ?? $userId;

    if ($data === 'check_channels') {
        if (checkMandatoryChannels($chatId, $userId)) {
            Telegram::answerCallback($callbackId, '✅ تم التحقق بنجاح!', true);
            handleStart($chatId, $userId, $query['from'], '/start');
        } else {
            Telegram::answerCallback($callbackId, '❌ لم تشترك في جميع القنوات بعد', true);
        }
        return;
    }

    Telegram::answerCallback($callbackId);
}

// ═══════════════════════════════════════════════════════════════
//  Admin Commands
// ═══════════════════════════════════════════════════════════════

function handleAdminCommand(int $chatId, string $text, array $message): void
{
    // /stats - System statistics
    if ($text === '/stats') {
        $totalUsers = UserStore::count();
        $orders = new JsonStore(DATA_DIR . '/orders.json');
        $totalOrders = $orders->count();
        $transactions = new JsonStore(DATA_DIR . '/transactions.json');
        $totalTransactions = $transactions->count();

        $msg = "📊 <b>إحصائيات النظام</b>\n\n";
        $msg .= "👥 المستخدمين: <b>{$totalUsers}</b>\n";
        $msg .= "📦 الطلبات: <b>{$totalOrders}</b>\n";
        $msg .= "💳 المعاملات: <b>{$totalTransactions}</b>\n";

        Telegram::sendMessage($chatId, $msg);
        return;
    }

    // /broadcast <message> - Send to all users
    if (str_starts_with($text, '/broadcast ')) {
        $broadcastText = substr($text, 11);
        handleBroadcast($chatId, $broadcastText);
        return;
    }

    // /addpoints <user_id> <amount> - Add points to user
    if (str_starts_with($text, '/addpoints ')) {
        $parts = explode(' ', $text);
        if (count($parts) >= 3) {
            $targetId = (int) $parts[1];
            $amount = (int) $parts[2];
            $user = UserStore::get($targetId);
            if ($user) {
                UserStore::addBalance($targetId, $amount, 'Admin added points');
                $newBalance = UserStore::get($targetId)['balance'];
                Telegram::sendMessage($chatId, "✅ تم إضافة {$amount} نقطة للمستخدم {$targetId}\nالرصيد الجديد: {$newBalance}");
                Telegram::sendMessage($targetId, "🎁 تم إضافة <b>{$amount}</b> نقطة إلى رصيدك بواسطة الإدارة!\n💰 رصيدك الجديد: <b>{$newBalance}</b>");
            } else {
                Telegram::sendMessage($chatId, "❌ المستخدم غير موجود");
            }
        }
        return;
    }

    // /ban <user_id> - Ban user
    if (str_starts_with($text, '/ban ')) {
        $targetId = (int) substr($text, 5);
        if (UserStore::update($targetId, ['is_banned' => true])) {
            Telegram::sendMessage($chatId, "🚫 تم حظر المستخدم {$targetId}");
        } else {
            Telegram::sendMessage($chatId, "❌ المستخدم غير موجود");
        }
        return;
    }

    // /unban <user_id> - Unban user
    if (str_starts_with($text, '/unban ')) {
        $targetId = (int) substr($text, 7);
        if (UserStore::update($targetId, ['is_banned' => false])) {
            Telegram::sendMessage($chatId, "✅ تم فك حظر المستخدم {$targetId}");
        } else {
            Telegram::sendMessage($chatId, "❌ المستخدم غير موجود");
        }
        return;
    }

    // /setref <value> - Set referral points value
    if (str_starts_with($text, '/setref ')) {
        $value = (int) substr($text, 8);
        Settings::set('referral_points', $value);
        Telegram::sendMessage($chatId, "✅ تم تعيين نقاط الإحالة إلى: {$value}");
        return;
    }

    // /setchannel <channel_username> - Add mandatory channel
    if (str_starts_with($text, '/setchannel ')) {
        $channel = ltrim(substr($text, 12), '@');
        $settings = Settings::get();
        $channels = $settings['mandatory_channels'];
        if (!in_array($channel, $channels)) {
            $channels[] = $channel;
            Settings::set('mandatory_channels', $channels);
            Telegram::sendMessage($chatId, "✅ تمت إضافة القناة @{$channel} كقناة إجبارية");
        } else {
            Telegram::sendMessage($chatId, "⚠️ القناة موجودة بالفعل");
        }
        return;
    }

    // /removechannel <channel_username> - Remove mandatory channel
    if (str_starts_with($text, '/removechannel ')) {
        $channel = ltrim(substr($text, 15), '@');
        $settings = Settings::get();
        $channels = array_values(array_filter($settings['mandatory_channels'], fn($c) => $c !== $channel));
        Settings::set('mandatory_channels', $channels);
        Telegram::sendMessage($chatId, "✅ تمت إزالة القناة @{$channel}");
        return;
    }

    // /setnews <text> - Set news ticker
    if (str_starts_with($text, '/setnews ')) {
        $news = substr($text, 9);
        Settings::set('news_ticker', $news);
        Telegram::sendMessage($chatId, "✅ تم تحديث شريط الأخبار");
        return;
    }

    // /help - Admin help
    if ($text === '/help') {
        $help = "📋 <b>أوامر المدير:</b>\n\n";
        $help .= "/stats - إحصائيات النظام\n";
        $help .= "/broadcast &lt;msg&gt; - إرسال للجميع\n";
        $help .= "/addpoints &lt;id&gt; &lt;amount&gt; - إضافة نقاط\n";
        $help .= "/ban &lt;id&gt; - حظر مستخدم\n";
        $help .= "/unban &lt;id&gt; - فك حظر\n";
        $help .= "/setref &lt;value&gt; - قيمة نقاط الإحالة\n";
        $help .= "/setchannel &lt;@ch&gt; - إضافة قناة إجبارية\n";
        $help .= "/removechannel &lt;@ch&gt; - إزالة قناة\n";
        $help .= "/setnews &lt;text&gt; - شريط الأخبار\n";
        Telegram::sendMessage($chatId, $help);
        return;
    }

    // Default - show Web App button
    sendWebAppButton($chatId);
}

// ═══════════════════════════════════════════════════════════════
//  Broadcasting
// ═══════════════════════════════════════════════════════════════

function handleBroadcast(int $adminChatId, string $text): void
{
    $userIds = UserStore::getAllIds();
    $total = count($userIds);
    $success = 0;
    $failed = 0;

    Telegram::sendMessage($adminChatId, "📡 جاري الإرسال إلى {$total} مستخدم...");

    foreach ($userIds as $uid) {
        $user = UserStore::get($uid);
        if (!$user || $user['is_banned']) {
            $failed++;
            continue;
        }
        $result = Telegram::sendMessage($uid, $text);
        if (isset($result['ok']) && $result['ok']) {
            $success++;
        } else {
            $failed++;
        }
        usleep(50000); // 50ms delay to avoid rate limits
    }

    Telegram::sendMessage($adminChatId, "✅ تم الإرسال\n✅ نجح: {$success}\n❌ فشل: {$failed}\n📊 الإجمالي: {$total}");
}

// ═══════════════════════════════════════════════════════════════
//  Keyboard Helpers
// ═══════════════════════════════════════════════════════════════

function getMainKeyboard(string $lang = 'ar'): array
{
    $webAppUrl = WEBAPP_URL . '/index.php';
    $buttonText = $lang === 'ar' ? '🚀 فتح WA7M BOOST' : '🚀 Open WA7M BOOST';

    return [
        'inline_keyboard' => [
            [
                [
                    'text'    => $buttonText,
                    'web_app' => ['url' => $webAppUrl],
                ],
            ],
            [
                [
                    'text' => $lang === 'ar' ? '👥 دعوة صديق' : '👥 Invite Friend',
                    'callback_data' => 'referral_link',
                ],
                [
                    'text' => $lang === 'ar' ? '💰 رصيدي' : '💰 My Balance',
                    'callback_data' => 'my_balance',
                ],
            ],
        ],
    ];
}

function sendWebAppButton(int $chatId): void
{
    $msg = "🚀 <b>WA7M BOOST</b>\n\n📱 اضغط الزر لفتح التطبيق / Press button to open app";
    Telegram::sendMessage($chatId, $msg, [
        'reply_markup' => json_encode(getMainKeyboard()),
    ]);
}

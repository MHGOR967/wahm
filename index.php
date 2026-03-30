<?php
/**
 * WA7M BOOST - Telegram Web App UI
 * Premium Social Media Marketing Platform
 * 
 * @package WA7M
 */
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>WA7M BOOST</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }

        :root {
            --tg-bg: var(--tg-theme-bg-color, #0a0a0f);
            --tg-text: var(--tg-theme-text-color, #ffffff);
            --tg-hint: var(--tg-theme-hint-color, #8b8b9e);
            --tg-link: var(--tg-theme-link-color, #6c5ce7);
            --tg-btn: var(--tg-theme-button-color, #6c5ce7);
            --tg-btn-text: var(--tg-theme-button-text-color, #ffffff);
            --tg-secondary: var(--tg-theme-secondary-bg-color, #13131f);
            --accent: #8b5cf6;
            --accent-glow: rgba(139, 92, 246, 0.3);
            --gold: #f59e0b;
            --success: #10b981;
            --danger: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        body {
            font-family: 'Cairo', 'Inter', sans-serif;
            background: var(--tg-bg);
            color: var(--tg-text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        [dir="ltr"] body { font-family: 'Inter', 'Cairo', sans-serif; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 4px; }

        /* Glass morphism card */
        .glass {
            background: linear-gradient(135deg, rgba(139,92,246,0.08) 0%, rgba(59,130,246,0.05) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(139,92,246,0.15);
            border-radius: 16px;
        }

        .glass-dark {
            background: rgba(15,15,25,0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 16px;
        }

        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #8b5cf6 0%, #06b6d4 50%, #8b5cf6 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shimmer 3s linear infinite;
        }

        @keyframes shimmer {
            0% { background-position: 0% center; }
            100% { background-position: 200% center; }
        }

        /* Glow button */
        .btn-glow {
            background: linear-gradient(135deg, #8b5cf6, #6366f1);
            box-shadow: 0 4px 15px rgba(139,92,246,0.4);
            transition: all 0.3s ease;
        }
        .btn-glow:hover { box-shadow: 0 6px 25px rgba(139,92,246,0.6); transform: translateY(-1px); }
        .btn-glow:active { transform: translateY(0); }

        .btn-gold {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 4px 15px rgba(245,158,11,0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 4px 15px rgba(16,185,129,0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 4px 15px rgba(239,68,68,0.3);
        }

        /* News ticker */
        .ticker-wrap {
            overflow: hidden;
            background: linear-gradient(90deg, rgba(139,92,246,0.15), rgba(59,130,246,0.1));
            border-bottom: 1px solid rgba(139,92,246,0.2);
        }
        .ticker {
            display: inline-block;
            white-space: nowrap;
            animation: ticker 20s linear infinite;
        }
        @keyframes ticker {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        [dir="ltr"] .ticker {
            animation: ticker-ltr 20s linear infinite;
        }
        @keyframes ticker-ltr {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* Platform icons */
        .platform-icon {
            width: 40px; height: 40px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .platform-instagram { background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); }
        .platform-telegram { background: linear-gradient(135deg, #2AABEE, #229ED9); }
        .platform-twitter { background: #000; }
        .platform-tiktok { background: linear-gradient(135deg, #000, #25F4EE); }
        .platform-youtube { background: linear-gradient(135deg, #FF0000, #CC0000); }
        .platform-facebook { background: linear-gradient(135deg, #1877F2, #0C5DC7); }
        .platform-snapchat { background: #FFFC00; color: #000; }
        .platform-spotify { background: linear-gradient(135deg, #1DB954, #1aa34a); }
        .platform-other { background: linear-gradient(135deg, #6b7280, #4b5563); }

        /* VIP badges */
        .badge-bronze { background: linear-gradient(135deg, #cd7f32, #a0522d); }
        .badge-silver { background: linear-gradient(135deg, #c0c0c0, #808080); }
        .badge-gold { background: linear-gradient(135deg, #ffd700, #daa520); }

        /* Tab bar */
        .tab-bar {
            background: rgba(10,10,15,0.95);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(139,92,246,0.15);
        }
        .tab-item {
            transition: all 0.2s ease;
        }
        .tab-item.active {
            color: var(--accent);
        }
        .tab-item.active .tab-dot {
            opacity: 1;
            transform: scaleX(1);
        }
        .tab-dot {
            opacity: 0;
            transform: scaleX(0);
            transition: all 0.3s ease;
        }

        /* Slide animations */
        .slide-up { animation: slideUp 0.3s ease-out; }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in { animation: fadeIn 0.3s ease-out; }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Loading spinner */
        .spinner {
            border: 3px solid rgba(139,92,246,0.2);
            border-top: 3px solid var(--accent);
            border-radius: 50%;
            width: 32px; height: 32px;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Bottom sheet */
        .bottom-sheet {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            z-index: 100;
            transform: translateY(100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .bottom-sheet.open { transform: translateY(0); }
        .bottom-sheet-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 99;
        }

        /* Input styling */
        input, textarea, select {
            background: rgba(255,255,255,0.05) !important;
            border: 1px solid rgba(139,92,246,0.2) !important;
            color: var(--tg-text) !important;
            border-radius: 12px !important;
            padding: 12px 16px !important;
            font-family: inherit !important;
            font-size: 14px !important;
            width: 100%;
            outline: none !important;
            transition: border-color 0.2s !important;
        }
        input:focus, textarea:focus, select:focus {
            border-color: var(--accent) !important;
            box-shadow: 0 0 0 3px rgba(139,92,246,0.1) !important;
        }
        input::placeholder, textarea::placeholder { color: var(--tg-hint) !important; }

        /* Pulse dot */
        .pulse-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--success);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.5); }
        }

        /* Status colors */
        .status-pending { color: #f59e0b; }
        .status-processing { color: #3b82f6; }
        .status-completed { color: #10b981; }
        .status-canceled { color: #ef4444; }
        .status-refunded { color: #8b5cf6; }
    </style>
</head>
<body x-data="app()" x-init="init()" x-cloak>

    <!-- ═══ Loading Screen ═══ -->
    <div x-show="loading" class="fixed inset-0 z-[200] flex items-center justify-center" style="background: #0a0a0f;">
        <div class="text-center">
            <div class="text-5xl font-black gradient-text mb-4">WA7M</div>
            <div class="text-sm text-gray-400 mb-6">BOOST</div>
            <div class="spinner mx-auto"></div>
        </div>
    </div>

    <!-- ═══ News Ticker ═══ -->
    <div x-show="!loading && newsTicker" class="ticker-wrap py-2 px-4">
        <div class="ticker text-sm font-medium">
            <span class="text-yellow-400 mr-2">📢</span>
            <span x-text="lang === 'ar' ? newsTicker : newsTickerEn || newsTicker"></span>
        </div>
    </div>

    <!-- ═══ Main Content ═══ -->
    <div x-show="!loading" class="pb-24">

        <!-- ─── Home Page ─── -->
        <div x-show="page === 'home'" class="slide-up">
            <!-- Header / Profile Card -->
            <div class="p-4">
                <div class="glass p-5 relative overflow-hidden">
                    <!-- BG decoration -->
                    <div class="absolute top-0 right-0 w-32 h-32 rounded-full opacity-10" style="background: radial-gradient(circle, var(--accent), transparent);"></div>

                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-xl font-bold" :class="'badge-' + user.vip_tier">
                                <span x-text="user.first_name?.charAt(0) || 'W'"></span>
                            </div>
                            <div>
                                <div class="font-bold text-lg" x-text="user.first_name"></div>
                                <div class="text-xs flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase" :class="'badge-' + user.vip_tier" x-text="lang === 'ar' ? tierLabels[user.vip_tier]?.ar : tierLabels[user.vip_tier]?.en"></span>
                                    <template x-if="user.vip_discount > 0">
                                        <span class="text-green-400 text-[10px]" x-text="'-' + user.vip_discount + '%'"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="toggleLang()" class="w-9 h-9 rounded-full glass-dark flex items-center justify-center text-sm font-bold hover:scale-105 transition">
                                <span x-text="lang === 'ar' ? 'EN' : 'ع'"></span>
                            </button>
                            <template x-if="user.is_admin">
                                <button @click="page = 'admin'" class="w-9 h-9 rounded-full glass-dark flex items-center justify-center text-sm hover:scale-105 transition">⚙️</button>
                            </template>
                        </div>
                    </div>

                    <!-- Balance -->
                    <div class="text-center py-4">
                        <div class="text-xs text-gray-400 mb-1" x-text="t('balance')"></div>
                        <div class="text-4xl font-black gradient-text" x-text="formatNumber(user.balance)"></div>
                        <div class="text-xs text-gray-500 mt-1" x-text="t('points')"></div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-3 gap-3 mt-3">
                        <div class="text-center glass-dark p-3 rounded-xl">
                            <div class="text-lg font-bold text-purple-400" x-text="formatNumber(user.total_spent)"></div>
                            <div class="text-[10px] text-gray-500" x-text="t('spent')"></div>
                        </div>
                        <div class="text-center glass-dark p-3 rounded-xl">
                            <div class="text-lg font-bold text-blue-400" x-text="user.orders_count"></div>
                            <div class="text-[10px] text-gray-500" x-text="t('orders')"></div>
                        </div>
                        <div class="text-center glass-dark p-3 rounded-xl">
                            <div class="text-lg font-bold text-green-400" x-text="user.referral_count"></div>
                            <div class="text-[10px] text-gray-500" x-text="t('referrals')"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-3 px-4 mb-4">
                <button @click="page = 'recharge'" class="btn-gold text-white font-bold py-3.5 rounded-2xl text-sm flex items-center justify-center gap-2">
                    <span>⭐</span>
                    <span x-text="t('buy_points')"></span>
                </button>
                <button @click="page = 'services'" class="btn-glow text-white font-bold py-3.5 rounded-2xl text-sm flex items-center justify-center gap-2">
                    <span>🚀</span>
                    <span x-text="t('services')"></span>
                </button>
            </div>

            <!-- Quick Links Grid -->
            <div class="grid grid-cols-4 gap-3 px-4 mb-6">
                <button @click="page = 'orders'" class="glass-dark p-3 rounded-2xl text-center hover:scale-[0.97] transition">
                    <div class="text-2xl mb-1">📦</div>
                    <div class="text-[10px] text-gray-400" x-text="t('my_orders')"></div>
                </button>
                <button @click="page = 'tasks'" x-show="appSettings.tasks_enabled" class="glass-dark p-3 rounded-2xl text-center hover:scale-[0.97] transition">
                    <div class="text-2xl mb-1">🎯</div>
                    <div class="text-[10px] text-gray-400" x-text="t('earn_points')"></div>
                </button>
                <button @click="page = 'referral'" x-show="appSettings.referral_enabled" class="glass-dark p-3 rounded-2xl text-center hover:scale-[0.97] transition">
                    <div class="text-2xl mb-1">👥</div>
                    <div class="text-[10px] text-gray-400" x-text="t('invite')"></div>
                </button>
                <button @click="page = 'support'" x-show="appSettings.support_enabled" class="glass-dark p-3 rounded-2xl text-center hover:scale-[0.97] transition">
                    <div class="text-2xl mb-1">💬</div>
                    <div class="text-[10px] text-gray-400" x-text="t('support')"></div>
                </button>
            </div>

            <!-- Promo Code Section -->
            <div class="px-4 mb-4">
                <div class="glass-dark p-4 rounded-2xl">
                    <div class="flex items-center gap-3">
                        <input type="text" x-model="promoCode" :placeholder="t('enter_promo')" class="flex-1 text-sm">
                        <button @click="redeemPromo()" class="btn-glow text-white font-bold px-5 py-3 rounded-xl text-sm whitespace-nowrap" :disabled="!promoCode || promoLoading">
                            <span x-show="!promoLoading" x-text="t('redeem')"></span>
                            <span x-show="promoLoading" class="spinner w-4 h-4 border-2 inline-block"></span>
                        </button>
                    </div>
                    <div x-show="promoMessage" class="mt-2 text-xs text-center" :class="promoSuccess ? 'text-green-400' : 'text-red-400'" x-text="promoMessage"></div>
                </div>
            </div>

            <!-- VIP Tiers Info -->
            <div class="px-4 mb-4">
                <h3 class="text-sm font-bold text-gray-400 mb-3 px-1" x-text="t('vip_levels')"></h3>
                <div class="flex gap-3 overflow-x-auto pb-2">
                    <template x-for="(tier, key) in appSettings.vip_tiers" :key="key">
                        <div class="glass-dark p-4 rounded-2xl min-w-[130px] text-center" :class="user.vip_tier === key ? 'border-2 border-purple-500' : ''">
                            <div class="w-10 h-10 rounded-full mx-auto mb-2 flex items-center justify-center text-lg" :class="'badge-' + key">
                                <span x-text="key === 'bronze' ? '🥉' : (key === 'silver' ? '🥈' : '🥇')"></span>
                            </div>
                            <div class="text-xs font-bold" x-text="lang === 'ar' ? tier.label_ar : tier.label_en"></div>
                            <div class="text-[10px] text-gray-500 mt-1" x-text="tier.discount + '% ' + t('discount')"></div>
                            <div class="text-[10px] text-gray-600 mt-1" x-text="formatNumber(tier.min_spend) + ' ' + t('points')"></div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ─── Recharge / Buy Points Page ─── -->
        <div x-show="page === 'recharge'" class="slide-up p-4">
            <div class="flex items-center gap-3 mb-6">
                <button @click="page = 'home'" class="w-9 h-9 rounded-full glass-dark flex items-center justify-center">←</button>
                <h2 class="text-xl font-bold" x-text="t('buy_points')"></h2>
            </div>

            <!-- Balance Display -->
            <div class="glass p-6 text-center mb-6">
                <div class="text-sm text-gray-400" x-text="t('current_balance')"></div>
                <div class="text-3xl font-black gradient-text mt-1" x-text="formatNumber(user.balance)"></div>
            </div>

            <!-- Quick Purchase Options -->
            <h3 class="text-sm font-bold text-gray-400 mb-3" x-text="t('quick_purchase')"></h3>
            <div class="grid grid-cols-2 gap-3 mb-6">
                <template x-for="option in starOptions" :key="option.stars">
                    <button @click="purchaseStars(option.stars)" class="glass-dark p-4 rounded-2xl text-center hover:scale-[0.97] transition" :class="purchaseLoading ? 'opacity-50' : ''">
                        <div class="text-2xl mb-1">⭐</div>
                        <div class="text-lg font-bold text-yellow-400" x-text="option.stars + ' ' + t('stars')"></div>
                        <div class="text-xs text-gray-400 mt-1" x-text="formatNumber(option.points) + ' ' + t('points')"></div>
                    </button>
                </template>
            </div>

            <!-- Custom Amount -->
            <h3 class="text-sm font-bold text-gray-400 mb-3" x-text="t('custom_amount')"></h3>
            <div class="glass-dark p-4 rounded-2xl mb-4">
                <div class="flex items-center gap-3 mb-3">
                    <input type="number" x-model="customStars" :placeholder="t('enter_stars')" min="50" max="10000" class="flex-1">
                    <button @click="purchaseStars(customStars)" class="btn-gold text-white font-bold px-5 py-3 rounded-xl text-sm whitespace-nowrap" :disabled="!customStars || purchaseLoading">
                        <span x-show="!purchaseLoading" x-text="t('buy')"></span>
                        <span x-show="purchaseLoading" class="spinner w-4 h-4 border-2 inline-block"></span>
                    </button>
                </div>
                <div x-show="customStars > 0" class="text-center text-sm text-gray-400">
                    = <span class="text-yellow-400 font-bold" x-text="formatNumber(customStars * appSettings.points_per_star)"></span> <span x-text="t('points')"></span>
                </div>
            </div>

            <div class="text-center text-xs text-gray-600 mt-4" x-text="t('payment_note')"></div>
        </div>

        <!-- ─── Services Page ─── -->
        <div x-show="page === 'services'" class="slide-up">
            <div class="p-4">
                <div class="flex items-center gap-3 mb-4">
                    <button @click="page = 'home'" class="w-9 h-9 rounded-full glass-dark flex items-center justify-center">←</button>
                    <h2 class="text-xl font-bold flex-1" x-text="t('services')"></h2>
                    <div class="text-sm text-gray-400">
                        💰 <span x-text="formatNumber(user.balance)"></span>
                    </div>
                </div>

                <!-- Search Bar -->
                <div class="relative mb-4">
                    <input type="text" x-model="serviceSearch" :placeholder="t('search_services')" class="!pr-10">
                    <span class="absolute top-1/2 -translate-y-1/2 left-3 text-gray-500">🔍</span>
                </div>

                <!-- Platform Filter -->
                <div class="flex gap-2 overflow-x-auto pb-3 mb-4">
                    <button @click="selectedPlatform = ''" class="px-4 py-2 rounded-full text-xs font-bold whitespace-nowrap transition"
                        :class="selectedPlatform === '' ? 'btn-glow text-white' : 'glass-dark text-gray-400'" x-text="t('all')">
                    </button>
                    <template x-for="p in platforms" :key="p">
                        <button @click="selectedPlatform = p" class="px-4 py-2 rounded-full text-xs font-bold whitespace-nowrap transition capitalize"
                            :class="selectedPlatform === p ? 'btn-glow text-white' : 'glass-dark text-gray-400'" x-text="p">
                        </button>
                    </template>
                </div>
            </div>

            <!-- Services List -->
            <div class="px-4 space-y-3 pb-4">
                <div x-show="servicesLoading" class="text-center py-8">
                    <div class="spinner mx-auto"></div>
                </div>

                <template x-for="service in filteredServices" :key="service.id">
                    <button @click="selectService(service)" class="glass-dark p-4 rounded-2xl w-full text-right hover:scale-[0.98] transition">
                        <div class="flex items-center gap-3">
                            <div class="platform-icon" :class="'platform-' + service.platform">
                                <span x-text="platformEmoji(service.platform)"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-sm truncate" x-text="lang === 'ar' ? service.name : (service.name_en || service.name)"></div>
                                <div class="text-[10px] text-gray-500 mt-0.5" x-text="service.category"></div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <span x-text="t('min') + ': ' + formatNumber(service.min)"></span> •
                                    <span x-text="t('max') + ': ' + formatNumber(service.max)"></span>
                                </div>
                            </div>
                            <div class="text-left">
                                <div class="text-sm font-bold text-purple-400" x-text="formatNumber(service.price)"></div>
                                <div class="text-[10px] text-gray-500" x-text="t('per_1k')"></div>
                                <template x-if="user.vip_discount > 0">
                                    <div class="text-[10px] text-green-400 line-through" x-text="formatNumber(service.original_price)"></div>
                                </template>
                            </div>
                        </div>
                    </button>
                </template>

                <div x-show="!servicesLoading && filteredServices.length === 0" class="text-center py-8 text-gray-500">
                    <div class="text-4xl mb-2">🔍</div>
                    <div x-text="t('no_services')"></div>
                </div>
            </div>
        </div>

        <!-- ─── Order Form (Bottom Sheet) ─── -->
        <div x-show="showOrderSheet" class="bottom-sheet-overlay" @click="showOrderSheet = false" x-transition.opacity></div>
        <div class="bottom-sheet" :class="showOrderSheet ? 'open' : ''">
            <div class="glass rounded-t-3xl p-6 max-h-[80vh] overflow-y-auto" style="background: #13131f;">
                <!-- Handle -->
                <div class="w-12 h-1 rounded-full bg-gray-600 mx-auto mb-4"></div>

                <template x-if="selectedService">
                    <div>
                        <h3 class="text-lg font-bold mb-1" x-text="lang === 'ar' ? selectedService.name : (selectedService.name_en || selectedService.name)"></h3>
                        <div class="text-xs text-gray-400 mb-4" x-text="selectedService.category + ' • ' + selectedService.platform"></div>

                        <div class="space-y-4">
                            <div>
                                <label class="text-xs text-gray-400 mb-1 block" x-text="t('link')"></label>
                                <input type="url" x-model="orderLink" :placeholder="t('enter_link')" dir="ltr">
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 mb-1 block" x-text="t('quantity')"></label>
                                <input type="number" x-model="orderQuantity" :min="selectedService.min" :max="selectedService.max"
                                    :placeholder="t('min') + ': ' + selectedService.min + ' - ' + t('max') + ': ' + selectedService.max">
                            </div>

                            <!-- Price calculation -->
                            <div class="glass-dark p-4 rounded-xl">
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="text-gray-400" x-text="t('price_per_1k')"></span>
                                    <span x-text="formatNumber(selectedService.price)"></span>
                                </div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="text-gray-400" x-text="t('quantity')"></span>
                                    <span x-text="formatNumber(orderQuantity || 0)"></span>
                                </div>
                                <template x-if="user.vip_discount > 0">
                                    <div class="flex justify-between text-sm mb-2 text-green-400">
                                        <span x-text="t('vip_discount')"></span>
                                        <span x-text="'-' + user.vip_discount + '%'"></span>
                                    </div>
                                </template>
                                <div class="border-t border-gray-700 pt-2 mt-2 flex justify-between font-bold">
                                    <span x-text="t('total')"></span>
                                    <span class="text-purple-400" x-text="formatNumber(calculateOrderPrice()) + ' ' + t('points')"></span>
                                </div>
                            </div>

                            <div x-show="orderError" class="text-red-400 text-xs text-center" x-text="orderError"></div>

                            <button @click="submitOrder()" class="btn-glow text-white font-bold w-full py-4 rounded-2xl text-base" :disabled="orderLoading">
                                <span x-show="!orderLoading" x-text="t('place_order')"></span>
                                <span x-show="orderLoading" class="spinner w-5 h-5 border-2 inline-block"></span>
                            </button>

                            <div class="text-center text-xs text-gray-500">
                                💰 <span x-text="t('your_balance') + ': ' + formatNumber(user.balance) + ' ' + t('points')"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- ─── Orders Page ─── -->
        <div x-show="page === 'orders'" class="slide-up p-4">
            <div class="flex items-center gap-3 mb-6">
                <button @click="page = 'home'" class="w-9 h-9 rounded-full glass-dark flex items-center justify-center">←</button>
                <h2 class="text-xl font-bold" x-text="t('my_orders')"></h2>
            </div>

            <div x-show="ordersLoading" class="text-center py-8">
                <div class="spinner mx-auto"></div>
            </div>

            <div class="space-y-3">
                <template x-for="order in orders" :key="order.id">
                    <div class="glass-dark p-4 rounded-2xl">
                        <div class="flex items-center justify-between mb-2">
                            <div class="font-bold text-sm" x-text="order.service_name"></div>
                            <span class="px-2 py-1 rounded-full text-[10px] font-bold"
                                :class="{
                                    'bg-yellow-500/20 text-yellow-400': order.status === 'pending',
                                    'bg-blue-500/20 text-blue-400': order.status === 'processing',
                                    'bg-green-500/20 text-green-400': order.status === 'completed',
                                    'bg-red-500/20 text-red-400': order.status === 'canceled' || order.status === 'refunded',
                                    'bg-purple-500/20 text-purple-400': order.status === 'partial'
                                }"
                                x-text="t('status_' + order.status)">
                            </span>
                        </div>
                        <div class="text-xs text-gray-500 space-y-1">
                            <div>🔗 <span class="text-gray-400 truncate inline-block max-w-[200px] align-bottom" x-text="order.link" dir="ltr"></span></div>
                            <div class="flex justify-between">
                                <span>📊 <span x-text="formatNumber(order.quantity)"></span></span>
                                <span>💰 <span x-text="formatNumber(order.price)"></span> <span x-text="t('points')"></span></span>
                            </div>
                            <div class="text-gray-600" x-text="formatDate(order.created_at)"></div>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="!ordersLoading && orders.length === 0" class="text-center py-12 text-gray-500">
                <div class="text-5xl mb-3">📦</div>
                <div x-text="t('no_orders')"></div>
            </div>
        </div>

        <!-- ─── Tasks Page ─── -->
        <div x-show="page === 'tasks'" class="slide-up p-4">
            <div class="flex items-center gap-3 mb-6">
                <button @click="page = 'home'" class="w-9 h-9 rounded-full glass-dark flex items-center justify-center">←</button>
                <h2 class="text-xl font-bold" x-text="t('earn_points')"></h2>
            </div>

            <div class="glass p-4 rounded-2xl mb-6 text-center">
                <div class="text-sm text-gray-400" x-text="t('complete_tasks_earn')"></div>
            </div>

            <div x-show="tasksLoading" class="text-center py-8">
                <div class="spinner mx-auto"></div>
            </div>

            <div class="space-y-3">
                <template x-for="task in tasks" :key="task.id">
                    <div class="glass-dark p-4 rounded-2xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg"
                                :class="task.completed ? 'bg-green-500/20' : 'bg-purple-500/20'">
                                <span x-text="task.completed ? '✅' : (task.type === 'join_channel' ? '📢' : '🔗')"></span>
                            </div>
                            <div class="flex-1">
                                <div class="font-bold text-sm" x-text="lang === 'ar' ? task.title : (task.title_en || task.title)"></div>
                                <div class="text-xs text-gray-500 mt-0.5" x-text="lang === 'ar' ? task.description : (task.description_en || task.description)"></div>
                            </div>
                            <div class="text-left">
                                <div class="text-sm font-bold text-yellow-400">+<span x-text="task.points"></span></div>
                                <template x-if="!task.completed">
                                    <button @click="completeTask(task)" class="btn-glow text-white text-xs px-3 py-1.5 rounded-lg mt-1"
                                        :disabled="taskLoading === task.id">
                                        <span x-show="taskLoading !== task.id" x-text="t('do_task')"></span>
                                        <span x-show="taskLoading === task.id" class="spinner w-3 h-3 border-2 inline-block"></span>
                                    </button>
                                </template>
                                <template x-if="task.completed">
                                    <div class="text-xs text-green-400 mt-1" x-text="t('completed')"></div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="!tasksLoading && tasks.length === 0" class="text-center py-12 text-gray-500">
                <div class="text-5xl mb-3">🎯</div>
                <div x-text="t('no_tasks')"></div>
            </div>
        </div>

        <!-- ─── Referral Page ─── -->
        <div x-show="page === 'referral'" class="slide-up p-4">
            <div class="flex items-center gap-3 mb-6">
                <button @click="page = 'home'" class="w-9 h-9 rounded-full glass-dark flex items-center justify-center">←</button>
                <h2 class="text-xl font-bold" x-text="t('referral_program')"></h2>
            </div>

            <!-- Referral Stats -->
            <div class="glass p-6 text-center mb-6">
                <div class="text-4xl mb-3">👥</div>
                <div class="text-sm text-gray-400" x-text="t('earn_per_referral')"></div>
                <div class="text-3xl font-black text-yellow-400 mt-2" x-text="'+' + formatNumber(referralStats.points_per_referral)"></div>
                <div class="text-xs text-gray-500 mt-1" x-text="t('points_per_invite')"></div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-6">
                <div class="glass-dark p-4 rounded-2xl text-center">
                    <div class="text-2xl font-bold text-purple-400" x-text="referralStats.count"></div>
                    <div class="text-xs text-gray-500" x-text="t('total_invites')"></div>
                </div>
                <div class="glass-dark p-4 rounded-2xl text-center">
                    <div class="text-2xl font-bold text-green-400" x-text="formatNumber(referralStats.earnings)"></div>
                    <div class="text-xs text-gray-500" x-text="t('total_earned')"></div>
                </div>
            </div>

            <!-- Referral Link -->
            <div class="glass-dark p-4 rounded-2xl mb-4">
                <label class="text-xs text-gray-400 mb-2 block" x-text="t('your_link')"></label>
                <div class="flex gap-2">
                    <input type="text" :value="referralLink" readonly class="flex-1 text-xs" dir="ltr">
                    <button @click="copyReferralLink()" class="btn-glow text-white px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap">
                        <span x-text="t('copy')"></span>
                    </button>
                </div>
            </div>

            <button @click="shareReferralLink()" class="btn-success text-white font-bold w-full py-3.5 rounded-2xl text-sm flex items-center justify-center gap-2">
                <span>📤</span>
                <span x-text="t('share_link')"></span>
            </button>

            <div class="text-center text-xs text-gray-600 mt-4" x-text="t('referral_note')"></div>
        </div>

        <!-- ─── Support Page ─── -->
        <div x-show="page === 'support'" class="slide-up p-4">
            <div class="flex items-center gap-3 mb-6">
                <button @click="page = 'home'" class="w-9 h-9 rounded-full glass-dark flex items-center justify-center">←</button>
                <h2 class="text-xl font-bold" x-text="t('support')"></h2>
            </div>

            <!-- New Ticket -->
            <div x-show="!viewingTicket" class="mb-6">
                <div class="glass p-5 rounded-2xl mb-6">
                    <h3 class="font-bold mb-3" x-text="t('new_ticket')"></h3>
                    <div class="space-y-3">
                        <input type="text" x-model="ticketSubject" :placeholder="t('subject')">
                        <textarea x-model="ticketMessage" :placeholder="t('describe_issue')" rows="4" class="resize-none"></textarea>
                        <button @click="createTicket()" class="btn-glow text-white font-bold w-full py-3 rounded-xl text-sm" :disabled="ticketLoading || !ticketSubject || !ticketMessage">
                            <span x-show="!ticketLoading" x-text="t('send_ticket')"></span>
                            <span x-show="ticketLoading" class="spinner w-4 h-4 border-2 inline-block"></span>
                        </button>
                    </div>
                </div>

                <!-- Existing Tickets -->
                <h3 class="font-bold text-sm text-gray-400 mb-3" x-text="t('my_tickets')"></h3>
                <div class="space-y-3">
                    <template x-for="ticket in tickets" :key="ticket.id">
                        <button @click="viewTicket(ticket)" class="glass-dark p-4 rounded-2xl w-full text-right">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-sm truncate flex-1" x-text="ticket.subject"></span>
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold ml-2"
                                    :class="{
                                        'bg-green-500/20 text-green-400': ticket.status === 'open',
                                        'bg-blue-500/20 text-blue-400': ticket.status === 'answered',
                                        'bg-gray-500/20 text-gray-400': ticket.status === 'closed'
                                    }"
                                    x-text="t('ticket_' + ticket.status)">
                                </span>
                            </div>
                            <div class="text-xs text-gray-600 mt-1" x-text="formatDate(ticket.created_at)"></div>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Ticket Detail View -->
            <div x-show="viewingTicket">
                <button @click="viewingTicket = null" class="text-sm text-purple-400 mb-4 flex items-center gap-1">← <span x-text="t('back')"></span></button>
                <template x-if="viewingTicket">
                    <div>
                        <h3 class="font-bold text-lg mb-1" x-text="viewingTicket.subject"></h3>
                        <div class="text-xs text-gray-500 mb-4" x-text="formatDate(viewingTicket.created_at)"></div>

                        <div class="space-y-3 mb-4 max-h-[50vh] overflow-y-auto">
                            <template x-for="(msg, idx) in viewingTicket.messages" :key="idx">
                                <div class="p-3 rounded-xl text-sm" :class="msg.from === 'admin' ? 'glass border-l-4 border-purple-500' : 'glass-dark'">
                                    <div class="text-xs text-gray-500 mb-1" x-text="msg.from === 'admin' ? t('admin') : t('you')"></div>
                                    <div x-text="msg.text"></div>
                                    <div class="text-[10px] text-gray-600 mt-1" x-text="formatDate(msg.created_at)"></div>
                                </div>
                            </template>
                        </div>

                        <template x-if="viewingTicket.status !== 'closed'">
                            <div class="flex gap-2">
                                <input type="text" x-model="ticketReply" :placeholder="t('type_reply')" class="flex-1">
                                <button @click="replyToTicket()" class="btn-glow text-white px-4 py-2 rounded-xl text-sm font-bold" :disabled="!ticketReply">
                                    <span x-text="t('send')"></span>
                                </button>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <!-- ─── Admin Panel ─── -->
        <div x-show="page === 'admin'" class="slide-up p-4">
            <div class="flex items-center gap-3 mb-6">
                <button @click="page = 'home'" class="w-9 h-9 rounded-full glass-dark flex items-center justify-center">←</button>
                <h2 class="text-xl font-bold" x-text="t('admin_panel')"></h2>
            </div>

            <!-- Admin Stats -->
            <div class="grid grid-cols-2 gap-3 mb-6">
                <div class="glass p-4 rounded-2xl text-center">
                    <div class="text-2xl font-bold text-purple-400" x-text="adminStats.total_users || 0"></div>
                    <div class="text-xs text-gray-500" x-text="t('users')"></div>
                </div>
                <div class="glass p-4 rounded-2xl text-center">
                    <div class="text-2xl font-bold text-blue-400" x-text="adminStats.total_orders || 0"></div>
                    <div class="text-xs text-gray-500" x-text="t('orders')"></div>
                </div>
                <div class="glass p-4 rounded-2xl text-center">
                    <div class="text-2xl font-bold text-green-400" x-text="formatNumber(adminStats.total_revenue || 0)"></div>
                    <div class="text-xs text-gray-500" x-text="t('revenue')"></div>
                </div>
                <div class="glass p-4 rounded-2xl text-center">
                    <div class="text-2xl font-bold text-yellow-400" x-text="adminStats.open_tickets || 0"></div>
                    <div class="text-xs text-gray-500" x-text="t('open_tickets')"></div>
                </div>
            </div>

            <!-- Admin Menu -->
            <div class="space-y-3">
                <button @click="adminPage = 'settings'; loadAdminSettings()" class="glass-dark p-4 rounded-2xl w-full flex items-center gap-3 hover:scale-[0.98] transition">
                    <span class="text-2xl">⚙️</span>
                    <div class="flex-1 text-right"><span class="font-bold text-sm" x-text="t('settings')"></span></div>
                    <span class="text-gray-600">→</span>
                </button>
                <button @click="adminPage = 'provider'; page = 'admin_sub'" class="glass-dark p-4 rounded-2xl w-full flex items-center gap-3 hover:scale-[0.98] transition">
                    <span class="text-2xl">🔗</span>
                    <div class="flex-1 text-right"><span class="font-bold text-sm" x-text="t('provider_api')"></span></div>
                    <span class="text-gray-600">→</span>
                </button>
                <button @click="adminPage = 'promos'; loadAdminPromos(); page = 'admin_sub'" class="glass-dark p-4 rounded-2xl w-full flex items-center gap-3 hover:scale-[0.98] transition">
                    <span class="text-2xl">🎟️</span>
                    <div class="flex-1 text-right"><span class="font-bold text-sm" x-text="t('promo_codes')"></span></div>
                    <span class="text-gray-600">→</span>
                </button>
                <button @click="adminPage = 'tasks_admin'; loadAdminTasks(); page = 'admin_sub'" class="glass-dark p-4 rounded-2xl w-full flex items-center gap-3 hover:scale-[0.98] transition">
                    <span class="text-2xl">🎯</span>
                    <div class="flex-1 text-right"><span class="font-bold text-sm" x-text="t('manage_tasks')"></span></div>
                    <span class="text-gray-600">→</span>
                </button>
                <button @click="adminPage = 'broadcast'; page = 'admin_sub'" class="glass-dark p-4 rounded-2xl w-full flex items-center gap-3 hover:scale-[0.98] transition">
                    <span class="text-2xl">📡</span>
                    <div class="flex-1 text-right"><span class="font-bold text-sm" x-text="t('broadcast')"></span></div>
                    <span class="text-gray-600">→</span>
                </button>
                <button @click="adminPage = 'tickets_admin'; loadAdminTickets(); page = 'admin_sub'" class="glass-dark p-4 rounded-2xl w-full flex items-center gap-3 hover:scale-[0.98] transition">
                    <span class="text-2xl">🎫</span>
                    <div class="flex-1 text-right"><span class="font-bold text-sm" x-text="t('manage_tickets')"></span></div>
                    <span class="text-gray-600">→</span>
                </button>
                <button @click="syncOrders()" class="glass-dark p-4 rounded-2xl w-full flex items-center gap-3 hover:scale-[0.98] transition">
                    <span class="text-2xl">🔄</span>
                    <div class="flex-1 text-right"><span class="font-bold text-sm" x-text="t('sync_orders')"></span></div>
                    <span class="text-gray-600">→</span>
                </button>
            </div>
        </div>

        <!-- ─── Admin Sub Pages ─── -->
        <div x-show="page === 'admin_sub'" class="slide-up p-4">
            <div class="flex items-center gap-3 mb-6">
                <button @click="page = 'admin'" class="w-9 h-9 rounded-full glass-dark flex items-center justify-center">←</button>
                <h2 class="text-xl font-bold" x-text="adminPageTitle()"></h2>
            </div>

            <!-- Provider API Settings -->
            <div x-show="adminPage === 'provider'">
                <div class="glass p-5 rounded-2xl space-y-4">
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block" x-text="t('api_url')">API URL</label>
                        <input type="url" x-model="adminProviderUrl" placeholder="https://provider.com/api/v2" dir="ltr">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block" x-text="t('api_key')">API Key</label>
                        <input type="text" x-model="adminProviderKey" placeholder="Your API key" dir="ltr">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block" x-text="t('profit_margin')"></label>
                        <input type="number" x-model="adminProfitMargin" placeholder="20" min="0" max="500">
                        <div class="text-xs text-gray-600 mt-1" x-text="t('profit_margin_note')"></div>
                    </div>

                    <div class="flex gap-3">
                        <button @click="saveProviderSettings()" class="btn-glow text-white font-bold flex-1 py-3 rounded-xl text-sm">
                            <span x-text="t('save')"></span>
                        </button>
                        <button @click="testProvider()" class="btn-success text-white font-bold flex-1 py-3 rounded-xl text-sm">
                            <span x-text="t('test_connection')"></span>
                        </button>
                    </div>

                    <button @click="importServices()" class="btn-gold text-white font-bold w-full py-3 rounded-xl text-sm" :disabled="importLoading">
                        <span x-show="!importLoading" x-text="t('import_services')"></span>
                        <span x-show="importLoading" class="spinner w-4 h-4 border-2 inline-block"></span>
                    </button>

                    <div x-show="providerMessage" class="text-sm text-center" :class="providerSuccess ? 'text-green-400' : 'text-red-400'" x-text="providerMessage"></div>
                </div>
            </div>

            <!-- Promo Codes Management -->
            <div x-show="adminPage === 'promos'">
                <div class="glass p-5 rounded-2xl space-y-4 mb-6">
                    <h3 class="font-bold" x-text="t('create_promo')"></h3>
                    <input type="text" x-model="newPromoCode" :placeholder="t('promo_code')" class="uppercase" dir="ltr">
                    <input type="number" x-model="newPromoPoints" :placeholder="t('points')" min="1">
                    <input type="number" x-model="newPromoMaxUses" :placeholder="t('max_uses') + ' (0 = ' + t('unlimited') + ')'" min="0">
                    <input type="number" x-model="newPromoExpiry" :placeholder="t('expires_in_days') + ' (0 = ' + t('never') + ')'" min="0">
                    <button @click="createPromo()" class="btn-glow text-white font-bold w-full py-3 rounded-xl text-sm" :disabled="!newPromoCode || !newPromoPoints">
                        <span x-text="t('create')"></span>
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="promo in adminPromos" :key="promo.code">
                        <div class="glass-dark p-4 rounded-2xl flex items-center justify-between">
                            <div>
                                <div class="font-bold text-sm font-mono" x-text="promo.code"></div>
                                <div class="text-xs text-gray-500">
                                    💎 <span x-text="promo.points"></span> •
                                    📊 <span x-text="promo.uses + '/' + (promo.max_uses || '∞')"></span>
                                </div>
                            </div>
                            <button @click="deletePromo(promo.code)" class="text-red-400 text-xs px-3 py-1.5 rounded-lg glass-dark">🗑️</button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Tasks Management -->
            <div x-show="adminPage === 'tasks_admin'">
                <div class="glass p-5 rounded-2xl space-y-4 mb-6">
                    <h3 class="font-bold" x-text="t('create_task')"></h3>
                    <input type="text" x-model="newTaskTitle" :placeholder="t('task_title_ar')">
                    <input type="text" x-model="newTaskTitleEn" :placeholder="t('task_title_en')">
                    <select x-model="newTaskType">
                        <option value="join_channel" x-text="t('join_channel')"></option>
                        <option value="visit_link" x-text="t('visit_link')"></option>
                    </select>
                    <input type="text" x-model="newTaskTarget" :placeholder="t('target_channel_or_link')" dir="ltr">
                    <input type="number" x-model="newTaskPoints" :placeholder="t('points')" min="1">
                    <button @click="createTask()" class="btn-glow text-white font-bold w-full py-3 rounded-xl text-sm" :disabled="!newTaskTitle || !newTaskPoints">
                        <span x-text="t('create')"></span>
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="task in adminTasks" :key="task.id">
                        <div class="glass-dark p-4 rounded-2xl flex items-center justify-between">
                            <div>
                                <div class="font-bold text-sm" x-text="task.title"></div>
                                <div class="text-xs text-gray-500">🎯 <span x-text="task.type"></span> • 💎 <span x-text="task.points"></span></div>
                            </div>
                            <button @click="deleteTask(task.id)" class="text-red-400 text-xs px-3 py-1.5 rounded-lg glass-dark">🗑️</button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Broadcast -->
            <div x-show="adminPage === 'broadcast'">
                <div class="glass p-5 rounded-2xl space-y-4">
                    <h3 class="font-bold" x-text="t('send_broadcast')"></h3>
                    <textarea x-model="broadcastMessage" :placeholder="t('broadcast_message')" rows="6" class="resize-none"></textarea>
                    <div class="text-xs text-gray-500" x-text="t('html_supported')"></div>
                    <button @click="sendBroadcast()" class="btn-danger text-white font-bold w-full py-3 rounded-xl text-sm" :disabled="!broadcastMessage || broadcastLoading">
                        <span x-show="!broadcastLoading" x-text="t('send_to_all')"></span>
                        <span x-show="broadcastLoading" class="spinner w-4 h-4 border-2 inline-block"></span>
                    </button>
                    <div x-show="broadcastResult" class="text-sm text-center text-green-400" x-text="broadcastResult"></div>
                </div>
            </div>

            <!-- Tickets Management -->
            <div x-show="adminPage === 'tickets_admin'">
                <div class="space-y-3">
                    <template x-for="ticket in adminTickets" :key="ticket.id">
                        <div class="glass-dark p-4 rounded-2xl">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-sm" x-text="ticket.subject"></span>
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold"
                                    :class="{
                                        'bg-green-500/20 text-green-400': ticket.status === 'open',
                                        'bg-blue-500/20 text-blue-400': ticket.status === 'answered',
                                        'bg-gray-500/20 text-gray-400': ticket.status === 'closed'
                                    }"
                                    x-text="ticket.status">
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 mb-2">
                                👤 <span x-text="ticket.username"></span> • <span x-text="formatDate(ticket.created_at)"></span>
                            </div>

                            <!-- Messages -->
                            <div class="space-y-2 mb-3 max-h-40 overflow-y-auto">
                                <template x-for="(msg, idx) in ticket.messages" :key="idx">
                                    <div class="p-2 rounded-lg text-xs" :class="msg.from === 'admin' ? 'bg-purple-500/10 border-l-2 border-purple-500' : 'bg-white/5'">
                                        <span class="font-bold" x-text="msg.from === 'admin' ? 'Admin' : 'User'"></span>:
                                        <span x-text="msg.text"></span>
                                    </div>
                                </template>
                            </div>

                            <!-- Reply -->
                            <div class="flex gap-2" x-data="{reply: ''}">
                                <input type="text" x-model="reply" :placeholder="t('type_reply')" class="flex-1 !text-xs !py-2">
                                <button @click="adminReplyTicket(ticket.id, reply); reply = ''" class="btn-glow text-white px-3 py-2 rounded-lg text-xs" :disabled="!reply">
                                    <span x-text="t('send')"></span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Settings -->
            <div x-show="adminPage === 'settings'">
                <div class="glass p-5 rounded-2xl space-y-4">
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block" x-text="t('referral_points_value')"></label>
                        <input type="number" x-model="adminSettings.referral_points" min="0">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block" x-text="t('points_per_star')"></label>
                        <input type="number" x-model="adminSettings.points_per_star" min="1">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block" x-text="t('news_ticker_ar')"></label>
                        <input type="text" x-model="adminSettings.news_ticker">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block" x-text="t('news_ticker_en')"></label>
                        <input type="text" x-model="adminSettings.news_ticker_en">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block" x-text="t('mandatory_channels')"></label>
                        <input type="text" x-model="channelsInput" :placeholder="t('channels_comma_separated')" dir="ltr">
                        <div class="text-xs text-gray-600 mt-1" x-text="t('channels_note')"></div>
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" x-model="adminSettings.tasks_enabled" class="!w-5 !h-5 !p-0">
                            <span class="text-sm" x-text="t('enable_tasks')"></span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" x-model="adminSettings.referral_enabled" class="!w-5 !h-5 !p-0">
                            <span class="text-sm" x-text="t('enable_referrals')"></span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" x-model="adminSettings.support_enabled" class="!w-5 !h-5 !p-0">
                            <span class="text-sm" x-text="t('enable_support')"></span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" x-model="adminSettings.maintenance_mode" class="!w-5 !h-5 !p-0">
                            <span class="text-sm text-red-400" x-text="t('maintenance_mode')"></span>
                        </label>
                    </div>

                    <button @click="saveAdminSettings()" class="btn-glow text-white font-bold w-full py-3 rounded-xl text-sm">
                        <span x-text="t('save_settings')"></span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- ═══ Bottom Tab Bar ═══ -->
    <div x-show="!loading" class="tab-bar fixed bottom-0 left-0 right-0 z-50">
        <div class="flex items-center justify-around py-2 px-4 max-w-lg mx-auto">
            <button @click="goTo('home')" class="tab-item flex flex-col items-center py-1 px-3" :class="page === 'home' ? 'active' : 'text-gray-500'">
                <span class="text-lg">🏠</span>
                <span class="text-[10px] mt-0.5" x-text="t('home')"></span>
                <div class="tab-dot w-4 h-0.5 rounded-full bg-purple-500 mt-1"></div>
            </button>
            <button @click="goTo('services')" class="tab-item flex flex-col items-center py-1 px-3" :class="page === 'services' ? 'active' : 'text-gray-500'">
                <span class="text-lg">🚀</span>
                <span class="text-[10px] mt-0.5" x-text="t('services')"></span>
                <div class="tab-dot w-4 h-0.5 rounded-full bg-purple-500 mt-1"></div>
            </button>
            <button @click="goTo('orders')" class="tab-item flex flex-col items-center py-1 px-3" :class="page === 'orders' ? 'active' : 'text-gray-500'">
                <span class="text-lg">📦</span>
                <span class="text-[10px] mt-0.5" x-text="t('orders')"></span>
                <div class="tab-dot w-4 h-0.5 rounded-full bg-purple-500 mt-1"></div>
            </button>
            <button @click="goTo('recharge')" class="tab-item flex flex-col items-center py-1 px-3" :class="page === 'recharge' ? 'active' : 'text-gray-500'">
                <span class="text-lg">⭐</span>
                <span class="text-[10px] mt-0.5" x-text="t('recharge')"></span>
                <div class="tab-dot w-4 h-0.5 rounded-full bg-purple-500 mt-1"></div>
            </button>
            <button @click="goTo('profile_menu')" class="tab-item flex flex-col items-center py-1 px-3" :class="['referral','tasks','support','admin','admin_sub'].includes(page) ? 'active' : 'text-gray-500'">
                <span class="text-lg">👤</span>
                <span class="text-[10px] mt-0.5" x-text="t('more')"></span>
                <div class="tab-dot w-4 h-0.5 rounded-full bg-purple-500 mt-1"></div>
            </button>
        </div>
    </div>

    <!-- ═══ More Menu (Bottom Sheet) ═══ -->
    <div x-show="showMoreMenu" class="bottom-sheet-overlay" @click="showMoreMenu = false" x-transition.opacity></div>
    <div class="bottom-sheet" :class="showMoreMenu ? 'open' : ''" style="z-index: 101;">
        <div class="glass rounded-t-3xl p-6" style="background: #13131f;">
            <div class="w-12 h-1 rounded-full bg-gray-600 mx-auto mb-4"></div>
            <div class="space-y-2">
                <button @click="showMoreMenu = false; page = 'tasks'" x-show="appSettings.tasks_enabled" class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-white/5 transition">
                    <span class="text-xl">🎯</span>
                    <span class="font-bold text-sm" x-text="t('earn_points')"></span>
                </button>
                <button @click="showMoreMenu = false; page = 'referral'" x-show="appSettings.referral_enabled" class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-white/5 transition">
                    <span class="text-xl">👥</span>
                    <span class="font-bold text-sm" x-text="t('invite')"></span>
                </button>
                <button @click="showMoreMenu = false; page = 'support'" x-show="appSettings.support_enabled" class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-white/5 transition">
                    <span class="text-xl">💬</span>
                    <span class="font-bold text-sm" x-text="t('support')"></span>
                </button>
                <template x-if="user.is_admin">
                    <button @click="showMoreMenu = false; page = 'admin'; loadAdminStats()" class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-white/5 transition">
                        <span class="text-xl">⚙️</span>
                        <span class="font-bold text-sm" x-text="t('admin_panel')"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    <!-- ═══ Toast Notification ═══ -->
    <div x-show="toast.show" x-transition class="fixed top-4 left-4 right-4 z-[300] flex justify-center">
        <div class="px-6 py-3 rounded-2xl text-sm font-bold shadow-2xl max-w-sm text-center"
            :class="toast.type === 'success' ? 'bg-green-500 text-white' : (toast.type === 'error' ? 'bg-red-500 text-white' : 'bg-purple-500 text-white')"
            x-text="toast.message">
        </div>
    </div>

<script>
// ═══════════════════════════════════════════════════════════════
//  Translations
// ═══════════════════════════════════════════════════════════════
const translations = {
    ar: {
        home: 'الرئيسية', services: 'الخدمات', orders: 'الطلبات', recharge: 'شحن', more: 'المزيد',
        balance: 'رصيدك', points: 'نقطة', spent: 'المنفق', referrals: 'الإحالات',
        buy_points: 'شراء نقاط', current_balance: 'رصيدك الحالي',
        quick_purchase: 'شراء سريع', custom_amount: 'مبلغ مخصص',
        stars: 'نجمة', enter_stars: 'أدخل عدد النجوم', buy: 'شراء',
        payment_note: 'الدفع عبر نجوم تيليجرام - آمن وفوري',
        search_services: 'ابحث عن خدمة...', all: 'الكل',
        min: 'الحد الأدنى', max: 'الحد الأقصى', per_1k: 'لكل 1000',
        no_services: 'لا توجد خدمات', link: 'الرابط', enter_link: 'أدخل رابط الحساب أو المنشور',
        quantity: 'الكمية', price_per_1k: 'السعر لكل 1000', vip_discount: 'خصم VIP',
        total: 'الإجمالي', place_order: 'تنفيذ الطلب', your_balance: 'رصيدك',
        my_orders: 'طلباتي', no_orders: 'لا توجد طلبات بعد',
        status_pending: 'قيد الانتظار', status_processing: 'جاري التنفيذ', status_completed: 'مكتمل',
        status_canceled: 'ملغي', status_refunded: 'مسترد', status_partial: 'جزئي',
        earn_points: 'اكسب نقاط', complete_tasks_earn: 'أكمل المهام واحصل على نقاط مجانية!',
        no_tasks: 'لا توجد مهام حالياً', do_task: 'تنفيذ', completed: 'مكتمل ✅',
        referral_program: 'برنامج الإحالة', earn_per_referral: 'اكسب نقاط مع كل دعوة!',
        points_per_invite: 'نقطة لكل دعوة', total_invites: 'إجمالي الدعوات', total_earned: 'الأرباح',
        your_link: 'رابط الدعوة الخاص بك', copy: 'نسخ', share_link: 'مشاركة الرابط',
        referral_note: 'تحصل على النقاط بعد أن يفتح صديقك التطبيق',
        support: 'الدعم الفني', new_ticket: 'تذكرة جديدة', subject: 'الموضوع',
        describe_issue: 'اوصف مشكلتك...', send_ticket: 'إرسال التذكرة',
        my_tickets: 'تذاكري', ticket_open: 'مفتوحة', ticket_answered: 'تم الرد', ticket_closed: 'مغلقة',
        type_reply: 'اكتب رداً...', send: 'إرسال', back: 'رجوع', admin: 'الإدارة', you: 'أنت',
        enter_promo: 'أدخل كود الخصم', redeem: 'تفعيل',
        vip_levels: 'مستويات VIP', discount: 'خصم',
        admin_panel: 'لوحة التحكم', users: 'المستخدمين', revenue: 'الإيرادات', open_tickets: 'تذاكر مفتوحة',
        settings: 'الإعدادات', provider_api: 'ربط المزود API', promo_codes: 'أكواد الخصم',
        manage_tasks: 'إدارة المهام', broadcast: 'الإرسال الجماعي', manage_tickets: 'إدارة التذاكر',
        sync_orders: 'مزامنة الطلبات',
        api_url: 'رابط API', api_key: 'مفتاح API', profit_margin: 'نسبة الربح %',
        profit_margin_note: 'يضاف على سعر المزود الأصلي', save: 'حفظ',
        test_connection: 'اختبار الاتصال', import_services: 'استيراد الخدمات',
        create_promo: 'إنشاء كود جديد', promo_code: 'الكود', max_uses: 'عدد الاستخدامات',
        unlimited: 'غير محدود', expires_in_days: 'ينتهي بعد (أيام)', never: 'لا ينتهي', create: 'إنشاء',
        create_task: 'إنشاء مهمة جديدة', task_title_ar: 'عنوان المهمة (عربي)',
        task_title_en: 'عنوان المهمة (إنجليزي)', join_channel: 'اشتراك في قناة',
        visit_link: 'زيارة رابط', target_channel_or_link: 'القناة أو الرابط المستهدف',
        send_broadcast: 'إرسال رسالة للجميع', broadcast_message: 'اكتب الرسالة...',
        html_supported: 'يدعم تنسيق HTML', send_to_all: 'إرسال للجميع',
        referral_points_value: 'قيمة نقاط الإحالة', points_per_star: 'نقاط لكل نجمة',
        news_ticker_ar: 'شريط الأخبار (عربي)', news_ticker_en: 'شريط الأخبار (إنجليزي)',
        mandatory_channels: 'القنوات الإجبارية', channels_comma_separated: 'القنوات مفصولة بفواصل',
        channels_note: 'مثال: channel1,channel2', enable_tasks: 'تفعيل المهام',
        enable_referrals: 'تفعيل الإحالات', enable_support: 'تفعيل الدعم الفني',
        maintenance_mode: 'وضع الصيانة', save_settings: 'حفظ الإعدادات',
        invite: 'دعوة أصدقاء',
    },
    en: {
        home: 'Home', services: 'Services', orders: 'Orders', recharge: 'Top Up', more: 'More',
        balance: 'Your Balance', points: 'Points', spent: 'Spent', referrals: 'Referrals',
        buy_points: 'Buy Points', current_balance: 'Current Balance',
        quick_purchase: 'Quick Purchase', custom_amount: 'Custom Amount',
        stars: 'Stars', enter_stars: 'Enter star count', buy: 'Buy',
        payment_note: 'Secure payment via Telegram Stars',
        search_services: 'Search services...', all: 'All',
        min: 'Min', max: 'Max', per_1k: 'per 1K',
        no_services: 'No services found', link: 'Link', enter_link: 'Enter account or post link',
        quantity: 'Quantity', price_per_1k: 'Price per 1K', vip_discount: 'VIP Discount',
        total: 'Total', place_order: 'Place Order', your_balance: 'Your Balance',
        my_orders: 'My Orders', no_orders: 'No orders yet',
        status_pending: 'Pending', status_processing: 'Processing', status_completed: 'Completed',
        status_canceled: 'Canceled', status_refunded: 'Refunded', status_partial: 'Partial',
        earn_points: 'Earn Points', complete_tasks_earn: 'Complete tasks to earn free points!',
        no_tasks: 'No tasks available', do_task: 'Start', completed: 'Done ✅',
        referral_program: 'Referral Program', earn_per_referral: 'Earn points with every invite!',
        points_per_invite: 'points per invite', total_invites: 'Total Invites', total_earned: 'Earnings',
        your_link: 'Your Referral Link', copy: 'Copy', share_link: 'Share Link',
        referral_note: 'You earn points when your friend opens the app',
        support: 'Support', new_ticket: 'New Ticket', subject: 'Subject',
        describe_issue: 'Describe your issue...', send_ticket: 'Submit Ticket',
        my_tickets: 'My Tickets', ticket_open: 'Open', ticket_answered: 'Answered', ticket_closed: 'Closed',
        type_reply: 'Type a reply...', send: 'Send', back: 'Back', admin: 'Admin', you: 'You',
        enter_promo: 'Enter promo code', redeem: 'Redeem',
        vip_levels: 'VIP Tiers', discount: 'Discount',
        admin_panel: 'Admin Panel', users: 'Users', revenue: 'Revenue', open_tickets: 'Open Tickets',
        settings: 'Settings', provider_api: 'Provider API', promo_codes: 'Promo Codes',
        manage_tasks: 'Manage Tasks', broadcast: 'Broadcast', manage_tickets: 'Manage Tickets',
        sync_orders: 'Sync Orders',
        api_url: 'API URL', api_key: 'API Key', profit_margin: 'Profit Margin %',
        profit_margin_note: 'Added on top of provider\'s original price', save: 'Save',
        test_connection: 'Test Connection', import_services: 'Import Services',
        create_promo: 'Create New Code', promo_code: 'Code', max_uses: 'Max Uses',
        unlimited: 'Unlimited', expires_in_days: 'Expires in (days)', never: 'Never', create: 'Create',
        create_task: 'Create New Task', task_title_ar: 'Task Title (Arabic)',
        task_title_en: 'Task Title (English)', join_channel: 'Join Channel',
        visit_link: 'Visit Link', target_channel_or_link: 'Target channel or link',
        send_broadcast: 'Send Message to All', broadcast_message: 'Write your message...',
        html_supported: 'HTML formatting supported', send_to_all: 'Send to All',
        referral_points_value: 'Referral Points Value', points_per_star: 'Points per Star',
        news_ticker_ar: 'News Ticker (Arabic)', news_ticker_en: 'News Ticker (English)',
        mandatory_channels: 'Mandatory Channels', channels_comma_separated: 'Channels separated by commas',
        channels_note: 'Example: channel1,channel2', enable_tasks: 'Enable Tasks',
        enable_referrals: 'Enable Referrals', enable_support: 'Enable Support',
        maintenance_mode: 'Maintenance Mode', save_settings: 'Save Settings',
        invite: 'Invite Friends',
    }
};

// ═══════════════════════════════════════════════════════════════
//  Alpine.js App
// ═══════════════════════════════════════════════════════════════
function app() {
    return {
        // ─── State ───
        loading: true,
        page: 'home',
        lang: 'ar',
        user: {},
        appSettings: {},
        newsTicker: '',
        newsTickerEn: '',

        // Services
        services: [],
        platforms: [],
        selectedPlatform: '',
        serviceSearch: '',
        servicesLoading: false,
        selectedService: null,
        showOrderSheet: false,
        orderLink: '',
        orderQuantity: 0,
        orderError: '',
        orderLoading: false,

        // Orders
        orders: [],
        ordersLoading: false,

        // Recharge
        customStars: '',
        purchaseLoading: false,
        starOptions: [
            { stars: 50, points: 0 },
            { stars: 100, points: 0 },
            { stars: 250, points: 0 },
            { stars: 500, points: 0 },
            { stars: 1000, points: 0 },
            { stars: 2500, points: 0 },
        ],

        // Tasks
        tasks: [],
        tasksLoading: false,
        taskLoading: null,

        // Referral
        referralLink: '',
        referralStats: { count: 0, earnings: 0, points_per_referral: 0 },

        // Promo
        promoCode: '',
        promoLoading: false,
        promoMessage: '',
        promoSuccess: false,

        // Support
        ticketSubject: '',
        ticketMessage: '',
        ticketLoading: false,
        tickets: [],
        viewingTicket: null,
        ticketReply: '',

        // Admin
        adminStats: {},
        adminPage: '',
        adminSettings: {},
        adminPromos: [],
        adminTasks: [],
        adminTickets: [],
        adminProviderUrl: '',
        adminProviderKey: '',
        adminProfitMargin: 20,
        channelsInput: '',
        providerMessage: '',
        providerSuccess: false,
        importLoading: false,
        broadcastMessage: '',
        broadcastLoading: false,
        broadcastResult: '',
        newPromoCode: '', newPromoPoints: '', newPromoMaxUses: 0, newPromoExpiry: 0,
        newTaskTitle: '', newTaskTitleEn: '', newTaskType: 'join_channel', newTaskTarget: '', newTaskPoints: '',

        // More menu
        showMoreMenu: false,

        // Toast
        toast: { show: false, message: '', type: 'success' },

        // VIP tier labels
        tierLabels: {
            bronze: { ar: 'برونزي', en: 'Bronze' },
            silver: { ar: 'فضي', en: 'Silver' },
            gold: { ar: 'ذهبي', en: 'Gold' },
        },

        // ─── Init ───
        async init() {
            try {
                const tg = window.Telegram?.WebApp;
                if (tg) {
                    tg.ready();
                    tg.expand();
                    tg.enableClosingConfirmation();
                }

                const initData = tg?.initData || '';
                const res = await this.api('init', { init_data: initData });

                if (res.ok) {
                    this.user = res.user;
                    this.appSettings = res.settings;
                    this.newsTicker = res.settings.news_ticker || '';
                    this.newsTickerEn = res.settings.news_ticker_en || '';
                    this.lang = this.user.language || 'ar';
                    this.updateDirection();
                    this.updateStarOptions();

                    // Auto-load if admin
                    if (this.user.is_admin) {
                        this.loadAdminStats();
                    }
                }
            } catch (e) {
                console.error('Init error:', e);
            }

            this.loading = false;
        },

        // ─── API Helper ───
        async api(action, data = {}, method = 'POST') {
            const tg = window.Telegram?.WebApp;
            const headers = { 'Content-Type': 'application/json' };
            if (tg?.initData) {
                headers['X-Init-Data'] = tg.initData;
            }

            const url = `api.php?action=${action}`;
            const options = { method, headers };
            if (method === 'POST') {
                options.body = JSON.stringify(data);
            }

            const res = await fetch(url, options);
            return res.json();
        },

        // ─── Translation ───
        t(key) {
            return translations[this.lang]?.[key] || translations['ar'][key] || key;
        },

        // ─── Helpers ───
        formatNumber(n) {
            return Number(n || 0).toLocaleString();
        },

        formatDate(ts) {
            if (!ts) return '';
            const d = new Date(ts * 1000);
            return d.toLocaleDateString(this.lang === 'ar' ? 'ar-EG' : 'en-US', {
                year: 'numeric', month: 'short', day: 'numeric',
                hour: '2-digit', minute: '2-digit',
            });
        },

        platformEmoji(platform) {
            const map = {
                instagram: '📸', telegram: '✈️', twitter: '𝕏', tiktok: '🎵',
                youtube: '▶️', facebook: '👤', snapchat: '👻', spotify: '🎧', other: '🌐',
            };
            return map[platform] || '🌐';
        },

        updateDirection() {
            const html = document.documentElement;
            if (this.lang === 'ar') {
                html.setAttribute('dir', 'rtl');
                html.setAttribute('lang', 'ar');
            } else {
                html.setAttribute('dir', 'ltr');
                html.setAttribute('lang', 'en');
            }
        },

        toggleLang() {
            this.lang = this.lang === 'ar' ? 'en' : 'ar';
            this.updateDirection();
            this.api('profile.update', { language: this.lang });
        },

        updateStarOptions() {
            const pps = this.appSettings.points_per_star || 20;
            this.starOptions = this.starOptions.map(o => ({
                ...o,
                points: o.stars * pps,
            }));
        },

        showToast(message, type = 'success', duration = 3000) {
            this.toast = { show: true, message, type };
            setTimeout(() => { this.toast.show = false; }, duration);
        },

        goTo(page) {
            if (page === 'profile_menu') {
                this.showMoreMenu = true;
                return;
            }
            this.page = page;
            if (page === 'services') this.loadServices();
            if (page === 'orders') this.loadOrders();
            if (page === 'tasks') this.loadTasks();
            if (page === 'referral') this.loadReferral();
            if (page === 'support') this.loadTickets();
        },

        // ─── Services ───
        async loadServices() {
            this.servicesLoading = true;
            const res = await this.api('services', {}, 'POST');
            if (res.ok) {
                this.services = res.services;
                this.platforms = [...new Set(res.services.map(s => s.platform).filter(Boolean))];
            }
            this.servicesLoading = false;
        },

        get filteredServices() {
            let list = this.services;
            if (this.selectedPlatform) {
                list = list.filter(s => s.platform === this.selectedPlatform);
            }
            if (this.serviceSearch) {
                const q = this.serviceSearch.toLowerCase();
                list = list.filter(s =>
                    (s.name || '').toLowerCase().includes(q) ||
                    (s.name_en || '').toLowerCase().includes(q) ||
                    (s.category || '').toLowerCase().includes(q)
                );
            }
            return list;
        },

        selectService(service) {
            this.selectedService = service;
            this.orderLink = '';
            this.orderQuantity = service.min;
            this.orderError = '';
            this.showOrderSheet = true;
        },

        calculateOrderPrice() {
            if (!this.selectedService || !this.orderQuantity) return 0;
            const pricePerUnit = this.selectedService.price / 1000;
            return Math.ceil(pricePerUnit * this.orderQuantity);
        },

        async submitOrder() {
            if (!this.orderLink) {
                this.orderError = this.lang === 'ar' ? 'يرجى إدخال الرابط' : 'Please enter the link';
                return;
            }
            if (!this.orderQuantity || this.orderQuantity < this.selectedService.min) {
                this.orderError = this.t('min') + ': ' + this.selectedService.min;
                return;
            }

            const price = this.calculateOrderPrice();
            if (price > this.user.balance) {
                this.orderError = this.lang === 'ar' ? 'رصيد غير كافٍ' : 'Insufficient balance';
                return;
            }

            this.orderLoading = true;
            this.orderError = '';

            const res = await this.api('order.create', {
                service_id: this.selectedService.id,
                link: this.orderLink,
                quantity: parseInt(this.orderQuantity),
            });

            this.orderLoading = false;

            if (res.ok) {
                this.user.balance = res.balance;
                this.showOrderSheet = false;
                this.showToast(this.lang === 'ar' ? 'تم تنفيذ الطلب بنجاح! ✅' : 'Order placed successfully! ✅');
            } else {
                this.orderError = res.error || 'Order failed';
            }
        },

        // ─── Orders ───
        async loadOrders() {
            this.ordersLoading = true;
            const res = await this.api('orders', {}, 'POST');
            if (res.ok) this.orders = res.orders;
            this.ordersLoading = false;
        },

        // ─── Recharge / Payment ───
        async purchaseStars(stars) {
            stars = parseInt(stars);
            if (!stars || stars < 1) return;

            this.purchaseLoading = true;
            const res = await this.api('payment.invoice', { stars });
            this.purchaseLoading = false;

            if (res.ok && res.url) {
                const tg = window.Telegram?.WebApp;
                if (tg?.openInvoice) {
                    tg.openInvoice(res.url, async (status) => {
                        if (status === 'paid') {
                            // Refresh balance
                            const verify = await this.api('payment.verify', {});
                            if (verify.ok) {
                                this.user.balance = verify.balance;
                            }
                            this.showToast(this.lang === 'ar' ? 'تم الدفع بنجاح! 🎉' : 'Payment successful! 🎉');
                        }
                    });
                } else {
                    window.open(res.url, '_blank');
                }
            } else {
                this.showToast(res.error || 'Failed to create invoice', 'error');
            }
        },

        // ─── Tasks ───
        async loadTasks() {
            this.tasksLoading = true;
            const res = await this.api('tasks', {}, 'POST');
            if (res.ok) this.tasks = res.tasks;
            this.tasksLoading = false;
        },

        async completeTask(task) {
            if (task.type === 'join_channel' && task.target) {
                const tg = window.Telegram?.WebApp;
                if (tg?.openTelegramLink) {
                    tg.openTelegramLink('https://t.me/' + task.target);
                } else {
                    window.open('https://t.me/' + task.target, '_blank');
                }
                // Wait a bit then verify
                await new Promise(r => setTimeout(r, 3000));
            }

            if (task.type === 'visit_link' && task.target) {
                const tg = window.Telegram?.WebApp;
                if (tg?.openLink) {
                    tg.openLink(task.target);
                } else {
                    window.open(task.target, '_blank');
                }
                await new Promise(r => setTimeout(r, 2000));
            }

            this.taskLoading = task.id;
            const res = await this.api('task.complete', { task_id: task.id });
            this.taskLoading = null;

            if (res.ok) {
                task.completed = true;
                this.user.balance = res.balance;
                this.showToast((this.lang === 'ar' ? 'حصلت على ' : 'You earned ') + res.points + ' ' + this.t('points') + '! 🎉');
            } else {
                this.showToast(res.error || 'Task verification failed', 'error');
            }
        },

        // ─── Referral ───
        async loadReferral() {
            const res = await this.api('referral', {}, 'POST');
            if (res.ok) {
                this.referralLink = res.link;
                this.referralStats = res.stats;
            }
        },

        copyReferralLink() {
            navigator.clipboard?.writeText(this.referralLink);
            this.showToast(this.lang === 'ar' ? 'تم نسخ الرابط! 📋' : 'Link copied! 📋');
        },

        shareReferralLink() {
            const tg = window.Telegram?.WebApp;
            const text = this.lang === 'ar'
                ? `🚀 انضم إلى WA7M BOOST واحصل على نقاط مجانية!\n${this.referralLink}`
                : `🚀 Join WA7M BOOST and get free points!\n${this.referralLink}`;

            if (tg?.openTelegramLink) {
                tg.openTelegramLink(`https://t.me/share/url?url=${encodeURIComponent(this.referralLink)}&text=${encodeURIComponent(text)}`);
            } else if (navigator.share) {
                navigator.share({ text, url: this.referralLink });
            } else {
                this.copyReferralLink();
            }
        },

        // ─── Promo ───
        async redeemPromo() {
            this.promoLoading = true;
            this.promoMessage = '';
            const res = await this.api('promo.redeem', { code: this.promoCode });
            this.promoLoading = false;

            if (res.ok) {
                this.user.balance = res.balance;
                this.promoSuccess = true;
                this.promoMessage = (this.lang === 'ar' ? 'تم! حصلت على ' : 'Done! You earned ') + res.points + ' ' + this.t('points');
                this.promoCode = '';
                this.showToast(this.promoMessage);
            } else {
                this.promoSuccess = false;
                this.promoMessage = res.error || 'Invalid code';
            }
        },

        // ─── Support ───
        async loadTickets() {
            const res = await this.api('tickets', {}, 'POST');
            if (res.ok) this.tickets = res.tickets;
        },

        async createTicket() {
            this.ticketLoading = true;
            const res = await this.api('ticket.create', {
                subject: this.ticketSubject,
                message: this.ticketMessage,
            });
            this.ticketLoading = false;

            if (res.ok) {
                this.showToast(this.lang === 'ar' ? 'تم إرسال التذكرة! 📩' : 'Ticket submitted! 📩');
                this.ticketSubject = '';
                this.ticketMessage = '';
                this.loadTickets();
            }
        },

        viewTicket(ticket) {
            this.viewingTicket = ticket;
        },

        async replyToTicket() {
            if (!this.ticketReply || !this.viewingTicket) return;
            await this.api('ticket.reply', {
                ticket_id: this.viewingTicket.id,
                message: this.ticketReply,
            });
            this.viewingTicket.messages.push({
                from: 'user',
                text: this.ticketReply,
                created_at: Math.floor(Date.now() / 1000),
            });
            this.ticketReply = '';
        },

        // ─── Admin ───
        async loadAdminStats() {
            const res = await this.api('admin.stats', {}, 'POST');
            if (res.ok) this.adminStats = res.stats;
        },

        async loadAdminSettings() {
            const res = await this.api('admin.settings.get', {}, 'POST');
            if (res.ok) {
                this.adminSettings = res.settings;
                this.adminProviderUrl = res.settings.provider_api_url || '';
                this.adminProviderKey = res.settings.provider_api_key || '';
                this.adminProfitMargin = res.settings.default_profit_margin || 20;
                this.channelsInput = (res.settings.mandatory_channels || []).join(',');
            }
            this.page = 'admin_sub';
        },

        async saveAdminSettings() {
            this.adminSettings.mandatory_channels = this.channelsInput
                ? this.channelsInput.split(',').map(c => c.trim()).filter(Boolean)
                : [];
            await this.api('admin.settings', this.adminSettings);
            this.appSettings = { ...this.appSettings, ...this.adminSettings };
            this.newsTicker = this.adminSettings.news_ticker || '';
            this.newsTickerEn = this.adminSettings.news_ticker_en || '';
            this.showToast(this.lang === 'ar' ? 'تم حفظ الإعدادات ✅' : 'Settings saved ✅');
        },

        async saveProviderSettings() {
            await this.api('admin.settings', {
                provider_api_url: this.adminProviderUrl,
                provider_api_key: this.adminProviderKey,
                default_profit_margin: parseInt(this.adminProfitMargin),
            });
            this.providerMessage = this.lang === 'ar' ? 'تم الحفظ ✅' : 'Saved ✅';
            this.providerSuccess = true;
        },

        async testProvider() {
            const res = await this.api('admin.provider.test', {}, 'POST');
            if (res.ok) {
                this.providerMessage = (this.lang === 'ar' ? 'متصل! الخدمات: ' : 'Connected! Services: ') + res.services_count;
                this.providerSuccess = true;
            } else {
                this.providerMessage = res.error || 'Connection failed';
                this.providerSuccess = false;
            }
        },

        async importServices() {
            this.importLoading = true;
            const res = await this.api('admin.services.import', {}, 'POST');
            this.importLoading = false;
            if (res.ok) {
                this.providerMessage = (this.lang === 'ar' ? 'تم استيراد ' : 'Imported ') + res.imported + (this.lang === 'ar' ? ' خدمة' : ' services');
                this.providerSuccess = true;
            } else {
                this.providerMessage = res.error || 'Import failed';
                this.providerSuccess = false;
            }
        },

        async loadAdminPromos() {
            const res = await this.api('admin.promos', {}, 'POST');
            if (res.ok) this.adminPromos = res.promos;
        },

        async createPromo() {
            await this.api('admin.promo.create', {
                code: this.newPromoCode,
                points: parseInt(this.newPromoPoints),
                max_uses: parseInt(this.newPromoMaxUses) || 0,
                expires_in_days: parseInt(this.newPromoExpiry) || 0,
            });
            this.newPromoCode = '';
            this.newPromoPoints = '';
            this.loadAdminPromos();
            this.showToast(this.lang === 'ar' ? 'تم إنشاء الكود ✅' : 'Code created ✅');
        },

        async deletePromo(code) {
            await this.api('admin.promo.delete', { code });
            this.loadAdminPromos();
        },

        async loadAdminTasks() {
            const res = await this.api('admin.tasks', {}, 'POST');
            if (res.ok) this.adminTasks = res.tasks;
        },

        async createTask() {
            await this.api('admin.tasks.create', {
                title: this.newTaskTitle,
                title_en: this.newTaskTitleEn || this.newTaskTitle,
                type: this.newTaskType,
                target: this.newTaskTarget,
                points: parseInt(this.newTaskPoints),
            });
            this.newTaskTitle = '';
            this.newTaskTitleEn = '';
            this.newTaskTarget = '';
            this.newTaskPoints = '';
            this.loadAdminTasks();
            this.showToast(this.lang === 'ar' ? 'تم إنشاء المهمة ✅' : 'Task created ✅');
        },

        async deleteTask(id) {
            await this.api('admin.task.delete', { task_id: id });
            this.loadAdminTasks();
        },

        async sendBroadcast() {
            this.broadcastLoading = true;
            const res = await this.api('admin.broadcast', { message: this.broadcastMessage });
            this.broadcastLoading = false;
            if (res.ok) {
                this.broadcastResult = `✅ ${res.success} | ❌ ${res.failed} | 📊 ${res.total}`;
                this.broadcastMessage = '';
            }
        },

        async loadAdminTickets() {
            const res = await this.api('admin.tickets', {}, 'POST');
            if (res.ok) this.adminTickets = res.tickets;
        },

        async adminReplyTicket(ticketId, message) {
            if (!message) return;
            await this.api('admin.ticket.reply', { ticket_id: ticketId, message });
            this.loadAdminTickets();
            this.showToast(this.lang === 'ar' ? 'تم إرسال الرد ✅' : 'Reply sent ✅');
        },

        async syncOrders() {
            const res = await this.api('admin.orders.sync', {}, 'POST');
            if (res.ok) {
                this.showToast((this.lang === 'ar' ? 'تمت المزامنة: ' : 'Synced: ') + res.synced + (this.lang === 'ar' ? ' طلب' : ' orders'));
            }
        },

        adminPageTitle() {
            const titles = {
                settings: this.t('settings'),
                provider: this.t('provider_api'),
                promos: this.t('promo_codes'),
                tasks_admin: this.t('manage_tasks'),
                broadcast: this.t('broadcast'),
                tickets_admin: this.t('manage_tickets'),
            };
            return titles[this.adminPage] || '';
        },
    };
}
</script>
</body>
</html>

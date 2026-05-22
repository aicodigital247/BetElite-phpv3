/**
 * BETELITE - Sports Prediction SaaS Platform Clientside SPA engine
 * Developer: Senior Telegram Mini App Architect
 * Mode: Pure Vanilla JS / jQuery / AJAX REST Integrator
 */

// Global State
const state = {
  user: {
    id: 3,
    username: 'buyer_bob',
    fullname: 'Bob Buyer',
    role: 'USER', // 'USER', 'PREDICTOR', 'ADMIN'
    isVip: 0,
    balance: 100.00,
    predictorEarnings: 0.00
  },
  cart: [],
  matches: [],
  predictions: [],
  ads: [],
  referrals: [],
  activeSportFilter: 'all',
  activeMarketFilter: 'all',
  currentTab: 'marketplace'
};

// Initialize App
$(document).ready(function() {
  // 1. Detect existing PHP session outputs if available
  if (window.BETELITE_SESSION && window.BETELITE_SESSION.user_id) {
    state.user.id = BETELITE_SESSION.user_id;
    state.user.username = '@' + BETELITE_SESSION.username;
    state.user.fullname = BETELITE_SESSION.first_name || 'BetElite User';
    state.user.role = BETELITE_SESSION.role;
    state.user.balance = parseFloat(BETELITE_SESSION.balance || 0.00);
    state.user.isVip = parseInt(BETELITE_SESSION.is_vip || 0);
  } else {
    // Show role chooser modal in dev preview environment to simulate profiles
    setTimeout(() => {
      $('#login-modal-overlay').removeClass('hidden');
    }, 400);
  }

  // 2. Initialize Telegram WebApp SDK if running within Telegram Client
  initTelegramMiniApp();

  // 3. Load Cart from localStorage
  loadLocalCart();

  // 4. Run Core Data Fetches
  fetchInitialData();

  // 5. Start Fake Live Match simulator ticker loop
  startLiveTickerLoop();

  // Refresh lucide icons initially
  lucide.createIcons();
});

/**
 * Configure Telegram WebApp Core properties
 */
function initTelegramMiniApp() {
  if (typeof Telegram !== 'undefined' && Telegram.WebApp) {
    const webApp = Telegram.WebApp;
    webApp.ready();
    webApp.expand();
    
    // Sync TG User details to state if available
    const initDataUser = webApp.initDataUnsafe ? webApp.initDataUnsafe.user : null;
    if (initDataUser) {
      state.user.id = initDataUser.id;
      state.user.username = initDataUser.username ? '@' + initDataUser.username : '@tg_player';
      state.user.fullname = (initDataUser.first_name || '') + ' ' + (initDataUser.last_name || '');
      
      // Request details from register API with synced details
      registerOrSyncTelegramUser(initDataUser);
    }
    
    // Trigger vibration feedback
    if (webApp.HapticFeedback) {
      webApp.HapticFeedback.impactOccurred('medium');
    }
    
    // Safe area spacing layout shifts
    if (webApp.viewportHeight) {
      $('main').css('min-height', (webApp.viewportHeight - 120) + 'px');
    }
  }
}

/**
 * Sync profile with endpoint register
 */
function registerOrSyncTelegramUser(tgUser) {
  $.ajax({
    url: '/api/register.php',
    type: 'POST',
    data: {
      telegram_id: tgUser.id,
      username: tgUser.username || '',
      first_name: tgUser.first_name || '',
      last_name: tgUser.last_name || '',
      avatar_url: tgUser.photo_url || ''
    },
    success: function(res) {
      if (res.success && res.user) {
        state.user.id = res.user.id;
        state.user.role = res.user.role;
        state.user.balance = parseFloat(res.user.balance);
        state.user.isVip = parseInt(res.user.is_vip);
        updateUserHeaderHUD();
      }
    }
  });
}

/**
 * Choose testing identity role during development preview
 */
function loginAsRole(role) {
  $('#login-modal-overlay').addClass('hidden');
  
  // Set in-memory mock users based on role
  if (role === 'ADMIN') {
    state.user = {
      id: 1,
      username: '@admin_demo',
      fullname: 'BetElite Admin Supervisor',
      role: 'ADMIN',
      isVip: 1,
      balance: 10000.00,
      predictorEarnings: 0.00
    };
  } else if (role === 'PREDICTOR') {
    state.user = {
      id: 2,
      username: '@predictor_john',
      fullname: 'John Expert Predictor',
      role: 'PREDICTOR',
      isVip: 1,
      balance: 250.00,
      predictorEarnings: 480.00
    };
  } else {
    state.user = {
      id: 3,
      username: '@buyer_bob',
      fullname: 'Bob Regular Buyer',
      role: 'USER',
      isVip: 0,
      balance: 100.00,
      predictorEarnings: 0.00
    };
  }

  // Inject session details to backend mock via ajax call
  $.ajax({
    url: '/api/login.php',
    type: 'POST',
    data: { role: role },
    success: function() {
      showToast(`Welcome! Authenticated as ${role}`, 'success');
      updateUserHeaderHUD();
      fetchInitialData();
    }
  });
}

/**
 * Synchronize Header HUD layout metrics
 */
function updateUserHeaderHUD() {
  $('#user-fullname').text(state.user.fullname);
  $('#user-username').text(state.user.username);
  $('#user-role-badge').text(state.user.role);
  
  // Color code Role badge
  if (state.user.role === 'ADMIN') {
    $('#user-role-badge').removeClass().addClass('absolute -bottom-1 -right-1 bg-hotOrange text-white text-[9px] font-extrabold px-1 rounded-sm border border-bgDark');
    $('#nav-btn-admin').removeClass('hidden');
    $('#predictor-wallet-pocket').addClass('hidden');
  } else if (state.user.role === 'PREDICTOR') {
    $('#user-role-badge').removeClass().addClass('absolute -bottom-1 -right-1 bg-mintGreen text-bgDark text-[9px] font-extrabold px-1 rounded-sm border border-bgDark');
    $('#nav-btn-admin').addClass('hidden');
    $('#predictor-wallet-pocket').removeClass('hidden');
    $('#predictor-earnings-bal').text('$' + parseFloat(state.user.predictorEarnings).toFixed(2));
  } else {
    $('#user-role-badge').removeClass().addClass('absolute -bottom-1 -right-1 bg-blue-500 text-white text-[9px] font-extrabold px-1 rounded-sm border border-bgDark');
    $('#nav-btn-admin').addClass('hidden');
    $('#predictor-wallet-pocket').addClass('hidden');
  }

  // VIP crowns
  if (state.user.isVip) {
    $('#vip-star').removeClass('hidden');
  } else {
    $('#vip-star').addClass('hidden');
  }

  // Cash balances
  $('#hud-wallet-balance').text('$' + parseFloat(state.user.balance).toFixed(2));
  $('#wallet-balance-main').text('$' + parseFloat(state.user.balance).toFixed(2));
  
  // Update Referral input content based on user code
  const code = state.user.username ? state.user.username.replace('@', '') : 'BE' + state.user.id;
  $('#referral-link-input').val(`${window.location.origin}/index.php?startapp=${code}`);
}

/**
 * Tab switcher routing controls
 */
function switchTab(tabId) {
  state.currentTab = tabId;
  
  // Deactivate all sections & controls
  $('.tab-content-view').removeClass('active');
  $('.nav-btn').removeClass('text-mintGreen').addClass('text-gray-400');
  
  // Activate selected views
  $(`#${tabId}-view`).addClass('active');
  $(`#nav-btn-${tabId}`).removeClass('text-gray-400').addClass('text-mintGreen');
  
  // Specific view actions
  if (tabId === 'live') {
    renderLiveMatches();
  } else if (tabId === 'predictor') {
    renderPredictorHQ();
  } else if (tabId === 'admin') {
    renderAdminHQ();
  }
  
  // Trigger light haptic trigger feedback
  if (typeof Telegram !== 'undefined' && Telegram.WebApp && Telegram.WebApp.HapticFeedback) {
    Telegram.WebApp.HapticFeedback.selectionChanged();
  }

  // Rebuild Icons
  lucide.createIcons();
}

/**
 * Primary Core AJAX Data queries
 */
function fetchInitialData() {
  // Fetch promotional ads
  $.ajax({
    url: '/api/ads.php',
    type: 'GET',
    success: function(res) {
      if (res.success && res.ads) {
        state.ads = res.ads;
        renderPromoAds();
      }
    }
  });

  // Fetch predictions listing
  $.ajax({
    url: '/api/predictions.php',
    type: 'GET',
    success: function(res) {
      if (res.success && res.predictions) {
        state.predictions = res.predictions;
        renderPredictionsArena();
      }
    }
  });

  // Fetch active matches
  $.ajax({
    url: '/api/live.php',
    type: 'GET',
    success: function(res) {
      if (res.success && res.matches) {
        state.matches = res.matches;
        renderLiveMatches();
        populateMatchDropdowns();
      }
    }
  });

  // Fetch affiliates referrals list
  $.ajax({
    url: '/api/referrals.php',
    type: 'GET',
    success: function(res) {
      if (res.success && res.referrals) {
        state.referrals = res.referrals;
        renderReferralsPage();
      }
    }
  });

  // Fetch transactions and wallet states
  $.ajax({
    url: '/api/wallet.php',
    type: 'GET',
    success: function(res) {
      if (res.success) {
        state.user.balance = parseFloat(res.balance);
        state.user.predictorEarnings = parseFloat(res.predictor_earnings || 0);
        updateUserHeaderHUD();
        renderTransactionLedgers(res.transactions || []);
      }
    }
  });
}

/**
 * Carousel Promo ads builder
 */
function renderPromoAds() {
  const container = $('#promo-ads-container');
  container.empty();
  
  if (state.ads.length === 0) return;
  
  const activeAds = state.ads.filter(a => a.status === 'ACTIVE');
  if (activeAds.length === 0) return;
  
  // Pick first active ad for display banner slot
  const ad = activeAds[0];
  const urlLink = ad.link || '#';
  
  const html = `
    <div onclick="trackAdClick(${ad.id}, '${urlLink}')" class="relative overflow-hidden rounded-2xl h-24 border border-goldVip/15 cursor-pointer bg-cover bg-center transition hover:brightness-105 active:scale-98 duration-150" style="background-image: url('${ad.image_url}')">
      <div class="absolute inset-0 bg-gradient-to-r from-bgDark via-bgDark/60 to-transparent p-4 flex flex-col justify-center">
        <span class="text-[8px] bg-goldVip text-bgDark px-2 py-0.5 rounded font-extrabold w-max tracking-wide mb-1 uppercase">SPONSORED PROMO</span>
        <h3 class="text-xs md:text-sm font-extrabold text-white text-shadow">${ad.title}</h3>
        <p class="text-[9px] text-gray-300 font-semibold font-mono mt-0.5">Click to unlock bonus promo code</p>
      </div>
    </div>
  `;
  container.html(html);
}

/**
 * Handle Ad impression action tracker
 */
function trackAdClick(adId, dest) {
  $.ajax({
    url: '/api/ads.php',
    type: 'POST',
    data: { action: 'click', ad_id: adId },
    success: function() {
      if (dest.startsWith('http')) {
        window.open(dest, '_blank');
      } else {
        // Local route string navigate like #wallet
        const routedTab = dest.replace('#', '');
        if (['wallet', 'live', 'referrals'].includes(routedTab)) {
          switchTab(routedTab);
        }
      }
    }
  });
}

/**
 * Dynamic Arena Cards renderer
 */
function renderPredictionsArena() {
  const container = $('#predictions-arena-grid');
  container.empty();

  // Filters application
  let list = state.predictions;
  if (state.activeSportFilter !== 'all') {
    list = list.filter(p => {
      const parentMatch = state.matches.find(m => m.id === parseInt(p.match_id));
      return parentMatch && parentMatch.sport === state.activeSportFilter;
    });
  }
  if (state.activeMarketFilter === 'vip') {
    list = list.filter(p => parseInt(p.is_vip) === 1);
  } else if (state.activeMarketFilter === 'free') {
    list = list.filter(p => parseFloat(p.price) === 0.00);
  }

  if (list.length === 0) {
    container.html(`
      <div class="glass-panel p-8 text-center text-gray-400">
        <i data-lucide="shield-alert" class="w-8 h-8 text-amber-500 mx-auto mb-2"></i>
        <h4 class="text-xs font-bold text-white uppercase">No Prediction Tickets Available</h4>
        <p class="text-[10px] text-gray-500 mt-1">Check back later or change filters to explore.</p>
      </div>
    `);
    lucide.createIcons();
    return;
  }

  list.forEach(p => {
    const parentMatch = state.matches.find(m => m.id === parseInt(p.match_id)) || {
      home_team: 'Manchester Utd', away_team: 'Chelsea', home_logo: '🔴', away_logo: '🔵', sport: 'Football', status: 'SCHEDULED'
    };
    
    const parsedTips = JSON.parse(p.tips_json || '[]');
    const isLocked = parseInt(p.is_vip) === 1 && state.user.isVip === 0;
    
    let borderStyle = 'border-white/5';
    let vipBadge = '';
    
    if (parseInt(p.is_vip) === 1) {
      borderStyle = 'neon-border-gold';
      vipBadge = `<span class="vip-badge-gradient text-bgDark text-[8px] font-extrabold px-1.5 py-0.5 rounded flex items-center gap-0.5 tracking-wider"><i data-lucide="crown" class="w-2.5 h-2.5"></i> VIP ONLY</span>`;
    }

    // Determine Buy state vs Cart
    const existsInCart = state.cart.some(it => it.id === p.id);
    let actionBtn = '';

    if (parseFloat(p.price) === 0) {
      actionBtn = `<button class="bg-[#172b22] border border-mintGreen/30 text-mintGreen text-[11px] font-extrabold py-2 px-3 rounded-lg uppercase tracking-wider">🆓 Standard Unlocked</button>`;
    } else if (isLocked) {
      actionBtn = `<button onclick="switchTab('wallet')" class="vip-badge-gradient text-bgDark text-[11px] font-extrabold py-2 px-3 rounded-lg uppercase tracking-wider flex items-center gap-1"><i data-lucide="lock" class="w-3 h-3"></i> Get VIP Pass</button>`;
    } else {
      actionBtn = existsInCart 
        ? `<button onclick="toggleCart()" class="bg-[#2a2012]/80 border border-orange-500/30 text-orange-400 text-[11px] font-extrabold py-2 px-3 rounded-lg uppercase tracking-wider flex items-center gap-1">🛒 In Cart</button>`
        : `<button onclick="addPredictionToCart(${p.id})" class="btn-emerald-graded text-[11px] font-bold py-2 px-3.5 rounded-lg uppercase flex items-center gap-1.5"><i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i> Buy ($${parseFloat(p.price).toFixed(0)})</button>`;
    }

    // Generate tips summary display list (Lock blur support)
    let tipsSummaryHtml = '';
    if (isLocked) {
      tipsSummaryHtml = `
        <div class="relative overflow-hidden bg-bgDark/70 p-3 rounded-xl border border-white/5 my-3 py-4 text-center filter blur-[2.5px] pointer-events-none">
          <p class="text-xs text-amber-500 font-extrabold">TOP HIDDEN TIP BUNDLE - VIP MEMBERS ONLY</p>
          <p class="text-[10px] text-gray-400">3 Premium Picks</p>
        </div>
      `;
    } else {
      tipsSummaryHtml = `<div class="space-y-1.5 my-3">`;
      parsedTips.forEach((tip, idx) => {
        tipsSummaryHtml += `
          <div class="flex items-center justify-between text-xs bg-[#0c0e15]/60 hover:bg-[#151a24]/60 p-2.5 rounded-xl border border-white/5">
            <div class="flex items-center gap-2">
              <span class="text-[9px] bg-white/5 text-gray-400 font-mono w-4.5 h-4.5 rounded-full flex items-center justify-center">${idx+1}</span>
              <div>
                <p class="text-white font-bold leading-none text-xs">${cleanStr(tip.prediction)}</p>
                <p class="text-[10px] text-gray-400 font-medium mt-0.5">Selection: <span class="text-emerald-400 font-bold font-mono">${cleanStr(tip.option)}</span></p>
              </div>
            </div>
            <span class="text-xs bg-mintGreen/15 text-mintGreen px-2 py-0.5 rounded font-mono font-bold">${parseFloat(tip.odds).toFixed(2)}</span>
          </div>
        `;
      });
      tipsSummaryHtml += `</div>`;
    }

    const html = `
      <div class="glass-block glass-panel p-4 border ${borderStyle} relative transition-all duration-200 hover:scale-[1.01]">
        <!-- Top row header -->
        <div class="flex items-center justify-between border-b border-white/5 pb-2.5 mb-2.5">
          <div class="flex items-center gap-1.5">
            <span class="text-[11px] text-gray-400 font-semibold font-mono flex items-center gap-1">
              🏠 ${parentMatch.home_team} vs ✈️ ${parentMatch.away_team}
            </span>
          </div>
          ${vipBadge}
        </div>

        <!-- Body segment title -->
        <div class="flex justify-between items-start gap-3">
          <div>
            <span class="text-[9px] bg-red-500/10 text-red-400 border border-red-500/20 px-1.5 py-0.5 rounded font-bold uppercase tracking-wider">🔥 HOT Ticket</span>
            <h4 class="text-sm font-extrabold text-white mt-1 leading-snug tracking-tight">${cleanStr(p.title)}</h4>
            <p class="text-[11px] text-gray-400 mt-1 leading-relaxed">${cleanStr(p.description || '')}</p>
          </div>
          <div class="text-right">
            <span class="text-[10px] text-gray-400 block font-semibold">Total Odds</span>
            <span class="text-xl font-black font-mono text-[#fbbf24] tracking-tight">${parseFloat(p.total_odds).toFixed(2)}</span>
          </div>
        </div>

        <!-- Render combo tips inside ticket -->
        ${tipsSummaryHtml}

        <!-- Bottom action drawer -->
        <div class="flex items-center justify-between border-t border-white/5 pt-3">
          <div class="flex items-center gap-2">
            <div class="flex flex-col">
              <span class="text-[9px] text-gray-500 uppercase font-bold tracking-wider">Confidence%</span>
              <span class="text-xs font-black font-mono text-white text-emerald-400">${p.confidence}% Verified</span>
            </div>
          </div>
          ${actionBtn}
        </div>
      </div>
    `;
    container.append(html);
  });

  lucide.createIcons();
}

/**
 * Filter Market selector logic
 */
function filterMarket(type) {
  state.activeMarketFilter = type;
  $('.market-filter-btn').removeClass('bg-mintGreen text-bgDark text-gray-400').addClass('text-gray-400');
  
  if (type === 'all') {
    $('.market-filter-btn').eq(0).addClass('bg-mintGreen text-bgDark');
  } else if (type === 'vip') {
    $('.market-filter-btn').eq(1).addClass('bg-mintGreen text-bgDark');
  } else {
    $('.market-filter-btn').eq(2).addClass('bg-mintGreen text-bgDark');
  }
  
  renderPredictionsArena();
}

/**
 * Filter Sport selector pill UI logic
 */
function filterSport(sport) {
  state.activeSportFilter = sport;
  $('.sport-pill').removeClass('active');
  const targetBtn = $(`.sport-pill:contains('${sport}')`);
  if (targetBtn.length) {
    targetBtn.addClass('active');
  } else {
    $('.sport-pill').eq(0).addClass('active');
  }
  renderPredictionsArena();
}

/**
 * Live matches sync rendering
 */
function renderLiveMatches() {
  const container = $('#live-matches-container');
  container.empty();

  if (state.matches.length === 0) {
    container.html(`<p class="text-xs text-gray-400 text-center py-8">No live sports at this moment.</p>`);
    return;
  }

  state.matches.forEach(m => {
    let timerBadge = '';
    let livePulseDot = '';
    let bentoStatBox = '';

    if (m.status === 'LIVE') {
      timerBadge = `<span class="bg-red-500/15 text-red-400 font-mono text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider flex items-center gap-1 border border-red-500/20"><i data-lucide="radio" class="w-3.5 h-3.5 animate-pulse text-red-500"></i> ${m.live_timer}</span>`;
      livePulseDot = `<span class="w-2 h-2 bg-red-500 rounded-full live-pulse"></span>`;
      
      const stats = JSON.parse(m.extra_stats || '{"possession":[50,50],"shots_on_target":[3,2]}');
      const posA = stats.possession ? stats.possession[0] : 50;
      const posB = stats.possession ? stats.possession[1] : 50;
      
      bentoStatBox = `
        <div class="mt-3.5 p-3 bg-bgDark border border-white/5 rounded-xl space-y-2">
          <div class="flex items-center justify-between text-[10px] text-gray-400 font-mono">
            <span>Home Goal Pressure</span>
            <span>Away Goal Pressure</span>
          </div>
          <!-- Progress dual possession slider -->
          <div class="h-2 w-full bg-[#181a24] rounded-full overflow-hidden flex">
            <div class="bg-mintGreen h-full" style="width: ${posA}%"></div>
            <div class="bg-blue-500 h-full" style="width: ${posB}%"></div>
          </div>
          <div class="flex justify-between items-center text-xs font-bold font-mono">
            <span class="text-mintGreen">${posA}% Possession</span>
            <span class="text-blue-500">${posB}% Possession</span>
          </div>
        </div>
      `;
    } else if (m.status === 'COMPLETED') {
      timerBadge = `<span class="bg-white/5 text-gray-400 font-mono text-[10px] px-2 py-0.5 rounded-full font-bold">FT COMPLETED</span>`;
    } else {
      timerBadge = `<span class="bg-mintGreen/10 text-mintGreen font-mono text-[10px] px-2 py-0.5 rounded-full font-bold">Kickoff in ${m.live_timer || '2 Hours'}</span>`;
    }

    const html = `
      <div class="glass-panel p-4 relative border border-white/5">
        <div class="flex items-center justify-between border-b border-white/5 pb-2.5 mb-3">
          <span class="text-[10px] text-gray-400 font-extrabold uppercase tracking-widest flex items-center gap-1.5">
            ${livePulseDot}
            ${m.sport === 'Football' ? '🏟️' : m.sport === 'Basketball' ? '🏀' : m.sport === 'Tennis' ? '🎾' : '🎮'} ${m.sport} Tournament
          </span>
          ${timerBadge}
        </div>

        <div class="grid grid-cols-3 items-center text-center py-2">
          <!-- Team A -->
          <div>
            <div class="w-12 h-12 bg-white/5 rounded-full flex items-center justify-center mx-auto text-2xl border border-white/5 mb-1.5 shadow">
              ${m.home_logo || '🔴'}
            </div>
            <span class="text-xs font-extrabold text-[#f1f3f5] block truncate px-1">${m.home_team}</span>
          </div>

          <!-- Score display -->
          <div>
            <div class="text-2xl font-black font-mono tracking-wider text-white">
              ${m.home_score} : ${m.away_score}
            </div>
            <span class="text-[9px] text-[#fbbf24] font-bold uppercase tracking-widest">Live Stats API</span>
          </div>

          <!-- Team B -->
          <div>
            <div class="w-12 h-12 bg-white/5 rounded-full flex items-center justify-center mx-auto text-2xl border border-white/5 mb-1.5 shadow">
              ${m.away_logo || '🔵'}
            </div>
            <span class="text-xs font-extrabold text-[#f1f3f5] block truncate px-1">${m.away_team}</span>
          </div>
        </div>

        ${bentoStatBox}
      </div>
    `;
    container.append(html);
  });

  lucide.createIcons();
}

/**
 * Predictor center action view
 */
function renderPredictorHQ() {
  $('#predictor-stats-total').text(state.predictions.filter(p => p.predictor_id === state.user.id).length);
  
  const container = $('#predictor-tips-history-list');
  container.empty();
  
  const myList = state.predictions.filter(p => p.predictor_id === state.user.id);
  if (myList.length === 0) {
    container.html(`<p class="text-xs text-gray-500 text-center py-4">No prediction tickets created yet. Build one above!</p>`);
    return;
  }

  myList.forEach(p => {
    let badge = '';
    if (p.status === 'WON') {
      badge = `<span class="bg-[#12281a] border border-mintGreen/30 text-mintGreen text-[9px] font-extrabold px-2 py-0.5 rounded">WINNER</span>`;
    } else if (p.status === 'LOST') {
      badge = `<span class="bg-[#2a1312] border border-red-500/30 text-red-400 text-[9px] font-extrabold px-2 py-0.5 rounded">LOSS</span>`;
    } else {
      badge = `<span class="bg-[#1c1a12] border border-amber-500/30 text-amber-500 text-[9px] font-extrabold px-2 py-0.5 rounded">PENDING RESULT</span>`;
    }

    const html = `
      <div class="p-3 bg-bgDark border border-white/5 rounded-xl space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-[10px] text-gray-300 font-bold max-w-[160px] truncate">${cleanStr(p.title)}</span>
          <div class="flex gap-1.5 items-center">
            <span class="text-xs font-mono font-bold text-gray-500">$${parseFloat(p.price).toFixed(2)}</span>
            ${badge}
          </div>
        </div>
        <p class="text-[9px] text-gray-500 font-mono">Date Published: ${new Date(p.created_at || Date.now()).toLocaleDateString()}</p>
      </div>
    `;
    container.append(html);
  });
}

/**
 * Dynamic Admin actions room
 */
function renderAdminHQ() {
  // Matches control settled segment
  const container = $('#admin-matches-control-grid');
  container.empty();

  if (state.matches.length === 0) {
    container.html(`<p class="text-xs text-gray-400 text-center">No active sport events created.</p>`);
    return;
  }

  state.matches.forEach(m => {
    const html = `
      <div class="p-3 bg-bgDark border border-white/5 rounded-xl space-y-3">
        <div class="flex justify-between items-center border-b border-white/5 pb-1.5">
          <span class="text-[10px] text-gray-400 font-mono">🏠 ${m.home_team} vs ✈️ ${m.away_team} (${m.status})</span>
          <span class="text-[10px] text-mintGreen font-mono font-bold">${m.sport}</span>
        </div>
        
        <div class="flex items-center justify-between gap-3">
          <!-- Set Score parameters -->
          <div class="flex items-center gap-1">
            <input type="number" id="adm-score-home-${m.id}" value="${m.home_score}" min="0" class="w-10 bg-panelGrey border border-white/10 p-1 rounded font-mono text-center text-xs text-white">
            <span class="text-xs">:</span>
            <input type="number" id="adm-score-away-${m.id}" value="${m.away_score}" min="0" class="w-10 bg-panelGrey border border-white/10 p-1 rounded font-mono text-center text-xs text-white">
            <button onclick="updateAdminMatchScore(${m.id})" class="bg-blue-600/25 border border-blue-500/30 text-blue-400 text-[10px] font-extrabold px-1.5 py-1 rounded">Update</button>
          </div>
          
          <!-- Settle Prediction actions -->
          <div class="flex gap-1.5">
            <button onclick="settleMatchGroup(${m.id}, 'COMPLETED')" class="bg-mintGreen text-bgDark text-[10px] font-bold px-2 py-1.5 rounded-lg">End Match</button>
          </div>
        </div>
      </div>
    `;
    container.append(html);
  });

  // User list display
  const userList = $('#admin-users-list');
  userList.empty();
  
  // Dummy user records in preview DB
  const mockUsers = [
    { id: 1, name: 'BETELITE Admin', user: 'admin_demo', role: 'ADMIN', badgeColor: 'bg-red-500' },
    { id: 2, name: 'John Expert Predictor', user: 'predictor_john', role: 'PREDICTOR', badgeColor: 'bg-mintGreen' },
    { id: 3, name: 'Bob Regular Buyer', user: 'buyer_bob', role: 'USER', badgeColor: 'bg-blue-500' },
  ];

  mockUsers.forEach(u => {
    const html = `
      <div class="flex items-center justify-between p-2.5 bg-bgDark border border-white/5 rounded-xl">
        <div>
          <h4 class="text-xs font-bold text-white leading-none">${u.name}</h4>
          <span class="text-[9px] text-gray-400 mt-0.5 block font-mono">@${u.user}</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-[9px] ${u.badgeColor} text-bgDark font-extrabold px-1.5 py-0.5 rounded">${u.role}</span>
          <button onclick="changeAdminUserRole(${u.id}, '${u.role === 'PREDICTOR' ? 'USER' : 'PREDICTOR'}')" class="bg-[#1e2330] border border-white/5 hover:bg-white/5 text-[10px] font-bold py-1 px-2 rounded-lg text-white">
            Swap Role
          </button>
        </div>
      </div>
    `;
    userList.append(html);
  });
}

/**
 * Update active sport events live scores values
 */
function updateAdminMatchScore(matchId) {
  const h = parseInt($(`#adm-score-home-${matchId}`).val() || 0);
  const a = parseInt($(`#adm-score-away-${matchId}`).val() || 0);
  
  $.ajax({
    url: '/api/admin.php',
    type: 'POST',
    data: {
      action: 'score_update',
      match_id: matchId,
      home_score: h,
      away_score: a
    },
    success: function(res) {
      if (res.success) {
        showToast('Live scores updated successfully!', 'success');
        fetchInitialData();
      }
    }
  });
}

/**
 * VIP Pass payment
 */
function purchaseVipPass() {
  if (state.user.balance < 29.99) {
    showToast('Insufficient wallet balance to unlock premium pass!', 'error');
    return;
  }

  $.ajax({
    url: '/api/wallet.php',
    type: 'POST',
    data: { action: 'vip_purchase' },
    success: function(res) {
      if (res.success) {
        state.user.isVip = 1;
        state.user.balance -= 29.99;
        showToast('Hurray! You are now a premium BETELITE VIP member!', 'success');
        updateUserHeaderHUD();
        fetchInitialData();
      }
    }
  });
}

/**
 * Settle sports result winner groups
 */
function settleMatchGroup(matchId, status) {
  $.ajax({
    url: '/api/admin.php',
    type: 'POST',
    data: {
      action: 'settle_match',
      match_id: matchId,
      status: status
    },
    success: function(res) {
      if (res.success) {
        showToast('Sports event ended. Preds settled and payout executed!', 'success');
        fetchInitialData();
      }
    }
  });
}

/**
 * Handle administrative privilege updates
 */
function changeAdminUserRole(userId, newRole) {
  $.ajax({
    url: '/api/admin.php',
    type: 'POST',
    data: {
      action: 'change_role',
      user_id: userId,
      role: newRole
    },
    success: function(res) {
      if (res.success) {
        showToast(`Role upgraded to ${newRole}! Sync active`, 'success');
        if (state.user.id === userId) {
          state.user.role = newRole;
          updateUserHeaderHUD();
        }
        fetchInitialData();
      }
    }
  });
}

/**
 * Render Transaction ledger history
 */
function renderTransactionLedgers(transactions) {
  const container = $('#transaction-logs-list');
  container.empty();

  if (transactions.length === 0) {
    container.html(`<p class="text-xs text-gray-500 text-center py-4">No records found inside ledger log.</p>`);
    return;
  }

  transactions.forEach(t => {
    let typeClass = 'text-green-400';
    let sign = '+';
    
    if (['PURCHASE', 'WITHDRAWAL', 'VIP_UPGRADE'].includes(t.type)) {
      typeClass = 'text-red-400';
      sign = '-';
    }

    const html = `
      <div class="flex items-center justify-between p-2.5 bg-bgDark border border-white/5 rounded-xl">
        <div>
          <h4 class="text-xs font-bold text-white uppercase">${cleanStr(t.type)}</h4>
          <span class="text-[9px] text-gray-400 font-mono">${t.reference}</span>
        </div>
        <div class="text-right">
          <span class="text-xs font-mono font-bold ${typeClass}">${sign}$${parseFloat(t.amount).toFixed(2)}</span>
          <span class="text-[9px] text-gray-500 block">${t.status}</span>
        </div>
      </div>
    `;
    container.append(html);
  });
}

/**
 * Shopping Cart triggers
 */
function addPredictionToCart(predictionId) {
  const foundPred = state.predictions.find(p => p.id === predictionId);
  if (!foundPred) return;

  if (state.cart.some(it => it.id === predictionId)) {
    showToast('Ticket already added to prediction cart!', 'error');
    return;
  }

  state.cart.push(foundPred);
  saveLocalCart();
  updateCartBadge();
  showToast('Standard prediction ticket added to cart! 🛒', 'success');
  renderPredictionsArena();
}

function removeCartItem(predictionId) {
  state.cart = state.cart.filter(it => it.id !== predictionId);
  saveLocalCart();
  updateCartBadge();
  renderClientCartView();
  renderPredictionsArena();
}

/**
 * Handle Storage persistent lists
 */
function loadLocalCart() {
  const loaded = localStorage.getItem('betelite_cart');
  if (loaded) {
    state.cart = JSON.parse(loaded);
  }
  updateCartBadge();
}

function saveLocalCart() {
  localStorage.setItem('betelite_cart', JSON.stringify(state.cart));
}

function updateCartBadge() {
  $('#cart-badge').text(state.cart.length);
}

function toggleCart() {
  const drawer = $('#cart-drawer');
  const overlay = $('#cart-drawer-overlay');
  
  if (drawer.hasClass('translate-x-full')) {
    renderClientCartView();
    overlay.removeClass('pointer-events-none opacity-0');
    drawer.removeClass('translate-x-full');
  } else {
    overlay.addClass('pointer-events-none opacity-0');
    drawer.addClass('translate-x-full');
  }
}

/**
 * Render Cart items listing drawer
 */
function renderClientCartView() {
  const container = $('#cart-items-container');
  container.empty();

  let totalPrice = 0.00;
  
  if (state.cart.length === 0) {
    container.html(`
      <div class="text-center py-6 text-gray-500">
        <i data-lucide="shopping-basket" class="w-8 h-8 text-gray-600 mx-auto mb-2"></i>
        <p class="text-xs font-bold">Your cart is empty</p>
        <p class="text-[10px] text-gray-500 mt-1">Explore Predictions arena slots first</p>
      </div>
    `);
    $('#cart-total-price').text('$0.00');
    lucide.createIcons();
    return;
  }

  state.cart.forEach(item => {
    totalPrice += parseFloat(item.price);
    
    const html = `
      <div class="p-3 bg-bgDark border border-white/5 rounded-xl flex items-center justify-between">
        <div>
          <span class="text-[9px] bg-white/5 text-gray-400 px-1.5 py-0.5 rounded font-mono font-bold">Odds: ${parseFloat(item.total_odds).toFixed(2)}</span>
          <h4 class="text-xs font-bold text-white mt-1 max-w-[150px] truncate">${cleanStr(item.title)}</h4>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-xs font-bold text-mintGreen font-mono">$${parseFloat(item.price).toFixed(2)}</span>
          <button onclick="removeCartItem(${item.id})" class="p-1 hover:bg-white/5 text-red-400 rounded-lg">
            <i data-lucide="trash-2" class="w-4 h-4"></i>
          </button>
        </div>
      </div>
    `;
    container.append(html);
  });

  $('#cart-total-price').text('$' + totalPrice.toFixed(2));
  lucide.createIcons();
}

/**
 * Execute predictions check-out
 */
function checkoutCartBundle() {
  let totalCost = 0.00;
  state.cart.forEach(it => totalCost += parseFloat(it.price));

  if (state.cart.length === 0) {
    showToast('Add item combos to cart before checkout!', 'error');
    return;
  }

  if (state.user.balance < totalCost) {
    showToast('Insufficient wallet balance! Please reload cash.', 'error');
    return;
  }

  const ids = state.cart.map(it => it.id);
  
  $.ajax({
    url: '/api/cart.php',
    type: 'POST',
    data: { prediction_ids: JSON.stringify(ids) },
    success: function(res) {
      if (res.success) {
        showToast('Checkout complete! Tickets added to Order History.', 'success');
        state.user.balance -= totalCost;
        state.cart = [];
        saveLocalCart();
        updateCartBadge();
        toggleCart();
        updateUserHeaderHUD();
        fetchInitialData();
      } else {
        showToast(res.message || 'Payment execution failed!', 'error');
      }
    }
  });
}

/**
 * Handle Payout withdrawal requests
 */
function withdrawPredictorSales() {
  if (state.user.predictorEarnings < 10) {
    showToast('Minimum payout withdraw is $10.00!', 'error');
    return;
  }

  $.ajax({
    url: '/api/predictor.php',
    type: 'POST',
    data: { action: 'withdraw' },
    success: function(res) {
      if (res.success) {
        showToast('Earnings transferred to your primary Vault Balance!', 'success');
        fetchInitialData();
      }
    }
  });
}

/**
 * Client deposits modal
 */
function openDepositModal() { $('#deposit-modal').removeClass('hidden'); }
function closeDepositModal() { $('#deposit-modal').addClass('hidden'); }

function executeDeposit(e) {
  e.preventDefault();
  const amt = parseFloat($('#deposit-amount').val() || 0);
  const method = $('input[name="payment_method"]:checked').val();
  
  if (amt < 5) {
    showToast('Minimum billing deposit is $5.00', 'error');
    return;
  }

  $.ajax({
    url: '/api/wallet.php',
    type: 'POST',
    data: { action: 'deposit', amount: amt, payment_method: method },
    success: function(res) {
      if (res.success) {
        showToast(`Refill approved! Added $${amt.toFixed(2)} to wallet!`, 'success');
        closeDepositModal();
        fetchInitialData();
      }
    }
  });
}

/**
 * Withdraw panel
 */
function openWithdrawModal() { $('#withdraw-modal').removeClass('hidden'); }
function closeWithdrawModal() { $('#withdraw-modal').addClass('hidden'); }

function executeWithdraw(e) {
  e.preventDefault();
  const amt = parseFloat($('#withdraw-amount').val() || 0);
  const add = $('#payout-address').val();
  const rawMethod = $('#payout-method').val();
  
  if (state.user.balance < amt) {
    showToast('Insufficient funds!', 'error');
    return;
  }

  $.ajax({
    url: '/api/wallet.php',
    type: 'POST',
    data: { action: 'withdraw', amount: amt, address: add, method: rawMethod },
    success: function(res) {
      if (res.success) {
        showToast(`Withdrawal proposal of $${amt.toFixed(2)} submitted for processing!`, 'success');
        closeWithdrawModal();
        fetchInitialData();
      }
    }
  });
}

/**
 * Predictor Publisher
 */
function openPublishPredictionModal() {
  $('#publish-modal').removeClass('hidden');
}
function closePublishPredictionModal() {
  $('#publish-modal').addClass('hidden');
}

function executePublishPrediction(e) {
  e.preventDefault();
  
  const mId = $('#pub-match-id').val();
  const title = $('#pub-title').val();
  const dsc = $('#pub-desc').val();
  const price = parseFloat($('#pub-price').val() || 0);
  const isVip = parseInt($('#pub-vip').val() || 0);

  // Parse combo tips builder
  const tips = [];
  const predsPr = $('.pub-tip-prediction');
  const optsPr = $('.pub-tip-option');
  const oddsPr = $('.pub-tip-odds');

  for (let idx = 0; idx < 3; idx++) {
    tips.push({
      prediction: predsPr.eq(idx).val(),
      option: optsPr.eq(idx).val(),
      odds: parseFloat(oddsPr.eq(idx).val() || 1.1)
    });
  }

  $.ajax({
    url: '/api/predictor.php',
    type: 'POST',
    data: {
      action: 'publish',
      match_id: mId,
      title: title,
      description: dsc,
      price: price,
      is_vip: isVip,
      tips: JSON.stringify(tips)
    },
    success: function(res) {
      if (res.success) {
        showToast('Ticket Combo successfully launched inside marketplace!', 'success');
        closePublishPredictionModal();
        fetchInitialData();
        // Clear inputs
        $('#pub-title').val('');
        $('#pub-desc').val('');
      } else {
        showToast(res.message || 'Listing error!', 'error');
      }
    }
  });
}

/**
 * Match Injection modal triggers
 */
function openCreateMatchModal() { $('#admin-create-match-modal').removeClass('hidden'); }
function closeCreateMatchModal() { $('#admin-create-match-modal').addClass('hidden'); }

function executeCreateMatch(e) {
  e.preventDefault();
  
  const sport = $('#match-sport').val();
  const home = $('#match-home').val();
  const away = $('#match-away').val();
  const homeLogo = $('#match-home-logo').val();
  const awayLogo = $('#match-away-logo').val();
  const hrs = $('#match-hours').val();

  $.ajax({
    url: '/api/admin.php',
    type: 'POST',
    data: {
      action: 'create_match',
      sport: sport,
      home_team: home,
      away_team: away,
      home_logo: homeLogo,
      away_logo: awayLogo,
      offset_hours: hrs
    },
    success: function(res) {
      if (res.success) {
        showToast('New match injected successfully!', 'success');
        closeCreateMatchModal();
        fetchInitialData();
      }
    }
  });
}

function openConfigureAdsModal() {
  showToast('Advertisers feature locked to global settings section.', 'info');
}

function populateMatchDropdowns() {
  const select = $('#pub-match-id');
  select.empty();
  
  state.matches.forEach(m => {
    if (m.status !== 'COMPLETED') {
      select.append(`<option value="${m.id}">${m.home_team} vs ${m.away_team} (${m.sport})</option>`);
    }
  });
}

/**
 * Referrals list renderer
 */
function renderReferralsPage() {
  $('#referrals-count').text(state.referrals.length);
  const container = $('#referrals-table-list');
  container.empty();

  if (state.referrals.length === 0) {
    container.html(`<p class="text-xs text-gray-500 text-center py-4">No recruits yet. Invite buddies!</p>`);
    return;
  }

  state.referrals.forEach(r => {
    const html = `
      <div class="flex items-center justify-between p-2.5 bg-bgDark border border-white/5 rounded-xl text-xs font-mono">
        <span class="text-white">ID: ${r.referred_id}</span>
        <span class="text-goldVip font-bold">+$${parseFloat(r.commission_earned || 0.00).toFixed(2)}</span>
      </div>
    `;
    container.append(html);
  });
}

function copyReferralLink() {
  const inp = document.getElementById('referral-link-input');
  inp.select();
  inp.setSelectionRange(0, 99999);
  navigator.clipboard.writeText(inp.value);
  showToast('Affiliate Link Copied to clipboard!', 'success');
}

function inviteTelegramFriend() {
  const link = $('#referral-link-input').val();
  const tUrl = `https://t.me/share/url?url=${encodeURIComponent(link)}&text=${encodeURIComponent('Join the elite Sports Prediction betting Mini App on Telegram! 🏆')}`;
  window.open(tUrl, '_blank');
}

/**
 * Fake Live simulation ticks (Simulated dynamic event updater)
 */
function startLiveTickerLoop() {
  setInterval(() => {
    // Process only if user is watching live matches
    if (state.currentTab === 'live' || state.currentTab === 'admin') {
      const activeGame = state.matches.find(m => m.status === 'LIVE');
      if (activeGame) {
        // Random chance of goal score shift
        const roll = Math.random();
        if (roll > 0.88) {
          const side = Math.random() > 0.5 ? 'home' : 'away';
          if (side === 'home') activeGame.home_score++;
          else activeGame.away_score++;
          
          showToast(`⚽ GOAL! ${side === 'home' ? activeGame.home_team : activeGame.away_team} scores. Match score is now ${activeGame.home_score}:${activeGame.away_score}!`, 'info');
        }
        
        // Advance timer
        let curMin = parseInt(activeGame.live_timer ? activeGame.live_timer.replace("'", "") : '45');
        curMin = isNaN(curMin) ? 45 : curMin + 1;
        
        if (curMin >= 90) {
          activeGame.live_timer = '90+';
          activeGame.status = 'COMPLETED';
          showToast(`⏱️ Full time whistle blown inside ${activeGame.home_team} vs ${activeGame.away_team}!`, 'info');
        } else {
          activeGame.live_timer = curMin + "'";
        }
        
        renderLiveMatches();
        if (state.currentTab === 'admin') renderAdminHQ();
      }
    }
  }, 12000);
}

/**
 * Clean sanitization strings XSS safe
 */
function cleanStr(str) {
  if (!str) return '';
  return str.toString()
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

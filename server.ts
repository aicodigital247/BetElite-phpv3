import express from "express";
import path from "path";
import { createServer as createViteServer } from "vite";

async function startServer() {
  const app = express();
  const PORT = 3000;

  // Use middleware to parse request bodies
  app.use(express.json());
  app.use(express.urlencoded({ extended: true }));

  // Dynamic In-memory Mock Database for development preview
  const db = {
    users: [
      { id: 1, username: 'admin_demo', fullname: 'BetElite Supervisor Admin', role: 'ADMIN', balance: 10000.00, isVip: 1, predictor_earnings: 0.00 },
      { id: 2, username: 'predictor_john', fullname: 'John Expert Predictor', role: 'PREDICTOR', balance: 250.00, isVip: 1, predictor_earnings: 480.00 },
      { id: 3, username: 'buyer_bob', fullname: 'Bob Regular Buyer', role: 'USER', balance: 100.00, isVip: 0, predictor_earnings: 0.00 },
    ],
    matches: [
      {
        id: 1,
        sport: 'Football',
        home_team: 'Manchester United',
        away_team: 'Chelsea',
        home_logo: '🔴',
        away_logo: '🔵',
        match_time: new Date(Date.now() + 7200000).toISOString(),
        status: 'SCHEDULED',
        home_score: 0,
        away_score: 0,
        live_timer: null,
        extra_stats: null
      },
      {
        id: 2,
        sport: 'Football',
        home_team: 'Real Madrid',
        away_team: 'Barcelona',
        home_logo: '⚪',
        away_logo: '🔵🔴',
        match_time: new Date(Date.now() - 3600000).toISOString(),
        status: 'LIVE',
        home_score: 2,
        away_score: 1,
        live_timer: "57'",
        extra_stats: '{"possession":[52,48],"shots_on_target":[6,4],"yellow_cards":[1,2],"corners":[4,3]}'
      },
      {
        id: 3,
        sport: 'Basketball',
        home_team: 'LA Lakers',
        away_team: 'Golden State Warriors',
        home_logo: '🟡',
        away_logo: '🔵🟡',
        match_time: new Date(Date.now() + 14400000).toISOString(),
        status: 'SCHEDULED',
        home_score: 0,
        away_score: 0,
        live_timer: null,
        extra_stats: null
      },
      {
        id: 4,
        sport: 'Tennis',
        home_team: 'Novak Djokovic',
        away_team: 'Carlos Alcaraz',
        home_logo: '🎾',
        away_logo: '🇪🇸',
        match_time: new Date(Date.now() - 10800000).toISOString(),
        status: 'COMPLETED',
        home_score: 3,
        away_score: 1,
        live_timer: 'FT',
        extra_stats: '{"aces":[12,8],"double_faults":[2,4],"unforced_errors":[24,31]}'
      },
      {
        id: 5,
        sport: 'eSports',
        home_team: 'Natus Vincere',
        away_team: 'FaZe Clan',
        home_logo: '💛',
        away_logo: '❤️',
        match_time: new Date(Date.now() + 3600000).toISOString(),
        status: 'SCHEDULED',
        home_score: 0,
        away_score: 0,
        live_timer: null,
        extra_stats: null
      }
    ],
    predictions: [
      {
        id: 1,
        predictor_id: 2,
        match_id: 1,
        title: 'Man Utd vs Chelsea - Super Combo',
        description: 'Highly analysed bundle for the Manchester Derby-like rival event. Safe odds.',
        price: '15.00',
        tips_json: '[{"prediction":"Match Winner","option":"Manchester United","odds":"2.10","confidence":"82"},{"prediction":"Both Teams to Score","option":"Yes","odds":"1.65","confidence":"78"},{"prediction":"Total Goals","option":"Over 2.5","odds":"1.75","confidence":"85"}]',
        total_odds: '6.06',
        confidence: 81,
        is_vip: 0,
        status: 'PENDING',
        sales_count: 4,
        views: 32,
        created_at: new Date().toISOString()
      },
      {
        id: 2,
        predictor_id: 2,
        match_id: 2,
        title: 'El Clasico Live VIP Ticket',
        description: 'Premium live tips for the ongoing El Clasico. Extremely hot.',
        price: '25.00',
        tips_json: '[{"prediction":"Next Goal (3rd Goal)","option":"Real Madrid","odds":"2.40","confidence":"90"},{"prediction":"Total Cards","option":"Over 4.5","odds":"1.80","confidence":"95"}]',
        total_odds: '4.32',
        confidence: 92,
        is_vip: 1,
        status: 'PENDING',
        sales_count: 2,
        views: 15,
        created_at: new Date().toISOString()
      },
      {
        id: 3,
        predictor_id: 2,
        match_id: 4,
        title: 'Tennis Finals Masterpiece',
        description: 'The Novak Djokovic masterclass prediction bundle.',
        price: '10.00',
        tips_json: '[{"prediction":"Match Winner","option":"Novak Djokovic","odds":"1.60","confidence":"95"},{"prediction":"Set Handicap","option":"Djokovic -1.5","odds":"1.90","confidence":"90"}]',
        total_odds: '3.04',
        confidence: 92,
        is_vip: 0,
        status: 'WON',
        sales_count: 9,
        views: 84,
        created_at: new Date(Date.now() - 10800000).toISOString()
      }
    ],
    transactions: [
      { id: 1, user_id: 3, amount: '50.00', type: 'DEPOSIT', status: 'COMPLETED', payment_method: 'Telegram Stars', reference: 'TXD731420', created_at: new Date().toISOString() }
    ],
    referrals: [
      { id: 1, referrer_id: 3, referred_id: 18452, commission_earned: '10.00', status: 'PAID', created_at: new Date().toISOString() },
      { id: 2, referrer_id: 3, referred_id: 47102, commission_earned: '5.00', status: 'PENDING', created_at: new Date().toISOString() }
    ],
    ads: [
      { id: 1, title: '🔥 UPGRADE TO VIP - Get Fixed Combo tickets over 15+ Odds!', image_url: 'https://images.unsplash.com/photo-1518063319789-7217e6706b04?q=80&w=600', link: '#wallet', type: 'BANNER', status: 'ACTIVE' },
      { id: 2, title: '⚡ Sponsor: Stake with 1XBET! Promo Code: BETELITE', image_url: 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?q=80&w=600', link: 'https://1xbet.com', type: 'BANNER', status: 'ACTIVE' }
    ]
  };

  // Keep Track of Active Logged-in session profile during dev preview
  let sessionUser = db.users[2]; // Default Bob Buyer

  // --- API ROUTE INTERCEPTORS simulating the PHP Endpoints ---

  // 1. Mock Login (Identity Swapper)
  app.post("/api/login.php", (req, res) => {
    const requestedRole = req.body.role || 'USER';
    const matched = db.users.find(u => u.role === requestedRole);
    if (matched) {
      sessionUser = matched;
      res.json({ success: true, message: `Session swaped to ${requestedRole}`, user: matched });
    } else {
      res.json({ success: false, message: 'Identity role mapping error' });
    }
  });

  // 2. Mock Register
  app.post("/api/register.php", (req, res) => {
    const { telegram_id, username, first_name, last_name, avatar_url } = req.body;
    let existing = db.users.find(u => u.username === username);
    if (!existing) {
      existing = {
        id: db.users.length + 1,
        username: username || 'tg_user',
        fullname: `${first_name || 'BetElite'} ${last_name || 'User'}`,
        role: 'USER',
        balance: 100.00,
        isVip: 0,
        predictor_earnings: 0.00
      };
      db.users.push(existing);
    }
    sessionUser = existing;
    res.json({ success: true, message: 'Telegram Mini App user registered', user: existing });
  });

  // 3. Mock Ads
  app.get("/api/ads.php", (req, res) => {
    res.json({ success: true, ads: db.ads });
  });
  app.post("/api/ads.php", (req, res) => {
    res.json({ success: true, message: 'Ad click tracked' });
  });

  // 4. Mock Predictions
  app.get("/api/predictions.php", (req, res) => {
    res.json({ success: true, predictions: db.predictions });
  });

  // 5. Mock Live Matches
  app.get("/api/live.php", (req, res) => {
    res.json({ success: true, matches: db.matches });
  });

  // 6. Mock Referrals
  app.get("/api/referrals.php", (req, res) => {
    res.json({ success: true, referrals: db.referrals });
  });

  // 7. Mock Wallet Details, deposits and withdrawals
  app.get("/api/wallet.php", (req, res) => {
    res.json({
      success: true,
      balance: sessionUser.balance,
      predictor_earnings: sessionUser.predictor_earnings,
      transactions: db.transactions.filter(t => t.user_id === sessionUser.id)
    });
  });

  app.post("/api/wallet.php", (req, res) => {
    const { action, amount, payment_method, address, method } = req.body;
    const valAmt = parseFloat(amount || "0");

    if (action === 'deposit') {
      sessionUser.balance += valAmt;
      const tx = {
        id: db.transactions.length + 1,
        user_id: sessionUser.id,
        amount: valAmt.toFixed(2),
        type: 'DEPOSIT',
        status: 'COMPLETED',
        payment_method: payment_method || 'Telegram Stars',
        reference: 'TXD' + Math.floor(100000 + Math.random() * 900000),
        created_at: new Date().toISOString()
      };
      db.transactions.push(tx);
      res.json({ success: true, balance: sessionUser.balance });
    } else if (action === 'withdraw') {
      if (sessionUser.balance < valAmt) {
        res.json({ success: false, message: 'Insufficient balance' });
        return;
      }
      sessionUser.balance -= valAmt;
      const tx = {
        id: db.transactions.length + 1,
        user_id: sessionUser.id,
        amount: valAmt.toFixed(2),
        type: 'WITHDRAWAL',
        status: 'PENDING',
        payment_method: method || 'USDT TRC20',
        reference: 'TXW' + Math.floor(100000 + Math.random() * 900000),
        created_at: new Date().toISOString()
      };
      db.transactions.push(tx);
      res.json({ success: true, balance: sessionUser.balance });
    } else if (action === 'vip_purchase') {
      if (sessionUser.balance < 29.99) {
        res.json({ success: false, message: 'Insufficient balance' });
        return;
      }
      sessionUser.balance -= 29.99;
      sessionUser.isVip = 1;
      const tx = {
        id: db.transactions.length + 1,
        user_id: sessionUser.id,
        amount: '29.99',
        type: 'VIP_UPGRADE',
        status: 'COMPLETED',
        payment_method: 'Internal balance checkout',
        reference: 'TXV' + Math.floor(100000 + Math.random() * 900000),
        created_at: new Date().toISOString()
      };
      db.transactions.push(tx);
      res.json({ success: true, balance: sessionUser.balance });
    }
  });

  // 8. Mock Admin and events creation
  app.post("/api/admin.php", (req, res) => {
    const { action, sport, home_team, away_team, home_logo, away_logo, offset_hours, match_id, home_score, away_score, status } = req.body;
    
    if (action === 'create_match') {
      const g = {
        id: db.matches.length + 1,
        sport: sport || 'Football',
        home_team: home_team || 'Home Team',
        away_team: away_team || 'Away Team',
        home_logo: home_logo || '🔴',
        away_logo: away_logo || '🔵',
        match_time: new Date(Date.now() + parseInt(offset_hours || "2") * 3600000).toISOString(),
        status: 'SCHEDULED',
        home_score: 0,
        away_score: 0,
        live_timer: null,
        extra_stats: null
      };
      db.matches.push(g);
      res.json({ success: true, message: 'Event scheduled' });
    } else if (action === 'score_update') {
      const g = db.matches.find(m => m.id === parseInt(match_id));
      if (g) {
        g.home_score = parseInt(home_score);
        g.away_score = parseInt(away_score);
        g.status = 'LIVE';
        g.live_timer = "65'";
        g.extra_stats = '{"possession":[55,45],"shots_on_target":[7,3]}';
      }
      res.json({ success: true });
    } else if (action === 'settle_match') {
      const g = db.matches.find(m => m.id === parseInt(match_id));
      if (g) {
        g.status = 'COMPLETED';
        g.live_timer = 'FT';
      }
      db.predictions.filter(p => p.match_id === parseInt(match_id)).forEach(p => {
        p.status = Math.random() > 0.4 ? 'WON' : 'LOST';
      });
      res.json({ success: true });
    } else if (action === 'change_role') {
      const usr = db.users.find(u => u.id === parseInt(req.body.user_id));
      if (usr) {
        usr.role = req.body.role;
      }
      res.json({ success: true });
    }
  });

  // 9. Mock Predictor HQ
  app.post("/api/predictor.php", (req, res) => {
    const { action, match_id, title, description, price, is_vip, tips } = req.body;
    
    if (action === 'publish') {
      const tipsParsed = JSON.parse(tips || "[]");
      let totalOdds = 1.0;
      tipsParsed.forEach((t: any) => totalOdds *= parseFloat(t.odds || "1.0"));
      
      const draft = {
        id: db.predictions.length + 1,
        predictor_id: sessionUser.id,
        match_id: parseInt(match_id),
        title: title || 'Gold Combo Tips ticket',
        description: description || 'No reasoning',
        price: parseFloat(price).toFixed(2),
        tips_json: tips,
        total_odds: totalOdds.toFixed(2),
        confidence: Math.floor(75 + Math.random() * 20),
        is_vip: parseInt(is_vip),
        status: 'PENDING' as const,
        sales_count: 0,
        views: 1,
        created_at: new Date().toISOString()
      };
      db.predictions.push(draft);
      res.json({ success: true });
    } else if (action === 'withdraw') {
      const pay = sessionUser.predictor_earnings;
      sessionUser.balance += pay;
      sessionUser.predictor_earnings = 0;
      
      const tx = {
        id: db.transactions.length + 1,
        user_id: sessionUser.id,
        amount: pay.toFixed(2),
        type: 'EARNING' as const,
        status: 'COMPLETED' as const,
        payment_method: 'Wallet payouts transfer',
        reference: 'TXP' + Math.floor(100000 + Math.random() * 900000),
        created_at: new Date().toISOString()
      };
      db.transactions.push(tx);
      res.json({ success: true });
    }
  });

  // 10. Mock Cart payment
  app.post("/api/cart.php", (req, res) => {
    const ids = JSON.parse(req.body.prediction_ids || "[]");
    let totalCost = 0.00;
    
    const items = db.predictions.filter(p => ids.includes(p.id));
    items.forEach(it => totalCost += parseFloat(it.price));
    
    if (sessionUser.balance < totalCost) {
      res.json({ success: false, message: 'Insufficient balance' });
      return;
    }

    sessionUser.balance -= totalCost;
    items.forEach(it => {
      it.sales_count++;
      // Split reward: 80% to vendor John (predictor_john)
      const vend = db.users.find(u => u.id === it.predictor_id);
      if (vend) {
        vend.predictor_earnings += parseFloat(it.price) * 0.80;
      }
    });

    const tx = {
      id: db.transactions.length + 1,
      user_id: sessionUser.id,
      amount: totalCost.toFixed(2),
      type: 'PURCHASE' as const,
      status: 'COMPLETED' as const,
      payment_method: 'Internal Wallet Balance',
      reference: 'TXC' + Math.floor(100000 + Math.random() * 900000),
      created_at: new Date().toISOString()
    };
    db.transactions.push(tx);
    res.json({ success: true, balance: sessionUser.balance });
  });

  // Vite development-container assets delivery
  if (process.env.NODE_ENV !== "production") {
    const vite = await createViteServer({
      server: { middlewareMode: true },
      appType: "spa",
    });
    app.use(vite.middlewares);
  } else {
    const distPath = path.join(process.cwd(), 'dist');
    app.use(express.static(distPath));
    app.get('*', (req, res) => {
      res.sendFile(path.join(distPath, 'index.html'));
    });
  }

  app.listen(PORT, "0.0.0.0", () => {
    console.log(`🏆 BETELITE Development server actively streaming on port http://localhost:${PORT}`);
  });
}

startServer();

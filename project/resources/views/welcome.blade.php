@extends('layouts.marketing')

@section('title', 'Campus Coin')

@section('content')
<section class="hero" id="home">
    <div class="hero-glow hero-glow-one"></div>
    <div class="hero-glow hero-glow-two"></div>
    <div class="hero-mesh" aria-hidden="true"></div>
    <svg class="hero-chart-backdrop" viewBox="0 0 1440 760" preserveAspectRatio="none" aria-hidden="true">
        <defs>
            <linearGradient id="hero-chart-fill" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="var(--theme-accent-soft)" stop-opacity=".22"/><stop offset="1" stop-color="var(--theme-accent-soft)" stop-opacity="0"/></linearGradient>
            <filter id="hero-chart-glow" x="-20%" y="-80%" width="140%" height="260%"><feGaussianBlur stdDeviation="9"/></filter>
        </defs>
        <path class="hero-chart-area" d="M0 598 C110 542 135 580 224 510 S342 548 423 450 S544 495 628 392 S755 448 839 326 S965 378 1052 294 S1177 343 1260 228 S1372 269 1440 166 V760 H0Z"/>
        <path class="hero-chart-glow" d="M0 598 C110 542 135 580 224 510 S342 548 423 450 S544 495 628 392 S755 448 839 326 S965 378 1052 294 S1177 343 1260 228 S1372 269 1440 166"/>
        <path class="hero-chart-line" d="M0 598 C110 542 135 580 224 510 S342 548 423 450 S544 495 628 392 S755 448 839 326 S965 378 1052 294 S1177 343 1260 228 S1372 269 1440 166"/>
        <circle class="hero-chart-node" cx="423" cy="450" r="4"/><circle class="hero-chart-node" cx="839" cy="326" r="4"/><circle class="hero-chart-node" cx="1260" cy="228" r="4"/>
    </svg>
    <span class="hero-star"></span><span class="hero-star"></span><span class="hero-star"></span><span class="hero-star"></span>
    <span class="hero-float float-one">−$12.00</span>
    <span class="hero-float float-two">+ $350</span>
    <span class="hero-float float-three">☕ &nbsp; Food</span>
    <span class="hero-float float-four">+ $24.50</span>
    <span class="hero-corner hero-corner-left"></span>
    <span class="hero-corner hero-corner-right"></span>

    <div class="hero-socials"><span>Your money, your way</span><svg class="icon"><use href="#icon-spark"/></svg></div>
    <div class="hero-content">
        <span class="hero-chip"><span class="status-dot"></span> A little more clarity. A lot more freedom.</span>
        <h1 class="hero-title" aria-label="Make every coin count">
            <span class="hero-title-static">Make every</span>
            <span class="hero-title-rotating"><span class="hero-title-word is-current">coin count.</span><span class="hero-title-word">day count.</span><span class="hero-title-word">goal possible.</span></span>
        </h1>
        <p class="hero-copy">A simple, student-first way to track spending, plan a budget, and feel good about what comes next. No linked bank account needed.</p>
        <ul class="hero-perks">
            <li><svg class="icon"><use href="#icon-check"/></svg>Know where it goes</li>
            <li><svg class="icon"><use href="#icon-check"/></svg>Make a plan that fits</li>
            <li><svg class="icon"><use href="#icon-check"/></svg>Save for what matters</li>
        </ul>
        <div class="hero-actions">
            <a class="button button-light" href="{{ route('register') }}">Create your account <span class="button-spark"><svg class="icon"><use href="#icon-arrow"/></svg></span></a>
            <a class="button button-quiet" href="#how-it-works">Take a look</a>
        </div>
    </div>
    <a class="hero-scroll" href="#money-story">Scroll to explore <svg class="icon"><use href="#icon-arrow-down"/></svg></a>
</section>

<div class="home-scroll-progress" aria-hidden="true"></div>

<div class="ticker" aria-label="Example student transactions">
    <div class="ticker-track">
        <span class="ticker-item"><strong>Allowance</strong><span>+$350.00</span><span class="ticker-price">INCOME</span></span><span class="ticker-separator">✳</span>
        <span class="ticker-item"><strong>Campus café</strong><span>−$8.50</span><span class="ticker-soft">FOOD</span></span><span class="ticker-separator">✳</span>
        <span class="ticker-item"><strong>Bus pass</strong><span>−$24.00</span><span class="ticker-soft">TRANSPORT</span></span><span class="ticker-separator">✳</span>
        <span class="ticker-item"><strong>Textbooks</strong><span>−$42.75</span><span class="ticker-soft">ACADEMICS</span></span><span class="ticker-separator">✳</span>
        <span class="ticker-item"><strong>Part-time shift</strong><span>+$95.00</span><span class="ticker-price">INCOME</span></span><span class="ticker-separator">✳</span>
        <span class="ticker-item"><strong>Movie night</strong><span>−$16.00</span><span class="ticker-soft">FUN</span></span><span class="ticker-separator">✳</span>
        <span class="ticker-item"><strong>Allowance</strong><span>+$350.00</span><span class="ticker-price">INCOME</span></span><span class="ticker-separator">✳</span>
        <span class="ticker-item"><strong>Campus café</strong><span>−$8.50</span><span class="ticker-soft">FOOD</span></span><span class="ticker-separator">✳</span>
        <span class="ticker-item"><strong>Bus pass</strong><span>−$24.00</span><span class="ticker-soft">TRANSPORT</span></span><span class="ticker-separator">✳</span>
        <span class="ticker-item"><strong>Textbooks</strong><span>−$42.75</span><span class="ticker-soft">ACADEMICS</span></span><span class="ticker-separator">✳</span>
        <span class="ticker-item"><strong>Part-time shift</strong><span>+$95.00</span><span class="ticker-price">INCOME</span></span><span class="ticker-separator">✳</span>
        <span class="ticker-item"><strong>Movie night</strong><span>−$16.00</span><span class="ticker-soft">FUN</span></span>
    </div>
</div>

<section class="coin-story" id="money-story" aria-label="Your campus life, with money in mind">
    <div class="coin-story-stage">
        <div class="coin-story-copy">
            <span class="eyebrow">Life moves fast. Your money plan can keep up.</span>
            <p>Between class, friends, and everything in between, every little choice adds up.</p>
            <a class="button button-light" href="{{ $accountDestination }}">{{ auth()->check() && auth()->user()->isAdmin() ? 'Open admin overview' : 'Log in to your dashboard' }} <svg class="icon"><use href="#icon-arrow"/></svg></a>
        </div>
        <div class="coin-marquee" aria-hidden="true">
            <div class="coin-marquee-track">
                <span>Every coin counts.</span><span>Every coin counts.</span><span>Every coin counts.</span><span>Every coin counts.</span>
                <span>Every coin counts.</span><span>Every coin counts.</span><span>Every coin counts.</span><span>Every coin counts.</span>
            </div>
        </div>
        <svg class="coin-glass" viewBox="0 0 300 520" aria-hidden="true">
            <defs>
                <linearGradient id="glass-fill" x1="0" x2="1" y1="0" y2="1"><stop offset="0" stop-color="#F5FAFF" stop-opacity=".8"/><stop offset=".23" stop-color="#49A9E9" stop-opacity=".1"/><stop offset=".49" stop-color="#EAF6FF" stop-opacity=".72"/><stop offset=".71" stop-color="#40B1ED" stop-opacity=".16"/><stop offset="1" stop-color="#F3FAFF" stop-opacity=".58"/></linearGradient>
                <linearGradient id="glass-edge" x1="0" x2="1" y1="0" y2="0"><stop stop-color="#DAF1FF" stop-opacity=".85"/><stop offset=".47" stop-color="#63C5FF" stop-opacity=".18"/><stop offset="1" stop-color="#fff" stop-opacity=".78"/></linearGradient>
                <radialGradient id="helix-bead-fill" cx="32%" cy="25%" r="78%"><stop stop-color="#F3FAFF"/><stop offset=".22" stop-color="#B7E0FF"/><stop offset=".58" stop-color="#43A5E9"/><stop offset="1" stop-color="#084C75"/></radialGradient>
                <filter id="glass-blur"><feGaussianBlur stdDeviation="14"/></filter>
                <filter id="helix-bead-glow" x="-100%" y="-100%" width="300%" height="300%"><feGaussianBlur stdDeviation="5"/></filter>
            </defs>
            <ellipse cx="148" cy="263" rx="87" ry="205" fill="#0094E4" opacity=".13" filter="url(#glass-blur)"/>
            <path class="glass-ribbon glass-ribbon-back" d="M72 11C203 37 258 84 189 144c-29 25-93 45-85 86s102 48 121 91c28 63-58 93-160 188"/>
            <path class="glass-ribbon glass-ribbon-middle" d="M226 7C92 35 40 79 109 141c31 28 96 45 89 86s-104 49-122 94c-25 61 60 97 158 183"/>
            <path class="glass-ribbon glass-ribbon-front" d="M72 11C203 37 258 84 189 144c-29 25-93 45-85 86s102 48 121 91c28 63-58 93-160 188"/>
            <g class="helix-beads" aria-hidden="true">
                <g class="helix-bead" data-strand="front" data-offset=".2"><circle r="20" fill="#48A6E9" opacity=".65" filter="url(#helix-bead-glow)"/><circle r="16" fill="url(#helix-bead-fill)" stroke="#EAF6FF" stroke-opacity=".8" stroke-width="1.5"/><ellipse cx="-5" cy="-6" rx="4.2" ry="2.8" fill="#fff" opacity=".82"/></g>
                <g class="helix-bead" data-strand="middle" data-offset=".2"><circle r="20" fill="#48A6E9" opacity=".65" filter="url(#helix-bead-glow)"/><circle r="16" fill="url(#helix-bead-fill)" stroke="#EAF6FF" stroke-opacity=".8" stroke-width="1.5"/><ellipse cx="-5" cy="-6" rx="4.2" ry="2.8" fill="#fff" opacity=".82"/></g>
            </g>
        </svg>
        <span class="coin-story-star coin-star-one"></span><span class="coin-story-star coin-star-two"></span><span class="coin-story-star coin-star-three"></span>
    </div>
</section>

<section class="intro-section" id="about">
    <div class="section-wrap intro-grid">
        <div class="reveal">
            <p class="eyebrow">A fresh look at student money</p>
            <h2 class="intro-title">More in control.<br><span>Less money stress.</span></h2>
            <p class="intro-copy">Track your allowance, part-time income, and everyday expenses all in one place. See what's left for the month without linking your bank account.</p>
            <ul class="feature-checks">
                <li><svg class="icon"><use href="#icon-check"/></svg>Made for student life</li>
                <li><svg class="icon"><use href="#icon-check"/></svg>Log it as it happens</li>
                <li><svg class="icon"><use href="#icon-check"/></svg>See a clearer picture</li>
                <li><svg class="icon"><use href="#icon-check"/></svg>Always your choice</li>
            </ul>
        </div>
        <div class="intro-terminal reveal reveal-delay-2" aria-label="Campus Coin monthly budget dashboard preview">
            <div class="terminal-toolbar"><span class="brand-mark"><svg class="icon"><use href="#icon-wallet"/></svg></span><span>Campus Coin&nbsp; / &nbsp;Monthly overview</span><span class="terminal-symbol">SEPTEMBER &nbsp;⌄</span></div>
            <div class="terminal-main">
                <div class="terminal-chart">
                    <span class="chart-caption">Money left this month</span>
                    <svg viewBox="0 0 480 180" role="img" aria-label="Savings trend rising across six months">
                        <defs><linearGradient id="chart-fill" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="var(--theme-accent-soft)" stop-opacity=".25"/><stop offset="1" stop-color="var(--theme-accent-soft)" stop-opacity="0"/></linearGradient></defs>
                        <path class="chart-area" d="M0 150 45 127 78 139 113 96 146 114 182 88 211 105 249 56 279 75 313 45 348 67 382 34 416 53 480 12V180H0Z"/>
                        <path class="chart-line" d="M0 150 45 127 78 139 113 96 146 114 182 88 211 105 249 56 279 75 313 45 348 67 382 34 416 53 480 12"/>
                    </svg>
                    <div class="chart-months"><span>APR</span><span>MAY</span><span>JUN</span><span>JUL</span><span>AUG</span><span>SEP</span></div>
                </div>
                <div class="terminal-stats">
                    <div class="terminal-stat"><span>Left to spend</span><strong>$284.50</strong></div>
                    <div class="terminal-stat"><span>Income this month</span><strong class="positive">+$920.00</strong></div>
                    <div class="terminal-stat"><span>Saved so far</span><strong>$120.00</strong></div>
                </div>
            </div>
            <div class="terminal-bottom"><div class="terminal-pill"><span>Food</span><b>$48 / $150</b></div><div class="terminal-pill"><span>Transport</span><b>$24 / $60</b></div><div class="terminal-pill"><span>Study</span><b>$32 / $90</b></div></div>
        </div>
    </div>
</section>

<section class="how-section" id="how-it-works">
    <div class="section-wrap">
        <p class="eyebrow" style="text-align:center">One small step at a time</p>
        <h2 class="section-heading">A clearer picture of your <span>money.</span></h2>
        <p class="section-lede">Start simple. Check in whenever you like. Build a way of managing money that works for you.</p>
        <div class="steps-panel reveal" data-scroll-steps>
          <div class="steps-sticky">
            <div class="steps-visual" aria-live="polite" aria-atomic="true">
                <svg class="visual-wave" viewBox="0 0 1200 360" preserveAspectRatio="none" aria-hidden="true"><defs><linearGradient id="wave-fill" x1="0" x2="1" y1="0" y2="0"><stop offset="0" stop-color="#043551" stop-opacity=".05"/><stop offset=".5" stop-color="#126CA7" stop-opacity=".55"/><stop offset="1" stop-color="#062232" stop-opacity=".12"/></linearGradient></defs><path d="M0 112c142 50 147 113 284 69s211-106 350-40 159 143 289 112 151-102 277-45v152H0Z"/></svg>
                <div class="program-stack step-visual step-visual-1">
                    <div class="program-mini"><strong>Campus Coin</strong><span>Monthly overview</span><b>September</b></div>
                    <div class="program-mini"><strong>01 &nbsp;Log your money</strong><span>Income and everyday spending</span><a class="button button-quiet" href="{{ route('transactions.index') }}">Add a transaction</a></div>
                    <div class="program-mini"><strong>02 &nbsp;Make your plan</strong><span>Pick a budget that feels right</span><span class="ticker-price">Your budget, your way</span></div>
                    <div class="program-mini"><strong>03 &nbsp;See your progress</strong><span>Check in. Celebrate the wins.</span><span class="ticker-price">One month at a time</span></div>
                </div>
                <div class="steps-screenshot step-visual step-visual-2" aria-hidden="true">
                    <div class="mini-app-bar"><span>Campus Coin&nbsp; / &nbsp;My monthly budget</span><span>THIS MONTH&nbsp; ●</span></div>
                    <div class="mini-app-body"><div class="mini-balance"><span>Spending this month</span><strong>$635.50</strong><span>across 18 transactions</span><div class="mini-progress"><i style="width:68%"></i></div></div><div class="mini-balance mini-categories"><span>Food &nbsp; $148</span><span>Transport &nbsp; $52</span><span>Study &nbsp; $86</span><span>Fun &nbsp; $74</span></div></div>
                </div>
                <div class="steps-screenshot step-visual step-visual-3" aria-hidden="true">
                    <div class="mini-app-bar"><span>Campus Coin&nbsp; / &nbsp;Savings goal</span><span class="ticker-price">ON YOUR WAY ✓</span></div>
                    <div class="mini-app-body"><div class="mini-balance"><span>Saving for</span><strong>Spring break</strong><span>$420 saved toward $600</span><div class="mini-progress"><i style="width:70%"></i></div></div><div class="mini-balance mini-goal-ring"><div class="goal-ring"><span>70%</span></div><span>Little steps add up.</span></div></div>
                </div>
            </div>
            <div class="step-tabs" role="list" aria-label="How Campus Coin works">
                <div class="step-tab" role="listitem" aria-current="step" data-step="1"><span class="step-number">[ Step 1 ]</span><strong>Log what comes in and goes out</strong><p>Add your allowance, earnings, coffee, books, and everything else that matters.</p></div>
                <div class="step-tab" role="listitem" data-step="2"><span class="step-number">[ Step 2 ]</span><strong>Set a monthly budget</strong><p>Choose a comfortable limit for each category and adjust it whenever life changes.</p></div>
                <div class="step-tab" role="listitem" data-step="3"><span class="step-number">[ Step 3 ]</span><strong>Make progress on your goals</strong><p>Keep an eye on your habits and build savings at a pace that feels right.</p></div>
            </div>
          </div>
        </div>
    </div>
</section>

<section class="programs-section" id="budgets">
    <div class="section-wrap">
        <p class="eyebrow" style="text-align:center">A little structure. Lots of flexibility.</p>
        <h2 class="section-heading">A budget built around <span>your campus life.</span></h2>
        <p class="section-lede">Start with a few ideas for everyday categories, then make the numbers your own. These are sample monthly amounts, not rules.</p>
        <div class="program-tabs" role="tablist" aria-label="Focus on a budget category">
            <button class="program-tab" role="tab" aria-selected="true" type="button" data-program="Food">Food</button>
            <button class="program-tab" role="tab" aria-selected="false" type="button" data-program="Transport">Transport</button>
            <button class="program-tab" role="tab" aria-selected="false" type="button" data-program="Study">Study</button>
            <button class="program-tab" role="tab" aria-selected="false" type="button" data-program="Fun">Fun</button>
            <button class="program-tab" role="tab" aria-selected="false" type="button" data-program="Savings">Savings</button>
        </div>
        <div class="program-table-wrap reveal">
            <table class="program-table">
                <thead><tr><th>Sample monthly plan</th><th class="selected-program"><small>Food</small><strong>$150</strong></th><th><small>Transport</small><strong>$60</strong></th><th><small>Study</small><strong>$90</strong></th><th><small>Fun</small><strong>$100</strong></th><th><small>Savings</small><strong>$50</strong></th></tr></thead>
                <tbody>
                    <tr><td>Planned amount</td><td>$150</td><td>$60</td><td>$90</td><td>$100</td><td>$50</td></tr>
                    <tr><td>Example spent</td><td>$88</td><td>$24</td><td>$32</td><td>$46</td><td>$30</td></tr>
                    <tr><td>Still available</td><td>$62</td><td>$36</td><td>$58</td><td>$54</td><td>$20</td></tr>
                    <tr><td>Friendly reminder</td><td>At 80%</td><td>At 80%</td><td>At 80%</td><td>At 80%</td><td>At 80%</td></tr>
                    <tr><td>Track your habits</td><td>Meals & snacks</td><td>Bus & rides</td><td>Books & supplies</td><td>Friends & fun</td><td>Future you</td></tr>
                    <tr><td>Monthly plan</td><td>Change any time</td><td>Change any time</td><td>Change any time</td><td>Change any time</td><td>Change any time</td></tr>
                    <tr><td>Bank connection</td><td>No link needed</td><td>No link needed</td><td>No link needed</td><td>No link needed</td><td>No link needed</td></tr>
                    <tr><td>It's your money</td><td class="value-positive">Your call</td><td class="value-positive">Your call</td><td class="value-positive">Your call</td><td class="value-positive">Your call</td><td class="value-positive">Your call</td></tr>
                </tbody>
            </table>
        </div>
        <p class="program-footnote">Illustrative examples only. Set your own amounts to match your needs and circumstances.</p>
        <div class="program-spotlight reveal" id="goals">
            <div><p class="eyebrow">Give yourself something to look forward to</p><h3>A goal, one coin at a time.</h3><p>Save for new headphones, next semester's books, or a well-deserved weekend away. Pick a target that matters to you and follow your progress.</p><a class="button button-quiet" href="{{ route('student.dashboard') }}">Explore your dashboard <svg class="icon"><use href="#icon-arrow"/></svg></a></div>
            <div class="spotlight-graphic" aria-label="Savings goal illustration"><div class="goal-preview"><div class="goal-preview-icon"><svg class="icon"><use href="#icon-spark"/></svg></div><span>New laptop</span><strong>$420 <small>/ $800</small></strong><div class="mini-progress"><i style="width:52%"></i></div><small>52% there</small></div></div>
        </div>
    </div>
</section>

<section class="countdown-section">
    <div class="countdown-inner reveal">
        <span class="tiny-tag">A fresh start, every month</span>
        <p class="countdown-label">The new month begins in</p>
        <div class="countdown-clock" data-countdown>
            <div class="countdown-unit"><strong data-days>00</strong><span>Days</span></div><span class="countdown-colon">:</span>
            <div class="countdown-unit"><strong data-hours>00</strong><span>Hours</span></div><span class="countdown-colon">:</span>
            <div class="countdown-unit"><strong data-minutes>00</strong><span>Minutes</span></div><span class="countdown-colon">:</span>
            <div class="countdown-unit"><strong data-seconds>00</strong><span>Seconds</span></div>
        </div>
        <p class="countdown-note">Every month is another chance to reset your budget and start fresh.</p>
        <div class="countdown-actions"><a class="button button-light" href="{{ route('register') }}">Start your plan <svg class="icon"><use href="#icon-arrow"/></svg></a><a class="button button-quiet" href="{{ $accountDestination }}">{{ $accountDestinationLabel }}</a></div>
    </div>
</section>

<section class="stats-section" id="features">
    <div class="section-wrap">
        <p class="eyebrow" style="text-align:center">Your whole money picture</p>
        <h2 class="section-heading">The little details add up to <span>a lot more clarity.</span></h2>
        <p class="section-lede">Keep the parts of student spending that matter most in one calm, easy-to-understand place.</p>
        <div class="stats-grid reveal">
            <article class="stat-card"><strong>01<span></span></strong><p>A simple monthly snapshot of your income, spending, and balance</p></article>
            <article class="stat-card"><strong>06<span></span></strong><p>Months of trends to help you see the bigger picture</p></article>
            <article class="stat-card"><strong>80<span>%</span></strong><p>Choose when to get a gentle budget heads-up</p></article>
            <article class="stat-card"><strong>0<span></span></strong><p>Bank links needed. You stay in control of your entries.</p></article>
        </div>
    </div>
</section>

<section class="story-section" id="story">
    <div class="section-wrap story-grid">
        <div class="story-copy reveal">
            <p class="eyebrow">[ The student money story ]</p>
            <h2>From little everyday choices to <span class="gradient-text">a little more breathing room.</span></h2>
            <div class="story-timeline">
                <article class="story-item is-current"><strong>Week 1</strong> Get everything down in one place.<p>Add your allowance, shifts, groceries, transit, and all the coffee runs.</p></article>
                <article class="story-item"><strong>Week 2</strong> Spot the patterns in the everyday.<p>See which categories are taking more of your budget than you expected.</p></article>
                <article class="story-item"><strong>Month 1</strong> Make room for the things that matter.<p>Use your own spending picture to set a monthly plan that actually feels realistic.</p></article>
            </div>
            <a class="button button-quiet" href="{{ route('reports.index') }}" style="margin-top:19px">See your spending clearly <svg class="icon"><use href="#icon-arrow"/></svg></a>
        </div>
        <div class="story-chart" aria-label="Illustration of small savings growing over six months">
            <span class="story-label">Little by little</span>
            <div class="story-chart-grid"></div>
            <svg class="story-line" viewBox="0 0 600 330" preserveAspectRatio="none" aria-hidden="true">
                <defs>
                    <linearGradient id="story-line-gradient" x1="0" x2="1" y1="1" y2="0"><stop offset="0" stop-color="var(--theme-accent)"/><stop offset=".58" stop-color="var(--theme-accent-soft)"/><stop offset="1" stop-color="var(--theme-text)"/></linearGradient>
                    <linearGradient id="story-area-gradient" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="var(--theme-accent-soft)" stop-opacity=".3"/><stop offset="1" stop-color="var(--theme-accent-soft)" stop-opacity="0"/></linearGradient>
                </defs>
                <path class="story-line-area" d="M12 260 C55 245 57 183 114 197 S182 214 226 145 S292 165 334 119 S405 144 448 62 S516 91 588 20 V330 H12Z"/>
                <path class="story-line-glow" pathLength="1" d="M12 260 C55 245 57 183 114 197 S182 214 226 145 S292 165 334 119 S405 144 448 62 S516 91 588 20"/>
                <path class="story-line-core" pathLength="1" d="M12 260 C55 245 57 183 114 197 S182 214 226 145 S292 165 334 119 S405 144 448 62 S516 91 588 20"/>
                <circle class="story-line-tracer" r="6" cx="12" cy="260"/>
            </svg>
            <i class="story-point"></i><i class="story-point"></i><i class="story-point"></i><i class="story-point"></i>
            <div class="story-year"><span>Month 1</span><span>Month 2</span><span>Month 3</span><span>Month 6</span></div>
        </div>
    </div>
</section>

<section class="payout-section" id="saving-goal">
    <div class="section-wrap">
        <p class="eyebrow">Small steps. Big excitement.</p>
        <div class="payout-panel reveal">
            <span class="payout-watermark" aria-hidden="true">Your goal</span>
            <div class="payout-card">
                <div class="payout-top"><span>CAMPUS COIN&nbsp; / &nbsp;SAVING GOAL</span><svg class="icon"><use href="#icon-spark"/></svg></div>
                <h3>A little trip after finals</h3>
                <p class="payout-amount">$420 <span>of $600</span></p>
                <div class="mini-progress"><i style="width:70%"></i></div>
                <span class="payout-seal"><svg class="icon"><use href="#icon-check"/></svg> You're getting there</span>
            </div>
        </div>
        <p>Make a goal that feels like you. Track your progress, cheer yourself on, and save at a pace that works for your life.</p>
    </div>
</section>

<section class="benefits-section" id="insights">
    <div class="section-wrap">
        <p class="eyebrow" style="text-align:center">Made for real student budgets</p>
        <h2 class="section-heading">A little more confidence with <span>every coin.</span></h2>
        <div class="benefits-grid">
            <article class="benefit-card reveal"><div class="benefit-art"><svg class="icon"><use href="#icon-chart"/></svg></div><h3>A snapshot that makes sense</h3><p>See what came in, what went out, and what's left this month. Simple numbers, all in one place.</p></article>
            <article class="benefit-card reveal reveal-delay-1"><div class="benefit-art"><svg class="icon"><use href="#icon-wallet"/></svg></div><h3>Categories that fit campus life</h3><p>Sort the things you actually spend on: food, transit, textbooks, rent, subscriptions, and fun.</p></article>
            <article class="benefit-card reveal reveal-delay-2"><div class="benefit-art"><svg class="icon"><use href="#icon-bolt"/></svg></div><h3>Gentle budget reminders</h3><p>Know when you’re getting close to your category limit, without judgment or money jargon.</p></article>
            <article class="benefit-card reveal reveal-delay-3"><div class="benefit-art"><svg class="icon"><use href="#icon-spark"/></svg></div><h3>Ideas for a little extra breathing room</h3><p>Get practical saving tips, tailored to student spending habits and the choices you've recorded.</p></article>
        </div>
    </div>
</section>

<section class="platform-section">
    <div class="section-wrap">
        <p class="eyebrow" style="text-align:center">Everything in its place</p>
        <h2 class="section-heading">Your money, at a <span>glance.</span></h2>
        <p class="section-lede">A friendly overview for daily check-ins and end-of-month planning alike.</p>
        <div class="platform-card reveal">
            <div class="platform-window">
                <div class="platform-nav"><span class="brand-mark"><svg class="icon"><use href="#icon-wallet"/></svg></span><span>Overview</span><span>Activity</span><span>Budgets</span><span>Reports</span><span>◉</span></div>
                <div class="platform-dashboard">
                    <div class="platform-chart-box">
                        <div class="platform-chart-heading"><span>This month<br><strong>$284.50 <small>left to spend</small></strong></span><span>SEPTEMBER &nbsp;⌄</span></div>
                        <div class="candle-chart budget-bars"><div class="budget-bar-row"><span>Food</span><i><b style="width:66%"></b></i><strong>$88 / $150</strong></div><div class="budget-bar-row"><span>Transport</span><i><b style="width:40%"></b></i><strong>$24 / $60</strong></div><div class="budget-bar-row"><span>Study</span><i><b style="width:36%"></b></i><strong>$32 / $90</strong></div><div class="budget-bar-row"><span>Fun</span><i><b style="width:46%"></b></i><strong>$46 / $100</strong></div></div>
                    </div>
                    <div class="platform-side"><div class="platform-side-box">Money in<strong class="positive">$920.00</strong></div><div class="platform-side-box">Money out<strong>$635.50</strong></div><div class="platform-side-box">Saved this month<strong class="positive">$120.00</strong></div></div>
                </div>
                <div class="platform-widgets"><div class="platform-footer-box"><strong>Latest activity</strong>Campus café &nbsp; · &nbsp; Today</div><div class="platform-footer-box"><strong>Study supplies</strong>Budget on track&nbsp; ●</div><div class="platform-footer-box"><strong>Next goal</strong>Spring break&nbsp; · &nbsp;70%</div></div>
            </div>
            <div class="platform-cta"><a class="button button-quiet" href="{{ $accountDestination }}">{{ auth()->check() && auth()->user()->isAdmin() ? 'Open admin overview' : 'Log in to explore your dashboard' }} <svg class="icon"><use href="#icon-arrow"/></svg></a></div>
        </div>
    </div>
</section>

<section class="faq-section" id="faq">
    <div class="section-wrap faq-layout">
        <div class="faq-intro">
            <p class="eyebrow">Good questions</p>
            <h2>Money stuff, made a little clearer.</h2>
            <p>Everything you need to know to get started with Campus Coin.</p>
        </div>
        <div>
            <div class="faq-list">
                <details><summary>Do I need to connect my bank account?</summary><div class="faq-answer">No. You enter your income and spending yourself; Campus Coin doesn't need to connect to your bank to give you a clear money overview.</div></details>
                <details><summary>What can I add as income?</summary><div class="faq-answer">Add any money you want to keep track of, such as an allowance, part-time pay, a scholarship, or a gift.</div></details>
                <details><summary>Can I edit my budget when my plans change?</summary><div class="faq-answer">Yes. You can update your category budgets as your semester, income, or expenses change.</div></details>
                <details><summary>How do I know when I'm close to a category limit?</summary><div class="faq-answer">Budgets can show a reminder when spending reaches 80% of your limit and flag when you reach or exceed it.</div></details>
                <details><summary>Can I track savings goals?</summary><div class="faq-answer">You can record a savings goal and check your progress alongside your income, expenses, and monthly budget.</div></details>
                <details><summary>Can I see how my spending changes over time?</summary><div class="faq-answer">Reports include category breakdowns and income-versus-expense trends, including a six-month view.</div></details>
                <details><summary>Can I organize expenses like books, transit, and meals?</summary><div class="faq-answer">Yes. Start with the student-focused categories, then add your own categories to better match how you spend.</div></details>
                <details><summary>Can I change my plan as the semester changes?</summary><div class="faq-answer">Yes. Update your monthly budget, spending categories, and savings targets any time your schedule or priorities shift.</div></details>
                <details><summary>How do I get started?</summary><div class="faq-answer">Create an account, add a few income and expense entries, and set a first monthly budget. You can adjust your plan whenever you need to.</div></details>
            </div>
            <div class="faq-more"><a class="button button-quiet" href="{{ route('register') }}">Create your free account <svg class="icon"><use href="#icon-arrow"/></svg></a></div>
        </div>
    </div>
</section>

<section class="community-section" id="community">
    <div class="section-wrap community-panel reveal">
        <div class="community-word" aria-hidden="true">You’ve got this.</div>
        <div class="community-content">
            <p class="eyebrow">Your next chapter starts here</p>
            <h2>A little less guessing.<br>A lot more confidence.</h2>
            <p>Start with what you have. Build a clearer picture of your spending and find a way forward that works for your student life.</p>
            <a class="button button-light" href="{{ route('register') }}">Make every coin count <svg class="icon"><use href="#icon-arrow"/></svg></a>
        </div>
    </div>
</section>

<section class="blog-section" id="journal">
    <div class="section-wrap">
        <div class="blog-heading">
            <div><p class="eyebrow">Friendly ideas for student life</p><h2>Little money notes</h2><p>Simple ways to make your student budget work harder.</p></div>
        </div>
        <div class="blog-track" id="blog-track">
            <article class="blog-card"><div class="blog-art blog-art-phone"><div class="blog-phone"><div class="phone-line"></div><div class="phone-line"></div><div class="phone-line"></div><div class="phone-line"></div></div></div><div class="blog-card-content"><h3>A simple first budget for student life</h3><p>Start with income you can count on, regular costs, and the things you want to make room for.</p><a class="button button-quiet" href="{{ route('budgets.index') }}">Plan a budget <svg class="icon"><use href="#icon-arrow"/></svg></a></div></article>
            <article class="blog-card"><div class="blog-art blog-art-chart"><div class="blog-chart-line"></div></div><div class="blog-card-content"><h3>Where does your weekly allowance go?</h3><p>A low-pressure way to check spending patterns without judging every cup of coffee.</p><a class="button button-quiet" href="{{ route('transactions.index') }}">Track spending <svg class="icon"><use href="#icon-arrow"/></svg></a></div></article>
            <article class="blog-card"><div class="blog-art blog-art-person"><div class="blog-person"></div></div><div class="blog-card-content"><h3>Make textbook costs easier to plan for</h3><p>A little advance planning can make it easier to set aside money for course materials.</p><a class="button button-quiet" href="{{ route('saving-tips.index') }}">Find a saving tip <svg class="icon"><use href="#icon-arrow"/></svg></a></div></article>
            <article class="blog-card"><div class="blog-art blog-art-neon"><div class="blog-goal-art"><svg class="icon"><use href="#icon-spark"/></svg></div></div><div class="blog-card-content"><h3>Start a small savings goal that sticks</h3><p>Pick something that matters to you, choose a doable target, and celebrate each step.</p><a class="button button-quiet" href="{{ route('student.dashboard') }}">See your progress <svg class="icon"><use href="#icon-arrow"/></svg></a></div></article>
        </div>
    </div>
</section>
@endsection

<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WorxNet — Find Trusted Tech &amp; Security Specialists Near You</title>
    <meta name="description" content="WorxNet connects South African homes and businesses with verified local specialists in CCTV, alarms, access control, smart home automation, Wi-Fi &amp; networking, and audio installation.">
    <meta name="theme-color" content="#1424a3">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

<a class="skip-link" href="#main">Skip to content</a>

<!-- ============ HEADER ============ -->
<header class="site-header" id="top">
    <div class="wrap header-wrap">
        <a href="#top" class="brand">
            <span class="brand-text">Worx<em>Net</em></span>
        </a>

        <nav class="main-nav" id="mainNav" aria-label="Primary">
            <a href="#find">Find a Specialist</a>
            <a href="#categories">Services</a>
            <a href="#projects">Post a Project</a>
            <a href="#how">How it Works</a>
            <a href="#pros">For Providers</a>
        </nav>

        <div class="header-actions">
            @include('layouts.partials.header-actions', ['joinRoute' => route('register'), 'joinLabel' => 'Join as a Pro'])
            @if (auth()->check() && auth()->user()->isProvider())
                <a href="#projects" class="btn btn-primary">Find Projects</a>
            @else
                <a href="#find" class="btn btn-primary">Find Help Now</a>
            @endif
            <button class="nav-toggle" id="navToggle" aria-expanded="false" aria-controls="mainNav" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

<main id="main">

    <!-- ============ HERO / DISCOVERY ============ -->
    <section class="hero" id="find">
        <div class="hero-bg" aria-hidden="true"></div>
        <div class="wrap hero-wrap">
            <div class="hero-copy">
                <span class="eyebrow">South Africa's tech &amp; security marketplace</span>
                <h1>Get matched with <span>verified local specialists</span> in minutes</h1>
                <p class="lede">
                    CCTV, alarms, access control, NDI &amp; AV systems, smart home automation, Wi-Fi &amp; connectivity,
                    IP network infrastructure, and audio installation — for homes and businesses across South Africa.
                </p>

                <ul class="hero-points">
                    <li>Nearby, available, verified professionals</li>
                    <li>Compare quotes before you commit — pay Cash on Delivery</li>
                    <li>Your location is only shared with your consent</li>
                </ul>
            </div>

            <div class="finder-card" aria-labelledby="finderTitle">
                <h2 id="finderTitle">Find a specialist near you</h2>
                <p class="finder-sub">Tell us what you need — we'll show best-matched local professionals.</p>

                <form id="finderForm" novalidate>
                    <div class="field">
                        <label for="serviceSelect">Service needed</label>
                        <select id="serviceSelect" required>
                            <option value="">Select a service…</option>
                            <option value="cctv">CCTV &amp; Surveillance</option>
                            <option value="alarms">Alarm Systems</option>
                            <option value="access">Access Control</option>
                            <option value="ndi">NDI / AV &amp; Video Systems</option>
                            <option value="smarthome">Smart Home Automation</option>
                            <option value="wifi">Wi-Fi &amp; Internet Connectivity</option>
                            <option value="network">Computer &amp; IP Network Infrastructure</option>
                            <option value="audio">Audio &amp; Sound Technology</option>
                        </select>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label for="propertySelect">Property type</label>
                            <select id="propertySelect">
                                <option value="home">Home / Residential</option>
                                <option value="complex">Apartment / Complex</option>
                                <option value="business">Small Business</option>
                                <option value="office">Office / Corporate</option>
                                <option value="retail">Retail / Hospitality</option>
                                <option value="estate">Estate / Industrial / Farm</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="urgencySelect">Urgency</label>
                            <select id="urgencySelect">
                                <option value="emergency">Emergency (ASAP)</option>
                                <option value="24h" selected>Within 24 hours</option>
                                <option value="week">This week</option>
                                <option value="planned">Planned installation</option>
                            </select>
                        </div>
                    </div>

                    <div class="field">
                        <label for="locationInput">Your location</label>
                        <div class="location-row">
                            <input type="text" id="locationInput" placeholder="e.g. Sandton, Gauteng" autocomplete="off">
                            <button type="button" class="btn btn-locate" id="useLocationBtn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2v3M12 19v3M2 12h3M19 12h3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="2"/></svg>
                                Use my location
                            </button>
                        </div>
                        <p class="field-hint" id="locationStatus">We only use your location to match you with nearby specialists — with your permission.</p>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Search Specialists</button>
                </form>
            </div>
        </div>
    </section>

    <!-- ============ RESULTS ============ -->
    <section class="results-section" id="results">
        <div class="wrap">
            <div class="results-head">
                <div>
                    <h2 id="resultsTitle">Available specialists near you</h2>
                    <p id="resultsSub" class="muted">Select a service above and search to see live-ranked matches.</p>
                </div>
                <div class="results-filters" id="resultsFilters" hidden>
                    <label class="sr-only" for="sortSelect">Sort by</label>
                    <select id="sortSelect">
                        <option value="match">Best match</option>
                        <option value="distance">Closest first</option>
                        <option value="rating">Highest rated</option>
                        <option value="response">Fastest response</option>
                    </select>
                </div>
            </div>

            <div class="results-layout">
                <div class="provider-list" id="providerList"></div>
                <aside class="coverage-panel" id="coveragePanel" aria-label="Coverage overview">
                    <div class="coverage-card">
                        <div class="coverage-head">
                            <span>Live coverage</span>
                            <span class="coverage-live" id="coverageLiveStatus" data-state="connecting">
                                <span class="coverage-live-dot"></span> Connecting…
                            </span>
                        </div>
                        <div class="coverage-map" id="coverageMap" aria-hidden="true">
                            <div class="coverage-ring r1"></div>
                            <div class="coverage-ring r2"></div>
                            <div class="coverage-ring r3"></div>
                            <div class="coverage-pin you">You</div>
                            <div id="coveragePins"></div>
                        </div>
                        <p class="coverage-note">Live provider distances, availability, and en-route progress — exact addresses are only shared once a booking is confirmed.</p>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <!-- ============ ENQUIRY MODAL ============ -->
    <div class="modal-overlay" id="enquiryOverlay" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="enquiryTitle">
            <button class="modal-close" id="enquiryClose" aria-label="Close">&times;</button>
            <div id="enquiryFormView">
                <h3 id="enquiryTitle">Request help</h3>
                <p class="muted" id="enquirySummary"></p>
                <form id="enquiryForm">
                    <div class="field">
                        <label for="enquiryName">Your name</label>
                        <input type="text" id="enquiryName" required>
                    </div>
                    <div class="field">
                        <label for="enquiryContact">Phone or email</label>
                        <input type="text" id="enquiryContact" required>
                    </div>
                    <div class="field">
                        <label for="enquiryNotes">Additional details</label>
                        <textarea id="enquiryNotes" rows="3" placeholder="Describe the issue or project…"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Send Request</button>
                    <p class="field-hint">Your request is sent securely to the provider, who will confirm availability and respond with a quote.</p>
                </form>
            </div>
            <div id="enquirySuccessView" hidden>
                <div class="success-icon" aria-hidden="true">✓</div>
                <h3>Request prepared</h3>
                <p class="muted">We\u2019ve received your request \u2014 <strong id="enquiryProviderName"></strong> will now be notified and respond with availability and a quote.</p>
                <button class="btn btn-primary btn-block" id="enquiryDoneBtn">Done</button>
            </div>
        </div>
    </div>

    <!-- ============ CATEGORIES ============ -->
    <section class="section" id="categories">
        <div class="wrap">
            <div class="section-head">
                <span class="eyebrow">What we cover</span>
                <h2>Every technology system, one platform</h2>
                <p class="muted">From a single camera install to full network infrastructure — WorxNet covers it.</p>
            </div>
            <div class="category-grid">
                <div class="category-card"><span class="cat-icon">📹</span><h3>CCTV &amp; Surveillance</h3><p>Cameras, DVR/NVR, remote viewing, night vision, monitoring setup.</p></div>
                <div class="category-card"><span class="cat-icon">🚨</span><h3>Alarm Systems</h3><p>Intruder alarms, electric fencing, beams, armed-response integration.</p></div>
                <div class="category-card"><span class="cat-icon">🔐</span><h3>Access Control</h3><p>Biometrics, intercoms, gate motors, visitor management, keypads.</p></div>
                <div class="category-card"><span class="cat-icon">🎛️</span><h3>NDI &amp; AV Systems</h3><p>Video-over-IP, streaming, conferencing, broadcast &amp; production setups.</p></div>
                <div class="category-card"><span class="cat-icon">🏠</span><h3>Smart Home Automation</h3><p>Lighting, climate, blinds, voice control, integrated home systems.</p></div>
                <div class="category-card"><span class="cat-icon">📶</span><h3>Wi-Fi &amp; Connectivity</h3><p>Fibre readiness, mesh Wi-Fi, wireless links, dead-zone elimination.</p></div>
                <div class="category-card"><span class="cat-icon">🖧</span><h3>IP Network Infrastructure</h3><p>Structured cabling, switching, routing, firewalls, server rooms.</p></div>
                <div class="category-card"><span class="cat-icon">🔊</span><h3>Audio &amp; Sound Technology</h3><p>Home theatre, multi-room audio, commercial PA, acoustic tuning.</p></div>
            </div>
        </div>
    </section>

    <!-- ============ PROJECTS / BIDDING ============ -->
    <section class="section section-alt" id="projects">
        <div class="wrap">
            <div class="section-head">
                <span class="eyebrow">Bigger job? Post a project</span>
                <h2>List your project, set a budget, receive bids</h2>
                <p class="muted">Describe your project in detail, define your budget, and let verified providers bid — you see every bid immediately.</p>
            </div>

            <div class="projects-layout">
                <form class="project-form" id="projectForm">
                    <div class="field">
                        <label for="projTitle">Project title</label>
                        <input type="text" id="projTitle" placeholder="e.g. Full CCTV install for 4-bedroom house" required>
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label for="projCategory">Service category</label>
                            <select id="projCategory" required>
                                <option value="">Select…</option>
                                <option value="cctv">CCTV &amp; Surveillance</option>
                                <option value="alarms">Alarm Systems</option>
                                <option value="access">Access Control</option>
                                <option value="ndi">NDI / AV Systems</option>
                                <option value="smarthome">Smart Home Automation</option>
                                <option value="wifi">Wi-Fi &amp; Connectivity</option>
                                <option value="network">IP Network Infrastructure</option>
                                <option value="audio">Audio &amp; Sound Technology</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="projLocation">Area / suburb</label>
                            <input type="text" id="projLocation" placeholder="e.g. Umhlanga, KZN" required>
                        </div>
                    </div>
                    <div class="field">
                        <label for="projDescription">Project details</label>
                        <textarea id="projDescription" rows="4" placeholder="Describe scope, equipment, number of points/cameras/rooms, timeline, and any requirements…" required></textarea>
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label for="projBudgetType">Budget</label>
                            <select id="projBudgetType">
                                <option value="range">Set a budget range</option>
                                <option value="fixed">Fixed budget</option>
                                <option value="quote">Request quotations (no budget shown)</option>
                            </select>
                        </div>
                        <div class="field" id="projBudgetFields">
                            <label for="projBudgetMin">Budget range (R)</label>
                            <div class="budget-inputs">
                                <input type="number" id="projBudgetMin" placeholder="Min" min="0">
                                <span>–</span>
                                <input type="number" id="projBudgetMax" placeholder="Max" min="0">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Post Project</button>
                    <p class="field-hint">Your project is posted live — verified providers can see it and place bids.</p>
                </form>

                <div class="project-list" id="projectList" aria-label="Posted projects and bids">
                    <div class="empty-state">Loading live projects…</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ HOW IT WORKS ============ -->
    <section class="section" id="how">
        <div class="wrap">
            <div class="section-head">
                <span class="eyebrow">Simple, transparent process</span>
                <h2>How WorxNet works</h2>
            </div>
            <div class="steps-grid">
                <div class="step"><span class="step-num">1</span><h3>Choose your service</h3><p>Tell us what you need — from a quick repair to a full installation project.</p></div>
                <div class="step"><span class="step-num">2</span><h3>Share your location</h3><p>Use live location or enter your area — always with your explicit permission.</p></div>
                <div class="step"><span class="step-num">3</span><h3>Compare verified pros</h3><p>Review ratings, coverage, availability, and bids from local specialists.</p></div>
                <div class="step"><span class="step-num">4</span><h3>Book &amp; pay COD</h3><p>Confirm your specialist and pay directly on completion — Cash on Delivery.</p></div>
            </div>
        </div>
    </section>

    <!-- ============ TRUST ============ -->
    <section class="section section-alt trust-section">
        <div class="wrap trust-wrap">
            <div class="section-head left">
                <span class="eyebrow">Trust &amp; safety</span>
                <h2>Verified professionals. Transparent matching.</h2>
                <p class="muted">Every provider on WorxNet is reviewed before appearing in your results.</p>
            </div>
            <ul class="trust-grid">
                <li><strong>Identity &amp; business verified</strong><span>Registered businesses and confirmed identities only.</span></li>
                <li><strong>Security-compliance checked</strong><span>Relevant credentials reviewed for regulated security work.</span></li>
                <li><strong>Location privacy first</strong><span>Exact addresses only shared once a booking is confirmed.</span></li>
                <li><strong>Real reviews only</strong><span>Ratings unlock only after a completed job.</span></li>
            </ul>
        </div>
    </section>

    <!-- ============ PROMOTIONS STRIP ============ -->
    <section class="promo-strip">
        <div class="wrap promo-wrap">
            <div>
                <span class="eyebrow light">Featured this month</span>
                <h2>Fibre-ready Wi-Fi upgrades — sponsored specialists in your area</h2>
            </div>
            <a href="#find" class="btn btn-invert">View Featured Providers</a>
        </div>
    </section>

    <!-- ============ FOR PROVIDERS ============ -->
    <section class="section pros-section" id="pros">
        <div class="wrap pros-wrap">
            <div>
                <span class="eyebrow">For technicians &amp; installation companies</span>
                <h2>Grow your business with WorxNet</h2>
                <p class="muted">Get matched with local leads, bid on projects, and build your reputation with verified reviews.</p>
                <ul class="pros-list">
                    <li>Receive relevant leads and project invitations in your service area</li>
                    <li>Bid on posted projects with transparent, itemised quotes</li>
                    <li>Get paid directly by customers — Cash on Delivery, no platform payment holding</li>
                    <li>Build trust with verified badges and genuine customer reviews</li>
                </ul>
                <a href="{{ route('register') }}" class="btn btn-primary">Apply as a Provider</a>
            </div>
            <div class="pros-card">
                <h3>Provider snapshot</h3>
                <dl>
                    <div><dt>Verified providers</dt><dd>Nationwide, growing weekly</dd></div>
                    <div><dt>Categories covered</dt><dd>8 core technology disciplines</dd></div>
                    <div><dt>Payment model</dt><dd>Cash on Delivery — direct to you</dd></div>
                </dl>
            </div>
        </div>
    </section>

</main>

<!-- ============ FOOTER ============ -->
<footer class="site-footer">
    <div class="wrap footer-wrap">
        <div class="footer-brand">
            <div class="footer-brand-row">
                <span class="brand-text">Worx<em>Net</em></span>
            </div>
            <p class="muted">South Africa's marketplace for tech &amp; security specialists.</p>
        </div>
        <div class="footer-col">
            <h4>Platform</h4>
            <a href="#find">Find a Specialist</a>
            <a href="#categories">Services</a>
            <a href="#projects">Post a Project</a>
            <a href="#pros">For Providers</a>
        </div>
        <div class="footer-col">
            <h4>Company</h4>
            <a href="#">About WorxNet</a>
            <a href="#">Contact Support</a>
            <a href="#">Safety Centre</a>
        </div>
        <div class="footer-col">
            <h4>Legal</h4>
            <a href="#">Terms of Service</a>
            <a href="#">Privacy Policy (POPIA)</a>
            <a href="#">Provider Agreement</a>
        </div>
    </div>
    <div class="wrap footer-bottom">
        <p>&copy; <span id="year"></span> WorxNet. worxnet.co.za — Coming soon nationwide.</p>
        <a href="{{ url('/admin') }}" class="footer-admin-link" title="Platform management &amp; data administration">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2l7 4v6c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V6l7-4z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
            Management Portal
        </a>
    </div>
</footer>

</body>
</html>

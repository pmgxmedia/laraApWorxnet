import './bootstrap';

/* =====================================================
   WorxNet Prototype — client-side demo interactions
   All provider, bid, and location data below is illustrative
   sample data only, used to demonstrate the matching and
   bidding experience ahead of the Laravel marketplace backend.
   ===================================================== */

document.getElementById('year') && (document.getElementById('year').textContent = new Date().getFullYear());

/* ---------- Mobile nav ---------- */
const navToggle = document.getElementById('navToggle');
navToggle?.addEventListener('click', () => {
    const isOpen = document.body.classList.toggle('nav-open');
    navToggle.setAttribute('aria-expanded', String(isOpen));
});
document.querySelectorAll('.main-nav a').forEach((a) => {
    a.addEventListener('click', () => document.body.classList.remove('nav-open'));
});

/* ---------- Service labels (for display only — real data now comes from the backend) ---------- */
const SERVICE_LABELS = {
    cctv: 'CCTV & Surveillance',
    alarms: 'Alarm Systems',
    access: 'Access Control',
    ndi: 'NDI / AV Systems',
    smarthome: 'Smart Home Automation',
    wifi: 'Wi-Fi & Connectivity',
    network: 'IP Network Infrastructure',
    audio: 'Audio & Sound Technology',
};

/* ---------- HTML escaping (all server/user data rendered via innerHTML must be escaped) ---------- */
function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (ch) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
    })[ch]);
}

/* ---------- Location handling ---------- */
const locationInput = document.getElementById('locationInput');
const useLocationBtn = document.getElementById('useLocationBtn');
const locationStatus = document.getElementById('locationStatus');
let currentCoords = null; // { lat, lng } when the user has shared their live location

useLocationBtn?.addEventListener('click', () => {
    if (!('geolocation' in navigator)) {
        locationStatus.textContent = 'Live location isn\u2019t supported in this browser. Please enter your area manually.';
        return;
    }
    useLocationBtn.classList.add('is-active');
    useLocationBtn.textContent = 'Locating\u2026';
    locationStatus.textContent = 'Requesting permission to use your live location\u2026';

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const { latitude, longitude } = position.coords;
            currentCoords = { lat: latitude, lng: longitude };
            locationInput.value = `Live location (${latitude.toFixed(4)}, ${longitude.toFixed(4)})`;
            resetLocateButton('Location shared \u2014 showing specialists near you.');
        },
        (error) => {
            currentCoords = null;
            const reason = error.code === error.PERMISSION_DENIED
                ? 'Location permission was denied.'
                : 'We couldn\u2019t detect your location.';
            resetLocateButton(`${reason} Please enter your suburb or address manually.`);
        },
        { enableHighAccuracy: true, timeout: 8000 }
    );
});

// If the user edits the location text manually, the precise coordinates no longer apply.
locationInput?.addEventListener('input', () => {
    currentCoords = null;
});

function resetLocateButton(message) {
    useLocationBtn.classList.remove('is-active');
    useLocationBtn.innerHTML = `
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2v3M12 19v3M2 12h3M19 12h3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="2"/></svg>
        Use my location`;
    locationStatus.textContent = message;
}

/* ---------- Finder / matching (backed by /providers/search) ---------- */
const finderForm = document.getElementById('finderForm');
const providerList = document.getElementById('providerList');
const resultsTitle = document.getElementById('resultsTitle');
const resultsSub = document.getElementById('resultsSub');
const resultsFilters = document.getElementById('resultsFilters');
const sortSelect = document.getElementById('sortSelect');

let lastResults = [];
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

finderForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    runSearch();
    document.getElementById('results').scrollIntoView({ behavior: 'smooth' });
});

sortSelect?.addEventListener('change', () => renderResults(lastResults, sortSelect.value));

async function runSearch() {
    const service = document.getElementById('serviceSelect').value;
    const urgency = document.getElementById('urgencySelect').value;
    const propertyType = document.getElementById('propertySelect')?.value;
    const location = locationInput.value.trim();

    if (!service) {
        resultsTitle.textContent = 'Please select a service';
        resultsSub.textContent = 'Choose the type of help you need so we can find the right specialists.';
        providerList.innerHTML = '';
        resultsFilters.hidden = true;
        return;
    }

    if (!currentCoords && !location) {
        resultsTitle.textContent = 'Please add your location';
        resultsSub.textContent = 'Share your live location or type your suburb so we can find specialists near you.';
        providerList.innerHTML = '';
        resultsFilters.hidden = true;
        return;
    }

    resultsTitle.textContent = 'Searching\u2026';
    resultsSub.textContent = 'Finding verified specialists that match your request.';
    providerList.innerHTML = '<div class="empty-state">Searching for nearby specialists\u2026</div>';

    const payload = {
        service_category: service,
        urgency,
        property_type: propertyType,
    };

    if (currentCoords) {
        payload.lat = currentCoords.lat;
        payload.lng = currentCoords.lng;
    } else {
        payload.location_text = location;
    }

    try {
        const response = await fetch('/providers/search', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken ?? '',
            },
            body: JSON.stringify(payload),
        });

        if (!response.ok) {
            const body = await response.json().catch(() => ({}));
            const firstError = body?.errors ? Object.values(body.errors)[0]?.[0] : null;
            throw new Error(firstError || body?.message || 'Search failed. Please try again.');
        }

        const data = await response.json();
        const matches = (data.providers || []).map((p) => ({
            ...p,
            distanceKm: p.distance_km,
            responseMins: p.response_minutes,
            available: p.available_now,
            verified: p.verified,
            emergency: p.emergency_ready,
            matchScore: p.match_score,
            area: p.area,
            reviews: p.reviews,
            rating: p.rating,
            name: p.name,
            initials: initialsFor(p.name),
            tags: (p.services || []).map((s) => SERVICE_LABELS[s] || s),
        }));

        lastResults = matches;

        resultsFilters.hidden = matches.length === 0;
        resultsTitle.textContent = matches.length
            ? `${matches.length} specialist${matches.length > 1 ? 's' : ''} matched for ${SERVICE_LABELS[service] || service}`
            : `No specialists currently available for ${SERVICE_LABELS[service] || service}`;
        resultsSub.textContent = data.used_coordinates
            ? 'Showing results ranked by distance from your live location.'
            : location
                ? `Showing results near "${location}".`
                : 'Showing nationwide results \u2014 add your location for closer matches.';

        renderResults(matches, 'match');
    } catch (err) {
        resultsTitle.textContent = 'Something went wrong';
        resultsSub.textContent = err.message || 'Please try your search again.';
        resultsFilters.hidden = true;
        providerList.innerHTML = '<div class="empty-state">We couldn\u2019t load specialists right now. Please try again shortly.</div>';
        lastResults = [];
    }
}

function initialsFor(name) {
    return (name || '')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((w) => w[0].toUpperCase())
        .join('');
}

function renderResults(matches, sortBy) {
    if (!matches.length) {
        providerList.innerHTML = '<div class="empty-state">No matching specialists yet. Try a different service or check back soon \u2014 our network is growing.</div>';
        return;
    }

    const sorted = [...matches].sort((a, b) => {
        if (sortBy === 'distance') return (a.distanceKm ?? Infinity) - (b.distanceKm ?? Infinity);
        if (sortBy === 'rating') return b.rating - a.rating;
        if (sortBy === 'response') return (a.responseMins ?? Infinity) - (b.responseMins ?? Infinity);
        return b.matchScore - a.matchScore;
    });

    providerList.innerHTML = sorted.map((p, i) => providerCardHtml(p, i)).join('');

    providerList.querySelectorAll('[data-request-idx]').forEach((btn) => {
        btn.addEventListener('click', () => openEnquiry(sorted[Number(btn.dataset.requestIdx)]));
    });
}

function providerCardHtml(p, idx) {
    const distanceLabel = p.distanceKm != null
        ? `\u2248 ${Number(p.distanceKm).toFixed(1)} km away`
        : 'Distance unavailable';

    return `
    <article class="provider-card">
        <div class="provider-avatar">${p.initials}</div>
        <div class="provider-main">
            <h3>${p.name}
                ${p.verified ? '<span class="badge">Verified</span>' : ''}
                ${p.featured ? '<span class="badge featured">Featured</span>' : ''}
                ${p.emergency ? '<span class="badge emergency">Emergency Ready</span>' : ''}
            </h3>
            <div class="provider-meta">
                <span>\ud83d\udccd ${p.area}</span>
                <span>${distanceLabel}</span>
                <span>Responds in ~${p.responseMins} min</span>
            </div>
            <div class="provider-tags">${p.tags.map((t) => `<span>${t}</span>`).join('')}</div>
        </div>
        <div class="provider-side">
            <div class="rating">\u2605 ${Number(p.rating).toFixed(1)} <span>(${p.reviews})</span></div>
            <div class="availability ${p.available ? 'available' : 'busy'}">${p.available ? 'Available now' : 'Busy'}</div>
            <button class="btn btn-primary btn-sm" data-request-idx="${idx}">Request Help</button>
        </div>
    </article>`;
}

/* ---------- Enquiry modal ---------- */
const enquiryOverlay = document.getElementById('enquiryOverlay');
const enquiryClose = document.getElementById('enquiryClose');
const enquirySummary = document.getElementById('enquirySummary');
const enquiryForm = document.getElementById('enquiryForm');
const enquiryFormView = document.getElementById('enquiryFormView');
const enquirySuccessView = document.getElementById('enquirySuccessView');
const enquiryProviderName = document.getElementById('enquiryProviderName');
const enquiryDoneBtn = document.getElementById('enquiryDoneBtn');

function openEnquiry(provider) {
    enquiryContext = {
        category: provider.category ?? document.getElementById('serviceSelect')?.value ?? null,
        providerName: provider.name,
        area: provider.area ?? null,
        property_type: document.getElementById('propertySelect')?.value ?? 'home',
        urgency: document.getElementById('urgencySelect')?.value ?? 'planned',
        lat: provider.lat ?? null,
        lng: provider.lng ?? null,
    };
    enquirySummary.textContent = `Send your request to ${provider.name} (${provider.area}).`;
    enquiryProviderName.textContent = provider.name;
    enquiryFormView.hidden = false;
    enquirySuccessView.hidden = true;
    enquiryForm.reset();
    enquiryOverlay.hidden = false;
}

function closeEnquiry() {
    enquiryOverlay.hidden = true;
}

enquiryClose?.addEventListener('click', closeEnquiry);
enquiryOverlay?.addEventListener('click', (e) => {
    if (e.target === enquiryOverlay) closeEnquiry();
});
let enquiryContext = null;

enquiryForm?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const payload = {
        service_category: enquiryContext?.category ?? document.getElementById('serviceSelect')?.value,
        provider_name: enquiryContext?.providerName ?? null,
        property_type: enquiryContext?.property_type ?? 'home',
        urgency: enquiryContext?.urgency ?? 'planned',
        area_label: enquiryContext?.area ?? null,
        lat: enquiryContext?.lat ?? null,
        lng: enquiryContext?.lng ?? null,
        contact_name: document.getElementById('enquiryName').value.trim(),
        contact_phone: document.getElementById('enquiryContact').value.trim(),
        notes: document.getElementById('enquiryNotes').value.trim(),
    };

    const sendBtn = enquiryForm.querySelector('button[type="submit"]');
    sendBtn.disabled = true;
    sendBtn.textContent = 'Sending\u2026';

    try {
        const response = await fetch('/service-requests', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(payload),
        });

        if (response.status === 401 || response.status === 419) {
            window.location.assign('/login');
            return;
        }

        if (!response.ok) {
            const body = await response.json().catch(() => ({}));
            const firstError = body?.errors ? Object.values(body.errors)[0]?.[0] : null;
            throw new Error(firstError || body?.message || 'Your request could not be sent. Please try again.');
        }

        enquiryFormView.hidden = true;
        enquirySuccessView.hidden = false;
        enquiryDoneBtn?.focus();
    } catch (err) {
        enquiryFormView.hidden = false;
        enquirySuccessView.hidden = true;
        enquirySummary.textContent = err.message;
    } finally {
        sendBtn.disabled = false;
        sendBtn.textContent = 'Send Request';
    }
});
enquiryDoneBtn?.addEventListener('click', closeEnquiry);

/* ---------- Live coverage radar (websocket-powered) ---------- */
const coveragePins = document.getElementById('coveragePins');
const coverageLiveStatus = document.getElementById('coverageLiveStatus');
const coverageMaxDistanceKm = 60; // distance at which a pin sits on the outer ring (r3)
const coveragePinEls = new Map(); // providerId -> pin element

function setCoverageLiveState(state, label) {
    if (!coverageLiveStatus) return;
    coverageLiveStatus.dataset.state = state;
    coverageLiveStatus.innerHTML = `<span class="coverage-live-dot"></span> ${label}`;
}

function coveragePolarToPercent(distanceKm, bearingDeg) {
    // Map distance (0..coverageMaxDistanceKm) to a radius of 4%..48% from the centre,
    // and bearing (0 = north) to an angle around the "You" pin.
    const radiusPct = 4 + Math.min(1, distanceKm / coverageMaxDistanceKm) * 44;
    const angleRad = (bearingDeg - 90) * (Math.PI / 180); // shift so 0deg (north) points up
    const top = 50 + radiusPct * Math.sin(angleRad);
    const left = 50 + radiusPct * Math.cos(angleRad);
    return { top: Math.max(2, Math.min(98, top)), left: Math.max(2, Math.min(98, left)) };
}

function coverageAvailabilityClass(provider) {
    if (provider.status === 'Offline' || provider.available_now === false) return 'is-offline';
    if (provider.status === 'En route') return 'is-busy';
    return 'is-available';
}

function renderCoveragePin(provider) {
    const { top, left } = coveragePolarToPercent(provider.distance_km ?? 0, provider.bearing_deg ?? 0);
    const stateClass = coverageAvailabilityClass(provider);
    const progress = provider.path_progress ?? 0;
    const statusLabel = provider.status || (provider.available_now ? 'Available' : 'Offline');

    let pin = coveragePinEls.get(provider.id);
    if (!pin) {
        pin = document.createElement('div');
        pin.className = 'coverage-pin p';
        pin.innerHTML = '<div class="coverage-pin-progress"></div><div class="coverage-pin-tooltip"></div>';
        coveragePins.appendChild(pin);
        coveragePinEls.set(provider.id, pin);
    }

    pin.className = `coverage-pin p ${stateClass}`;
    pin.style.top = `${top}%`;
    pin.style.left = `${left}%`;
    pin.querySelector('.coverage-pin-progress').style.setProperty('--progress', progress > 0 && progress < 100 ? progress : 0);
    pin.querySelector('.coverage-pin-tooltip').textContent =
        `${provider.name} \u2014 ${Number(provider.distance_km ?? 0).toFixed(1)}km \u00b7 ${statusLabel}`;
}

async function loadCoverageSnapshot() {
    if (!coveragePins) return;

    try {
        const response = await fetch('/coverage/feed', { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error('Failed to load coverage');

        const data = await response.json();
        coveragePins.innerHTML = '';
        coveragePinEls.clear();

        if (!data.providers?.length) {
            coveragePins.innerHTML = '<p class="coverage-empty">No nearby providers to display yet.</p>';
            return;
        }

        data.providers.forEach(renderCoveragePin);
    } catch (err) {
        setCoverageLiveState('offline', 'Unavailable');
    }
}

function subscribeToCoverageChannel() {
    if (!window.Echo) {
        setCoverageLiveState('offline', 'Live updates unavailable');
        return;
    }

    setCoverageLiveState('connecting', 'Connecting\u2026');

    window.Echo.connector.pusher.connection.bind('connected', () => {
        setCoverageLiveState('live', 'Live');
    });
    window.Echo.connector.pusher.connection.bind('disconnected', () => {
        setCoverageLiveState('offline', 'Reconnecting\u2026');
    });
    window.Echo.connector.pusher.connection.bind('unavailable', () => {
        setCoverageLiveState('offline', 'Offline');
    });
    window.Echo.connector.pusher.connection.bind('failed', () => {
        setCoverageLiveState('offline', 'Offline');
    });

    window.Echo.channel('coverage').listen('.provider.updated', (provider) => {
        renderCoveragePin(provider);
    });
}

loadCoverageSnapshot();
subscribeToCoverageChannel();

/* ---------- Projects & bidding (live data from /projects/feed) ---------- */
const projectForm = document.getElementById('projectForm');
const projectList = document.getElementById('projectList');
const projBudgetType = document.getElementById('projBudgetType');
const projBudgetFields = document.getElementById('projBudgetFields');

let liveProjects = [];
let localProjects = []; // prototype-only projects added via the form on this page view

async function loadProjects() {
    projectList.innerHTML = '<div class="empty-state">Loading live projects\u2026</div>';

    try {
        const response = await fetch('/projects/feed', {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) throw new Error('Failed to load projects');

        const data = await response.json();
        liveProjects = data.projects || [];
        renderProjects();
    } catch (err) {
        projectList.innerHTML = '<div class="empty-state">We couldn\u2019t load live projects right now. Please try again shortly.</div>';
    }
}

function renderProjects() {
    const all = [...localProjects, ...liveProjects];

    if (!all.length) {
        projectList.innerHTML = '<div class="empty-state">No projects posted yet \u2014 be the first to list one above.</div>';
        return;
    }

    projectList.innerHTML = all.map(projectCardHtml).join('');

    projectList.querySelectorAll('[data-toggle-bids]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const panel = projectList.querySelector(`[data-bids-panel="${btn.dataset.toggleBids}"]`);
            if (!panel) return;
            const isOpen = panel.classList.toggle('is-open');
            btn.setAttribute('aria-expanded', String(isOpen));
            btn.querySelector('.toggle-caret').textContent = isOpen ? '\u25b2' : '\u25bc';
            btn.querySelector('.toggle-label').textContent = isOpen ? 'Hide bids' : 'View bids';
        });
    });
}

function projectCardHtml(project, i) {
    const bidsCount = project.bids?.length ?? project.bids_count ?? 0;
    const uid = project.id ?? `local-${i}`;

    const bidsHtml = bidsCount
        ? project.bids.map((b) => `
            <div class="bid-row">
                <div class="bid-avatar">${initialsFor(b.provider_name)}</div>
                <div style="flex:1;">
                    <div>
                        ${b.provider_name}
                        ${b.provider_verified ? '<span class="badge" style="margin-left:6px;">Verified</span>' : ''}
                    </div>
                    <div class="muted" style="font-size:0.78rem;">
                        ${b.scope ? b.scope : ''}
                        ${b.provider_rating ? ` &middot; \u2605 ${Number(b.provider_rating).toFixed(1)} (${b.provider_reviews ?? 0})` : ''}
                        ${b.submitted_at ? ` &middot; ${b.submitted_at}` : ''}
                    </div>
                </div>
                <div class="bid-amount">R ${Number(b.amount).toLocaleString('en-ZA')}</div>
            </div>`).join('')
        : '<p class="muted" style="font-size:0.85rem;">No bids yet \u2014 be the first provider to respond.</p>';

    const postedMeta = project.posted_at
        ? `<span>Posted ${project.posted_at}${project.customer_first_name ? ` by ${project.customer_first_name}` : ''}</span>`
        : '';

    return `
    <article class="project-card">
        <div class="project-card-head">
            <h3>${project.title}</h3>
            <span class="project-budget">${project.budget_label ?? project.budgetLabel ?? 'Request quotations'}</span>
        </div>
        <div class="project-meta">
            <span>${project.category_label || SERVICE_LABELS[project.category] || project.category || ''}</span>
            <span>\ud83d\udccd ${project.location ?? ''}</span>
            ${postedMeta}
        </div>
        <p class="project-desc">${project.description ?? ''}</p>
        <button type="button" class="bid-toggle" data-toggle-bids="${uid}" aria-expanded="false">
            <span class="bid-count">${bidsCount} bid${bidsCount === 1 ? '' : 's'} received</span>
            <span class="toggle-label">View bids</span>
            <span class="toggle-caret">\u25bc</span>
        </button>
        <div class="bid-list" data-bids-panel="${uid}">${bidsHtml}</div>
    </article>`;
}

projBudgetType?.addEventListener('change', () => {
    projBudgetFields.style.display = projBudgetType.value === 'quote' ? 'none' : 'block';
});

projectForm?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const title = document.getElementById('projTitle').value.trim();
    const category = document.getElementById('projCategory').value;
    const location = document.getElementById('projLocation').value.trim();
    const description = document.getElementById('projDescription').value.trim();
    const budgetType = projBudgetType.value;
    const min = document.getElementById('projBudgetMin').value;
    const max = document.getElementById('projBudgetMax').value;

    const submitBtn = projectForm.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Publishing\u2026';

    try {
        const response = await fetch('/projects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                title,
                category,
                location,
                description,
                budget_type: budgetType,
                budget_min: min ? Number(min) : null,
                budget_max: max ? Number(max) : null,
            }),
        });

        if (response.status === 401 || response.status === 419) {
            window.location.assign('/login');
            return;
        }

        if (!response.ok) {
            const body = await response.json().catch(() => ({}));
            const firstError = body?.errors ? Object.values(body.errors)[0]?.[0] : null;
            throw new Error(firstError || body?.message || 'Your project could not be published. Please try again.');
        }

        projectForm.reset();
        projBudgetFields.style.display = 'block';
        await loadProjects();
        projectList.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (err) {
        const errView = document.getElementById('projFormError');
        if (errView) {
            errView.textContent = err.message;
            errView.hidden = false;
        }
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Post Project';
    }
});

loadProjects();

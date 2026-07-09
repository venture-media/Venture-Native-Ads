jQuery(function($) {
    'use strict';

    $('.venture-native-ads-wrapper').each(function() {
        const wrapper = $(this);
        const campaignId = wrapper.data('campaign-id');
        const hostUrl     = wrapper.data('host-url').replace(/\/$/, '');
        const secretKey   = wrapper.data('secret-key');
        const adDuration  = (ventureAdsConfig.adDuration || 15) * 1000;
        const fadeTime    = 250;
        const preloadTime = 5000; // 5 seconds before end

        let currentAd = null;
        let nextAd    = null;
        let timer     = null;
        let preloadTimer = null;
        let isFading  = false;

        if (!campaignId || !hostUrl || !secretKey) {
            wrapper.html('<p style="color:red;">Venture Native Ads configuration missing.</p>');
            return;
        }

        const apiBase = `${hostUrl}/wp-json/venture-native-ad-management/v1`;

        async function fetchAd() {
            try {
                const timestamp = Date.now();
                const res = await fetch(`${apiBase}/serve-campaign/${campaignId}?_=${timestamp}`, {
                    headers: { 'X-Venture-Secret': secretKey },
                    cache: 'no-store'
                });
                if (!res.ok) throw new Error('Network error');
                const data = await res.json();
                return data.ads && data.ads.length ? data.ads[0] : null;
            } catch (e) {
                console.error('Ad fetch error:', e);
                return null;
            }
        }

        function track(adId, type) {
            fetch(`${apiBase}/track`, {
                method: 'POST',
                headers: {
                    'X-Venture-Secret': secretKey,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ad_id: adId,
                    type: type,
                    site_identifier: location.hostname
                })
            }).catch(() => {}); // silent fail
        }

        function showAd(ad) {
            if (!ad) return;

            const html = `
                <div class="venture-ad-container">
                    <a href="${ad.target_url}" target="_blank" rel="nofollow sponsored" data-ad-id="${ad.id}">
                        <div class="ad-image-wrapper">
                            <img src="${ad.image_url}" alt="${ad.title}" loading="lazy">
                        </div>
                        <div class="ad-content">
                            <div class="ad-title">${ad.title}</div>
                            <div class="ad-url">${ad.target_url}</div>
                        </div>
                    </a>
                </div>`;
            
            wrapper.html(html);
            const container = wrapper.find('.venture-ad-container');

            // fade in
            setTimeout(() => container.addClass('visible'), 10);

            // track impression
            setTimeout(() => track(ad.id, 'impression'), fadeTime + 100);

            // click tracking
            container.find('a').on('click', function() {
                track($(this).data('ad-id'), 'click');
            });

            currentAd = ad;
            scheduleNext();
        }

        function scheduleNext() {
            if (timer) clearTimeout(timer);
            if (preloadTimer) clearTimeout(preloadTimer);

            timer = setTimeout(rotateNext, adDuration);

            // preload next ad 5s early
            preloadTimer = setTimeout(async () => {
                if (!isFading) nextAd = await fetchAd();
            }, Math.max(adDuration - preloadTime, 1000));
        }

        async function rotateNext() {
            if (isFading) return;
            isFading = true;

            const container = wrapper.find('.venture-ad-container');
            container.removeClass('visible');

            await new Promise(r => setTimeout(r, fadeTime));

            let adToShow = nextAd || await fetchAd();
            nextAd = null;

            if (adToShow) {
                showAd(adToShow);
            } else {
                wrapper.html('<p style="color:#999;text-align:center;">No ads available at the moment.</p>');
            }
            isFading = false;
        }

        // Start
        (async () => {
            const firstAd = await fetchAd();
            if (firstAd) showAd(firstAd);
            else wrapper.html('<p style="color:#999;text-align:center;">No ads available for this campaign.</p>');
        })();
    });
});

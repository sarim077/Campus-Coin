import './bootstrap';

const themeStorageKey = 'campus-coin-theme';
const systemTheme = window.matchMedia('(prefers-color-scheme: light)');
let hasExplicitTheme = false;
try {
    hasExplicitTheme = ['dark', 'light'].includes(localStorage.getItem(themeStorageKey));
} catch (error) {
    console.warn('Campus Coin could not access the saved theme preference.', error);
}

const getThemePalette = () => {
    const styles = getComputedStyle(document.documentElement);
    const value = (name) => styles.getPropertyValue(name).trim();
    const dark = document.documentElement.getAttribute('data-bs-theme') !== 'light';

    return {
        text: value('--theme-text'),
        muted: value('--theme-text-secondary'),
        grid: value('--theme-chart-grid'),
        series: dark
            ? ['#38BDF8', '#BAE6FD', '#2563EB', '#1E4FAF', '#0C4A6E', '#60A5FA']
            : ['#1D4ED8', '#0369A1', '#1E4FAF', '#0C4A6E', '#38BDF8', '#2563EB'],
        surface: value('--theme-raised'),
    };
};

const refreshCharts = () => {
    if (!window.Chart) return;

    const palette = getThemePalette();
    window.Chart.defaults.color = palette.muted;
    window.Chart.defaults.borderColor = palette.grid;

    Object.values(window.Chart.instances).forEach((chart) => {
        chart.options.color = palette.muted;
        chart.options.plugins.legend.labels.color = palette.muted;
        chart.options.plugins.tooltip.backgroundColor = palette.surface;
        chart.options.plugins.tooltip.titleColor = palette.text;
        chart.options.plugins.tooltip.bodyColor = palette.text;
        chart.options.plugins.tooltip.borderColor = palette.grid;
        chart.options.plugins.tooltip.borderWidth = 1;

        Object.values(chart.options.scales ?? {}).forEach((scale) => {
            if (scale.ticks) scale.ticks.color = palette.muted;
            if (scale.grid) scale.grid.color = palette.grid;
        });

        chart.data.datasets.forEach((dataset, index) => {
            const color = palette.series[index % palette.series.length];
            if (chart.config.type === 'doughnut' || chart.config.type === 'pie') {
                dataset.backgroundColor = palette.series;
                dataset.borderColor = palette.surface;
            } else {
                dataset.borderColor = color;
                dataset.backgroundColor = chart.config.type === 'bar'
                    ? color
                    : color === '#BAE6FD'
                        ? 'rgba(186, 230, 253, .16)'
                        : 'rgba(96, 165, 250, .2)';
            }
        });

        chart.update('none');
    });
};

const syncThemeControls = () => {
    const currentTheme = document.documentElement.getAttribute('data-bs-theme') === 'light' ? 'light' : 'dark';
    const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-label', `Switch to ${nextTheme} mode`);
        button.setAttribute('title', `Switch to ${nextTheme} mode`);
        button.setAttribute('aria-pressed', String(currentTheme === 'dark'));
        const label = button.querySelector('[data-theme-label]');
        if (label) label.textContent = currentTheme === 'dark' ? 'Dark' : 'Light';
    });
};

const applyTheme = (theme, { persist = false, transition = true } = {}) => {
    if (theme !== 'dark' && theme !== 'light') return;

    const root = document.documentElement;
    if (transition && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        root.classList.add('theme-transition');
        window.setTimeout(() => root.classList.remove('theme-transition'), 240);
    }
    root.setAttribute('data-bs-theme', theme);
    root.style.colorScheme = theme;

    const themeColor = document.querySelector('meta[name="theme-color"]');
    if (themeColor) themeColor.setAttribute('content', theme === 'dark' ? '#071426' : '#EAF3FF');

    if (persist) {
        hasExplicitTheme = true;
        try {
            localStorage.setItem(themeStorageKey, theme);
        } catch (error) {
            console.warn('Campus Coin could not save the theme preference.', error);
        }
    }

    syncThemeControls();
    refreshCharts();
    window.dispatchEvent(new CustomEvent('campuscoin:themechange', { detail: { theme } }));
};

window.campusCoinTheme = {
    getPalette: getThemePalette,
    refreshCharts,
};

document.addEventListener('DOMContentLoaded', () => {
    syncThemeControls();
    refreshCharts();
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme') === 'light' ? 'light' : 'dark';
            applyTheme(currentTheme === 'dark' ? 'light' : 'dark', { persist: true });
        });
    });

    systemTheme.addEventListener('change', (event) => {
        if (!hasExplicitTheme) applyTheme(event.matches ? 'light' : 'dark', { transition: false });
    });

    window.addEventListener('storage', (event) => {
        if (event.key !== themeStorageKey) return;
        hasExplicitTheme = event.newValue === 'dark' || event.newValue === 'light';
        applyTheme(hasExplicitTheme ? event.newValue : (systemTheme.matches ? 'light' : 'dark'), { transition: false });
    });

    const menuToggle = document.querySelector('.menu-toggle');
    const primaryNav = document.querySelector('.primary-nav');

    if (menuToggle && primaryNav) {
        menuToggle.addEventListener('click', () => {
            const isOpen = menuToggle.getAttribute('aria-expanded') === 'true';
            menuToggle.setAttribute('aria-expanded', String(!isOpen));
            menuToggle.setAttribute('aria-label', isOpen ? 'Open navigation' : 'Close navigation');
            primaryNav.classList.toggle('is-open', !isOpen);
        });

        primaryNav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                menuToggle.setAttribute('aria-expanded', 'false');
                menuToggle.setAttribute('aria-label', 'Open navigation');
                primaryNav.classList.remove('is-open');
            });
        });
    }

    const rotatingWords = [...document.querySelectorAll('.hero-title-word')];
    if (rotatingWords.length > 1 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        let currentWord = 0;
        window.setInterval(() => {
            const previousWord = rotatingWords[currentWord];
            previousWord.classList.remove('is-current');
            previousWord.classList.add('was-current');
            currentWord = (currentWord + 1) % rotatingWords.length;
            rotatingWords[currentWord].classList.remove('was-current');
            rotatingWords[currentWord].classList.add('is-current');
            window.setTimeout(() => previousWord.classList.remove('was-current'), 750);
        }, 3800);
    }

    const coinStory = document.querySelector('.coin-story');
    const coinStoryStage = coinStory?.querySelector('.coin-story-stage');
    const coinMarquee = coinStory?.querySelector('.coin-marquee-track');
    const coinGlass = coinStory?.querySelector('.coin-glass');
    const coinStoryStars = [...(coinStory?.querySelectorAll('.coin-story-star') ?? [])];
    const helixBeads = [...(coinGlass?.querySelectorAll('.helix-bead') ?? [])];
    const helixFront = coinGlass?.querySelector('.glass-ribbon-front');
    const helixBack = coinGlass?.querySelector('.glass-ribbon-middle');
    const siteHeader = document.querySelector('.site-header');
    const helixBeadPaths = helixBeads.flatMap((bead) => {
        const strand = bead.dataset.strand === 'front' ? helixFront : helixBack;
        if (!(strand instanceof SVGGeometryElement)) return [];

        return [{
            bead,
            strand,
            length: strand.getTotalLength(),
            offset: Number(bead.dataset.offset),
        }];
    });
    const positionHelixBeads = (progress) => {
        helixBeadPaths.forEach(({ bead, strand, length, offset }) => {
            if (length <= 0 || !Number.isFinite(offset)) return;
            const distance = ((offset + progress * 0.85) % 1) * length;
            const point = strand.getPointAtLength(distance);
            bead.setAttribute('transform', `translate(${point.x.toFixed(2)} ${point.y.toFixed(2)})`);
        });
    };
    positionHelixBeads(0);

    if (
        coinStory instanceof HTMLElement
        && coinStoryStage instanceof HTMLElement
        && coinMarquee instanceof HTMLElement
        && coinGlass instanceof SVGElement
        && !window.matchMedia('(prefers-reduced-motion: reduce)').matches
    ) {
        let scrollFrame = 0;
        const updateStoryMotion = () => {
            scrollFrame = 0;
            const sectionTop = coinStory.getBoundingClientRect().top;
            const travelDistance = Math.max(1, coinStory.offsetHeight - coinStoryStage.offsetHeight);
            const stickyTop = siteHeader?.getBoundingClientRect().height ?? 0;
            const progress = Math.min(1, Math.max(0, (stickyTop - sectionTop) / travelDistance));

            coinMarquee.style.transform = `translate3d(0, ${(-50 * progress).toFixed(3)}%, 0)`;
            coinGlass.style.transform = `perspective(900px) translate3d(0, ${(14 - 28 * progress).toFixed(2)}px, 0) rotateY(${(-10 + 20 * progress).toFixed(2)}deg)`;
            positionHelixBeads(progress);
            coinStoryStars.forEach((star, index) => {
                const direction = index % 2 === 0 ? 1 : -1;
                star.style.transform = `translateY(${((progress - 0.5) * direction * 22).toFixed(2)}px)`;
            });
        };
        const scheduleStoryMotion = () => {
            if (!scrollFrame) scrollFrame = window.requestAnimationFrame(updateStoryMotion);
        };

        window.addEventListener('scroll', scheduleStoryMotion, { passive: true });
        window.addEventListener('resize', scheduleStoryMotion);
        scheduleStoryMotion();
    }

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (document.body.classList.contains('fx-site')) {
        const newSectionReveals = [...document.querySelectorAll(
            '.fx-site main > section:not(.hero):not(.coin-story) .section-wrap > *:not(.reveal)'
        )];
        newSectionReveals.forEach((element, index) => {
            element.classList.add('reveal', `reveal-delay-${(index % 3) + 1}`);
        });

        const staggeredReveals = [
            ...document.querySelectorAll('.benefit-card, .stat-card, .faq-list details, .blog-card'),
        ];
        staggeredReveals.forEach((element, index) => {
            if (element.classList.contains('reveal')) return;
            element.classList.add('reveal', `reveal-delay-${(index % 3) + 1}`);
        });

        const parallaxElements = [...document.querySelectorAll(
            '.story-chart-grid, .platform-window, .intro-terminal, .program-spotlight, .story-chart, .payout-card, .community-word'
        )];
        const hero = document.querySelector('.hero');
        const heroContent = hero?.querySelector('.hero-content');
        const heroMesh = hero?.querySelector('.hero-mesh');
        const storyItems = [...document.querySelectorAll('.story-item')];
        const storyChart = document.querySelector('.story-chart');
        const storyLine = storyChart?.querySelector('.story-line-core');
        const storyTracer = storyChart?.querySelector('.story-line-tracer');
        const storyLineLength = storyLine instanceof SVGGeometryElement ? storyLine.getTotalLength() : 0;
        if (storyChart && storyLineLength > 0) storyChart.classList.add('is-scroll-linked');
        parallaxElements.forEach((element) => element.classList.add('scroll-parallax'));

        const progressIndicator = document.querySelector('.home-scroll-progress');
        let homeScrollFrame = 0;
        const updateHomeScroll = () => {
            homeScrollFrame = 0;
            const scrollableHeight = document.documentElement.scrollHeight - window.innerHeight;
            const pageProgress = scrollableHeight > 0 ? window.scrollY / scrollableHeight : 0;
            if (progressIndicator) progressIndicator.style.transform = `scaleX(${Math.min(1, Math.max(0, pageProgress))})`;

            if (!reduceMotion) {
                if (hero instanceof HTMLElement) {
                    const heroProgress = Math.min(1, Math.max(0, -hero.getBoundingClientRect().top / Math.max(1, hero.offsetHeight)));
                    heroContent?.style.setProperty('--hero-scroll-offset', `${(-44 * heroProgress).toFixed(2)}px`);
                    heroContent?.style.setProperty('--hero-scroll-opacity', Math.max(0, 1 - heroProgress * 1.15).toFixed(3));
                    heroMesh?.style.setProperty('--hero-scroll-offset', `${(24 * heroProgress).toFixed(2)}px`);
                    heroMesh?.style.setProperty('--hero-scroll-scale', (1 + heroProgress * 0.035).toFixed(3));
                }
                parallaxElements.forEach((element) => {
                    const bounds = element.getBoundingClientRect();
                    const viewportProgress = (bounds.top + bounds.height / 2 - window.innerHeight / 2) / window.innerHeight;
                    const shift = Math.max(-38, Math.min(38, -viewportProgress * 23));
                    element.style.setProperty('--scroll-shift', `${shift.toFixed(2)}px`);
                });
            }

            if (storyChart && storyLine instanceof SVGGeometryElement && storyLineLength > 0) {
                const bounds = storyChart.getBoundingClientRect();
                const progressStart = window.innerHeight * 0.82;
                const progressEnd = -bounds.height * 0.2;
                const progress = Math.min(1, Math.max(0, (progressStart - bounds.top) / (progressStart - progressEnd)));
                storyChart.style.setProperty('--graph-progress', progress.toFixed(4));

                if (storyTracer instanceof SVGCircleElement) {
                    const point = storyLine.getPointAtLength(progress * storyLineLength);
                    storyTracer.setAttribute('cx', point.x.toFixed(2));
                    storyTracer.setAttribute('cy', point.y.toFixed(2));
                }

                if (storyItems.length) {
                    const activeIndex = Math.min(storyItems.length - 1, Math.floor(progress * storyItems.length));
                    storyItems.forEach((item, index) => item.classList.toggle('is-current', index === activeIndex));
                }
            }
        };
        const scheduleHomeScroll = () => {
            if (!homeScrollFrame) homeScrollFrame = window.requestAnimationFrame(updateHomeScroll);
        };

        window.addEventListener('scroll', scheduleHomeScroll, { passive: true });
        window.addEventListener('resize', scheduleHomeScroll);
        scheduleHomeScroll();
    }

    const revealElements = document.querySelectorAll('.reveal');
    if (!reduceMotion && 'IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });

        revealElements.forEach((element) => revealObserver.observe(element));
    } else {
        revealElements.forEach((element) => element.classList.add('is-visible'));
    }

    const scrollSteps = document.querySelector('[data-scroll-steps]');
    const stepsSticky = scrollSteps?.querySelector('.steps-sticky');
    const stepItems = [...(scrollSteps?.querySelectorAll('.step-tab') ?? [])];
    const programStack = scrollSteps?.querySelector('.program-stack');
    const stepScreens = [...(scrollSteps?.querySelectorAll('.steps-screenshot') ?? [])];

    if (scrollSteps instanceof HTMLElement && stepsSticky instanceof HTMLElement && stepItems.length) {
        scrollSteps.classList.add('steps-scroll-ready');
        let activeStep = 0;
        let stepsFrame = 0;

        const updateScrollSteps = () => {
            stepsFrame = 0;
            const scrollDistance = Math.max(1, scrollSteps.offsetHeight - stepsSticky.offsetHeight);
            const stickyTop = Number.parseFloat(getComputedStyle(stepsSticky).top) || 0;
            const progress = Math.min(1, Math.max(0, (stickyTop - scrollSteps.getBoundingClientRect().top) / scrollDistance));
            const nextStep = Math.min(stepItems.length - 1, Math.floor(progress * stepItems.length));

            if (nextStep === activeStep && scrollSteps.dataset.activeStep) return;
            activeStep = nextStep;
            const selectedStep = String(activeStep + 1);
            scrollSteps.dataset.activeStep = selectedStep;
            stepItems.forEach((item, index) => {
                if (index === activeStep) item.setAttribute('aria-current', 'step');
                else item.removeAttribute('aria-current');
            });
            if (programStack) programStack.classList.toggle('is-hidden', selectedStep !== '1');
            stepScreens.forEach((screen, index) => {
                screen.classList.toggle('is-visible', String(index + 2) === selectedStep);
            });
        };

        const scheduleScrollSteps = () => {
            if (!stepsFrame) stepsFrame = window.requestAnimationFrame(updateScrollSteps);
        };

        window.addEventListener('scroll', scheduleScrollSteps, { passive: true });
        window.addEventListener('resize', scheduleScrollSteps);
        scheduleScrollSteps();
    }

    const programTabs = [...document.querySelectorAll('.program-tab')];
    const programTable = document.querySelector('.program-table');
    const programColumns = { Food: 1, Transport: 2, Study: 3, Fun: 4, Savings: 5 };

    programTabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            programTabs.forEach((item) => item.setAttribute('aria-selected', String(item === tab)));
            const selectedColumn = programColumns[tab.dataset.program];

            programTable?.querySelectorAll('tr').forEach((row) => {
                row.querySelectorAll('th, td').forEach((cell, index) => {
                    cell.classList.toggle('selected-program', index === selectedColumn);
                });
            });
        });
    });

    const countdown = document.querySelector('[data-countdown]');
    if (countdown) {
        const getNextMonth = () => new Date(
            new Date().getFullYear(),
            new Date().getMonth() + 1,
            1,
        ).getTime();

        const updateCountdown = () => {
            const remaining = Math.max(0, getNextMonth() - Date.now());
            const values = {
                days: Math.floor(remaining / 86400000),
                hours: Math.floor((remaining % 86400000) / 3600000),
                minutes: Math.floor((remaining % 3600000) / 60000),
                seconds: Math.floor((remaining % 60000) / 1000),
            };

            Object.entries(values).forEach(([unit, value]) => {
                const output = countdown.querySelector(`[data-${unit}]`);
                if (output) output.textContent = String(value).padStart(2, '0');
            });
        };

        updateCountdown();
        window.setInterval(updateCountdown, 1000);
    }

    const cookieBanner = document.querySelector('.cookie-banner');
    if (cookieBanner) {
        let savedChoice = null;
        try {
            savedChoice = localStorage.getItem('campus-coin-cookie-choice');
        } catch (error) {
            savedChoice = null;
        }

        if (!savedChoice) {
            cookieBanner.hidden = false;
            window.setTimeout(() => cookieBanner.classList.add('is-visible'), 500);
        }

        cookieBanner.querySelectorAll('[data-cookie-choice]').forEach((button) => {
            button.addEventListener('click', () => {
                try {
                    localStorage.setItem('campus-coin-cookie-choice', button.dataset.cookieChoice);
                } catch (error) {
                    // The banner can still be dismissed when storage is unavailable.
                }
                cookieBanner.classList.remove('is-visible');
                window.setTimeout(() => { cookieBanner.hidden = true; }, 350);
            });
        });
    }

});

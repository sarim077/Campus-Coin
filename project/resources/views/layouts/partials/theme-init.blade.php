<script>
    (function () {
        const key = 'campus-coin-theme';
        let theme = null;
        try {
            theme = localStorage.getItem(key);
        } catch (error) {
            console.warn('Campus Coin could not read the saved theme preference.', error);
        }
        if (theme !== 'dark' && theme !== 'light') {
            theme = window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
        }
        document.documentElement.setAttribute('data-bs-theme', theme);
        document.documentElement.style.colorScheme = theme;
        const themeColor = document.querySelector('meta[name="theme-color"]');
        if (themeColor) themeColor.setAttribute('content', theme === 'dark' ? '#071426' : '#EAF3FF');
    })();
</script>

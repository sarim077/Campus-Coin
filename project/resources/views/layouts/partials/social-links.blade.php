<nav class="social-links" aria-label="Campus Coin social links">
    <a href="{{ $siteSettings->facebook_url ?: 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode(route('home')) }}" target="_blank" rel="noopener noreferrer" aria-label="Campus Coin on Facebook">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.5 21v-8.2h2.8l.4-3.2h-3.2V7.5c0-.9.3-1.6 1.6-1.6h1.7V3.1c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.3H7.3v3.2h2.8V21h3.4Z"/></svg>
    </a>
    @if($siteSettings->instagram_url)
        <a href="{{ $siteSettings->instagram_url }}" target="_blank" rel="noopener noreferrer" aria-label="Campus Coin on Instagram">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Zm0 2.2A2.8 2.8 0 0 0 4.2 7v10A2.8 2.8 0 0 0 7 19.8h10a2.8 2.8 0 0 0 2.8-2.8V7A2.8 2.8 0 0 0 17 4.2H7Zm5 2.3a5.5 5.5 0 1 1 0 11 5.5 5.5 0 0 1 0-11Zm0 2.2a3.3 3.3 0 1 0 0 6.6 3.3 3.3 0 0 0 0-6.6Zm5.8-3a1.3 1.3 0 1 1 0 2.6 1.3 1.3 0 0 1 0-2.6Z"/></svg>
        </a>
    @endif
    <a href="{{ $siteSettings->x_url ?: 'https://twitter.com/intent/tweet?url=' . urlencode(route('home')) . '&text=' . urlencode('Make every coin count with Campus Coin.') }}" target="_blank" rel="noopener noreferrer" aria-label="Campus Coin on X">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.9 3H22l-6.8 7.8L23.2 21h-6.3L12 14.8 6.6 21H3.5l7.3-8.4L3 3h6.5l4.5 5.9L18.9 3Zm-1.1 16h1.7L8.5 4.9H6.7L17.8 19Z"/></svg>
    </a>
    <a href="{{ $siteSettings->linkedin_url ?: 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode(route('home')) }}" target="_blank" rel="noopener noreferrer" aria-label="Campus Coin on LinkedIn">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5.2 8.5H2V22h3.2V8.5ZM3.6 2a1.9 1.9 0 1 0 0 3.8 1.9 1.9 0 0 0 0-3.8ZM22 14.3c0-4.1-2.2-6-5.2-6a4.5 4.5 0 0 0-4 2.2V8.5H9.6V22h3.2v-6.7c0-1.8.4-3.6 2.7-3.6s2.4 2.1 2.4 3.7V22H22v-7.7Z"/></svg>
    </a>
    @if($siteSettings->youtube_url)
        <a href="{{ $siteSettings->youtube_url }}" target="_blank" rel="noopener noreferrer" aria-label="Campus Coin on YouTube">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.6 3.6 12 3.6 12 3.6s-7.6 0-9.4.5A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.8.5 9.4.5 9.4.5s7.6 0 9.4-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8ZM9.6 15.6V8.4l6.3 3.6-6.3 3.6Z"/></svg>
        </a>
    @endif
    @if($siteSettings->whatsapp_url)
        <a href="{{ $siteSettings->whatsapp_url }}" target="_blank" rel="noopener noreferrer" aria-label="Campus Coin on WhatsApp">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 3.5A11.8 11.8 0 0 0 12.1 0C5.6 0 .3 5.3.3 11.8c0 2.1.6 4.1 1.6 5.9L.2 24l6.5-1.7a11.8 11.8 0 0 0 5.4 1.3h.1c6.5 0 11.8-5.3 11.8-11.8 0-3.2-1.2-6.1-3.5-8.3ZM12.1 21.6c-1.7 0-3.4-.5-4.8-1.4l-.4-.2-3.9 1 1-3.8-.3-.4a9.7 9.7 0 0 1-1.5-5.1c0-5.4 4.4-9.8 9.8-9.8 2.6 0 5.1 1 6.9 2.9a9.7 9.7 0 0 1 2.9 6.9c0 5.4-4.4 9.9-9.7 9.9Zm5.4-7.4c-.3-.1-1.7-.8-2-.9-.3-.1-.5-.1-.7.2-.2.3-.7.9-.9 1.1-.2.2-.3.2-.6.1a8.1 8.1 0 0 1-2.4-1.5 9 9 0 0 1-1.7-2.1c-.2-.3 0-.5.1-.6l.5-.6.3-.5c.1-.2 0-.4 0-.6l-.9-2.1c-.2-.5-.5-.4-.7-.4h-.6c-.2 0-.6.1-.9.4-.3.3-1.2 1.1-1.2 2.7 0 1.6 1.2 3.1 1.4 3.3.2.2 2.3 3.5 5.6 4.8.8.3 1.4.5 1.9.6.8.2 1.5.2 2 .1.6-.1 1.7-.7 1.9-1.3.2-.7.2-1.2.2-1.3-.1-.1-.3-.2-.6-.4Z"/></svg>
        </a>
    @endif
</nav>

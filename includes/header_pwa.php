<?php 
// includes/header_pwa.php
?>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link rel="manifest" href="../manifest.json">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="SMS Portal">
<link rel="icon" type="image/png" href="https://via.placeholder.com/192/0284c7/ffffff?text=SMS">

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('../service-worker.js')
            .then(reg => console.log('PWA Service Worker Registered!'))
            .catch(err => console.log('PWA Service Worker Failed:', err));
        });
    }
</script>
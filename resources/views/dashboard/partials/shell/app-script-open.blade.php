@verbatim
<script type="module">
        const { createApp, ref, computed, watch, onMounted, onBeforeUnmount, nextTick } = Vue;

        createApp({
            setup() {
                // Menu yang script-nya diisolasi (@push('menu-scripts')) mendaftarkan state/fungsinya ke sini; digabung otomatis ke return.
                const menuExports = {};
                // Pemuat data per tab milik menu terisolasi: menuLoaders.<tab> = () => {...}; dipanggil runActiveTabProtectedLoaders.
                const menuLoaders = {};
@endverbatim

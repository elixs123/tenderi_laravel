<!DOCTYPE html>
<html lang="bs" class="{{ auth()->check() ? auth()->user()->theme : 'light' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penny Plus | Tender Intelligence</title>
    
    <script>
        const userTheme = "{{ auth()->check() ? auth()->user()->theme : 'light' }}";
        if (userTheme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root { --bg-body: #f8fafc; --sidebar-bg: #ffffff; --text-main: #0f172a; }
        .dark { --bg-body: #0f172a; --sidebar-bg: rgba(30, 41, 59, 0.45); --text-main: #f8fafc; }

        body { background-color: var(--bg-body); color: var(--text-main); transition: background-color 0.3s ease; }
        .glass-card { background: var(--sidebar-bg); backdrop-filter: blur(12px); }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    </style>
    @livewireStyles
</head>
<body class="flex h-screen overflow-hidden antialiased transition-colors duration-300">

    <livewire:layout.sidebar />

    <main class="flex-1 h-full overflow-y-auto custom-scrollbar bg-slate-50 dark:bg-[#0f172a] transition-colors duration-300">
        {{ $slot }}
    </main>

    @livewireScripts
    <script>
        window.addEventListener('notify', (event) => {
            let detail = event.detail;
            let type = detail.type || (detail[0] && detail[0].type) || 'info';
            let message = detail.message || (detail[0] && detail[0].message) || 'Obavještenje';

            const Toast = Swal.mixin({
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a',
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({
                icon: type,
                title: message
            });
        });

        window.addEventListener('theme-updated', event => {
            const theme = event.detail.theme;
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            setTimeout(() => { lucide.createIcons(); }, 50);
        });

        document.addEventListener('livewire:navigated', () => { lucide.createIcons(); });
        document.addEventListener('DOMContentLoaded', () => { lucide.createIcons(); });
    </script>
</body>
</html>
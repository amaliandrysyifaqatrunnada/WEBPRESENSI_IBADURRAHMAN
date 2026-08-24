<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'PKBM IBADURRAHMAN - Admin Dashboard' }}</title>
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Material Symbols Outlined -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "tertiary-fixed-dim": "#83da85",
                        "error": "#ba1a1a",
                        "inverse-on-surface": "#e0f4ff",
                        "on-tertiary": "#ffffff",
                        "on-secondary-container": "#0c7521",
                        "surface-dim": "#c0dfee",
                        "on-tertiary-container": "#c7ffc3",
                        "surface": "#f4faff",
                        "outline-variant": "#bfcaba",
                        "surface-container-lowest": "#ffffff",
                        "on-background": "#001f2a",
                        "primary-container": "#2e7d32",
                        "secondary-fixed": "#98f994",
                        "outline": "#707a6c",
                        "on-surface": "#001f2a",
                        "primary-fixed": "#a3f69c",
                        "surface-container": "#d9f2ff",
                        "primary-fixed-dim": "#88d982",
                        "inverse-surface": "#163440",
                        "on-error-container": "#93000a",
                        "on-primary-fixed-variant": "#005312",
                        "on-tertiary-fixed-variant": "#005318",
                        "on-secondary-fixed": "#002204",
                        "tertiary-container": "#277d34",
                        "on-surface-variant": "#40493d",
                        "surface-container-high": "#ceedfd",
                        "surface-container-highest": "#c9e7f7",
                        "tertiary-fixed": "#9ff79f",
                        "secondary-fixed-dim": "#7ddc7a",
                        "on-tertiary-fixed": "#002105",
                        "secondary-container": "#98f994",
                        "surface-bright": "#f4faff",
                        "on-secondary": "#ffffff",
                        "background": "#f4faff",
                        "on-primary-container": "#cbffc2",
                        "surface-variant": "#c9e7f7",
                        "on-primary-fixed": "#002204",
                        "tertiary": "#00631e",
                        "error-container": "#ffdad6",
                        "on-secondary-fixed-variant": "#005313",
                        "primary": "#0d631b",
                        "surface-container-low": "#e6f6ff",
                        "surface-tint": "#1b6d24",
                        "on-primary": "#ffffff",
                        "on-error": "#ffffff",
                        "secondary": "#006e1c",
                        "inverse-primary": "#88d982"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px",
                        "12px": "12px"
                    },
                    "spacing": {
                        "card-gap": "24px",
                        "gutter": "20px",
                        "unit": "4px",
                        "container-padding": "24px",
                        "stack-md": "16px",
                        "stack-sm": "8px",
                        "stack-lg": "32px"
                    },
                    "fontFamily": {
                        "label-md": ["Plus Jakarta Sans"],
                        "label-sm": ["Plus Jakarta Sans"],
                        "headline-lg-mobile": ["Plus Jakarta Sans"],
                        "headline-sm": ["Plus Jakarta Sans"],
                        "body-md": ["Plus Jakarta Sans"],
                        "body-lg": ["Plus Jakarta Sans"],
                        "headline-lg": ["Plus Jakarta Sans"],
                        "body-sm": ["Plus Jakarta Sans"],
                        "headline-md": ["Plus Jakarta Sans"],
                        "display-lg": ["Plus Jakarta Sans"]
                    },
                    "fontSize": {
                        "label-md": ["14px", { "lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "600" }],
                        "label-sm": ["12px", { "lineHeight": "16px", "fontWeight": "600" }],
                        "headline-lg-mobile": ["28px", { "lineHeight": "36px", "fontWeight": "700" }],
                        "headline-sm": ["20px", { "lineHeight": "28px", "fontWeight": "600" }],
                        "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                        "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }],
                        "headline-lg": ["32px", { "lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "700" }],
                        "body-sm": ["14px", { "lineHeight": "20px", "fontWeight": "400" }],
                        "headline-md": ["24px", { "lineHeight": "32px", "fontWeight": "600" }],
                        "display-lg": ["48px", { "lineHeight": "60px", "letterSpacing": "-0.02em", "fontWeight": "700" }]
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #F7FAF7; }
        .card-layer-1 { background-color: #FFFFFF; border: 1px solid #E6ECE7; box-shadow: 0px 2px 8px rgba(38, 50, 56, 0.02); }
        .card-layer-2 { background-color: #FFFFFF; border: 1px solid #E6ECE7; box-shadow: 0px 4px 20px rgba(38, 50, 56, 0.06); }
        .btn-primary { background-color: #2E7D32; color: #FFFFFF; border-radius: 12px; }
        .btn-secondary { background-color: #E8F5E9; color: #2E7D32; border-radius: 12px; }
        .badge-success { background-color: rgba(46, 125, 50, 0.1); color: #2E7D32; }
        .badge-warning { background-color: rgba(255, 152, 0, 0.1); color: #F57C00; }
        .badge-danger { background-color: rgba(211, 47, 47, 0.1); color: #D32F2F; }
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1; 
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #bfcaba; 
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #707a6c; 
        }
    </style>
    <!-- Include SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Include ChartJS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="font-body-md text-body-md text-on-surface antialiased overflow-x-hidden">
    <!-- Mobile Drawer Overlay -->
    <div class="fixed inset-0 bg-on-surface/50 z-40 hidden md:hidden transition-opacity" id="mobile-drawer-overlay"></div>
    
    <!-- Sidebar Component -->
    <x-admin.sidebar />

    <!-- Main Content Wrapper -->
    <div class="w-full md:ml-[280px] md:w-[calc(100%-280px)] min-h-screen flex flex-col">
        <!-- Navbar Component -->
        <x-admin.navbar />

        <!-- Main Canvas -->
        <main class="flex-1 p-6 md:p-10 max-w-[1440px] mx-auto w-full">
            {{ $slot }}
        </main>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="fixed inset-0 bg-on-surface/50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white dark:bg-inverse-surface rounded-xl border border-outline-variant shadow-2xl w-full max-w-md p-6 relative overflow-hidden font-sans">
            <h3 class="font-headline-sm text-headline-sm text-on-surface mb-4 flex items-center gap-2 font-bold">
                <span class="material-symbols-outlined text-primary">manage_accounts</span>
                Edit Profil Admin
            </h3>
            <form id="editProfileForm" onsubmit="submitEditProfile(event)" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="flex flex-col items-center gap-3 pb-3 border-b border-outline-variant/30">
                    <div class="w-20 h-20 rounded-full overflow-hidden border border-outline-variant relative group">
                        <img id="modal-admin-avatar-preview" class="w-full h-full object-cover" src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : 'https://lh3.googleusercontent.com/aida-public/AB6AXuBWJS7O2hXfi6V7TxnvAgxfKYyilH2NMDYcVdIJnC19Td4Yu6tRSl_pMTa1YylQtoV9H7NCFPwznw8LUWrJx6m2JOZF1Dyqbn0sLVNVHonXGx-hvlx8brTl-tjOejtZx11M7P_Qfowt5_SBoox_bEl2POS3ZWjF0-vYsYObMrKHTKA7cch29rXsck5OyKF8T5pZYcf08jUQJY0mClKzXA_UjNFoHyuMfHlaMNZwMer_a7BaXj2Yh5EIyQ' }}"/>
                        <label for="admin-avatar-input" class="absolute inset-0 bg-black/40 text-white text-[10px] flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer font-medium uppercase tracking-wider">
                            Upload
                        </label>
                        <input type="file" id="admin-avatar-input" name="avatar" class="hidden" accept="image/*" onchange="previewAdminAvatar(event)">
                    </div>
                    <span class="text-xs text-on-surface-variant">Klik lingkaran foto untuk mengganti foto profil.</span>
                </div>
                <div class="flex flex-col">
                    <label class="font-label-md text-on-surface mb-2" for="admin-profile-name">Nama Lengkap</label>
                    <input id="admin-profile-name" name="name" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" type="text" value="{{ auth()->user()->name }}" required/>
                </div>
                <div class="flex flex-col">
                    <label class="font-label-md text-on-surface mb-2" for="admin-profile-email">Email</label>
                    <input id="admin-profile-email" name="email" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" type="email" value="{{ auth()->user()->email }}" required/>
                </div>
                <div class="pt-4 border-t border-outline-variant/30 flex justify-end gap-3">
                    <button type="button" onclick="closeEditProfileModal()" class="px-4 py-2 border border-outline-variant rounded-xl font-label-md hover:bg-surface-container-low transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2.5 bg-primary text-white rounded-xl font-label-md hover:bg-primary/95 transition-all shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="changePasswordModal" class="fixed inset-0 bg-on-surface/50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white dark:bg-inverse-surface rounded-xl border border-outline-variant shadow-2xl w-full max-w-md p-6 relative overflow-hidden font-sans">
            <h3 class="font-headline-sm text-headline-sm text-on-surface mb-4 flex items-center gap-2 font-bold">
                <span class="material-symbols-outlined text-primary">lock_reset</span>
                Ubah Kata Sandi
            </h3>
            <form id="changePasswordForm" onsubmit="submitChangePassword(event)" class="space-y-4">
                @csrf
                <div class="flex flex-col">
                    <label class="font-label-md text-on-surface mb-2" for="current-password">Kata Sandi Saat Ini</label>
                    <input id="current-password" name="current_password" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" type="password" required/>
                </div>
                <div class="flex flex-col">
                    <label class="font-label-md text-on-surface mb-2" for="new-password">Kata Sandi Baru</label>
                    <input id="new-password" name="password" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" type="password" required/>
                </div>
                <div class="flex flex-col">
                    <label class="font-label-md text-on-surface mb-2" for="new-password-confirmation">Konfirmasi Kata Sandi Baru</label>
                    <input id="new-password-confirmation" name="password_confirmation" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" type="password" required/>
                </div>
                <div class="pt-4 border-t border-outline-variant/30 flex justify-end gap-3">
                    <button type="button" onclick="closeChangePasswordModal()" class="px-4 py-2 border border-outline-variant rounded-xl font-label-md hover:bg-surface-container-low transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2.5 bg-primary text-white rounded-xl font-label-md hover:bg-primary/95 transition-all shadow-sm">Ubah</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Mobile Menu Script -->
    <script>
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');
        const mobileOverlay = document.getElementById('mobile-drawer-overlay');

        if (mobileMenuBtn && sidebar && mobileOverlay) {
            function toggleMenu() {
                const isClosed = sidebar.classList.contains('-translate-x-full');
                if (isClosed) {
                    sidebar.classList.remove('-translate-x-full');
                    mobileOverlay.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                } else {
                    sidebar.classList.add('-translate-x-full');
                    mobileOverlay.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            }

            mobileMenuBtn.addEventListener('click', toggleMenu);
            mobileOverlay.addEventListener('click', toggleMenu);
        }

        // Modal triggers and handlers
        function openEditProfileModal() {
            document.getElementById('editProfileModal').classList.remove('hidden');
        }
        function closeEditProfileModal() {
            document.getElementById('editProfileModal').classList.add('hidden');
        }
        function openChangePasswordModal() {
            document.getElementById('changePasswordModal').classList.remove('hidden');
        }
        function closeChangePasswordModal() {
            document.getElementById('changePasswordModal').classList.add('hidden');
            document.getElementById('changePasswordForm').reset();
        }
        function previewAdminAvatar(event) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('modal-admin-avatar-preview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        function submitEditProfile(e) {
            e.preventDefault();
            const form = document.getElementById('editProfileForm');
            const formData = new FormData(form);

            fetch("{{ route('admin.profile.update') }}", {
                method: "POST",
                body: formData,
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
            .then(async response => {
                const data = await response.json();
                if (response.ok && data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: data.message,
                        confirmButtonColor: '#2E7D32'
                    }).then(() => {
                        if (data.avatar) {
                            document.getElementById('navbar-admin-avatar').src = data.avatar;
                            document.getElementById('modal-admin-avatar-preview').src = data.avatar;
                        }
                        document.getElementById('dropdown-admin-name').textContent = data.name;
                        document.getElementById('dropdown-admin-email').textContent = data.email;
                        document.getElementById('admin-profile-name').value = data.name;
                        document.getElementById('admin-profile-email').value = data.email;
                        closeEditProfileModal();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Gagal mengubah profil.',
                        confirmButtonColor: '#2E7D32'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terjadi kesalahan sistem atau format berkas tidak valid.',
                    confirmButtonColor: '#2E7D32'
                });
            });
        }

        function submitChangePassword(e) {
            e.preventDefault();
            const form = document.getElementById('changePasswordForm');
            const formData = new FormData(form);

            fetch("{{ route('admin.profile.password') }}", {
                method: "POST",
                body: formData,
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
            .then(async response => {
                const data = await response.json();
                if (response.ok && data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: data.message,
                        confirmButtonColor: '#2E7D32'
                    }).then(() => {
                        closeChangePasswordModal();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ubah Sandi Gagal',
                        text: data.message || 'Kata sandi saat ini tidak cocok atau konfirmasi baru salah.',
                        confirmButtonColor: '#2E7D32'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Konfirmasi kata sandi tidak cocok atau data tidak valid.',
                    confirmButtonColor: '#2E7D32'
                });
            });
        }
    </script>
</body>
</html>

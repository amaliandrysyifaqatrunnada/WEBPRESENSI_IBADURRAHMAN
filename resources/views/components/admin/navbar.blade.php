<header class="flex justify-between items-center w-full h-16 px-8 border-b border-outline-variant dark:border-outline bg-surface/80 dark:bg-inverse-surface/80 backdrop-blur-md sticky top-0 z-40">
    <!-- Mobile Brand (Visible only on mobile) -->
    <div class="md:hidden font-headline-sm text-headline-sm font-bold text-primary dark:text-inverse-primary flex items-center gap-2">
        <button class="p-2 text-on-surface-variant hover:bg-surface-container rounded-full transition-colors mr-2" id="mobile-menu-btn">
            <span class="material-symbols-outlined">menu</span>
        </button>
        <span class="truncate">PKBM IBADURRAHMAN</span>
    </div>
    
    <!-- Desktop Product Name -->
    <div class="hidden md:flex items-center gap-2 font-headline-sm text-headline-sm font-bold text-primary dark:text-inverse-primary">
        <img alt="Logo" class="w-8 h-8 object-contain" src="{{ asset('images/logo-pkbm-transparent.png') }}"/>
        <span>PKBM IBADURRAHMAN</span>
    </div>

    <!-- Trailing Actions -->
    <div class="flex items-center gap-6 ml-auto">
        <!-- Search bar (Desktop) -->
        <div class="hidden lg:flex items-center bg-surface-container-low rounded-full px-4 py-1.5 border border-outline-variant/30 focus-within:border-primary/50 transition-colors">
            <span class="material-symbols-outlined text-on-surface-variant text-sm mr-2">search</span>
            <input class="bg-transparent border-none focus:ring-0 text-body-sm font-body-sm w-48 text-on-surface placeholder:text-on-surface-variant/70 p-0 outline-none" placeholder="Cari..." type="text"/>
        </div>
        <div class="flex items-center gap-4">
            <div class="relative ml-2" id="admin-profile-dropdown-wrapper">
                <button class="flex items-center gap-3 px-3 py-1.5 rounded-full hover:bg-surface-container transition-all cursor-pointer focus:outline-none" id="admin-profile-btn" onclick="toggleAdminDropdown(event)">
                    <div class="w-8 h-8 rounded-full overflow-hidden border border-outline-variant">
                        <img class="w-full h-full object-cover" id="navbar-admin-avatar" alt="Admin Avatar" src="{{ auth()->user()->avatar_url }}"/>
                    </div>
                    <div class="hidden sm:flex flex-col text-left select-none max-w-[150px]">
                        <span class="text-xs font-semibold text-on-surface truncate leading-tight" id="dropdown-admin-name">{{ auth()->user()->name }}</span>
                        <span class="text-[10px] text-on-surface-variant truncate mt-0.5 leading-none">{{ auth()->user()->unit ? auth()->user()->unit->name : '-' }}</span>
                    </div>
                    <span class="material-symbols-outlined text-on-surface-variant text-[18px]">expand_more</span>
                </button>
                <!-- Dropdown menu -->
                <div class="absolute right-0 mt-2 w-64 bg-white dark:bg-inverse-surface rounded-xl border border-outline-variant shadow-lg py-2 hidden z-50 font-sans" id="admin-profile-dropdown">
                    <div class="px-4 py-3 border-b border-outline-variant/30 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full overflow-hidden border border-outline-variant shrink-0">
                            <img class="w-full h-full object-cover" id="dropdown-admin-avatar" alt="Admin Avatar" src="{{ auth()->user()->avatar_url }}"/>
                        </div>
                        <div class="overflow-hidden">
                            <div class="font-label-md text-on-surface truncate font-semibold" id="dropdown-admin-header-name">{{ auth()->user()->name }}</div>
                            <div class="text-[11px] text-on-surface-variant truncate" id="dropdown-admin-email">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                    <div class="py-1">
                        <a href="{{ route('admin.profile.edit') }}" class="w-full text-left px-4 py-2.5 text-sm text-on-surface-variant hover:bg-surface-container-low transition-colors flex items-center gap-2.5">
                            <span class="material-symbols-outlined text-[18px]">person</span>
                            <span>Profil Saya</span>
                        </a>
                        <a href="{{ route('admin.profile.edit') }}" class="w-full text-left px-4 py-2.5 text-sm text-on-surface-variant hover:bg-surface-container-low transition-colors flex items-center gap-2.5">
                            <span class="material-symbols-outlined text-[18px]">edit</span>
                            <span>Ubah Profil</span>
                        </a>
                        <a href="{{ route('admin.profile.password.edit') }}" class="w-full text-left px-4 py-2.5 text-sm text-on-surface-variant hover:bg-surface-container-low transition-colors flex items-center gap-2.5">
                            <span class="material-symbols-outlined text-[18px]">lock</span>
                            <span>Ubah Password</span>
                        </a>
                    </div>
                    <div class="border-t border-outline-variant/30 my-1"></div>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-error hover:bg-error-container/20 transition-colors flex items-center gap-2.5 font-semibold">
                            <span class="material-symbols-outlined text-[18px]">logout</span>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
            
            <script>
                function toggleAdminDropdown(event) {
                    event.stopPropagation();
                    const dropdown = document.getElementById('admin-profile-dropdown');
                    if (dropdown) {
                        dropdown.classList.toggle('hidden');
                    }
                }
                
                document.addEventListener('click', function(event) {
                    const wrapper = document.getElementById('admin-profile-dropdown-wrapper');
                    const dropdown = document.getElementById('admin-profile-dropdown');
                    if (dropdown && wrapper && !wrapper.contains(event.target)) {
                        dropdown.classList.add('hidden');
                    }
                });
            </script>
        </div>
    </div>
</header>

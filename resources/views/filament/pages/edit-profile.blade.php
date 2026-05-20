<x-filament-panels::page>
    <div style="display: flex; flex-direction: column; gap: 24px; padding-bottom: 60px;">
        {{-- LinkedIn Style Profile Header --}}
        <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e5e7eb;">
            {{-- Cover Photo --}}
            <div style="height: 160px; background: linear-gradient(to right, #1e3a8a, #3b82f6); position: relative;">
                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.1; background-image: url('data:image/svg+xml,%3Csvg width=\'20\' height=\'20\' viewBox=\'0 0 20 20\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\' fill-rule=\'evenodd\'%3E%3Ccircle cx=\'3\' cy=\'3\' r=\'3\'/%3E%3Ccircle cx=\'13\' cy=\'13\' r=\'3\'/%3E%3C/g%3E%3C/svg%3E');"></div>
            </div>
            
            {{-- Profile Info Area --}}
            <div style="padding: 0 24px 24px 24px; position: relative;">
                {{-- Avatar --}}
                <div style="margin-top: -80px; margin-bottom: 16px;">
                    <div style="width: 152px; height: 152px; border-radius: 50%; border: 4px solid white; overflow: hidden; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: block; position: relative; z-index: 10;">
                        @if(auth()->user()->photo)
                            <img src="/profile-picture/{{ auth()->user()->photo }}" alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        @else
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #f3f4f6; color: #9ca3af; border-radius: 50%;">
                                <svg style="width: 80px; height: 80px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                        @endif
                    </div>
                </div>
                
                {{-- Text Info --}}
                <div>
                    <h1 style="font-size: 24px; font-weight: 600; color: #111827; margin: 0; line-height: 1.2; font-family: inherit;">{{ auth()->user()->name }}</h1>
                    
                    <div style="display: flex; flex-wrap: wrap; gap: 16px; font-size: 14px; color: #6b7280; margin-top: 12px; align-items: center;">
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            {{ auth()->user()->email }}
                        </span>
                        @if(auth()->user()->whatsapp)
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            {{ auth()->user()->whatsapp }}
                        </span>
                        @endif
                        @if(auth()->user()->goldar)
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                            Golongan Darah: {{ auth()->user()->goldar }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr; gap: 24px;">
            {{-- Profile Information Section --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
                    <div class="p-2 rounded-lg shadow-sm" style="background-color: #d97706;">
                        <x-heroicon-o-user-circle class="w-5 h-5 text-white" />
                    </div>
                    <h3 class="text-lg font-bold tracking-tight">Informasi Akun</h3>
                </div>
                <form wire:submit="updateProfile" class="p-6 pb-10">
                    {{ $this->profileForm }}
                    <br>
                    <div class="mt-8">
                        <button type="submit" wire:target="updateProfile" wire:loading.attr="disabled" class="w-full flex items-center justify-center gap-2 text-white font-semibold rounded-lg px-4 py-2.5 transition-all duration-300 group shadow-lg" style="background-color: #d97706; box-shadow: 0 10px 15px -3px rgba(217, 119, 6, 0.3);">
                            <span wire:loading.remove wire:target="updateProfile" class="flex items-center gap-2 font-semibold">
                                <x-heroicon-o-check class="w-5 h-5 group-hover:scale-110 transition-transform" />
                                Simpan Perubahan
                            </span>
                            <span wire:loading wire:target="updateProfile" class="flex items-center justify-center">
                                <svg class="animate-spin h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Password Section --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
                    <div class="p-2 bg-amber-500 rounded-lg shadow-sm">
                        <x-heroicon-o-key class="w-5 h-5 text-white" />
                    </div>
                    <h3 class="text-lg font-bold tracking-tight text-amber-600">Keamanan Password</h3>
                </div>
                
                <form wire:submit="updatePassword" class="p-6 pb-10">
                    {{ $this->passwordForm }}

                    <br>
                    <div class="mt-8">
                        <button type="submit" wire:target="updatePassword" wire:loading.attr="disabled" class="w-full flex items-center justify-center gap-2 text-white font-semibold rounded-lg px-4 py-2.5 transition-all duration-300 group shadow-lg" style="background-color: #d97706; box-shadow: 0 10px 15px -3px rgba(217, 119, 6, 0.3);">
                            <span wire:loading.remove wire:target="updatePassword" class="flex items-center gap-2 font-semibold">
                                <x-heroicon-o-lock-closed class="w-5 h-5 group-hover:scale-110 transition-transform" />
                                Ubah Kata Sandi
                            </span>
                            <span wire:loading wire:target="updatePassword" class="flex items-center justify-center">
                                <svg class="animate-spin h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <style>
        .password-strength-bar {
            height: 6px;
            border-radius: 3px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 8px;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .profile-avatar-shadow {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }

        .fi-btn-label {
            font-weight: 600;
        }

        /* Smooth section transitions */
        .fi-section {
            transition: transform 0.2s ease-in-out;
        }
    </style>
    @endpush
</x-filament-panels::page>

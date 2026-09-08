 <!-- Channel Selection -->
 <div class="form-group">
     <label><strong>Pilih Channel Verifikasi:</strong></label>     
     <div class="custom-control custom-radio">
         <input class="custom-control-input" type="radio" id="emailChannel" name="channel" value="email" checked>
         <label class="custom-control-label" for="emailChannel">
             <i class="fas fa-envelope mr-2"></i>Email
             <small class="d-block text-muted">Kode verifikasi akan dikirim ke alamat email</small>
         </label>
     </div>
     @if(Auth::user()->telegram_chat_id)
     <div class="custom-control custom-radio">
         <input class="custom-control-input" type="radio" id="telegramChannel" name="channel" value="telegram">
         <label class="custom-control-label" for="telegramChannel">
             <i class="fab fa-telegram mr-2"></i>Telegram
             <small class="d-block text-muted">Kode verifikasi akan dikirim ke bot Telegram</small>
         </label>
     </div>
     @else 
        <p class="pl-3">Id Telegram belum tersedia
        <a href="{{ Auth::user()->adminlte_profile_url() }}" class="btn btn-sm btn-primary">
            Tambahkan id telegram
        </a>
        </p>
     @endif
 </div>

 <!-- Email Section -->
 <div class="form-group" id="emailSection">
     <label for="emailIdentifier"><strong>Alamat Email:</strong></label>
     <input disabled type="email" class="form-control" id="emailIdentifier" name="identifier" placeholder="nama@example.com" value="{{ Auth::user()->email ?? '' }}" required>
     <small class="form-text text-muted">Pastikan email dapat diakses untuk menerima kode verifikasi</small>
 </div>

 <!-- Telegram Section (Hidden by default) -->
 <div class="form-group d-none" id="telegramSection">
     <label for="telegramIdentifier"><strong>Telegram Chat ID:</strong></label>
     <input disabled type="text" class="form-control" id="telegramIdentifier" name="identifier" placeholder="123456789" value="{{ Auth::user()->telegram_chat_id ?? '' }}">
     <small class="form-text text-muted">
         Dapatkan Chat ID dengan mengirim pesan ke <a href="https://t.me/userinfobot" target="_blank">@userinfobot</a>
     </small>
     <small class="form-text text-muted">
         <i class="fas fa-info-circle mr-1"></i>
         Hubungi bot {{ env('TELEGRAM_BOT_NAME', 'OpenSID_bot') }} dan ketik /start agar bot telegram dapat mengirim kode OTP ke Anda
     </small>
 </div>